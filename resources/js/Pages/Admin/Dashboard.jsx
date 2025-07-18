import React from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function AdminDashboard({ auth, stats }) {
    return (
        <AdminLayout user={auth.user}>
            <Head title="Tableau de bord - Administration" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* En-tête */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                        <p className="text-gray-600 mt-2">
                            Bienvenue, {auth.user.name} ({auth.user.role === 'admin' ? 'Administrateur' : 'Assistant'})
                        </p>
                    </div>

                    {/* Test simple */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Test du dashboard</h2>
                        <p className="text-gray-600">
                            Si vous voyez ce message, le dashboard fonctionne !
                        </p>
                        <div className="mt-4">
                            <p><strong>Utilisateur:</strong> {auth.user.name}</p>
                            <p><strong>Email:</strong> {auth.user.email}</p>
                            <p><strong>Rôle:</strong> {auth.user.role}</p>
                        </div>
                    </div>

                    {/* KPIs simples */}
                    <div className="grid grid-cols-2 md:grid-cols-3 gap-6 mt-8">
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-orange-500">{stats?.pending || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">En attente</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-green-500">{stats?.accepted || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">Acceptés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-red-500">{stats?.rejected || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">Refusés</div>
                        </div>
                    </div>

                    {/* Message de succès */}
                    <div className="bg-green-50 border border-green-200 rounded-lg p-6 mt-8">
                        <h3 className="text-lg font-semibold text-green-800 mb-2">✅ Dashboard opérationnel</h3>
                        <p className="text-green-700">
                            Le système de gestion des rendez-vous est maintenant fonctionnel avec le menu de navigation admin.
                        </p>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 