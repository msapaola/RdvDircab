<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filtres
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('email_verified_at', '!=', null);
            } elseif ($request->status === 'inactive') {
                $query->where('email_verified_at', null);
            }
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $users = $query->paginate(15)->withQueryString();

        // Statistiques
        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'assistants' => User::where('role', 'assistant')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['role', 'search', 'status', 'sort_by', 'sort_order']),
            'auth' => [
                'user' => auth()->user() ? [
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'role' => auth()->user()->role,
                ] : null,
            ],
        ]);
    }

    public function show(User $user)
    {
        
        // Récupérer l'historique des activités
        $activities = Activity::where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Statistiques de l'utilisateur
        $userStats = [
            'appointments_processed' => \App\Models\Appointment::where('processed_by', $user->id)->count(),
            'last_activity' => $activities->first()?->created_at,
            'created_at' => $user->created_at,
            'email_verified_at' => $user->email_verified_at,
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'activities' => $activities,
            'stats' => $userStats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,assistant',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Marquer l'email comme vérifié pour les comptes admin
        if ($request->role === 'admin') {
            $user->markEmailAsVerified();
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Utilisateur créé');

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,assistant',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Marquer l'email comme vérifié pour les admins
        if ($request->role === 'admin' && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Utilisateur modifié');

        return redirect()->back()
            ->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher la suppression du dernier admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            
            if ($adminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer le dernier administrateur.');
            }
        }

        $userName = $user->name;
        $user->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Utilisateur {$userName} supprimé");

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            $user->email_verified_at = null;
            $message = 'Compte désactivé';
        } else {
            $user->email_verified_at = now();
            $message = 'Compte activé';
        }

        $user->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log($message);

        return redirect()->back()
            ->with('success', $message . ' avec succès.');
    }
}
