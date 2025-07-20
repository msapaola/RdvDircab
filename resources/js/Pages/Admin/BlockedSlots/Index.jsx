import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';
import DashboardMenu from '@/Components/DashboardMenu';

export default function Index({ blockedSlots, stats, filters }) {
    // Vérification défensive des props
    const safeBlockedSlots = blockedSlots || { data: [], total: 0, from: 0, to: 0, links: [] };
    const safeStats = stats || { total: 0, this_month: 0, next_month: 0 };
    const safeFilters = filters || {};
    
    // Fonctions utilitaires (déclarées avant utilisation)
    const formatTime = (time) => {
        return time.substring(0, 5); // Afficher HH:MM
    };

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const formatDateForInput = (date) => {
        if (!date) return '';
        return new Date(date).toISOString().split('T')[0];
    };
    
    // États
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [selectedSlot, setSelectedSlot] = useState(null);

    const filterForm = useForm({
        date_from: safeFilters.date_from || '',
        date_to: safeFilters.date_to || '',
        reason: safeFilters.reason || '',
        sort_by: safeFilters.sort_by || 'date',
        sort_order: safeFilters.sort_order || 'asc',
    });

    const createForm = useForm({
        date: formatDateForInput(new Date()), // Date d'aujourd'hui par défaut
        start_time: '',
        end_time: '',
        reason: '',
        recurring: false,
        recurring_until: '',
    });

    const editForm = useForm({
        date: '',
        start_time: '',
        end_time: '',
        reason: '',
        is_recurring: false,
        recurrence_type: 'weekly',
        recurrence_end_date: '',
    });

    const handleFilter = () => {
        filterForm.get(route('admin.blocked-slots.index'));
    };

    const handleReset = () => {
        filterForm.reset();
        router.get(route('admin.blocked-slots.index'));
    };

    const handleCreate = () => {
        createForm.post(route('admin.blocked-slots.store'), {
            onSuccess: () => {
                setShowCreateModal(false);
                createForm.reset();
                // Réinitialiser avec des valeurs sûres
                createForm.setData({
                    date: formatDateForInput(new Date()),
                    start_time: '',
                    end_time: '',
                    reason: '',
                    recurring: false,
                    recurring_until: '',
                });
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

    const handleDelete = () => {
        router.delete(route('admin.blocked-slots.destroy', selectedSlot.id), {
            onSuccess: () => {
                setShowDeleteModal(false);
                setSelectedSlot(null);
            },
        });
    };

    const openEditModal = (slot) => {
        setSelectedSlot(slot);
        
        editForm.setData({
            date: formatDateForInput(slot.date),
            start_time: slot.start_time,
            end_time: slot.end_time,
            reason: slot.reason,
            is_recurring: slot.is_recurring || false,
            recurrence_type: slot.recurrence_type || 'weekly',
            recurrence_end_date: formatDateForInput(slot.recurrence_end_date),
        });
        setShowEditModal(true);
    };

    const openDeleteModal = (slot) => {
        setSelectedSlot(slot);
        setShowDeleteModal(true);
    };

    return (
        <>
            <Head title="Gestion des créneaux bloqués - Administration" />
            
            <div className="min-h-screen bg-gray-50">
                <DashboardMenu />
                
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <div className="flex justify-between items-center">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">Gestion des créneaux bloqués</h1>
                                <p className="text-gray-600 mt-2">Bloquer des créneaux pour les rendez-vous</p>
                            </div>
                            <div className="flex space-x-3">
                                <Link
                                    href={route('admin.dashboard')}
                                    className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    ← Retour au tableau de bord
                                </Link>
                                <PrimaryButton onClick={() => setShowCreateModal(true)}>
                                    Nouveau créneau bloqué
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Statistiques rapides */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-gray-900">{safeStats.total}</div>
                            <div className="text-sm text-gray-600">Total créneaux bloqués</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-orange-500">{safeStats.this_month}</div>
                            <div className="text-sm text-gray-600">Ce mois-ci</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4 text-center">
                            <div className="text-xl font-bold text-blue-500">{safeStats.next_month}</div>
                            <div className="text-sm text-gray-600">Mois prochain</div>
                        </div>
                    </div>

                    {/* Filtres */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Filtres</h2>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
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
                                <label className="block text-sm font-medium text-gray-700 mb-1">Raison</label>
                                <input
                                    type="text"
                                    value={filterForm.data.reason}
                                    onChange={(e) => filterForm.setData('reason', e.target.value)}
                                    placeholder="Rechercher par raison..."
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
                                    <option value="date-asc">Date (croissant)</option>
                                    <option value="date-desc">Date (décroissant)</option>
                                    <option value="start_time-asc">Heure début (croissant)</option>
                                    <option value="start_time-desc">Heure début (décroissant)</option>
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

                    {/* Liste des créneaux bloqués */}
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Créneaux bloqués ({safeBlockedSlots.total})
                            </h2>
                        </div>
                        
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Heures
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Raison
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Créé par
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Récurrence
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {safeBlockedSlots.data && safeBlockedSlots.data.length > 0 ? (
                                        safeBlockedSlots.data.map((slot) => (
                                        <tr key={slot.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {formatDate(slot.date)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {formatTime(slot.start_time)} - {formatTime(slot.end_time)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-900 max-w-xs truncate">
                                                    {slot.reason}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {slot.created_by_user?.name || 'Système'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {slot.is_recurring ? (
                                                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        🔄 {slot.recurrence_type === 'daily' ? 'Quotidien' : 
                                                            slot.recurrence_type === 'weekly' ? 'Hebdomadaire' : 
                                                            slot.recurrence_type === 'monthly' ? 'Mensuel' : 'Récurrent'}
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        ⚪ Unique
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex space-x-3">
                                                    <button
                                                        onClick={() => openEditModal(slot)}
                                                        className="text-blue-600 hover:text-blue-900"
                                                    >
                                                        Modifier
                                                    </button>
                                                    <button
                                                        onClick={() => openDeleteModal(slot)}
                                                        className="text-red-600 hover:text-red-900"
                                                    >
                                                        Supprimer
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                    ) : (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-4 text-center text-gray-500">
                                                Aucun créneau bloqué trouvé
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {safeBlockedSlots.links && safeBlockedSlots.links.length > 0 && (
                            <div className="px-6 py-3 border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Affichage de {blockedSlots.from || 0} à {blockedSlots.to || 0} sur {blockedSlots.total || 0} résultats
                                    </div>
                                    <div className="flex space-x-2">
                                        {safeBlockedSlots.links.map((link, index) => {
                                            // Vérifier que link est un objet valide
                                            if (!link || typeof link !== 'object') {
                                                return null;
                                            }
                                            
                                            // Déterminer le texte à afficher
                                            let label = '';
                                            if (link.label) {
                                                // Nettoyer le HTML et extraire le texte
                                                label = link.label.replace(/<[^>]*>/g, '');
                                            }
                                            
                                            // Si pas de label, utiliser des icônes pour précédent/suivant
                                            if (!label) {
                                                if (index === 0) label = '«';
                                                else if (index === safeBlockedSlots.links.length - 1) label = '»';
                                                else label = (index).toString();
                                            }
                                            
                                            return (
                                                <Link
                                                    key={index}
                                                    href={link.url || '#'}
                                                    className={`px-3 py-2 text-sm rounded-md ${
                                                        link.active
                                                            ? 'bg-blue-500 text-white'
                                                            : 'bg-white text-gray-700 hover:bg-gray-50'
                                                    } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                                >
                                                    {label}
                                                </Link>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </main>
            </div>

            {/* Modal de création */}
            <Modal show={showCreateModal} onClose={() => setShowCreateModal(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Nouveau créneau bloqué</h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input
                                type="date"
                                value={createForm.data.date}
                                onChange={(e) => createForm.setData('date', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {createForm.errors.date && (
                                <p className="text-red-500 text-sm mt-1">{createForm.errors.date}</p>
                            )}
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                                <input
                                    type="time"
                                    value={createForm.data.start_time}
                                    onChange={(e) => createForm.setData('start_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                {createForm.errors.start_time && (
                                    <p className="text-red-500 text-sm mt-1">{createForm.errors.start_time}</p>
                                )}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
                                <input
                                    type="time"
                                    value={createForm.data.end_time}
                                    onChange={(e) => createForm.setData('end_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                {createForm.errors.end_time && (
                                    <p className="text-red-500 text-sm mt-1">{createForm.errors.end_time}</p>
                                )}
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Raison</label>
                            <input
                                type="text"
                                value={createForm.data.reason}
                                onChange={(e) => createForm.setData('reason', e.target.value)}
                                placeholder="Ex: Réunion, Congé, etc."
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {createForm.errors.reason && (
                                <p className="text-red-500 text-sm mt-1">{createForm.errors.reason}</p>
                            )}
                        </div>
                        <div className="flex items-center space-x-3">
                            <input
                                type="checkbox"
                                id="recurring"
                                checked={createForm.data.recurring}
                                onChange={(e) => createForm.setData('recurring', e.target.checked)}
                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label htmlFor="recurring" className="text-sm font-medium text-gray-700">
                                Créneau récurrent (hebdomadaire)
                            </label>
                        </div>
                        {createForm.data.recurring && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Jusqu'au</label>
                                <input
                                    type="date"
                                    value={createForm.data.recurring_until || formatDateForInput(new Date())}
                                    onChange={(e) => createForm.setData('recurring_until', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                {createForm.errors.recurring_until && (
                                    <p className="text-red-500 text-sm mt-1">{createForm.errors.recurring_until}</p>
                                )}
                            </div>
                        )}
                    </div>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowCreateModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleCreate} disabled={createForm.processing}>
                            {createForm.processing ? 'Création...' : 'Créer'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal d'édition */}
            <Modal show={showEditModal} onClose={() => setShowEditModal(false)} maxWidth="md">
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
                                required
                            />
                            {editForm.errors.date && (
                                <p className="text-red-500 text-sm mt-1">{editForm.errors.date}</p>
                            )}
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                                <input
                                    type="time"
                                    value={editForm.data.start_time}
                                    onChange={(e) => editForm.setData('start_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                {editForm.errors.start_time && (
                                    <p className="text-red-500 text-sm mt-1">{editForm.errors.start_time}</p>
                                )}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
                                <input
                                    type="time"
                                    value={editForm.data.end_time}
                                    onChange={(e) => editForm.setData('end_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                {editForm.errors.end_time && (
                                    <p className="text-red-500 text-sm mt-1">{editForm.errors.end_time}</p>
                                )}
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Raison</label>
                            <input
                                type="text"
                                value={editForm.data.reason}
                                onChange={(e) => editForm.setData('reason', e.target.value)}
                                placeholder="Ex: Réunion, Congé, etc."
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            />
                            {editForm.errors.reason && (
                                <p className="text-red-500 text-sm mt-1">{editForm.errors.reason}</p>
                            )}
                        </div>
                        
                        <div className="flex items-center space-x-3">
                            <input
                                type="checkbox"
                                id="edit_recurring"
                                checked={editForm.data.is_recurring}
                                onChange={(e) => editForm.setData('is_recurring', e.target.checked)}
                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label htmlFor="edit_recurring" className="text-sm font-medium text-gray-700">
                                Créneau récurrent
                            </label>
                        </div>
                        
                        {editForm.data.is_recurring && (
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Type de récurrence</label>
                                    <select
                                        value={editForm.data.recurrence_type}
                                        onChange={(e) => editForm.setData('recurrence_type', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="daily">Quotidien</option>
                                        <option value="weekly">Hebdomadaire</option>
                                        <option value="monthly">Mensuel</option>
                                    </select>
                                    {editForm.errors.recurrence_type && (
                                        <p className="text-red-500 text-sm mt-1">{editForm.errors.recurrence_type}</p>
                                    )}
                                </div>
                                
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Jusqu'au</label>
                                    <input
                                        type="date"
                                        value={editForm.data.recurrence_end_date}
                                        onChange={(e) => editForm.setData('recurrence_end_date', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    {editForm.errors.recurrence_end_date && (
                                        <p className="text-red-500 text-sm mt-1">{editForm.errors.recurrence_end_date}</p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowEditModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleEdit} disabled={editForm.processing}>
                            {editForm.processing ? 'Modification...' : 'Modifier'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal de suppression */}
            <Modal show={showDeleteModal} onClose={() => setShowDeleteModal(false)} maxWidth="sm">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Supprimer le créneau bloqué</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Êtes-vous sûr de vouloir supprimer le créneau bloqué du <strong>{selectedSlot && formatDate(selectedSlot.date)}</strong> ? 
                        Cette action est irréversible.
                    </p>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowDeleteModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleDelete} className="bg-red-600 hover:bg-red-700">
                            Supprimer
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </>
    );
} 