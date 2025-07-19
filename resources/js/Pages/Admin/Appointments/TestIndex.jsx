import React from 'react';
import { Head } from '@inertiajs/react';
import DashboardMenu from '@/Components/DashboardMenu';

export default function TestIndex({ appointments, stats, filters }) {
    return (
        <>
            <Head title="Test - Gestion des rendez-vous" />
            
            <div className="min-h-screen bg-gray-50">
                <DashboardMenu />
                
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <div className="flex justify-between items-center">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">Test - Gestion des rendez-vous</h1>
                                <p className="text-gray-600 mt-2">Page de test pour isoler le problème</p>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Test réussi !</h2>
                        <p className="text-gray-600">
                            Si vous voyez ce message, le problème ne vient pas du DashboardMenu.
                            <br />
                            Nombre de rendez-vous : {appointments?.total || 0}
                        </p>
                    </div>
                </main>
            </div>
        </>
    );
} 