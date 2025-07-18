import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/UI/Modal';

export default function Settings({ auth, settings }) {
    const [showBackupModal, setShowBackupModal] = useState(false);
    const [showRestoreModal, setShowRestoreModal] = useState(false);

    const settingsForm = useForm({
        site_name: settings.site_name || 'Cabinet du Gouverneur',
        site_description: settings.site_description || '',
        contact_email: settings.contact_email || '',
        contact_phone: settings.contact_phone || '',
        max_appointments_per_day: settings.max_appointments_per_day || 20,
        appointment_duration: settings.appointment_duration || 30,
        auto_expire_hours: settings.auto_expire_hours || 48,
        enable_notifications: settings.enable_notifications || true,
        maintenance_mode: settings.maintenance_mode || false,
    });

    const hoursForm = useForm({
        monday_start: settings.hours?.monday?.start || '08:00',
        monday_end: settings.hours?.monday?.end || '17:00',
        tuesday_start: settings.hours?.tuesday?.start || '08:00',
        tuesday_end: settings.hours?.tuesday?.end || '17:00',
        wednesday_start: settings.hours?.wednesday?.start || '08:00',
        wednesday_end: settings.hours?.wednesday?.end || '17:00',
        thursday_start: settings.hours?.thursday?.start || '08:00',
        thursday_end: settings.hours?.thursday?.end || '17:00',
        friday_start: settings.hours?.friday?.start || '08:00',
        friday_end: settings.hours?.friday?.end || '17:00',
        saturday_start: settings.hours?.saturday?.start || '08:00',
        saturday_end: settings.hours?.saturday?.end || '12:00',
        sunday_start: settings.hours?.sunday?.start || '',
        sunday_end: settings.hours?.sunday?.end || '',
    });

    const handleSaveSettings = () => {
        settingsForm.patch(route('admin.settings.update'), {
            onSuccess: () => {
                // Afficher un message de succès
            },
        });
    };

    const handleSaveHours = () => {
        hoursForm.patch(route('admin.settings.hours'), {
            onSuccess: () => {
                // Afficher un message de succès
            },
        });
    };

    const handleBackup = () => {
        // Logique de sauvegarde
        setShowBackupModal(false);
    };

    const handleRestore = () => {
        // Logique de restauration
        setShowRestoreModal(false);
    };

    const days = [
        { key: 'monday', label: 'Lundi' },
        { key: 'tuesday', label: 'Mardi' },
        { key: 'wednesday', label: 'Mercredi' },
        { key: 'thursday', label: 'Jeudi' },
        { key: 'friday', label: 'Vendredi' },
        { key: 'saturday', label: 'Samedi' },
        { key: 'sunday', label: 'Dimanche' },
    ];

    return (
        <AdminLayout user={auth.user}>
            <Head title="Paramètres - Administration" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* En-tête */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Paramètres</h1>
                        <p className="text-gray-600 mt-2">Configuration du système de gestion des rendez-vous</p>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Paramètres généraux */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Paramètres du site */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Paramètres du site</h2>
                                <div className="space-y-4">
                                        <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Nom du site
                                        </label>
                                        <input
                                                type="text"
                                            value={settingsForm.data.site_name}
                                            onChange={(e) => settingsForm.setData('site_name', e.target.value)}
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Description
                                        </label>
                                        <textarea
                                            value={settingsForm.data.site_description}
                                            onChange={(e) => settingsForm.setData('site_description', e.target.value)}
                                            rows="3"
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Email de contact
                                            </label>
                                            <input
                                                type="email"
                                                value={settingsForm.data.contact_email}
                                                onChange={(e) => settingsForm.setData('contact_email', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Téléphone de contact
                                            </label>
                                            <input
                                                type="text"
                                                value={settingsForm.data.contact_phone}
                                                onChange={(e) => settingsForm.setData('contact_phone', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                                        </div>

                            {/* Paramètres des rendez-vous */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Paramètres des rendez-vous</h2>
                                <div className="space-y-4">
                                    <div className="grid grid-cols-3 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                RDV max/jour
                                            </label>
                                            <input
                                                type="number"
                                                value={settingsForm.data.max_appointments_per_day}
                                                onChange={(e) => settingsForm.setData('max_appointments_per_day', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Durée RDV (min)
                                            </label>
                                            <input
                                                type="number"
                                                value={settingsForm.data.appointment_duration}
                                                onChange={(e) => settingsForm.setData('appointment_duration', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Expiration (heures)
                                            </label>
                                            <input
                                                type="number"
                                                value={settingsForm.data.auto_expire_hours}
                                                onChange={(e) => settingsForm.setData('auto_expire_hours', e.target.value)}
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            />
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="flex items-center">
                                            <input
                                                type="checkbox"
                                                id="enable_notifications"
                                                checked={settingsForm.data.enable_notifications}
                                                onChange={(e) => settingsForm.setData('enable_notifications', e.target.checked)}
                                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <label htmlFor="enable_notifications" className="ml-2 text-sm text-gray-700">
                                                Activer les notifications
                                            </label>
                                        </div>
                                        <div className="flex items-center">
                                            <input
                                                type="checkbox"
                                                id="maintenance_mode"
                                                checked={settingsForm.data.maintenance_mode}
                                                onChange={(e) => settingsForm.setData('maintenance_mode', e.target.checked)}
                                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <label htmlFor="maintenance_mode" className="ml-2 text-sm text-gray-700">
                                                Mode maintenance
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Horaires d'ouverture */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Horaires d'ouverture</h2>
                                <div className="space-y-4">
                                    {days.map((day) => (
                                        <div key={day.key} className="flex items-center space-x-4">
                                            <div className="w-24 text-sm font-medium text-gray-700">
                                                {day.label}
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <input
                                                    type="time"
                                                    value={hoursForm.data[`${day.key}_start`]}
                                                    onChange={(e) => hoursForm.setData(`${day.key}_start`, e.target.value)}
                                                    className="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                />
                                                <span className="text-gray-500">à</span>
                                                <input
                                                    type="time"
                                                    value={hoursForm.data[`${day.key}_end`]}
                                                    onChange={(e) => hoursForm.setData(`${day.key}_end`, e.target.value)}
                                                    className="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                />
                                            </div>
                                        </div>
                                    ))}
                                        </div>
                                    </div>

                            {/* Boutons d'action */}
                            <div className="flex space-x-4">
                                <PrimaryButton onClick={handleSaveSettings}>
                                    Sauvegarder les paramètres
                                </PrimaryButton>
                                <PrimaryButton onClick={handleSaveHours}>
                                    Sauvegarder les horaires
                                        </PrimaryButton>
                            </div>
                        </div>

                        {/* Actions système */}
                        <div className="space-y-6">
                            {/* Actions rapides */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Actions système</h2>
                                <div className="space-y-3">
                                    <button
                                        onClick={() => setShowBackupModal(true)}
                                        className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
                                    >
                                        💾 Créer une sauvegarde
                                    </button>
                                    <button
                                        onClick={() => setShowRestoreModal(true)}
                                        className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
                                    >
                                        🔄 Restaurer une sauvegarde
                                    </button>
                                    <button
                                        onClick={() => window.location.reload()}
                                        className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
                                    >
                                        🔄 Vider le cache
                                    </button>
                                </div>
                            </div>

                            {/* Informations système */}
                            <div className="bg-white rounded-lg shadow p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Informations système</h2>
                                <div className="space-y-3 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Version PHP:</span>
                                        <span className="font-medium">8.2.28</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Version Laravel:</span>
                                        <span className="font-medium">12.12.0</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Base de données:</span>
                                        <span className="font-medium">MySQL 8.0</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Espace disque:</span>
                                        <span className="font-medium">2.5 GB / 10 GB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal de sauvegarde */}
            <Modal show={showBackupModal} onClose={() => setShowBackupModal(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Créer une sauvegarde</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Cette action va créer une sauvegarde complète de la base de données et des fichiers.
                    </p>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowBackupModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleBackup}>
                            Créer la sauvegarde
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* Modal de restauration */}
            <Modal show={showRestoreModal} onClose={() => setShowRestoreModal(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Restaurer une sauvegarde</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        Attention : Cette action va remplacer toutes les données actuelles par celles de la sauvegarde.
                    </p>
                    <div className="mt-6 flex justify-end space-x-3">
                        <SecondaryButton onClick={() => setShowRestoreModal(false)}>
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton onClick={handleRestore} className="bg-red-600 hover:bg-red-700">
                            Restaurer
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </AdminLayout>
    );
} 