import React from 'react';
import { Head, Link } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function AppointmentUnavailable({ error, appointment = null }) {
    const getErrorMessage = () => {
        if (appointment) {
            switch (appointment.status) {
                case 'expired':
                    return {
                        title: 'Rendez-vous expiré',
                        message: 'Ce rendez-vous a expiré et n\'est plus accessible.',
                        icon: '⏰',
                        color: 'yellow'
                    };
                case 'canceled':
                    return {
                        title: 'Rendez-vous annulé',
                        message: 'Ce rendez-vous a été annulé par l\'administration.',
                        icon: '❌',
                        color: 'red'
                    };
                case 'canceled_by_requester':
                    return {
                        title: 'Rendez-vous annulé',
                        message: 'Ce rendez-vous a été annulé par le demandeur.',
                        icon: '🚫',
                        color: 'gray'
                    };
                case 'completed':
                    return {
                        title: 'Rendez-vous terminé',
                        message: 'Ce rendez-vous a été marqué comme terminé.',
                        icon: '✅',
                        color: 'green'
                    };
                default:
                    return {
                        title: 'Rendez-vous non accessible',
                        message: 'Ce rendez-vous n\'est plus accessible pour le moment.',
                        icon: '🔒',
                        color: 'blue'
                    };
            }
        }
        
        return {
            title: 'Rendez-vous non trouvé',
            message: 'Le rendez-vous que vous recherchez n\'existe pas ou n\'est plus accessible.',
            icon: '🔍',
            color: 'gray'
        };
    };

    const errorInfo = getErrorMessage();

    const getColorClasses = (color) => {
        const colors = {
            red: 'bg-red-50 border-red-200 text-red-800',
            yellow: 'bg-yellow-50 border-yellow-200 text-yellow-800',
            green: 'bg-green-50 border-green-200 text-green-800',
            blue: 'bg-blue-50 border-blue-200 text-blue-800',
            gray: 'bg-gray-50 border-gray-200 text-gray-800'
        };
        return colors[color] || colors.gray;
    };

    return (
        <>
            <Head title="Rendez-vous non accessible - Cabinet du Gouverneur" />
            
            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="bg-white shadow-sm border-b">
                    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="py-6 flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <ApplicationLogo className="h-14 w-auto" />
                            </div>
                            <Link 
                                href="/"
                                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                ← Retour à l'accueil
                            </Link>
                        </div>
                    </div>
                </header>

                {/* Main Content */}
                <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div className="text-center">
                        {/* Icon */}
                        <div className="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gray-100 mb-6">
                            <span className="text-4xl">{errorInfo.icon}</span>
                        </div>

                        {/* Title */}
                        <h1 className="text-3xl font-bold text-gray-900 mb-4">
                            {errorInfo.title}
                        </h1>

                        {/* Message */}
                        <p className="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                            {errorInfo.message}
                        </p>

                        {/* Error Details */}
                        {appointment && (
                            <div className={`max-w-md mx-auto mb-8 p-4 rounded-lg border ${getColorClasses(errorInfo.color)}`}>
                                <h3 className="text-sm font-medium mb-2">Détails du rendez-vous :</h3>
                                <div className="text-sm space-y-1">
                                    <p><strong>Objet :</strong> {appointment.subject}</p>
                                    <p><strong>Demandeur :</strong> {appointment.name}</p>
                                    <p><strong>Date :</strong> {appointment.preferred_date ? new Date(appointment.preferred_date).toLocaleDateString('fr-FR') : 'Non définie'}</p>
                                    <p><strong>Statut :</strong> {appointment.status}</p>
                                </div>
                            </div>
                        )}

                        {/* Actions */}
                        <div className="space-y-4">
                            <Link
                                href="/"
                                className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            >
                                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Retour à l'accueil
                            </Link>

                            <div className="text-sm text-gray-500">
                                <p>Besoin d'aide ? Contactez-nous :</p>
                                <p className="font-medium">+243 XXX XXX XXX</p>
                            </div>
                        </div>

                        {/* Additional Information */}
                        <div className="mt-12 max-w-2xl mx-auto">
                            <div className="bg-white rounded-lg shadow-sm border p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Que faire maintenant ?
                                </h3>
                                <div className="text-sm text-gray-600 space-y-3">
                                    <div className="flex items-start">
                                        <span className="text-blue-600 mr-2">•</span>
                                        <span>Si vous avez besoin d'un nouveau rendez-vous, vous pouvez en faire la demande depuis notre page d'accueil.</span>
                                    </div>
                                    <div className="flex items-start">
                                        <span className="text-blue-600 mr-2">•</span>
                                        <span>Pour toute question concernant votre rendez-vous, contactez-nous directement.</span>
                                    </div>
                                    <div className="flex items-start">
                                        <span className="text-blue-600 mr-2">•</span>
                                        <span>Consultez notre FAQ pour plus d'informations sur nos services.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
} 