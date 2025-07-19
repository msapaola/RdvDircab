import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';
import AttachmentViewer from '@/Components/UI/AttachmentViewer';

export default function Show({ appointment, activities }) {
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [showUpdateModal, setShowUpdateModal] = useState(false);
    const [rejectionReason, setRejectionReason] = useState('');
    const [cancelReason, setCancelReason] = useState('');

    const updateForm = useForm({
        admin_notes: appointment.admin_notes || '',
        preferred_date: appointment.preferred_date,
        preferred_time: appointment.preferred_time,
    });

    const handleAccept = () => {
        if (confirm('Êtes-vous sûr de vouloir accepter ce rendez-vous ?')) {
            router.post(route('admin.appointments.accept', appointment.id));
        }
    };

    const handleReject = () => {
        if (!rejectionReason.trim()) {
            alert('Veuillez indiquer une raison de refus.');
            return;
        }

        router.post(route('admin.appointments.reject', appointment.id), {
            rejection_reason: rejectionReason,
        });

        setShowRejectModal(false);
        setRejectionReason('');
    };

    const handleCancel = () => {
        if (!cancelReason.trim()) {
            alert('Veuillez indiquer une raison d\'annulation.');
            return;
        }

        router.post(route('admin.appointments.cancel', appointment.id), {
            admin_notes: cancelReason,
        });

        setShowCancelModal(false);
        setCancelReason('');
    };

    const handleUpdate = () => {
        updateForm.put(route('admin.appointments.update', appointment.id), {
            onSuccess: () => {
                setShowUpdateModal(false);
            },
        });
    };

    const getStatusColor = (status) => {
        const colors = {
            pending: 'orange',
            accepted: 'green',
            rejected: 'red',
            canceled: 'gray',
            canceled_by_requester: 'gray',
            expired: 'gray',
            completed: 'blue',
        };
        return colors[status] || 'gray';
    };

    const getPriorityColor = (priority) => {
        const colors = {
            normal: 'gray',
            urgent: 'red',
            official: 'blue',
        };
        return colors[priority] || 'gray';
    };

    return (
        <>
            <Head title={`Rendez-vous - ${appointment.subject}`} />
            
            <div className="min-h-screen bg-gray-50">
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <div className="flex justify-between items-center">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">Détail du rendez-vous</h1>
                                <p className="text-gray-600 mt-2">{appointment.subject}</p>
                            </div>
                            <Link
                                href={route('admin.appointments.index')}
                                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                ← Retour à la liste
                            </Link>
                        </div>
                    </div>
                </header>

                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Informations principales */}
                        <div className="lg:col-span-2">
                            <div className="bg-white rounded-lg shadow p-6 mb-6">
                                <div className="flex justify-between items-start mb-6">
                                    <h2 className="text-xl font-semibold text-gray-900">Informations du rendez-vous</h2>
                                    <div className="flex space-x-2">
                                        <StatusBadge 
                                            status={appointment.formatted_status}
                                            color={getStatusColor(appointment.status)}
                                        />
                                        <StatusBadge 
                                            status={appointment.formatted_priority}
                                            color={getPriorityColor(appointment.priority)}
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 className="text-sm font-medium text-gray-500 mb-2">Demandeur</h3>
                                        <div className="space-y-1">
                                            <p className="text-sm text-gray-900"><strong>Nom :</strong> {appointment.name}</p>
                                            <p className="text-sm text-gray-900"><strong>Email :</strong> {appointment.email}</p>
                                            <p className="text-sm text-gray-900"><strong>Téléphone :</strong> {appointment.phone}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 className="text-sm font-medium text-gray-500 mb-2">Détails</h3>
                                        <div className="space-y-1">
                                            <p className="text-sm text-gray-900"><strong>Date souhaitée :</strong> {appointment.preferred_date}</p>
                                            <p className="text-sm text-gray-900"><strong>Heure souhaitée :</strong> {appointment.preferred_time}</p>
                                            <p className="text-sm text-gray-900"><strong>Date de soumission :</strong> {appointment.created_at}</p>
                                            {appointment.processed_by && (
                                                <p className="text-sm text-gray-900">
                                                    <strong>Traité par :</strong> {appointment.processed_by.name}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-6">
                                    <h3 className="text-sm font-medium text-gray-500 mb-2">Objet</h3>
                                    <p className="text-sm text-gray-900">{appointment.subject}</p>
                                </div>

                                {appointment.message && (
                                    <div className="mt-6">
                                        <h3 className="text-sm font-medium text-gray-500 mb-2">Message</h3>
                                        <div className="bg-gray-50 rounded-lg p-4">
                                            <p className="text-sm text-gray-700">{appointment.message}</p>
                                        </div>
                                    </div>
                                )}

                                <AttachmentViewer 
                                    attachments={appointment.attachments} 
                                    appointmentId={appointment.id} 
                                />

                                {/* Actions */}
                                {appointment.status === 'pending' && (
                                    <div className="mt-6 pt-6 border-t border-gray-200">
                                        <h3 className="text-sm font-medium text-gray-900 mb-4">Actions</h3>
                                        <div className="flex space-x-3">
                                            <PrimaryButton onClick={handleAccept}>
                                                Accepter
                                            </PrimaryButton>
                                            <SecondaryButton onClick={() => setShowRejectModal(true)}>
                                                Refuser
                                            </SecondaryButton>
                                            <SecondaryButton onClick={() => setShowCancelModal(true)}>
                                                Annuler
                                            </SecondaryButton>
                                            <SecondaryButton onClick={() => setShowUpdateModal(true)}>
                                                Modifier
                                            </SecondaryButton>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Historique des activités */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h2 className="text-xl font-semibold text-gray-900 mb-4">Historique des actions</h2>
                                {activities.length > 0 ? (
                                    <div className="space-y-4">
                                        {activities.map((activity, index) => (
                                            <div key={index} className="flex items-start space-x-3">
                                                <div className="flex-shrink-0">
                                                    <div className="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm text-gray-900">
                                                        {activity.formatted_description}
                                                    </p>
                                                    <p className="text-xs text-gray-500 mt-1">
                                                        {activity.time_ago}
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
                            {/* Notes admin */}
                            <div className="bg-white rounded-lg shadow p-6 mb-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Notes administratives</h3>
                                {appointment.admin_notes ? (
                                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p className="text-sm text-blue-800">{appointment.admin_notes}</p>
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500">Aucune note administrative.</p>
                                )}
                            </div>

                            {/* Raison du refus */}
                            {appointment.rejection_reason && (
                                <div className="bg-white rounded-lg shadow p-6 mb-6">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Raison du refus</h3>
                                    <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <p className="text-sm text-red-800">{appointment.rejection_reason}</p>
                                    </div>
                                </div>
                            )}

                            {/* Informations système */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Informations système</h3>
                                <div className="space-y-2 text-sm">
                                    <p><strong>ID :</strong> {appointment.id}</p>
                                    <p><strong>Token :</strong> <code className="text-xs bg-gray-100 px-1 rounded">{appointment.secure_token}</code></p>
                                    <p><strong>IP :</strong> {appointment.ip_address}</p>
                                    <p><strong>Créé le :</strong> {appointment.created_at}</p>
                                    {appointment.updated_at !== appointment.created_at && (
                                        <p><strong>Modifié le :</strong> {appointment.updated_at}</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>

            {/* Modal de refus */}
            <Modal show={showRejectModal} onClose={() => setShowRejectModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Refuser le rendez-vous</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Veuillez indiquer la raison du refus pour le rendez-vous de {appointment.name}.
                    </p>
                    <textarea
                        value={rejectionReason}
                        onChange={(e) => setRejectionReason(e.target.value)}
                        placeholder="Raison du refus..."
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        rows="4"
                    />
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowRejectModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleReject}>
                            Confirmer le refus
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal d'annulation */}
            <Modal show={showCancelModal} onClose={() => setShowCancelModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Annuler le rendez-vous</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Veuillez indiquer la raison de l'annulation pour le rendez-vous de {appointment.name}.
                    </p>
                    <textarea
                        value={cancelReason}
                        onChange={(e) => setCancelReason(e.target.value)}
                        placeholder="Raison de l'annulation..."
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        rows="4"
                    />
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowCancelModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleCancel}>
                            Confirmer l'annulation
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal de modification */}
            <Modal show={showUpdateModal} onClose={() => setShowUpdateModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Modifier le rendez-vous</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Date souhaitée</label>
                            <input
                                type="date"
                                value={updateForm.data.preferred_date}
                                onChange={(e) => updateForm.setData('preferred_date', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Heure souhaitée</label>
                            <input
                                type="time"
                                value={updateForm.data.preferred_time}
                                onChange={(e) => updateForm.setData('preferred_time', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Notes administratives</label>
                            <textarea
                                value={updateForm.data.admin_notes}
                                onChange={(e) => updateForm.setData('admin_notes', e.target.value)}
                                placeholder="Notes administratives..."
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="4"
                            />
                        </div>
                    </div>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowUpdateModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleUpdate} disabled={updateForm.processing}>
                            {updateForm.processing ? 'Modification...' : 'Enregistrer'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </>
    );
} 