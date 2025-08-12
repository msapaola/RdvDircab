import React, { useState, useRef, useEffect } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import Modal from '@/Components/UI/Modal';
import AppointmentForm from '@/Components/Forms/AppointmentForm';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Home({ availableSlots, blockedSlots, businessHours, workingDays, errors, success, message, tracking_url }) {
    const [showAppointmentModal, setShowAppointmentModal] = useState(false);
    const [selectedSlot, setSelectedSlot] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitMessage, setSubmitMessage] = useState(null);
    
    const calendarRef = useRef(null);

    // Gérer les messages de succès et les erreurs depuis les props Inertia
    useEffect(() => {
        if (success && message) {
            setSubmitMessage({
                type: 'success',
                message: message,
                trackingUrl: tracking_url
            });
            setShowAppointmentModal(false);
            // Recharger le calendrier
            if (calendarRef.current) {
                calendarRef.current.getApi().refetchEvents();
            }
        }
    }, [success, message, tracking_url]);

    const appointmentForm = useForm({
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
        preferred_date: '',
        preferred_time: '',
        priority: 'normal',
        attachments: [],
    });

    // Préparer les événements pour FullCalendar
    const calendarEvents = [
        // Créneaux disponibles
        ...availableSlots.filter(slot => slot.available).map(slot => ({
            id: `available-${slot.date}-${slot.start_time}`,
            title: 'Disponible',
            start: `${slot.date}T${slot.start_time}`,
            end: `${slot.date}T${slot.end_time}`,
            backgroundColor: '#10B981',
            borderColor: '#10B981',
            textColor: '#ffffff',
            classNames: ['available-slot'],
            extendedProps: {
                type: 'available',
                slot: slot
            }
        })),
        
        // Créneaux bloqués
        ...blockedSlots.map(slot => ({
            id: `blocked-${slot.date}-${slot.start_time}`,
            title: slot.reason || 'Indisponible',
            start: `${slot.date}T${slot.start_time}`,
            end: `${slot.date}T${slot.end_time}`,
            backgroundColor: '#EF4444',
            borderColor: '#EF4444',
            textColor: '#ffffff',
            classNames: ['blocked-slot'],
            extendedProps: {
                type: 'blocked',
                slot: slot
            }
        }))
    ];

    const handleDateSelect = (selectInfo) => {
        const start = selectInfo.start;
        const end = selectInfo.end;
        
        // Vérifier si c'est un créneau disponible
        const availableSlot = availableSlots.find(slot => 
            slot.date === start.toISOString().split('T')[0] &&
            slot.start_time === start.toTimeString().slice(0, 5) &&
            slot.available
        );

        if (availableSlot) {
            setSelectedSlot(availableSlot);
            appointmentForm.setData({
                ...appointmentForm.data,
                preferred_date: availableSlot.date,
                preferred_time: availableSlot.start_time,
            });
            setShowAppointmentModal(true);
        }
    };

    const handleEventClick = (clickInfo) => {
        const event = clickInfo.event;
        const slot = event.extendedProps.slot;

        if (event.extendedProps.type === 'available') {
            setSelectedSlot(slot);
            appointmentForm.setData({
                ...appointmentForm.data,
                preferred_date: slot.date,
                preferred_time: slot.start_time,
            });
            setShowAppointmentModal(true);
        }
    };

    const handleSubmit = (formData) => {
        setIsSubmitting(true);
        setSubmitMessage(null);

        // Utiliser Inertia.js pour une meilleure gestion des erreurs
        router.post('/appointments', formData, {
            onFinish: () => {
                setIsSubmitting(false);
            }
        });
    };

    const closeModal = () => {
        setShowAppointmentModal(false);
        setSelectedSlot(null);
        setSubmitMessage(null);
        appointmentForm.reset();
    };

    return (
        <>
            <Head title="Demande de Rendez-vous - Cabinet du Gouverneur de Kinshasa" />
            
            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="bg-white shadow-sm border-b">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center py-6">
                            <div className="flex items-center gap-4">
                                <ApplicationLogo className="h-16 w-auto" />
                            </div>
                            <div className="text-right">
                                <p className="text-sm text-gray-500">
                                    Horaires d'ouverture
                                </p>
                                <p className="text-sm font-medium text-gray-900">
                                    Lundi - Vendredi: 8h00 - 17h00
                                </p>
                                <p className="text-xs text-gray-500">
                                    Pause déjeuner: 12h00 - 14h00
                                </p>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Titre principal déplacé sous le header */}
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-4 text-center">
                    <h1 className="text-3xl font-bold text-gray-900">
                        Cabinet du Gouverneur de Kinshasa
                    </h1>
                    <p className="text-gray-600 mt-2">
                        Demande de rendez-vous en ligne
                    </p>
                </div>

                {/* Main Content */}
                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Instructions */}
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                        <h2 className="text-lg font-semibold text-blue-900 mb-2">
                            Comment demander un rendez-vous ?
                        </h2>
                        <div className="grid md:grid-cols-3 gap-4 text-sm text-blue-800">
                            <div className="flex items-start">
                                <span className="bg-blue-200 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">
                                    1
                                </span>
                                <span>Consultez le calendrier ci-dessous et sélectionnez un créneau disponible (en vert)</span>
                            </div>
                            <div className="flex items-start">
                                <span className="bg-blue-200 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">
                                    2
                                </span>
                                <span>Remplissez le formulaire avec vos informations et l'objet de votre visite</span>
                            </div>
                            <div className="flex items-start">
                                <span className="bg-blue-200 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">
                                    3
                                </span>
                                <span>Recevez une confirmation par email avec un lien de suivi unique</span>
                            </div>
                        </div>
                    </div>

                    {/* Calendar */}
                    <div className="bg-white rounded-lg shadow-sm border">
                        <div className="p-6 border-b">
                            <h2 className="text-xl font-semibold text-gray-900">
                                Calendrier des créneaux disponibles
                            </h2>
                            <p className="text-gray-600 mt-1">
                                Cliquez sur un créneau vert pour demander un rendez-vous
                            </p>
                        </div>
                        
                        <div className="p-6">
                            <FullCalendar
                                ref={calendarRef}
                                plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
                                initialView="timeGridWeek"
                                headerToolbar={{
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                                }}
                                locale={frLocale}
                                selectable={true}
                                selectMirror={true}
                                dayMaxEvents={true}
                                weekends={false}
                                slotMinTime="08:00:00"
                                slotMaxTime="17:00:00"
                                slotDuration="01:00:00"
                                events={calendarEvents}
                                select={handleDateSelect}
                                eventClick={handleEventClick}
                                height="auto"
                                slotLabelFormat={{
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                }}
                                eventTimeFormat={{
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                }}
                                businessHours={{
                                    daysOfWeek: workingDays,
                                    startTime: businessHours.start,
                                    endTime: businessHours.end,
                                }}
                                selectConstraint={{
                                    startTime: businessHours.start,
                                    endTime: businessHours.end,
                                    daysOfWeek: workingDays,
                                }}
                                slotEventOverlap={false}
                                eventOverlap={false}
                            />
                        </div>
                    </div>

                    {/* Legend */}
                    <div className="mt-6 bg-white rounded-lg shadow-sm border p-4">
                        <h3 className="text-sm font-medium text-gray-900 mb-3">Légende</h3>
                        <div className="flex flex-wrap gap-4 text-sm">
                            <div className="flex items-center">
                                <div className="w-4 h-4 bg-green-500 rounded mr-2"></div>
                                <span>C Créneau disponible</span>
                            </div>
                            <div className="flex items-center">
                                <div className="w-4 h-4 bg-red-500 rounded mr-2"></div>
                                <span>C Créneau indisponible</span>
                            </div>
                            <div className="flex items-center">
                                <div className="w-4 h-4 bg-gray-300 rounded mr-2"></div>
                                <span>Weekend / Hors horaires</span>
                            </div>
                        </div>
                    </div>
                </main>

                {/* Footer */}
                <footer className="bg-gray-800 text-white py-8 mt-12">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <p className="text-sm">
                                © 2024 Cabinet du Gouverneur de Kinshasa. Tous droits réservés.
                            </p>
                            <p className="text-xs text-gray-400 mt-2">
                                Pour toute assistance, contactez-nous au +243 XXX XXX XXX
                            </p>
                        </div>
                    </div>
                </footer>
            </div>

            {/* Appointment Modal */}
            <Modal show={showAppointmentModal} onClose={closeModal} maxWidth="2xl">
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Demande de rendez-vous
                    </h2>
                    
                    {selectedSlot && (
                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p className="text-sm text-blue-800">
                                <strong>Créneau sélectionné :</strong> {selectedSlot.date} de {selectedSlot.start_time} à {selectedSlot.end_time}
                            </p>
                        </div>
                    )}

                    {submitMessage && (
                        <div className={`mb-6 p-4 rounded-lg ${
                            submitMessage.type === 'success' 
                                ? 'bg-green-50 border border-green-200 text-green-800' 
                                : 'bg-red-50 border border-red-200 text-red-800'
                        }`}>
                            <p className="text-sm">{submitMessage.message}</p>
                            {submitMessage.trackingUrl && (
                                <div className="mt-3">
                                    <a 
                                        href={submitMessage.trackingUrl}
                                        className="text-sm text-green-600 hover:text-green-800 underline"
                                    >
                                        Suivre ma demande →
                                    </a>
                                </div>
                            )}
                        </div>
                    )}

                    <AppointmentForm
                        form={appointmentForm}
                        onSubmit={handleSubmit}
                        isSubmitting={isSubmitting}
                        selectedSlot={selectedSlot}
                        onCancel={closeModal}
                        errors={errors || {}}
                    />
                </div>
            </Modal>
        </>
    );
} 