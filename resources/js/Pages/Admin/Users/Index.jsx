import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function Users({ auth, users }) {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);

    const createForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'assistant',
        is_active: true,
    });

    const editForm = useForm({
        name: '',
        email: '',
        role: '',
        is_active: true,
    });

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

    const handleDelete = (user) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.name} ?`)) {
            router.delete(route('admin.users.destroy', user.id));
        }
    };

    const handleToggleStatus = (user) => {
        const action = user.is_active ? 'désactiver' : 'activer';
        if (confirm(`Êtes-vous sûr de vouloir ${action} l'utilisateur ${user.name} ?`)) {
            router.post(route('admin.users.toggle-status', user.id));
        }
    };

    const handleChangeRole = (user) => {
        const newRole = user.role === 'admin' ? 'assistant' : 'admin';
        const action = newRole === 'admin' ? 'promouvoir administrateur' : 'rétrograder assistant';
        if (confirm(`Êtes-vous sûr de vouloir ${action} pour ${user.name} ?`)) {
            router.post(route('admin.users.change-role', user.id), {
                role: newRole,
            });
        }
    };

    const openEditModal = (user) => {
        setSelectedUser(user);
        editForm.setData({
            name: user.name,
            email: user.email,
            role: user.role,
            is_active: user.is_active,
        });
        setShowEditModal(true);
    };

    return (
        <AdminLayout user={auth.user}>
            <Head title="Gestion des utilisateurs - Administration" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* En-tête */}
                    <div className="mb-8 flex justify-between items-center">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Gestion des utilisateurs</h1>
                            <p className="text-gray-600 mt-2">Gestion des comptes administrateurs et assistants</p>
                        </div>
                        <PrimaryButton onClick={() => setShowCreateModal(true)}>
                            Ajouter un utilisateur
                        </PrimaryButton>
                    </div>

                    {/* Liste des utilisateurs */}
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Utilisateurs ({users.length})
                            </h2>
                        </div>
                        
                        {users.length === 0 ? (
                            <div className="px-6 py-12 text-center">
                                <p className="text-gray-500">Aucun utilisateur trouvé.</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Utilisateur
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Rôle
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Statut
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Dernière activité
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {users.map((user) => (
                                            <tr key={user.id} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {user.name}
                                                        </div>
                                                        <div className="text-sm text-gray-500">
                                                            {user.email}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                        user.role === 'admin' 
                                                            ? 'bg-purple-100 text-purple-800' 
                                                            : 'bg-blue-100 text-blue-800'
                                                    }`}>
                                                        {user.role === 'admin' ? 'Administrateur' : 'Assistant'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                        user.is_active 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-red-100 text-red-800'
                                                    }`}>
                                                        {user.is_active ? 'Actif' : 'Inactif'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {user.last_login_at 
                                                        ? new Date(user.last_login_at).toLocaleDateString('fr-FR')
                                                        : 'Jamais connecté'
                                                    }
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div className="flex space-x-2">
                                                        <Link
                                                            href={route('admin.users.show', user.id)}
                                                            className="text-blue-600 hover:text-blue-900"
                                                        >
                                                            Voir
                                                        </Link>
                                                        <button
                                                            onClick={() => openEditModal(user)}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Modifier
                                                        </button>
                                                        <button
                                                            onClick={() => handleToggleStatus(user)}
                                                            className={`${
                                                                user.is_active 
                                                                    ? 'text-red-600 hover:text-red-900' 
                                                                    : 'text-green-600 hover:text-green-900'
                                                            }`}
                                                        >
                                                            {user.is_active ? 'Désactiver' : 'Activer'}
                                                        </button>
                                                        {user.id !== auth.user.id && (
                                                            <button
                                                                onClick={() => handleChangeRole(user)}
                                                                className="text-orange-600 hover:text-orange-900"
                                                            >
                                                                {user.role === 'admin' ? 'Rétrograder' : 'Promouvoir'}
                                                            </button>
                                                        )}
                                                        {user.id !== auth.user.id && (
                                                            <button
                                                                onClick={() => handleDelete(user)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                Supprimer
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Modal de création */}
            <Modal show={showCreateModal} onClose={() => setShowCreateModal(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Ajouter un utilisateur</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <input
                                type="text"
                                value={createForm.data.name}
                                onChange={(e) => createForm.setData('name', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                value={createForm.data.email}
                                onChange={(e) => createForm.setData('email', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                            <input
                                type="password"
                                value={createForm.data.password}
                                onChange={(e) => createForm.setData('password', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                            <input
                                type="password"
                                value={createForm.data.password_confirmation}
                                onChange={(e) => createForm.setData('password_confirmation', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                        </div>
                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                id="is_active"
                                checked={createForm.data.is_active}
                                onChange={(e) => createForm.setData('is_active', e.target.checked)}
                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label htmlFor="is_active" className="ml-2 text-sm text-gray-700">
                                Compte actif
                            </label>
                        </div>
                    </div>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowCreateModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleCreate}>
                            Créer
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal de modification */}
            <Modal show={showEditModal} onClose={() => setShowEditModal(false)}>
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
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                value={editForm.data.email}
                                onChange={(e) => editForm.setData('email', e.target.value)}
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
                        </div>
                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                id="edit_is_active"
                                checked={editForm.data.is_active}
                                onChange={(e) => editForm.setData('is_active', e.target.checked)}
                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label htmlFor="edit_is_active" className="ml-2 text-sm text-gray-700">
                                Compte actif
                            </label>
                        </div>
                    </div>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowEditModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleEdit}>
                            Mettre à jour
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </AdminLayout>
    );
} 