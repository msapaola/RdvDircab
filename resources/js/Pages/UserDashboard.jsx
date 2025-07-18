import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function UserDashboard({ auth }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Tableau de bord
                </h2>
            }
        >
            <Head title="Tableau de bord" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-semibold mb-4">Bienvenue, {auth.user.name} !</h3>
                            <p className="text-gray-600 mb-4">
                                Vous êtes connecté en tant qu'utilisateur. Pour accéder aux fonctionnalités d'administration, 
                                veuillez contacter un administrateur.
                            </p>
                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 className="font-medium text-blue-900 mb-2">Fonctionnalités disponibles :</h4>
                                <ul className="text-blue-800 space-y-1">
                                    <li>• Consulter votre profil</li>
                                    <li>• Modifier vos informations personnelles</li>
                                    <li>• Changer votre mot de passe</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 