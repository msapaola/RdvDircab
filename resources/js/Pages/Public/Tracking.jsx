import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function Tracking({ appointment, activities }) {
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [isCanceling, setIsCanceling] = useState(false);

    const handleCancel = async () => {
        setIsCanceling(true);
        
        try {
            const response = await fetch(`/appointments/${appointment.secure_token}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            });

            const result = await response.json();

            if (result.success) {
                // Recharger la page pour mettre à jour le statut
                router.reload();
            } else {
                alert(result.message || 'Une erreur est survenue');
            }
        } catch (error) {
            alert('Une erreur de connexion est survenue');
        } finally {
            setIsCanceling(false);
            setShowCancelModal(false);
        }
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
            <Head title={`Suivi - ${appointment.subject}`} />
            
            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="bg-white shadow-sm border-b">
                    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="py-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900">
                                        Suivi de votre demande
                                    </h1>
                                    <p className="text-gray-600 mt-1">
                                        Cabinet du Gouverneur de Kinshasa
                                    </p>
                                </div>
                                <a 
                                    href="/"
                                    className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    ← Retour à l'accueil
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main Content */}
                <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Status Card */}
                    <div className="bg-white rounded-lg shadow-sm border mb-6">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Statut de votre demande
                                </h2>
                                <StatusBadge 
                                    status={appointment.formatted_status}
                                    color={getStatusColor(appointment.status)}
                                />
                            </div>
                            
                            <div className="grid md:grid-cols-2 gap-6">
                                <div>
                                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                                        Détails de la demande
                                    </h3>
                                    <div className="space-y-2 text-sm">
                                        <div>
                                            <span className="font-medium">Objet :</span> {appointment.subject}
                                        </div>
                                        <div>
                                            <span className="font-medium">Date souhaitée :</span> {appointment.preferred_date}
                                        </div>
                                        <div>
                                            <span className="font-medium">Heure souhaitée :</span> {appointment.preferred_time}
                                        </div>
                                        <div>
                                            <span className="font-medium">Priorité :</span>
                                            <StatusBadge 
                                                status={appointment.formatted_priority}
                                                color={getPriorityColor(appointment.priority)}
                                                size="sm"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                                        Vos informations
                                    </h3>
                                    <div className="space-y-2 text-sm">
                                        <div>
                                            <span className="font-medium">Nom :</span> {appointment.name}
                                        </div>
                                        <div>
                                            <span className="font-medium">Email :</span> {appointment.email}
                                        </div>
                                        <div>
                                            <span className="font-medium">Téléphone :</span> {appointment.phone}
                                        </div>
                                        <div>
                                            <span className="font-medium">Date de soumission :</span> {appointment.created_at}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {appointment.message && (
                                <div className="mt-6">
                                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                                        Message
                                    </h3>
                                    <div className="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                                        {appointment.message}
                                    </div>
                                </div>
                            )}

                            {appointment.attachments && appointment.attachments.length > 0 && (
                                <div className="mt-6">
                                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                                        Pièces jointes ({appointment.attachments.length})
                                    </h3>
                                    <div className="space-y-2">
                                        {appointment.attachments.map((attachment, index) => (
                                            <div key={index} className="flex items-center text-sm text-gray-600">
                                                <svg className="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
                                                </svg>
                                                {attachment.name}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Actions */}
                            {appointment.can_be_canceled_by_requester && (
                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <SecondaryButton
                                        onClick={() => setShowCancelModal(true)}
                                        className="text-red-600 border-red-300 hover:bg-red-50"
                                    >
                                        Annuler ce rendez-vous
                                    </SecondaryButton>
                                </div>
                            )}

                            {/* Admin Notes */}
                            {appointment.admin_notes && (
                                <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <h3 className="text-sm font-medium text-blue-900 mb-2">
                                        Note de l'administration
                                    </h3>
                                    <p className="text-sm text-blue-800">
                                        {appointment.admin_notes}
                                    </p>
                                </div>
                            )}

                            {/* Rejection Reason */}
                            {appointment.rejection_reason && (
                                <div className="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <h3 className="text-sm font-medium text-red-900 mb-2">
                                        Raison du refus
                                    </h3>
                                    <p className="text-sm text-red-800">
                                        {appointment.rejection_reason}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Activity History */}
                    <div className="bg-white rounded-lg shadow-sm border">
                        <div className="p-6 border-b">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Historique des actions
                            </h2>
                        </div>
                        
                        <div className="p-6">
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

                    {/* Information */}
                    <div className="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 className="text-sm font-medium text-blue-900 mb-2">
                            Informations importantes
                        </h3>
                        <ul className="text-sm text-blue-800 space-y-1">
                            <li>• Vous recevrez une notification par email lors de tout changement de statut</li>
                            <li>• Les rendez-vous acceptés peuvent être annulés jusqu'à 24h avant la date</li>
                            <li>• En cas d'urgence, contactez-nous directement au +243 XXX XXX XXX</li>
                        </ul>
                    </div>
                </main>
            </div>

            {/* Cancel Confirmation Modal */}
            <Modal show={showCancelModal} onClose={() => setShowCancelModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                        Confirmer l'annulation
                    </h3>
                    <p className="text-sm text-gray-600 mb-6">
                        Êtes-vous sûr de vouloir annuler votre rendez-vous ? Cette action ne peut pas être annulée.
                    </p>
                    
                    <div className="flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowCancelModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton
                            onClick={handleCancel}
                            disabled={isCanceling}
                            className="bg-red-600 hover:bg-red-700 focus:ring-red-500"
                        >
                            {isCanceling ? 'Annulation...' : 'Confirmer l\'annulation'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </>
    );
} 