import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';
import StatusBadge from '@/Components/UI/StatusBadge';
import DashboardMenu from '@/Components/DashboardMenu';

export default function Index({ users, stats, filters }) {
    console.log('Users component rendered with:', { users, stats, filters });
    
    // États de base
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);

    // Formulaires de base
    const filterForm = useForm({
        role: filters.role || '',
        search: filters.search || '',
        status: filters.status || '',
        sort_by: filters.sort_by || 'created_at',
        sort_order: filters.sort_order || 'desc',
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

    // Fonctions de base
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
    
    return (
        <>
            <Head title="Gestion des utilisateurs - Administration" />
            
            <div className="min-h-screen bg-gray-50">
                <DashboardMenu />
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-8">
                        Gestion des utilisateurs
                    </h1>
                    
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">
                            Test de la page
                        </h2>
                        
                        <div className="space-y-4">
                            <div>
                                <strong>Stats:</strong> {JSON.stringify(stats)}
                            </div>
                            
                            <div>
                                <strong>Filters:</strong> {JSON.stringify(filters)}
                            </div>
                            
                            <div>
                                <strong>Users count:</strong> {users?.data?.length || 0}
                            </div>
                            
                            <div className="flex space-x-3">
                                <PrimaryButton onClick={() => setShowCreateModal(true)}>
                                    Nouvel utilisateur
                                </PrimaryButton>
                                <SecondaryButton onClick={handleReset}>
                                    Réinitialiser
                                </SecondaryButton>
                            </div>
                            
                            <div>
                                <strong>Users data:</strong>
                                <pre className="text-xs bg-gray-100 p-2 mt-2 rounded">
                                    {JSON.stringify(users, null, 2)}
                                </pre>
                            </div>
                            
                            <div>
                                <strong>Test StatusBadge:</strong>
                                <div className="mt-2 space-x-2">
                                    <StatusBadge status="Administrateur" color="red" />
                                    <StatusBadge status="Assistant" color="blue" />
                                    <StatusBadge status="Actif" color="green" />
                                    <StatusBadge status="Inactif" color="gray" />
                                </div>
                            </div>
                        </div>
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
        </>
    );
} 