import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function Show({ user, activities, stats }) {
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const editForm = useForm({
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        role: user.roles[0]?.name || 'assistant',
    });

    const handleEdit = () => {
        editForm.put(route('admin.users.update', user.id), {
            onSuccess: () => {
                setShowEditModal(false);
            },
        });
    };

    const handleDelete = () => {
        router.delete(route('admin.users.destroy', user.id), {
            onSuccess: () => {
                router.visit(route('admin.users.index'));
            },
        });
    };

    const handleToggleStatus = () => {
        if (confirm(`Êtes-vous sûr de vouloir ${user.email_verified_at ? 'désactiver' : 'activer'} ce compte ?`)) {
            router.post(route('admin.users.toggle-status', user.id));
        }
    };

    const getRoleColor = (role) => {
        return role === 'admin' ? 'red' : 'blue';
    };

    const getStatusColor = (user) => {
        return user.email_verified_at ? 'green' : 'gray';
    };

    return (
        <>
            <Head title={`Utilisateur - ${user.name}`} />
            
            <div className="min-h-screen bg-gray-50">
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <div className="flex justify-between items-center">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">Détail de l'utilisateur</h1>
                                <p className="text-gray-600 mt-2">{user.name}</p>
                            </div>
                            <div className="flex space-x-3">
                                <Link
                                    href={route('admin.users.index')}
                                    className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    ← Retour à la liste
                                </Link>
                                <SecondaryButton onClick={() => setShowEditModal(true)}>
                                    Modifier
                                </SecondaryButton>
                                {user.id !== auth?.user?.id && (
                                    <SecondaryButton onClick={() => setShowDeleteModal(true)}>
                                        Supprimer
                                    </SecondaryButton>
                                )}
                            </div>
                        </div>
                    </div>
                </header>

                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Informations principales */}
                        <div className="lg:col-span-2">
                            <div className="bg-white rounded-lg shadow p-6 mb-6">
                                <div className="flex justify-between items-start mb-6">
                                    <h2 className="text-xl font-semibold text-gray-900">Informations de l'utilisateur</h2>
                                    <div className="flex space-x-2">
                                        <StatusBadge 
                                            status={user.roles[0]?.name === 'admin' ? 'Administrateur' : 'Assistant'}
                                            color={getRoleColor(user.roles[0]?.name)}
                                        />
                                        <StatusBadge 
                                            status={user.email_verified_at ? 'Actif' : 'Inactif'}
                                            color={getStatusColor(user)}
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 className="text-sm font-medium text-gray-500 mb-2">Informations personnelles</h3>
                                        <div className="space-y-1">
                                            <p className="text-sm text-gray-900"><strong>Nom :</strong> {user.name}</p>
                                            <p className="text-sm text-gray-900"><strong>Email :</strong> {user.email}</p>
                                            <p className="text-sm text-gray-900"><strong>Rôle :</strong> {user.roles[0]?.name === 'admin' ? 'Administrateur' : 'Assistant'}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 className="text-sm font-medium text-gray-500 mb-2">Informations système</h3>
                                        <div className="space-y-1">
                                            <p className="text-sm text-gray-900"><strong>ID :</strong> {user.id}</p>
                                            <p className="text-sm text-gray-900"><strong>Créé le :</strong> {stats.created_at}</p>
                                            {stats.email_verified_at && (
                                                <p className="text-sm text-gray-900">
                                                    <strong>Email vérifié le :</strong> {stats.email_verified_at}
                                                </p>
                                            )}
                                            {stats.last_activity && (
                                                <p className="text-sm text-gray-900">
                                                    <strong>Dernière activité :</strong> {stats.last_activity}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Statistiques de l'utilisateur */}
                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <h3 className="text-sm font-medium text-gray-500 mb-2">Statistiques</h3>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div className="bg-gray-50 rounded-lg p-3 text-center">
                                            <div className="text-lg font-bold text-blue-600">{stats.appointments_processed}</div>
                                            <div className="text-xs text-gray-600">RDV traités</div>
                                        </div>
                                    </div>
                                </div>

                                {/* Actions */}
                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <h3 className="text-sm font-medium text-gray-900 mb-4">Actions</h3>
                                    <div className="flex space-x-3">
                                        <SecondaryButton onClick={handleToggleStatus}>
                                            {user.email_verified_at ? 'Désactiver le compte' : 'Activer le compte'}
                                        </SecondaryButton>
                                        <SecondaryButton onClick={() => setShowEditModal(true)}>
                                            Modifier
                                        </SecondaryButton>
                                        {user.id !== auth?.user?.id && (
                                            <SecondaryButton onClick={() => setShowDeleteModal(true)}>
                                                Supprimer
                                            </SecondaryButton>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Historique des activités */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h2 className="text-xl font-semibold text-gray-900 mb-4">Historique des activités</h2>
                                {activities.length > 0 ? (
                                    <div className="space-y-4">
                                        {activities.map((activity, index) => (
                                            <div key={index} className="flex items-start space-x-3">
                                                <div className="flex-shrink-0">
                                                    <div className="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm text-gray-900">
                                                        {activity.description}
                                                    </p>
                                                    <p className="text-xs text-gray-500 mt-1">
                                                        {activity.created_at}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <p className="text-gray-500 text-sm">
                                            Aucune activité enregistrée pour le moment.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Sidebar */}
                        <div className="lg:col-span-1">
                            {/* Informations de sécurité */}
                            <div className="bg-white rounded-lg shadow p-6 mb-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Sécurité</h3>
                                <div className="space-y-3 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Email vérifié :</span>
                                        <span className={user.email_verified_at ? 'text-green-600' : 'text-red-600'}>
                                            {user.email_verified_at ? 'Oui' : 'Non'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Compte créé :</span>
                                        <span className="text-gray-900">{stats.created_at}</span>
                                    </div>
                                    {stats.email_verified_at && (
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">Vérifié le :</span>
                                            <span className="text-gray-900">{stats.email_verified_at}</span>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Permissions */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Permissions</h3>
                                <div className="space-y-2">
                                    {user.roles[0]?.name === 'admin' ? (
                                        <>
                                            <div className="flex items-center text-sm">
                                                <svg className="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                </svg>
                                                Accès complet à l'administration
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <svg className="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                </svg>
                                                Gestion des utilisateurs
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <svg className="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                </svg>
                                                Gestion des rendez-vous
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <div className="flex items-center text-sm">
                                                <svg className="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                </svg>
                                                Gestion des rendez-vous
                                            </div>
                                            <div className="flex items-center text-sm">
                                                <svg className="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                                                </svg>
                                                Gestion des utilisateurs
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>

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
                        Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{user.name}</strong> ? 
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