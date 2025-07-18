import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import Statistics from '@/Components/Admin/Statistics';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function Dashboard({ stats, nextAppointments, statsByDay, appointments, filters }) {
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [selectedAppointment, setSelectedAppointment] = useState(null);
    const [rejectionReason, setRejectionReason] = useState('');
    const [cancelReason, setCancelReason] = useState('');

    const filterForm = useForm({
        status: filters?.status || '',
        priority: filters?.priority || '',
        date_from: filters?.date_from || '',
        date_to: filters?.date_to || '',
        search: filters?.search || '',
        sort_by: filters?.sort_by || 'created_at',
        sort_order: filters?.sort_order || 'desc',
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
            <Head title="Tableau de bord - Administration" />
            <div className="min-h-screen bg-gray-50">
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                        <p className="text-gray-600 mt-2">Vue d'ensemble et gestion des rendez-vous</p>
                    </div>
                </header>

                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* KPIs */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-orange-500">{stats.pending}</div>
                            <div className="text-sm text-gray-600 mt-2">En attente</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-green-500">{stats.accepted}</div>
                            <div className="text-sm text-gray-600 mt-2">Acceptés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-red-500">{stats.rejected}</div>
                            <div className="text-sm text-gray-600 mt-2">Refusés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-gray-400">{stats.canceled}</div>
                            <div className="text-sm text-gray-600 mt-2">Annulés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-blue-500">{stats.completed}</div>
                            <div className="text-sm text-gray-600 mt-2">Terminés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-gray-500">{stats.expired}</div>
                            <div className="text-sm text-gray-600 mt-2">Expirés</div>
                        </div>
                    </div>

                    {/* Statistiques graphiques */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Statistiques sur 30 jours</h2>
                        <Statistics 
                            data={{
                                title: 'Évolution des rendez-vous sur 30 jours',
                                series: [
                                    {
                                        name: 'Rendez-vous',
                                        data: (statsByDay || []).map(item => ({
                                            x: new Date(item.day).getTime(),
                                            y: item.count
                                        }))
                                    }
                                ]
                            }}
                            type="line"
                        />
                    </div>

                    {/* Filtres pour les rendez-vous */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Filtres des rendez-vous</h2>
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
                        <div className="flex space-x-3">
                            <PrimaryButton onClick={handleFilter}>
                                Filtrer
                            </PrimaryButton>
                            <SecondaryButton onClick={handleReset}>
                                Réinitialiser
                            </SecondaryButton>
                        </div>
                    </div>

                    {/* Liste des rendez-vous récents */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <div className="flex justify-between items-center mb-4">
                            <h2 className="text-lg font-semibold text-gray-900">Rendez-vous récents</h2>
                            <Link
                                href={route('admin.appointments.index')}
                                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                Voir tous →
                            </Link>
                        </div>
                        {appointments && appointments.data && appointments.data.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Demandeur
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Objet
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
                                        {appointments.data.slice(0, 5).map((appointment) => (
                                            <tr key={appointment.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <div className="text-sm font-medium text-gray-900">{appointment.name}</div>
                                                        <div className="text-sm text-gray-500">{appointment.email}</div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">{appointment.preferred_date}</div>
                                                    <div className="text-sm text-gray-500">{appointment.preferred_time}</div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="text-sm text-gray-900 truncate max-w-xs">{appointment.subject}</div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                        appointment.priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                                        appointment.priority === 'official' ? 'bg-blue-100 text-blue-800' :
                                                        'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {appointment.formatted_priority}
                                                    </span>
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
                        ) : (
                            <div className="text-gray-500 text-center py-8">Aucun rendez-vous trouvé.</div>
                        )}
                    </div>

                    {/* Prochains rendez-vous acceptés */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Prochains rendez-vous acceptés</h2>
                        {nextAppointments && nextAppointments.length > 0 ? (
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th className="text-left py-2">Date</th>
                                        <th className="text-left py-2">Heure</th>
                                        <th className="text-left py-2">Demandeur</th>
                                        <th className="text-left py-2">Objet</th>
                                        <th className="text-left py-2">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {nextAppointments.map((rdv) => (
                                        <tr key={rdv.id} className="border-b">
                                            <td className="py-2">{rdv.preferred_date}</td>
                                            <td className="py-2">{rdv.preferred_time}</td>
                                            <td className="py-2">{rdv.name}</td>
                                            <td className="py-2">{rdv.subject}</td>
                                            <td className="py-2">
                                                <StatusBadge status={rdv.formatted_status} color="green" />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <div className="text-gray-500 text-sm">Aucun rendez-vous à venir.</div>
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
