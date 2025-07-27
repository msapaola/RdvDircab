import React, { useState, useEffect } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';
import DashboardMenu from '@/Components/DashboardMenu';
import SafeLink from '@/Components/SafeLink';
import AttachmentViewer from '@/Components/UI/AttachmentViewer';

export default function Index({ appointments, stats, filters }) {
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [showDetailsModal, setShowDetailsModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showBulkActionsModal, setShowBulkActionsModal] = useState(false);
    const [selectedAppointment, setSelectedAppointment] = useState(null);
    const [selectedAppointments, setSelectedAppointments] = useState([]);
    const [rejectionReason, setRejectionReason] = useState('');
    const [cancelReason, setCancelReason] = useState('');
    const [bulkAction, setBulkAction] = useState('');
    const [bulkReason, setBulkReason] = useState('');
    const [viewMode, setViewMode] = useState('table'); // 'table' or 'cards'
    const [autoRefresh, setAutoRefresh] = useState(false);
    const [showAcceptModal, setShowAcceptModal] = useState(false);
    const [isAccepting, setIsAccepting] = useState(false);
    const [acceptingAppointment, setAcceptingAppointment] = useState(null);

    // Auto-refresh every 30 seconds if enabled
    useEffect(() => {
        if (!autoRefresh) return;
        
        const interval = setInterval(() => {
            router.reload({ only: ['appointments', 'stats'] });
        }, 30000);
        
        return () => clearInterval(interval);
    }, [autoRefresh]);

    const filterForm = useForm({
        status: filters.status || '',
        priority: filters.priority || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        search: filters.search || '',
        sort_by: filters.sort_by || 'created_at',
        sort_order: filters.sort_order || 'desc',
        per_page: filters.per_page || 20,
    });

    const editForm = useForm({
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
        preferred_date: '',
        preferred_time: '',
        priority: 'normal',
        status: 'pending',
        admin_notes: '',
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
        }, {
            onSuccess: () => {
        setShowRejectModal(false);
        setSelectedAppointment(null);
        setRejectionReason('');
            }
        });
    };

    const handleCancel = () => {
        if (!cancelReason.trim()) {
            alert('Veuillez indiquer une raison d\'annulation.');
            return;
        }

        router.post(route('admin.appointments.cancel', selectedAppointment.id), {
            admin_notes: cancelReason,
        }, {
            onSuccess: () => {
        setShowCancelModal(false);
        setSelectedAppointment(null);
        setCancelReason('');
            }
        });
    };

    const handleAccept = (appointment) => {
        setAcceptingAppointment(appointment);
        setShowAcceptModal(true);
    };

    const confirmAccept = () => {
        setIsAccepting(true);
        
        router.post(route('admin.appointments.accept', acceptingAppointment.id), {}, {
            onSuccess: () => {
                setIsAccepting(false);
                setShowAcceptModal(false);
                setAcceptingAppointment(null);
            },
            onError: () => {
                setIsAccepting(false);
                alert('Une erreur est survenue lors de l\'acceptation du rendez-vous.');
            }
        });
    };

    const handleComplete = (appointment) => {
        if (confirm('Marquer ce rendez-vous comme terminé ?')) {
            router.post(route('admin.appointments.complete', appointment.id));
        }
    };

    const handleEdit = () => {
        editForm.put(route('admin.appointments.update', selectedAppointment.id), {
            onSuccess: () => {
                setShowEditModal(false);
                setSelectedAppointment(null);
                editForm.reset();
            }
        });
    };

    const openEditModal = (appointment) => {
        setSelectedAppointment(appointment);
        
        // Formater la date pour l'input HTML (yyyy-MM-dd)
        const formatDateForInput = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        };
        
        editForm.setData({
            name: appointment.name,
            email: appointment.email,
            phone: appointment.phone,
            subject: appointment.subject,
            message: appointment.message || '',
            preferred_date: formatDateForInput(appointment.preferred_date),
            preferred_time: appointment.preferred_time,
            priority: appointment.priority,
            status: appointment.status,
            admin_notes: appointment.admin_notes || '',
        });
        setShowEditModal(true);
    };

    const handleBulkAction = () => {
        if (!bulkAction) {
            alert('Veuillez sélectionner une action.');
            return;
        }

        if (selectedAppointments.length === 0) {
            alert('Veuillez sélectionner au moins un rendez-vous.');
            return;
        }

        if ((bulkAction === 'reject' || bulkAction === 'cancel') && !bulkReason.trim()) {
            alert('Veuillez indiquer une raison.');
            return;
        }

        // Préparer les données à envoyer
        const requestData = {
            appointment_ids: selectedAppointments,
            action: bulkAction,
        };

        // Ajouter la raison seulement si elle est nécessaire et non vide
        if ((bulkAction === 'reject' || bulkAction === 'cancel') && bulkReason.trim()) {
            requestData.reason = bulkReason.trim();
        }

        // Utiliser Inertia pour les actions en lot
        router.post(route('admin.appointments.bulk-action'), requestData, {
            onSuccess: () => {
                setShowBulkActionsModal(false);
                setSelectedAppointments([]);
                setBulkAction('');
                setBulkReason('');
            },
            onError: (errors) => {
                alert('Erreur lors de l\'exécution de l\'action en lot.');
            }
        });
    };

    const toggleAppointmentSelection = (appointmentId) => {
        setSelectedAppointments(prev => 
            prev.includes(appointmentId) 
                ? prev.filter(id => id !== appointmentId)
                : [...prev, appointmentId]
        );
    };

    const toggleAllAppointments = () => {
        if (selectedAppointments.length === appointments.data.length) {
            setSelectedAppointments([]);
        } else {
            setSelectedAppointments(appointments.data.map(app => app.id));
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

    const openDetailsModal = (appointment) => {
        setSelectedAppointment(appointment);
        setShowDetailsModal(true);
    };

    const getPriorityDisplay = (priority) => {
        switch (priority) {
            case 'urgent': return { text: 'URGENT', class: 'text-red-700 bg-red-100 border-red-300' };
            case 'official': return { text: 'OFFICIEL', class: 'text-blue-700 bg-blue-100 border-blue-300' };
            case 'normal': return { text: 'NORMALE', class: 'text-gray-700 bg-gray-100 border-gray-300' };
            default: return { text: 'NORMALE', class: 'text-gray-700 bg-gray-100 border-gray-300' };
        }
    };

    const getStatusDisplay = (status) => {
        switch (status) {
            case 'pending': return { text: 'EN ATTENTE', class: 'text-orange-700 bg-orange-100 border-orange-300' };
            case 'accepted': return { text: 'ACCEPTÉ', class: 'text-green-700 bg-green-100 border-green-300' };
            case 'rejected': return { text: 'REFUSÉ', class: 'text-red-700 bg-red-100 border-red-300' };
            case 'canceled': return { text: 'ANNULÉ', class: 'text-gray-700 bg-gray-100 border-gray-300' };
            case 'canceled_by_requester': return { text: 'ANNULÉ PAR LE CLIENT', class: 'text-gray-700 bg-gray-100 border-gray-300' };
            case 'completed': return { text: 'TERMINÉ', class: 'text-blue-700 bg-blue-100 border-blue-300' };
            case 'expired': return { text: 'EXPIRÉ', class: 'text-yellow-700 bg-yellow-100 border-yellow-300' };
            default: return { text: 'EN ATTENTE', class: 'text-orange-700 bg-orange-100 border-orange-300' };
        }
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const formatTime = (timeString) => {
        return timeString;
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
                                <p className="text-gray-600 mt-2">Outil de gestion efficace des demandes de rendez-vous</p>
                            </div>
                            <div className="flex items-center space-x-4">
                                <div className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="autoRefresh"
                                        checked={autoRefresh}
                                        onChange={(e) => setAutoRefresh(e.target.checked)}
                                        className="rounded border-gray-300"
                                    />
                                    <label htmlFor="autoRefresh" className="text-sm text-gray-600">
                                        Auto-refresh
                                    </label>
                                </div>
                                <SafeLink
                                href={route('admin.dashboard')}
                                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                ← Retour au tableau de bord
                                </SafeLink>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Statistiques rapides avec indicateurs visuels */}
                    <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
                        <div className="bg-white rounded-lg shadow p-4 text-center border-l-4 border-gray-400">
                            <div className="text-2xl font-bold text-gray-900">{stats.total}</div>
                            <div className="text-sm text-gray-600">Total</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center border-l-4 border-orange-400">
                            <div className="text-2xl font-bold text-orange-500">{stats.pending}</div>
                            <div className="text-sm text-gray-600">En attente</div>
                            {stats.pending > 0 && (
                                <div className="text-xs text-orange-600 mt-1">⚠️ Action requise</div>
                            )}
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center border-l-4 border-green-400">
                            <div className="text-2xl font-bold text-green-500">{stats.accepted}</div>
                            <div className="text-sm text-gray-600">Acceptés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center border-l-4 border-red-400">
                            <div className="text-2xl font-bold text-red-500">{stats.rejected}</div>
                            <div className="text-sm text-gray-600">Refusés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center border-l-4 border-gray-300">
                            <div className="text-2xl font-bold text-gray-400">{stats.canceled}</div>
                            <div className="text-sm text-gray-600">Annulés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center border-l-4 border-blue-400">
                            <div className="text-2xl font-bold text-blue-500">{stats.completed}</div>
                            <div className="text-sm text-gray-600">Terminés</div>
                        </div>
                    </div>

                    {/* Actions en lot */}
                    {selectedAppointments.length > 0 && (
                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-4">
                                    <span className="text-blue-800 font-medium">
                                        {selectedAppointments.length} rendez-vous sélectionné(s)
                                    </span>
                                    <button
                                        onClick={() => setShowBulkActionsModal(true)}
                                        className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700"
                                    >
                                        Actions en lot
                                    </button>
                                </div>
                                <button
                                    onClick={() => setSelectedAppointments([])}
                                    className="text-blue-600 hover:text-blue-800 text-sm"
                                >
                                    Annuler la sélection
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Filtres avancés */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-semibold text-gray-900">Filtres et recherche</h2>
                            <div className="flex items-center space-x-4">
                                <div className="flex items-center space-x-2">
                                    <button
                                        onClick={() => setViewMode('table')}
                                        className={`px-3 py-1 rounded-md text-sm font-medium ${
                                            viewMode === 'table' 
                                                ? 'bg-blue-100 text-blue-700' 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        }`}
                                    >
                                        Tableau
                                    </button>
                                    <button
                                        onClick={() => setViewMode('cards')}
                                        className={`px-3 py-1 rounded-md text-sm font-medium ${
                                            viewMode === 'cards' 
                                                ? 'bg-blue-100 text-blue-700' 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        }`}
                                    >
                                        Cartes
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
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
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Par page</label>
                                <select
                                    value={filterForm.data.per_page}
                                    onChange={(e) => filterForm.setData('per_page', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
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
                                    <option value="priority-desc">Priorité (haute → basse)</option>
                                    <option value="priority-asc">Priorité (basse → haute)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div className="flex space-x-3">
                            <PrimaryButton onClick={handleFilter}>
                                🔍 Appliquer les filtres
                            </PrimaryButton>
                            <SecondaryButton onClick={handleReset}>
                                🔄 Réinitialiser
                            </SecondaryButton>
                        </div>
                    </div>

                    {/* Vue tableau */}
                    {viewMode === 'table' && (
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                                <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Rendez-vous ({appointments.total})
                            </h2>
                                    <div className="flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            checked={selectedAppointments.length === appointments.data.length && appointments.data.length > 0}
                                            onChange={toggleAllAppointments}
                                            className="rounded border-gray-300"
                                        />
                                        <span className="text-sm text-gray-600">Sélectionner tout</span>
                                    </div>
                                </div>
                        </div>
                        
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedAppointments.length === appointments.data.length && appointments.data.length > 0}
                                                    onChange={toggleAllAppointments}
                                                    className="rounded border-gray-300"
                                                />
                                            </th>
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
                                            <tr key={appointment.id} className={`hover:bg-gray-50 ${
                                                appointment.status === 'pending' ? 'bg-orange-50' : ''
                                            }`}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedAppointments.includes(appointment.id)}
                                                        onChange={() => toggleAppointmentSelection(appointment.id)}
                                                        className="rounded border-gray-300"
                                                    />
                                                </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {appointment.name}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        {appointment.email}
                                                    </div>
                                                        <div className="text-xs text-gray-400">
                                                            {appointment.phone}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-900 max-w-xs truncate">
                                                    {appointment.subject}
                                                </div>
                                                    <div className="text-xs text-gray-500 mt-1">
                                                        Créé le {formatDate(appointment.created_at)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                        {formatDate(appointment.preferred_date)}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                        {formatTime(appointment.preferred_time)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full border ${getPriorityDisplay(appointment.priority).class}`}>
                                                        {getPriorityDisplay(appointment.priority).text}
                                                    </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full border ${getStatusDisplay(appointment.status).class}`}>
                                                        {getStatusDisplay(appointment.status).text}
                                                    </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex space-x-2">
                                                        <button
                                                            onClick={() => openDetailsModal(appointment)}
                                                            className="text-blue-600 hover:text-blue-900 flex items-center"
                                                        >
                                                            👁️ Voir
                                                            {appointment.attachments && appointment.attachments.length > 0 && (
                                                                <span className="ml-1 text-xs bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-full">
                                                                    📎 {appointment.attachments.length}
                                                                </span>
                                                            )}
                                                        </button>
                                                        
                                                        <button
                                                            onClick={() => openEditModal(appointment)}
                                                            className="text-purple-600 hover:text-purple-900"
                                                    >
                                                            ✏️ Modifier
                                                        </button>
                                                    
                                                    {appointment.status === 'pending' && (
                                                        <>
                                                            <button
                                                                onClick={() => handleAccept(appointment)}
                                                                className="text-green-600 hover:text-green-900"
                                                            >
                                                                    ✅ Accepter
                                                            </button>
                                                            <button
                                                                onClick={() => openRejectModal(appointment)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                    ❌ Refuser
                                                            </button>
                                                        </>
                                                    )}
                                                    
                                                    {appointment.status === 'accepted' && (
                                                        <>
                                                            <button
                                                                onClick={() => handleComplete(appointment)}
                                                                className="text-blue-600 hover:text-blue-900"
                                                            >
                                                                ✅ Terminer
                                                            </button>
                                                            <button
                                                                onClick={() => openCancelModal(appointment)}
                                                                className="text-gray-600 hover:text-gray-900"
                                                            >
                                                                🚫 Annuler
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

                            {/* Pagination améliorée */}
                        {appointments.links && (
                            <div className="px-6 py-3 border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Affichage de {appointments.from} à {appointments.to} sur {appointments.total} résultats
                                    </div>
                                    <div className="flex space-x-2">
                                        {appointments.links.map((link, index) => (
                                                link.url ? (
                                                    <SafeLink
                                                key={index}
                                                href={link.url}
                                                className={`px-3 py-2 text-sm rounded-md ${
                                                    link.active
                                                        ? 'bg-blue-500 text-white'
                                                        : 'bg-white text-gray-700 hover:bg-gray-50'
                                                        }`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                    />
                                                ) : (
                                                    <span
                                                        key={index}
                                                        className={`px-3 py-2 text-sm rounded-md opacity-50 cursor-not-allowed ${
                                                            link.active
                                                                ? 'bg-blue-500 text-white'
                                                                : 'bg-white text-gray-700'
                                                        }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                                )
                                        ))}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Vue cartes */}
                    {viewMode === 'cards' && (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {appointments.data.map((appointment) => (
                                <div key={appointment.id} className={`bg-white rounded-lg shadow-md border-l-4 ${
                                    appointment.status === 'pending' ? 'border-orange-400' :
                                    appointment.status === 'accepted' ? 'border-green-400' :
                                    appointment.status === 'rejected' ? 'border-red-400' :
                                    appointment.status === 'completed' ? 'border-blue-400' :
                                    'border-gray-400'
                                }`}>
                                    <div className="p-6">
                                        <div className="flex items-start justify-between mb-4">
                                            <div>
                                                <h3 className="text-lg font-semibold text-gray-900">
                                                    {appointment.name}
                                                </h3>
                                                <p className="text-sm text-gray-600">{appointment.email}</p>
                                                <p className="text-xs text-gray-500">{appointment.phone}</p>
                                            </div>
                                            <input
                                                type="checkbox"
                                                checked={selectedAppointments.includes(appointment.id)}
                                                onChange={() => toggleAppointmentSelection(appointment.id)}
                                                className="rounded border-gray-300"
                                            />
                                        </div>
                                        
                                        <div className="mb-4">
                                            <div className="flex items-center justify-between mb-2">
                                                <p className="text-sm text-gray-900 font-medium">
                                                    {appointment.subject}
                                                </p>
                                                {appointment.attachments && appointment.attachments.length > 0 && (
                                                    <span className="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                                                        📎 {appointment.attachments.length} fichier(s)
                                                    </span>
                                                )}
                                            </div>
                                            <p className="text-xs text-gray-600">
                                                {appointment.message && appointment.message.substring(0, 100)}...
                                            </p>
                                        </div>
                                        
                                        <div className="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <p className="text-xs text-gray-500">Date</p>
                                                <p className="text-sm font-medium">{formatDate(appointment.preferred_date)}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-gray-500">Heure</p>
                                                <p className="text-sm font-medium">{formatTime(appointment.preferred_time)}</p>
                                            </div>
                                        </div>
                                        
                                        <div className="flex items-center justify-between mb-4">
                                            <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full border ${getPriorityDisplay(appointment.priority).class}`}>
                                                {getPriorityDisplay(appointment.priority).text}
                                            </span>
                                            <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full border ${getStatusDisplay(appointment.status).class}`}>
                                                {getStatusDisplay(appointment.status).text}
                                            </span>
                                        </div>
                                        
                                        <div className="flex space-x-2">
                                            <button
                                                onClick={() => openDetailsModal(appointment)}
                                                className="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center justify-center"
                                            >
                                                👁️ Détails
                                                {appointment.attachments && appointment.attachments.length > 0 && (
                                                    <span className="ml-1 text-xs bg-blue-200 text-blue-800 px-1.5 py-0.5 rounded-full">
                                                        📎 {appointment.attachments.length}
                                                    </span>
                                                )}
                                            </button>
                                            
                                            <button
                                                onClick={() => openEditModal(appointment)}
                                                className="bg-purple-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-purple-700"
                                            >
                                                ✏️ Modifier
                                            </button>
                                            
                                            {appointment.status === 'pending' && (
                                                <div className="flex space-x-1">
                                                    <button
                                                        onClick={() => handleAccept(appointment)}
                                                        className="bg-green-600 text-white px-2 py-2 rounded-md text-sm hover:bg-green-700"
                                                        title="Accepter"
                                                    >
                                                        ✅
                                                    </button>
                                                    <button
                                                        onClick={() => openRejectModal(appointment)}
                                                        className="bg-red-600 text-white px-2 py-2 rounded-md text-sm hover:bg-red-700"
                                                        title="Refuser"
                                                    >
                                                        ❌
                                                    </button>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </main>
            </div>

            {/* Modal de détails */}
            <Modal show={showDetailsModal} onClose={() => setShowDetailsModal(false)} maxWidth="6xl">
                {selectedAppointment && (
                    <div className="p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-6">
                            Détails du rendez-vous - {selectedAppointment.name}
                        </h3>
                        
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div>
                                <h4 className="font-medium text-gray-900 mb-3">Informations du demandeur</h4>
                                <div className="space-y-3 text-sm">
                                    <p><span className="font-medium">Nom :</span> {selectedAppointment.name}</p>
                                    <p><span className="font-medium">Email :</span> {selectedAppointment.email}</p>
                                    <p><span className="font-medium">Téléphone :</span> {selectedAppointment.phone}</p>
                                    <p><span className="font-medium">Date de création :</span> {formatDate(selectedAppointment.created_at)}</p>
                                </div>
                            </div>
                            
                            <div>
                                <h4 className="font-medium text-gray-900 mb-3">Détails du rendez-vous</h4>
                                <div className="space-y-3 text-sm">
                                    <p><span className="font-medium">Objet :</span> {selectedAppointment.subject}</p>
                                    <p><span className="font-medium">Date souhaitée :</span> {formatDate(selectedAppointment.preferred_date)}</p>
                                    <p><span className="font-medium">Heure souhaitée :</span> {formatTime(selectedAppointment.preferred_time)}</p>
                                    <p><span className="font-medium">Priorité :</span> 
                                        <span className={`ml-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full border ${getPriorityDisplay(selectedAppointment.priority).class}`}>
                                            {getPriorityDisplay(selectedAppointment.priority).text}
                                        </span>
                                    </p>
                                    <p><span className="font-medium">Statut :</span> 
                                        <span className={`ml-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full border ${getStatusDisplay(selectedAppointment.status).class}`}>
                                            {getStatusDisplay(selectedAppointment.status).text}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <h4 className="font-medium text-gray-900 mb-3">Informations système</h4>
                                <div className="space-y-3 text-sm">
                                    <p><span className="font-medium">ID :</span> {selectedAppointment.id}</p>
                                    <p><span className="font-medium">Token :</span> <code className="text-xs bg-gray-100 px-1 rounded">{selectedAppointment.secure_token}</code></p>
                                    <p><span className="font-medium">IP :</span> {selectedAppointment.ip_address}</p>
                                    <p><span className="font-medium">Modifié le :</span> {formatDate(selectedAppointment.updated_at)}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                            {selectedAppointment.message && (
                                <div>
                                    <h4 className="font-medium text-gray-900 mb-3">Message du demandeur</h4>
                                    <div className="bg-gray-50 p-4 rounded-md">
                                        <p className="text-sm text-gray-700 whitespace-pre-wrap">{selectedAppointment.message}</p>
                                    </div>
                                </div>
                            )}
                            
                            {selectedAppointment.rejection_reason && (
                                <div>
                                    <h4 className="font-medium text-red-900 mb-3">Raison du refus</h4>
                                    <div className="bg-red-50 p-4 rounded-md">
                                        <p className="text-sm text-red-700">{selectedAppointment.rejection_reason}</p>
                                    </div>
                                </div>
                            )}
                            
                            {selectedAppointment.admin_notes && (
                                <div>
                                    <h4 className="font-medium text-gray-900 mb-3">Notes administratives</h4>
                                    <div className="bg-blue-50 p-4 rounded-md">
                                        <p className="text-sm text-blue-700">{selectedAppointment.admin_notes}</p>
                                    </div>
                                </div>
                            )}
                        </div>
                        
                        {/* Pièces jointes */}
                        {selectedAppointment.attachments && selectedAppointment.attachments.length > 0 && (
                            <div className="mt-8">
                                <h4 className="font-medium text-gray-900 mb-4">Pièces jointes ({selectedAppointment.attachments.length})</h4>
                                <div className="bg-gray-50 rounded-lg p-4">
                                    <AttachmentViewer 
                                        attachments={selectedAppointment.attachments} 
                                        appointmentId={selectedAppointment.id} 
                                    />
                                </div>
                            </div>
                        )}
                        
                        <div className="mt-6 flex justify-end space-x-3">
                            <SecondaryButton onClick={() => setShowDetailsModal(false)}>
                                Fermer
                            </SecondaryButton>
                        </div>
                    </div>
                )}
            </Modal>

            {/* Modal de modification */}
            <Modal show={showEditModal} onClose={() => setShowEditModal(false)} maxWidth="4xl">
                {selectedAppointment && (
                    <div className="p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-6">
                            Modifier le rendez-vous - {selectedAppointment.name}
                        </h3>
                        
                        <form onSubmit={(e) => { e.preventDefault(); handleEdit(); }}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Informations du demandeur */}
                                <div className="space-y-4">
                                    <h4 className="font-medium text-gray-900 border-b pb-2">Informations du demandeur</h4>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                                        <input
                                            type="text"
                                            value={editForm.data.name}
                                            onChange={(e) => editForm.setData('name', e.target.value)}
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        />
                                        {editForm.errors.name && (
                                            <p className="text-red-600 text-xs mt-1">{editForm.errors.name}</p>
                                        )}
                                    </div>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                        <input
                                            type="email"
                                            value={editForm.data.email}
                                            onChange={(e) => editForm.setData('email', e.target.value)}
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        />
                                        {editForm.errors.email && (
                                            <p className="text-red-600 text-xs mt-1">{editForm.errors.email}</p>
                                        )}
                                    </div>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                                        <input
                                            type="tel"
                                            value={editForm.data.phone}
                                            onChange={(e) => editForm.setData('phone', e.target.value)}
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        />
                                        {editForm.errors.phone && (
                                            <p className="text-red-600 text-xs mt-1">{editForm.errors.phone}</p>
                                        )}
                                    </div>
                                </div>
                                
                                {/* Détails du rendez-vous */}
                                <div className="space-y-4">
                                    <h4 className="font-medium text-gray-900 border-b pb-2">Détails du rendez-vous</h4>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Objet *</label>
                                        <input
                                            type="text"
                                            value={editForm.data.subject}
                                            onChange={(e) => editForm.setData('subject', e.target.value)}
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        />
                                        {editForm.errors.subject && (
                                            <p className="text-red-600 text-xs mt-1">{editForm.errors.subject}</p>
                                        )}
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Date souhaitée *</label>
                                            <input
                                                type="date"
                                                value={editForm.data.preferred_date}
                                                onChange={(e) => editForm.setData('preferred_date', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                            />
                                            {editForm.errors.preferred_date && (
                                                <p className="text-red-600 text-xs mt-1">{editForm.errors.preferred_date}</p>
                                            )}
                                        </div>
                                        
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Heure souhaitée *</label>
                                            <input
                                                type="time"
                                                value={editForm.data.preferred_time}
                                                onChange={(e) => editForm.setData('preferred_time', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                            />
                                            {editForm.errors.preferred_time && (
                                                <p className="text-red-600 text-xs mt-1">{editForm.errors.preferred_time}</p>
                                            )}
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Priorité *</label>
                                            <select
                                                value={editForm.data.priority}
                                                onChange={(e) => editForm.setData('priority', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                            >
                                                <option value="normal">NORMALE</option>
                                                <option value="urgent">URGENT</option>
                                                <option value="official">OFFICIEL</option>
                                            </select>
                                            {editForm.errors.priority && (
                                                <p className="text-red-600 text-xs mt-1">{editForm.errors.priority}</p>
                                            )}
                                        </div>
                                        
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                                            <select
                                                value={editForm.data.status}
                                                onChange={(e) => editForm.setData('status', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                            >
                                                <option value="pending">EN ATTENTE</option>
                                                <option value="accepted">ACCEPTÉ</option>
                                                <option value="rejected">REFUSÉ</option>
                                                <option value="canceled">ANNULÉ</option>
                                                <option value="completed">TERMINÉ</option>
                                                <option value="expired">EXPIRÉ</option>
                                            </select>
                                            {editForm.errors.status && (
                                                <p className="text-red-600 text-xs mt-1">{editForm.errors.status}</p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Message et notes */}
                            <div className="mt-6 space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Message du demandeur</label>
                                    <textarea
                                        value={editForm.data.message}
                                        onChange={(e) => editForm.setData('message', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows="3"
                                        placeholder="Message du demandeur..."
                                    />
                                    {editForm.errors.message && (
                                        <p className="text-red-600 text-xs mt-1">{editForm.errors.message}</p>
                                    )}
                                </div>
                                
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Notes administratives</label>
                                    <textarea
                                        value={editForm.data.admin_notes}
                                        onChange={(e) => editForm.setData('admin_notes', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows="3"
                                        placeholder="Notes administratives..."
                                    />
                                    {editForm.errors.admin_notes && (
                                        <p className="text-red-600 text-xs mt-1">{editForm.errors.admin_notes}</p>
                                    )}
                                </div>
            </div>
                            
                            <div className="mt-6 flex justify-end space-x-3">
                                <SecondaryButton type="button" onClick={() => setShowEditModal(false)}>
                                    Annuler
                                </SecondaryButton>
                                <PrimaryButton type="submit" disabled={editForm.processing}>
                                    {editForm.processing ? 'Enregistrement...' : 'Enregistrer les modifications'}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                )}
            </Modal>

            {/* Modal de refus */}
            <Modal show={showRejectModal} onClose={() => setShowRejectModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Refuser le rendez-vous</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Veuillez indiquer la raison du refus pour le rendez-vous de <strong>{selectedAppointment?.name}</strong>.
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
                        Veuillez indiquer la raison de l'annulation pour le rendez-vous de <strong>{selectedAppointment?.name}</strong>.
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

            {/* Modal d'actions en lot */}
            <Modal show={showBulkActionsModal} onClose={() => setShowBulkActionsModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                        Actions en lot ({selectedAppointments.length} rendez-vous)
                    </h3>
                    
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Action à effectuer</label>
                            <select
                                value={bulkAction}
                                onChange={(e) => setBulkAction(e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Sélectionner une action</option>
                                <option value="accept">Accepter</option>
                                <option value="reject">Refuser</option>
                                <option value="cancel">Annuler</option>
                                <option value="complete">Marquer comme terminé</option>
                            </select>
                        </div>
                        
                        {(bulkAction === 'reject' || bulkAction === 'cancel') && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    {bulkAction === 'reject' ? 'Raison du refus' : 'Raison de l\'annulation'}
                                </label>
                                <textarea
                                    value={bulkReason}
                                    onChange={(e) => setBulkReason(e.target.value)}
                                    placeholder={`Raison du ${bulkAction === 'reject' ? 'refus' : 'annulation'}...`}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    rows="3"
                                />
                            </div>
                        )}
                    </div>
                    
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowBulkActionsModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleBulkAction}>
                            Exécuter l'action
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal d'acceptation améliorée */}
            <Modal show={showAcceptModal} onClose={() => !isAccepting && setShowAcceptModal(false)} maxWidth="md">
                <div className="p-6">
                    <div className="text-center">
                        {!isAccepting ? (
                            <>
                                <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                    <svg className="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Accepter le rendez-vous
                                </h3>
                                <p className="text-sm text-gray-600 mb-6">
                                    Êtes-vous sûr de vouloir accepter le rendez-vous de <strong>{acceptingAppointment?.name}</strong> ?
                                </p>
                                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                    <h4 className="text-sm font-medium text-blue-900 mb-2">Détails du rendez-vous :</h4>
                                    <div className="text-sm text-blue-800 space-y-1">
                                        <p><strong>Objet :</strong> {acceptingAppointment?.subject}</p>
                                        <p><strong>Date :</strong> {acceptingAppointment?.preferred_date}</p>
                                        <p><strong>Heure :</strong> {acceptingAppointment?.preferred_time}</p>
                                        <p><strong>Priorité :</strong> {acceptingAppointment?.formatted_priority}</p>
                                    </div>
                                </div>
                                <p className="text-xs text-gray-500 mb-6">
                                    Un email de confirmation sera automatiquement envoyé au demandeur.
                                </p>
                                <div className="flex justify-end space-x-3">
                                    <SecondaryButton 
                                        onClick={() => setShowAcceptModal(false)}
                                        disabled={isAccepting}
                                    >
                                        Annuler
                                    </SecondaryButton>
                                    <PrimaryButton 
                                        onClick={confirmAccept}
                                        disabled={isAccepting}
                                        className="bg-green-600 hover:bg-green-700 focus:ring-green-500"
                                    >
                                        Accepter le rendez-vous
                                    </PrimaryButton>
                                </div>
                            </>
                        ) : (
                            <>
                                <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                                    <svg className="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Traitement en cours...
                                </h3>
                                <div className="space-y-3">
                                    <div className="flex items-center justify-center space-x-2">
                                        <div className="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
                                        <span className="text-sm text-gray-600">Mise à jour du statut</span>
                                    </div>
                                    <div className="flex items-center justify-center space-x-2">
                                        <div className="w-2 h-2 bg-blue-600 rounded-full animate-pulse" style={{animationDelay: '0.2s'}}></div>
                                        <span className="text-sm text-gray-600">Envoi de l'email de confirmation</span>
                                    </div>
                                    <div className="flex items-center justify-center space-x-2">
                                        <div className="w-2 h-2 bg-blue-600 rounded-full animate-pulse" style={{animationDelay: '0.4s'}}></div>
                                        <span className="text-sm text-gray-600">Finalisation...</span>
                                    </div>
                                </div>
                                <p className="text-xs text-gray-500 mt-4">
                                    Veuillez patienter, cette opération peut prendre quelques secondes.
                                </p>
                            </>
                        )}
                    </div>
                </div>
            </Modal>
        </>
    );
} 