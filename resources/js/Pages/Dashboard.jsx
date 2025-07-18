import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Statistics from '@/Components/Admin/Statistics';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function Dashboard({ auth, stats, nextAppointments, statsByDay, appointments, filters }) {
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

    // Déterminer si l'utilisateur est admin ou assistant
    const isAdminOrAssistant = auth.user?.role === 'admin' || auth.user?.role === 'assistant';

    // Contenu du dashboard
    const dashboardContent = (
        <>
            <Head title="Tableau de bord - Administration" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                        <div className="flex gap-2">
                            <PrimaryButton onClick={handleFilter}>
                                Appliquer les filtres
                            </PrimaryButton>
                            <SecondaryButton onClick={handleReset}>
                                Réinitialiser
                            </SecondaryButton>
                        </div>
                    </div>

                    {/* Liste des rendez-vous */}
                    {appointments && appointments.data && appointments.data.length > 0 ? (
                        <div className="bg-white rounded-lg shadow overflow-hidden">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h2 className="text-lg font-semibold text-gray-900">Rendez-vous récents</h2>
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
                                                Date souhaitée
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Statut
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Priorité
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
                                                    <div className="text-sm text-gray-900">
                                                        {appointment.subject}
                                                    </div>
                                                    {appointment.description && (
                                                        <div className="text-sm text-gray-500 truncate max-w-xs">
                                                            {appointment.description}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">
                                                        {new Date(appointment.preferred_date).toLocaleDateString('fr-FR')}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        {appointment.preferred_time}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <StatusBadge status={appointment.status} />
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                        appointment.priority === 'urgent' 
                                                            ? 'bg-red-100 text-red-800' 
                                                            : appointment.priority === 'official' 
                                                            ? 'bg-blue-100 text-blue-800' 
                                                            : 'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {appointment.priority === 'urgent' ? 'Urgente' : 
                                                         appointment.priority === 'official' ? 'Officielle' : 'Normale'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div className="flex space-x-2">
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
                                                            </>
                                                        )}
                                                        {appointment.status === 'accepted' && (
                                                            <button
                                                                onClick={() => handleComplete(appointment)}
                                                                className="text-blue-600 hover:text-blue-900"
                                                            >
                                                                Terminer
                                                            </button>
                                                        )}
                                                        <button
                                                            onClick={() => openUpdateModal(appointment)}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Modifier
                                                        </button>
                                                        <button
                                                            onClick={() => openCancelModal(appointment)}
                                                            className="text-yellow-600 hover:text-yellow-900"
                                                        >
                                                            Annuler
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(appointment)}
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
                            
                            {/* Pagination */}
                            {appointments.links && (
                                <div className="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1 flex justify-between sm:hidden">
                                            {appointments.prev_page_url && (
                                                <Link
                                                    href={appointments.prev_page_url}
                                                    className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                >
                                                    Précédent
                                                </Link>
                                            )}
                                            {appointments.next_page_url && (
                                                <Link
                                                    href={appointments.next_page_url}
                                                    className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                >
                                                    Suivant
                                                </Link>
                                            )}
                                        </div>
                                        <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <p className="text-sm text-gray-700">
                                                    Affichage de <span className="font-medium">{appointments.from}</span> à{' '}
                                                    <span className="font-medium">{appointments.to}</span> sur{' '}
                                                    <span className="font-medium">{appointments.total}</span> résultats
                                                </p>
                                            </div>
                                            <div>
                                                <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                    {appointments.links.map((link, index) => (
                                                        <Link
                                                            key={index}
                                                            href={link.url}
                                                            className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                                                                link.active
                                                                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                            } ${index === 0 ? 'rounded-l-md' : ''} ${
                                                                index === appointments.links.length - 1 ? 'rounded-r-md' : ''
                                                            }`}
                                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                                        />
                                                    ))}
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <p className="text-gray-500">Aucun rendez-vous trouvé.</p>
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
                            Confirmer l'annulation
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
        </>
    );

    // Utiliser le layout approprié selon le rôle
    if (isAdminOrAssistant) {
        return (
            <AdminLayout user={auth.user}>
                {dashboardContent}
            </AdminLayout>
        );
    } else {
        return (
            <AuthenticatedLayout user={auth.user}>
                {dashboardContent}
            </AuthenticatedLayout>
        );
    }
}
