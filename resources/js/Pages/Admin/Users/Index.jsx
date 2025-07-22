import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';
import StatusBadge from '@/Components/UI/StatusBadge';
import DashboardMenu from '@/Components/DashboardMenu';

export default function Index(props) {
    const { users, stats, filters } = props;
    const safeUsers = users || { data: [], total: 0, links: [], from: 0, to: 0 };
    const safeStats = stats || { total: 0, admins: 0, assistants: 0, verified: 0, unverified: 0 };
    const safeFilters = (filters && typeof filters === 'object' && !Array.isArray(filters)) ? filters : {};

    // États
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);

    // Formulaires
    const filterForm = useForm({
        role: safeFilters.role || '',
        search: safeFilters.search || '',
        status: safeFilters.status || '',
        sort_by: safeFilters.sort_by || 'created_at',
        sort_order: safeFilters.sort_order || 'desc',
    });

    const createForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'assistant',
    });

    const editForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
    });

    // Fonctions
    const handleFilter = () => {
        filterForm.get(route('admin.users.index'));
    };
    const handleReset = () => {
        filterForm.reset();
        router.get(route('admin.users.index'));
    };
    const handleCreate = () => {
        createForm.post(route('admin.users.store'), {
            onSuccess: () => {
                setShowCreateModal(false);
                createForm.reset();
            },
        });
    };
    const handleEdit = () => {
        editForm.put(route('admin.users.update', selectedUser.id), {
            onSuccess: () => {
                setShowEditModal(false);
                setSelectedUser(null);
                editForm.reset();
            },
        });
    };
    const handleDelete = () => {
        router.delete(route('admin.users.destroy', selectedUser.id), {
            onSuccess: () => {
                setShowDeleteModal(false);
                setSelectedUser(null);
            },
        });
    };
    const handleToggleStatus = (user) => {
        if (confirm(`Êtes-vous sûr de vouloir ${user.email_verified_at ? 'désactiver' : 'activer'} ce compte ?`)) {
            router.post(route('admin.users.toggle-status', user.id));
        }
    };
    const openEditModal = (user) => {
        setSelectedUser(user);
        editForm.setData({
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: '',
            role: user.role || 'assistant',
        });
        setShowEditModal(true);
    };
    const openDeleteModal = (user) => {
        setSelectedUser(user);
        setShowDeleteModal(true);
    };

    // Helpers
    const getRoleColor = (role) => (role === 'admin' ? 'red' : 'blue');
    const getStatusColor = (user) => (user.email_verified_at ? 'green' : 'gray');

    return (
        <>
            <Head title="Gestion des utilisateurs - Administration" />
            <div className="min-h-screen bg-gray-50">
                <DashboardMenu />
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-8">Gestion des utilisateurs</h1>

                    {/* Statistiques */}
                    <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-gray-900">{safeStats.total}</div>
                            <div className="text-sm text-gray-600">Total</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-red-500">{safeStats.admins}</div>
                            <div className="text-sm text-gray-600">Administrateurs</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-blue-500">{safeStats.assistants}</div>
                            <div className="text-sm text-gray-600">Assistants</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-green-500">{safeStats.verified}</div>
                            <div className="text-sm text-gray-600">Actifs</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-gray-400">{safeStats.unverified}</div>
                            <div className="text-sm text-gray-600">Inactifs</div>
                        </div>
                    </div>

                    {/* Filtres */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Filtres</h2>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                                <select
                                    value={filterForm.data.role}
                                    onChange={(e) => filterForm.setData('role', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Tous les rôles</option>
                                    <option value="admin">Administrateur</option>
                                    <option value="assistant">Assistant</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                <select
                                    value={filterForm.data.status}
                                    onChange={(e) => filterForm.setData('status', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Tous les statuts</option>
                                    <option value="active">Actif</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                                <input
                                    type="text"
                                    value={filterForm.data.search}
                                    onChange={(e) => filterForm.setData('search', e.target.value)}
                                    placeholder="Nom ou email..."
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Tri</label>
                                <select
                                    value={`${filterForm.data.sort_by}-${filterForm.data.sort_order}`}
                                    onChange={(e) => {
                                        const [sortBy, sortOrder] = e.target.value.split('-');
                                        filterForm.setData('sort_by', sortBy);
                                        filterForm.setData('sort_order', sortOrder);
                                    }}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="created_at-desc">Plus récents</option>
                                    <option value="created_at-asc">Plus anciens</option>
                                    <option value="name-asc">Nom (A-Z)</option>
                                    <option value="name-desc">Nom (Z-A)</option>
                                    <option value="email-asc">Email (A-Z)</option>
                                    <option value="email-desc">Email (Z-A)</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex space-x-3">
                            <Link
                                href={route('admin.dashboard')}
                                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                ← Retour au tableau de bord
                            </Link>
                            <PrimaryButton onClick={handleFilter}>Appliquer les filtres</PrimaryButton>
                            <SecondaryButton onClick={handleReset}>Réinitialiser</SecondaryButton>
                            <PrimaryButton onClick={() => setShowCreateModal(true)}>
                                Nouvel utilisateur
                            </PrimaryButton>
                        </div>
                    </div>

                    {/* Liste des utilisateurs */}
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Utilisateurs ({safeUsers.total})
                            </h2>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de création</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {safeUsers.data && safeUsers.data.map((user) => (
                                        <tr key={user.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900">{user.name}</div>
                                                    <div className="text-sm text-gray-500">{user.email}</div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <StatusBadge status={user.role === 'admin' ? 'Administrateur' : 'Assistant'} color={getRoleColor(user.role)} />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <StatusBadge status={user.email_verified_at ? 'Actif' : 'Inactif'} color={getStatusColor(user)} />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(user.created_at).toLocaleDateString('fr-FR')}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex space-x-2">
                                                    <button
                                                        onClick={() => openEditModal(user)}
                                                        className="text-green-600 hover:text-green-900"
                                                    >
                                                        Modifier
                                                    </button>
                                                    <button
                                                        onClick={() => handleToggleStatus(user)}
                                                        className={user.email_verified_at ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900'}
                                                    >
                                                        {user.email_verified_at ? 'Désactiver' : 'Activer'}
                                                    </button>
                                                    <button
                                                        onClick={() => openDeleteModal(user)}
                                                        className="text-red-600 hover:text-red-900"
                                                    >
                                                        Supprimer
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {/* Pagination */}
                        {safeUsers.links && safeUsers.links.length > 0 && (
                            <div className="px-6 py-3 border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Affichage de {safeUsers.from} à {safeUsers.to} sur {safeUsers.total} résultats
                                    </div>
                                    <div className="flex space-x-2">
                                        {safeUsers.links.map((link, index) => (
                                            link.url ? (
                                                <Link
                                                    key={index}
                                                    href={link.url}
                                                    className={`px-3 py-2 text-sm rounded-md ${
                                                        link.active
                                                            ? 'bg-blue-500 text-white'
                                                            : 'bg-white text-gray-700 hover:bg-gray-50'
                                                    }`}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            ) : (
                                                <span
                                                    key={index}
                                                    className="px-3 py-2 text-sm rounded-md opacity-50 cursor-not-allowed bg-white text-gray-400"
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            )
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Modal de création */}
            <Modal show={showCreateModal} onClose={() => setShowCreateModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Nouvel utilisateur</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <input
                                type="text"
                                value={createForm.data.name}
                                onChange={(e) => createForm.setData('name', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {createForm.errors.name && (
                                <p className="text-red-500 text-sm mt-1">{createForm.errors.name}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                value={createForm.data.email}
                                onChange={(e) => createForm.setData('email', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {createForm.errors.email && (
                                <p className="text-red-500 text-sm mt-1">{createForm.errors.email}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                            <input
                                type="password"
                                value={createForm.data.password}
                                onChange={(e) => createForm.setData('password', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {createForm.errors.password && (
                                <p className="text-red-500 text-sm mt-1">{createForm.errors.password}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                            <input
                                type="password"
                                value={createForm.data.password_confirmation}
                                onChange={(e) => createForm.setData('password_confirmation', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                            <select
                                value={createForm.data.role}
                                onChange={(e) => createForm.setData('role', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="assistant">Assistant</option>
                                <option value="admin">Administrateur</option>
                            </select>
                            {createForm.errors.role && (
                                <p className="text-red-500 text-sm mt-1">{createForm.errors.role}</p>
                            )}
                        </div>
                    </div>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowCreateModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleCreate} disabled={createForm.processing}>
                            {createForm.processing ? 'Création...' : 'Créer'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal de modification */}
            <Modal show={showEditModal} onClose={() => setShowEditModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Modifier l'utilisateur</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <input
                                type="text"
                                value={editForm.data.name}
                                onChange={(e) => editForm.setData('name', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {editForm.errors.name && (
                                <p className="text-red-500 text-sm mt-1">{editForm.errors.name}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                value={editForm.data.email}
                                onChange={(e) => editForm.setData('email', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {editForm.errors.email && (
                                <p className="text-red-500 text-sm mt-1">{editForm.errors.email}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe (optionnel)</label>
                            <input
                                type="password"
                                value={editForm.data.password}
                                onChange={(e) => editForm.setData('password', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            {editForm.errors.password && (
                                <p className="text-red-500 text-sm mt-1">{editForm.errors.password}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de passe</label>
                            <input
                                type="password"
                                value={editForm.data.password_confirmation}
                                onChange={(e) => editForm.setData('password_confirmation', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                            <select
                                value={editForm.data.role}
                                onChange={(e) => editForm.setData('role', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="assistant">Assistant</option>
                                <option value="admin">Administrateur</option>
                            </select>
                            {editForm.errors.role && (
                                <p className="text-red-500 text-sm mt-1">{editForm.errors.role}</p>
                            )}
                        </div>
                    </div>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowEditModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleEdit} disabled={editForm.processing}>
                            {editForm.processing ? 'Modification...' : 'Enregistrer'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal de suppression */}
            <Modal show={showDeleteModal} onClose={() => setShowDeleteModal(false)} maxWidth="sm">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Supprimer l'utilisateur</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{selectedUser?.name}</strong> ?
                        Cette action est irréversible.
                    </p>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowDeleteModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleDelete} className="bg-red-600 hover:bg-red-700">
                            Supprimer
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </>
    );
} 