import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function BlockedSlots({ auth, blockedSlots }) {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [selectedSlot, setSelectedSlot] = useState(null);

    const createForm = useForm({
        date: '',
        start_time: '',
        end_time: '',
        reason: '',
    });

    const editForm = useForm({
        date: '',
        start_time: '',
        end_time: '',
        reason: '',
    });

    const handleCreate = () => {
        createForm.post(route('admin.blocked-slots.store'), {
            onSuccess: () => {
                setShowCreateModal(false);
                createForm.reset();
            },
        });
    };

    const handleEdit = () => {
        editForm.put(route('admin.blocked-slots.update', selectedSlot.id), {
            onSuccess: () => {
                setShowEditModal(false);
                setSelectedSlot(null);
                editForm.reset();
            },
        });
    };

    const handleDelete = (slot) => {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce créneau bloqué ?')) {
            router.delete(route('admin.blocked-slots.destroy', slot.id));
        }
    };

    const openEditModal = (slot) => {
        setSelectedSlot(slot);
        editForm.setData({
            date: slot.date,
            start_time: slot.start_time,
            end_time: slot.end_time,
            reason: slot.reason,
        });
        setShowEditModal(true);
    };

    return (
        <AdminLayout user={auth.user}>
            <Head title="Créneaux bloqués - Administration" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* En-tête */}
                    <div className="mb-8 flex justify-between items-center">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Créneaux bloqués</h1>
                            <p className="text-gray-600 mt-2">Gestion des créneaux horaires non disponibles</p>
                        </div>
                        <PrimaryButton onClick={() => setShowCreateModal(true)}>
                            Ajouter un créneau bloqué
                        </PrimaryButton>
                    </div>

                    {/* Liste des créneaux bloqués */}
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Créneaux bloqués ({blockedSlots.length})
                            </h2>
                        </div>
                        
                        {blockedSlots.length === 0 ? (
                            <div className="px-6 py-12 text-center">
                                <p className="text-gray-500">Aucun créneau bloqué pour le moment.</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Heure
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Raison
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Créé par
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {blockedSlots.map((slot) => (
                                            <tr key={slot.id} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {new Date(slot.date).toLocaleDateString('fr-FR')}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">
                                                        {slot.start_time} - {slot.end_time}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="text-sm text-gray-900 max-w-xs truncate">
                                                        {slot.reason}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-500">
                                                        {slot.blocked_by_user?.name || 'Système'}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div className="flex space-x-2">
                                                        <button
                                                            onClick={() => openEditModal(slot)}
                                                            className="text-blue-600 hover:text-blue-900"
                                                        >
                                                            Modifier
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(slot)}
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
                        )}
                    </div>
                </div>
            </div>

            {/* Modal de création */}
            <Modal show={showCreateModal} onClose={() => setShowCreateModal(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Ajouter un créneau bloqué</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input
                                type="date"
                                value={createForm.data.date}
                                onChange={(e) => createForm.setData('date', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                                <input
                                    type="time"
                                    value={createForm.data.start_time}
                                    onChange={(e) => createForm.setData('start_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
                                <input
                                    type="time"
                                    value={createForm.data.end_time}
                                    onChange={(e) => createForm.setData('end_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Raison</label>
                            <textarea
                                value={createForm.data.reason}
                                onChange={(e) => createForm.setData('reason', e.target.value)}
                                placeholder="Raison du blocage..."
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="3"
                            />
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
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Modifier le créneau bloqué</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input
                                type="date"
                                value={editForm.data.date}
                                onChange={(e) => editForm.setData('date', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                                <input
                                    type="time"
                                    value={editForm.data.start_time}
                                    onChange={(e) => editForm.setData('start_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
                                <input
                                    type="time"
                                    value={editForm.data.end_time}
                                    onChange={(e) => editForm.setData('end_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Raison</label>
                            <textarea
                                value={editForm.data.reason}
                                onChange={(e) => editForm.setData('reason', e.target.value)}
                                placeholder="Raison du blocage..."
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="3"
                            />
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