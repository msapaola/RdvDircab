import { Head, useForm } from '@inertiajs/react';
import DashboardMenu from '@/Components/DashboardMenu';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useState } from 'react';

export default function Settings({ settings, businessHours, workingDays }) {
    const [activeTab, setActiveTab] = useState('general');

    const { data, setData, patch, processing, errors } = useForm({
        business_name: settings.business_name || '',
        business_email: settings.business_email || '',
        business_phone: settings.business_phone || '',
        business_address: settings.business_address || '',
        max_appointments_per_day: settings.max_appointments_per_day || 20,
        appointment_duration: settings.appointment_duration || 30,
        advance_booking_days: settings.advance_booking_days || 30,
        auto_expire_days: settings.auto_expire_days || 7,
        enable_email_notifications: settings.enable_email_notifications || true,
        enable_sms_notifications: settings.enable_sms_notifications || false,
    });

    const { data: hoursData, setData: setHoursData, patch: patchHours } = useForm({
        start_time: businessHours.start_time || '08:00',
        end_time: businessHours.end_time || '17:00',
        lunch_start: businessHours.lunch_start || '12:00',
        lunch_end: businessHours.lunch_end || '14:00',
        working_days: workingDays || [1, 2, 3, 4, 5], // Lundi à Vendredi
    });

    const submitGeneral = (e) => {
        e.preventDefault();
        patch(route('admin.settings.update'));
    };

    const submitHours = (e) => {
        e.preventDefault();
        patchHours(route('admin.settings.hours'));
    };

    const toggleWorkingDay = (day) => {
        const newDays = hoursData.working_days.includes(day)
            ? hoursData.working_days.filter(d => d !== day)
            : [...hoursData.working_days, day];
        setHoursData('working_days', newDays);
    };

    const dayNames = {
        1: 'Lundi',
        2: 'Mardi', 
        3: 'Mercredi',
        4: 'Jeudi',
        5: 'Vendredi',
        6: 'Samedi',
        0: 'Dimanche'
    };

    return (
        <>
            <Head title="Paramètres" />
            <div className="min-h-screen bg-gray-50">
                <DashboardMenu />
                
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <h1 className="text-3xl font-bold text-gray-900">Paramètres</h1>
                        <p className="text-gray-600 mt-2">Configuration du système</p>
                    </div>
                </header>

                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Onglets */}
                    <div className="mb-6 border-b border-gray-200">
                        <nav className="-mb-px flex space-x-8">
                            <button
                                onClick={() => setActiveTab('general')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'general'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Général
                            </button>
                            <button
                                onClick={() => setActiveTab('hours')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'hours'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Horaires
                            </button>
                            <button
                                onClick={() => setActiveTab('notifications')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'notifications'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Notifications
                            </button>
                        </nav>
                    </div>

                    {/* Onglet Général */}
                    {activeTab === 'general' && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Paramètres généraux</h3>
                                
                                <form onSubmit={submitGeneral} className="space-y-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <InputLabel htmlFor="business_name" value="Nom de l'établissement" />
                                            <TextInput
                                                id="business_name"
                                                type="text"
                                                name="business_name"
                                                value={data.business_name}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('business_name', e.target.value)}
                                            />
                                            <InputError message={errors.business_name} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="business_email" value="Email de contact" />
                                            <TextInput
                                                id="business_email"
                                                type="email"
                                                name="business_email"
                                                value={data.business_email}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('business_email', e.target.value)}
                                            />
                                            <InputError message={errors.business_email} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="business_phone" value="Téléphone" />
                                            <TextInput
                                                id="business_phone"
                                                type="text"
                                                name="business_phone"
                                                value={data.business_phone}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('business_phone', e.target.value)}
                                            />
                                            <InputError message={errors.business_phone} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="max_appointments_per_day" value="RDV max par jour" />
                                            <TextInput
                                                id="max_appointments_per_day"
                                                type="number"
                                                name="max_appointments_per_day"
                                                value={data.max_appointments_per_day}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('max_appointments_per_day', e.target.value)}
                                            />
                                            <InputError message={errors.max_appointments_per_day} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="appointment_duration" value="Durée RDV (minutes)" />
                                            <TextInput
                                                id="appointment_duration"
                                                type="number"
                                                name="appointment_duration"
                                                value={data.appointment_duration}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('appointment_duration', e.target.value)}
                                            />
                                            <InputError message={errors.appointment_duration} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="advance_booking_days" value="Réservation à l'avance (jours)" />
                                            <TextInput
                                                id="advance_booking_days"
                                                type="number"
                                                name="advance_booking_days"
                                                value={data.advance_booking_days}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('advance_booking_days', e.target.value)}
                                            />
                                            <InputError message={errors.advance_booking_days} className="mt-2" />
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="business_address" value="Adresse" />
                                        <textarea
                                            id="business_address"
                                            name="business_address"
                                            value={data.business_address}
                                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            rows="3"
                                            onChange={(e) => setData('business_address', e.target.value)}
                                        />
                                        <InputError message={errors.business_address} className="mt-2" />
                                    </div>

                                    <div className="flex justify-end">
                                        <PrimaryButton disabled={processing}>
                                            Sauvegarder
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}

                    {/* Onglet Horaires */}
                    {activeTab === 'hours' && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Horaires de travail</h3>
                                
                                <form onSubmit={submitHours} className="space-y-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <InputLabel htmlFor="start_time" value="Heure de début" />
                                            <TextInput
                                                id="start_time"
                                                type="time"
                                                name="start_time"
                                                value={hoursData.start_time}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setHoursData('start_time', e.target.value)}
                                            />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="end_time" value="Heure de fin" />
                                            <TextInput
                                                id="end_time"
                                                type="time"
                                                name="end_time"
                                                value={hoursData.end_time}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setHoursData('end_time', e.target.value)}
                                            />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="lunch_start" value="Début pause déjeuner" />
                                            <TextInput
                                                id="lunch_start"
                                                type="time"
                                                name="lunch_start"
                                                value={hoursData.lunch_start}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setHoursData('lunch_start', e.target.value)}
                                            />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="lunch_end" value="Fin pause déjeuner" />
                                            <TextInput
                                                id="lunch_end"
                                                type="time"
                                                name="lunch_end"
                                                value={hoursData.lunch_end}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setHoursData('lunch_end', e.target.value)}
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel value="Jours de travail" />
                                        <div className="mt-2 grid grid-cols-2 md:grid-cols-4 gap-3">
                                            {Object.entries(dayNames).map(([day, name]) => (
                                                <label key={day} className="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        checked={hoursData.working_days.includes(parseInt(day))}
                                                        onChange={() => toggleWorkingDay(parseInt(day))}
                                                        className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    />
                                                    <span className="ml-2 text-sm text-gray-700">{name}</span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="flex justify-end">
                                        <PrimaryButton disabled={processing}>
                                            Sauvegarder les horaires
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}

                    {/* Onglet Notifications */}
                    {activeTab === 'notifications' && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Paramètres de notifications</h3>
                                
                                <form onSubmit={submitGeneral} className="space-y-6">
                                    <div className="space-y-4">
                                        <div className="flex items-center">
                                            <input
                                                id="enable_email_notifications"
                                                type="checkbox"
                                                checked={data.enable_email_notifications}
                                                onChange={(e) => setData('enable_email_notifications', e.target.checked)}
                                                className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <label htmlFor="enable_email_notifications" className="ml-2 text-sm text-gray-700">
                                                Activer les notifications par email
                                            </label>
                                        </div>

                                        <div className="flex items-center">
                                            <input
                                                id="enable_sms_notifications"
                                                type="checkbox"
                                                checked={data.enable_sms_notifications}
                                                onChange={(e) => setData('enable_sms_notifications', e.target.checked)}
                                                className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <label htmlFor="enable_sms_notifications" className="ml-2 text-sm text-gray-700">
                                                Activer les notifications par SMS
                                            </label>
                                        </div>
                                    </div>

                                    <div className="flex justify-end">
                                        <PrimaryButton disabled={processing}>
                                            Sauvegarder
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
} 