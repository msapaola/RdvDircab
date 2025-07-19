import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';
import DashboardMenu from '@/Components/DashboardMenu';

export default function Index({ appointments, stats, filters }) {
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [selectedAppointment, setSelectedAppointment] = useState(null);
    const [rejectionReason, setRejectionReason] = useState('');
    const [cancelReason, setCancelReason] = useState('');

    const filterForm = useForm({
        status: filters.status || '',
        priority: filters.priority || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        search: filters.search || '',
        sort_by: filters.sort_by || 'created_at',
        sort_order: filters.sort_order || 'desc',
    });

    const handleFilter = () => {
        filterForm.get(route('admin.appointments.index'));
    };

    const handleReset = () => {
        filterForm.reset();
        router.get(route('admin.appointments.index'));
    };

    const handleReject = () => {
        if (!rejectionReason.trim()) {
            alert('Veuillez indiquer une raison de refus.');
            return;
        }

        router.post(route('admin.appointments.reject', selectedAppointment.id), {
            rejection_reason: rejectionReason,
        });

        setShowRejectModal(false);
        setSelectedAppointment(null);
        setRejectionReason('');
    };

    const handleCancel = () => {
        if (!cancelReason.trim()) {
            alert('Veuillez indiquer une raison d\'annulation.');
            return;
        }

        router.post(route('admin.appointments.cancel', selectedAppointment.id), {
            admin_notes: cancelReason,
        });

        setShowCancelModal(false);
        setSelectedAppointment(null);
        setCancelReason('');
    };

    const handleAccept = (appointment) => {
        if (confirm('Êtes-vous sûr de vouloir accepter ce rendez-vous ?')) {
            router.post(route('admin.appointments.accept', appointment.id));
        }
    };

    const openRejectModal = (appointment) => {
        setSelectedAppointment(appointment);
        setShowRejectModal(true);
    };

    const openCancelModal = (appointment) => {
        setSelectedAppointment(appointment);
        setShowCancelModal(true);
    };

    return (
        <>
            <Head title="Gestion des rendez-vous - Administration" />
            
            <div className="min-h-screen bg-gray-50">
                <DashboardMenu />
                
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <div className="flex justify-between items-center">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">Gestion des rendez-vous</h1>
                                <p className="text-gray-600 mt-2">Liste et gestion de tous les rendez-vous</p>
                            </div>
                            <Link
                                href={route('admin.dashboard')}
                                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                ← Retour au tableau de bord
                            </Link>
                        </div>
                    </div>
                </header>

                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Statistiques rapides */}
                    <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-gray-900">{stats.total}</div>
                            <div className="text-sm text-gray-600">Total</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-orange-500">{stats.pending}</div>
                            <div className="text-sm text-gray-600">En attente</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-green-500">{stats.accepted}</div>
                            <div className="text-sm text-gray-600">Acceptés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-red-500">{stats.rejected}</div>
                            <div className="text-sm text-gray-600">Refusés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-gray-400">{stats.canceled}</div>
                            <div className="text-sm text-gray-600">Annulés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-blue-500">{stats.completed}</div>
                            <div className="text-sm text-gray-600">Terminés</div>
                        </div>
                    </div>

                    {/* Filtres */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Filtres</h2>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                <select
                                    value={filterForm.data.status}
                                    onChange={(e) => filterForm.setData('status', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Tous les statuts</option>
                                    <option value="pending">En attente</option>
                                    <option value="accepted">Accepté</option>
                                    <option value="rejected">Refusé</option>
                                    <option value="canceled">Annulé</option>
                                    <option value="canceled_by_requester">Annulé par le demandeur</option>
                                    <option value="expired">Expiré</option>
                                    <option value="completed">Terminé</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                                <select
                                    value={filterForm.data.priority}
                                    onChange={(e) => filterForm.setData('priority', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Toutes les priorités</option>
                                    <option value="normal">Normale</option>
                                    <option value="urgent">Urgente</option>
                                    <option value="official">Officielle</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                                <input
                                    type="text"
                                    value={filterForm.data.search}
                                    onChange={(e) => filterForm.setData('search', e.target.value)}
                                    placeholder="Nom, email ou objet..."
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                                <input
                                    type="date"
                                    value={filterForm.data.date_from}
                                    onChange={(e) => filterForm.setData('date_from', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                                <input
                                    type="date"
                                    value={filterForm.data.date_to}
                                    onChange={(e) => filterForm.setData('date_to', e.target.value)}
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
                                    <option value="preferred_date-asc">Date RDV (croissant)</option>
                                    <option value="preferred_date-desc">Date RDV (décroissant)</option>
                                    <option value="name-asc">Nom (A-Z)</option>
                                    <option value="name-desc">Nom (Z-A)</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex space-x-3">
                            <PrimaryButton onClick={handleFilter}>
                                Appliquer les filtres
                            </PrimaryButton>
                            <SecondaryButton onClick={handleReset}>
                                Réinitialiser
                            </SecondaryButton>
                        </div>
                    </div>

                    {/* Liste des rendez-vous */}
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Rendez-vous ({appointments.total})
                            </h2>
                        </div>
                        
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Demandeur
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Objet
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date/Heure
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Priorité
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {appointments.data.map((appointment) => (
                                        <tr key={appointment.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {appointment.name}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        {appointment.email}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-900 max-w-xs truncate">
                                                    {appointment.subject}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {appointment.preferred_date}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    {appointment.preferred_time}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <StatusBadge 
                                                    status={appointment.formatted_priority}
                                                    color={appointment.priority === 'urgent' ? 'red' : appointment.priority === 'official' ? 'blue' : 'gray'}
                                                />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <StatusBadge 
                                                    status={appointment.formatted_status}
                                                    color={
                                                        appointment.status === 'pending' ? 'orange' :
                                                        appointment.status === 'accepted' ? 'green' :
                                                        appointment.status === 'rejected' ? 'red' :
                                                        appointment.status === 'canceled' || appointment.status === 'canceled_by_requester' ? 'gray' :
                                                        appointment.status === 'completed' ? 'blue' : 'gray'
                                                    }
                                                />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex space-x-2">
                                                    <Link
                                                        href={route('admin.appointments.show', appointment.id)}
                                                        className="text-blue-600 hover:text-blue-900"
                                                    >
                                                        Voir
                                                    </Link>
                                                    
                                                    {appointment.status === 'pending' && (
                                                        <>
                                                            <button
                                                                onClick={() => handleAccept(appointment)}
                                                                className="text-green-600 hover:text-green-900"
                                                            >
                                                                Accepter
                                                            </button>
                                                            <button
                                                                onClick={() => openRejectModal(appointment)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                Refuser
                                                            </button>
                                                            <button
                                                                onClick={() => openCancelModal(appointment)}
                                                                className="text-gray-600 hover:text-gray-900"
                                                            >
                                                                Annuler
                                                            </button>
                                                        </>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {appointments.links && (
                            <div className="px-6 py-3 border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Affichage de {appointments.from} à {appointments.to} sur {appointments.total} résultats
                                    </div>
                                    <div className="flex space-x-2">
                                        {appointments.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                className={`px-3 py-2 text-sm rounded-md ${
                                                    link.active
                                                        ? 'bg-blue-500 text-white'
                                                        : 'bg-white text-gray-700 hover:bg-gray-50'
                                                } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </main>
            </div>

            {/* Modal de refus */}
            <Modal show={showRejectModal} onClose={() => setShowRejectModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Refuser le rendez-vous</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Veuillez indiquer la raison du refus pour le rendez-vous de {selectedAppointment?.name}.
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
                        Veuillez indiquer la raison de l'annulation pour le rendez-vous de {selectedAppointment?.name}.
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
        </>
    );
} 