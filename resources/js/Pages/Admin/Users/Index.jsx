import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';

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
                            
                            <div>
                                <strong>Users data:</strong>
                                <pre className="text-xs bg-gray-100 p-2 mt-2 rounded">
                                    {JSON.stringify(users, null, 2)}
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
} 