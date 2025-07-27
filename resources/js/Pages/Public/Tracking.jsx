import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';
import ApplicationLogo from '@/Components/ApplicationLogo';

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

    const getStatusDisplay = (status) => {
        switch (status) {
            case 'pending': return 'En attente';
            case 'accepted': return 'Accepté';
            case 'rejected': return 'Refusé';
            case 'canceled': return 'Annulé';
            case 'canceled_by_requester': return 'Annulé par vous';
            case 'completed': return 'Terminé';
            case 'expired': return 'Expiré';
            default: return 'En attente';
        }
    };

    const getPriorityDisplay = (priority) => {
        switch (priority) {
            case 'normal': return 'Normal';
            case 'urgent': return 'Urgent';
            case 'official': return 'Officiel';
            default: return 'Normal';
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '';
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    };

    const formatDateTime = (dateTimeString) => {
        if (!dateTimeString) return '';
        return new Date(dateTimeString).toLocaleString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <>
            <Head title={`Suivi - ${appointment.subject}`} />
            
            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="bg-white shadow-sm border-b">
                    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="py-6 flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <ApplicationLogo className="h-14 w-auto" />
                            </div>
                            <a 
                                href="/"
                                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                ← Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </header>

                {/* Titre principal déplacé sous le header */}
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-4 text-center">
                    <h1 className="text-2xl font-bold text-gray-900">
                        Suivi de votre demande
                    </h1>
                    <p className="text-gray-600 mt-2">
                        Cabinet du Gouverneur de Kinshasa
                    </p>
                </div>

                {/* Main Content */}
                <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Status Card */}
                    <div className="bg-white rounded-lg shadow-sm border mb-6">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Statut de votre demande
                                </h2>
                                <span className="text-base font-medium">
                                    {appointment.status ? getStatusDisplay(appointment.status) : 'Non renseigné'}
                                </span>
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
                                            <span className="font-medium">Date souhaitée :</span> {appointment.preferred_date ? formatDate(appointment.preferred_date) : 'Non définie'}
                                        </div>
                                        <div>
                                            <span className="font-medium">Heure souhaitée :</span> {appointment.preferred_time ? appointment.preferred_time.slice(0,5).replace(':', 'h') : 'Non définie'}
                                        </div>
                                        <div>
                                            <span className="font-medium">Priorité :</span> {appointment.priority ? getPriorityDisplay(appointment.priority) : 'Non définie'}
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
                                            <span className="font-medium">Date de soumission :</span> {appointment.created_at ? formatDateTime(appointment.created_at) : 'Non définie'}
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
                                <div className="mt-6 pt-6 border-t border-gray-200 text-center">
                                    <button
                                        onClick={() => setShowCancelModal(true)}
                                        className="inline-block px-6 py-2 rounded bg-red-600 text-white font-semibold shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                                    >
                                        Annuler ce rendez-vous
                                    </button>
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