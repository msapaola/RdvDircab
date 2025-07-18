import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Statistics from '@/Components/Admin/Statistics';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function AdminDashboard({ auth, stats, nextAppointments, statsByDay, appointments, filters }) {
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [showUpdateModal, setShowUpdateModal] = useState(false);
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

    const updateForm = useForm({
        admin_notes: '',
        preferred_date: '',
        preferred_time: '',
    });

    const handleFilter = () => {
        filterForm.get(route('admin.dashboard'));
    };

    const handleReset = () => {
        filterForm.reset();
        router.get(route('admin.dashboard'));
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

    const handleUpdate = () => {
        updateForm.put(route('admin.appointments.update', selectedAppointment.id), {
            onSuccess: () => {
                setShowUpdateModal(false);
                setSelectedAppointment(null);
                updateForm.reset();
            },
        });
    };

    const openRejectModal = (appointment) => {
        setSelectedAppointment(appointment);
        setShowRejectModal(true);
    };

    const openCancelModal = (appointment) => {
        setSelectedAppointment(appointment);
        setShowCancelModal(true);
    };

    const openUpdateModal = (appointment) => {
        setSelectedAppointment(appointment);
        updateForm.setData({
            admin_notes: appointment.admin_notes || '',
            preferred_date: appointment.preferred_date,
            preferred_time: appointment.preferred_time,
        });
        setShowUpdateModal(true);
    };

    const handleComplete = (appointment) => {
        if (confirm('Marquer ce rendez-vous comme terminé ?')) {
            router.post(route('admin.appointments.complete', appointment.id));
        }
    };

    const handleDelete = (appointment) => {
        if (confirm('Êtes-vous sûr de vouloir supprimer définitivement ce rendez-vous ?')) {
            router.delete(route('admin.appointments.destroy', appointment.id));
        }
    };

    return (
        <AdminLayout user={auth.user}>
            <Head title="Tableau de bord - Administration" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* En-tête */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                        <p className="text-gray-600 mt-2">
                            Bienvenue, {auth.user.name} ({auth.user.role === 'admin' ? 'Administrateur' : 'Assistant'})
                        </p>
                    </div>

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

                    {/* Prochains rendez-vous */}
                    {nextAppointments && nextAppointments.length > 0 && (
                        <div className="bg-white rounded-lg shadow p-6 mb-8">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Prochains rendez-vous</h2>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nom
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Heure
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Objet
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {nextAppointments.map((appointment) => (
                                            <tr key={appointment.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {appointment.name}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {new Date(appointment.preferred_date).toLocaleDateString('fr-FR')}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {appointment.preferred_time}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {appointment.subject}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

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
                                    <option value="preferred_date-asc">Date croissante</option>
                                    <option value="preferred_date-desc">Date décroissante</option>
                                    <option value="name-asc">Nom A-Z</option>
                                    <option value="name-desc">Nom Z-A</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex gap-2">
                            <PrimaryButton onClick={handleFilter}>
                                Filtrer
                            </PrimaryButton>
                            <SecondaryButton onClick={handleReset}>
                                Réinitialiser
                            </SecondaryButton>
                        </div>
                    </div>

                    {/* Liste des rendez-vous */}
                    {appointments && appointments.data && appointments.data.length > 0 && (
                        <div className="bg-white rounded-lg shadow overflow-hidden">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h2 className="text-lg font-semibold text-gray-900">Rendez-vous récents</h2>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nom
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Email
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Objet
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date souhaitée
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
                                            <tr key={appointment.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {appointment.name}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {appointment.email}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {appointment.subject}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {appointment.preferred_date} à {appointment.preferred_time}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <StatusBadge status={appointment.status} />
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div className="flex space-x-2">
                                                        {appointment.status === 'pending' && (
                                                            <>
                                                                <PrimaryButton
                                                                    onClick={() => handleAccept(appointment)}
                                                                    className="text-xs px-2 py-1"
                                                                >
                                                                    Accepter
                                                                </PrimaryButton>
                                                                <SecondaryButton
                                                                    onClick={() => openRejectModal(appointment)}
                                                                    className="text-xs px-2 py-1"
                                                                >
                                                                    Refuser
                                                                </SecondaryButton>
                                                            </>
                                                        )}
                                                        <SecondaryButton
                                                            onClick={() => openUpdateModal(appointment)}
                                                            className="text-xs px-2 py-1"
                                                        >
                                                            Modifier
                                                        </SecondaryButton>
                                                        {appointment.status === 'accepted' && (
                                                            <PrimaryButton
                                                                onClick={() => handleComplete(appointment)}
                                                                className="text-xs px-2 py-1"
                                                            >
                                                                Terminer
                                                            </PrimaryButton>
                                                        )}
                                                        {appointment.status !== 'completed' && appointment.status !== 'expired' && (
                                                            <SecondaryButton
                                                                onClick={() => openCancelModal(appointment)}
                                                                className="text-xs px-2 py-1"
                                                            >
                                                                Annuler
                                                            </SecondaryButton>
                                                        )}
                                                        <Link
                                                            href={route('admin.appointments.show', appointment.id)}
                                                            className="text-blue-600 hover:text-blue-900 text-xs"
                                                        >
                                                            Voir
                                                        </Link>
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
                    )}
                </div>
            </div>

            {/* Modals */}
            <Modal show={showRejectModal} onClose={() => setShowRejectModal(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Refuser le rendez-vous</h3>
                    <textarea
                        value={rejectionReason}
                        onChange={(e) => setRejectionReason(e.target.value)}
                        placeholder="Raison du refus..."
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        rows="4"
                    />
                    <div className="mt-4 flex justify-end space-x-2">
                        <SecondaryButton onClick={() => setShowRejectModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleReject}>
                            Refuser
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            <Modal show={showCancelModal} onClose={() => setShowCancelModal(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Annuler le rendez-vous</h3>
                    <textarea
                        value={cancelReason}
                        onChange={(e) => setCancelReason(e.target.value)}
                        placeholder="Raison de l'annulation..."
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        rows="4"
                    />
                    <div className="mt-4 flex justify-end space-x-2">
                        <SecondaryButton onClick={() => setShowCancelModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleCancel}>
                            Confirmer
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            <Modal show={showUpdateModal} onClose={() => setShowUpdateModal(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Modifier le rendez-vous</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Notes administrateur</label>
                            <textarea
                                value={updateForm.data.admin_notes}
                                onChange={(e) => updateForm.setData('admin_notes', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="3"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Date préférée</label>
                                <input
                                    type="date"
                                    value={updateForm.data.preferred_date}
                                    onChange={(e) => updateForm.setData('preferred_date', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure préférée</label>
                                <input
                                    type="time"
                                    value={updateForm.data.preferred_time}
                                    onChange={(e) => updateForm.setData('preferred_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>
                    <div className="mt-4 flex justify-end space-x-2">
                        <SecondaryButton onClick={() => setShowUpdateModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleUpdate}>
                            Mettre à jour
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </AdminLayout>
    );
} 