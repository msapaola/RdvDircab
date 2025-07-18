import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useState } from 'react';

export default function ProfileIndex({ user, stats }) {
    const [activeTab, setActiveTab] = useState('overview');

    return (
        <AdminLayout
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Mon Profil
                </h2>
            }
        >
            <Head title="Mon Profil" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Onglets */}
                    <div className="mb-6 border-b border-gray-200">
                        <nav className="-mb-px flex space-x-8">
                            <button
                                onClick={() => setActiveTab('overview')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'overview'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Aperçu
                            </button>
                            <button
                                onClick={() => setActiveTab('activities')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'activities'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Activités
                            </button>
                            <button
                                onClick={() => setActiveTab('settings')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'settings'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Paramètres
                            </button>
                        </nav>
                    </div>

                    {/* Onglet Aperçu */}
                    {activeTab === 'overview' && (
                        <div className="space-y-6">
                            {/* Informations utilisateur */}
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center space-x-6">
                                        <div className="flex-shrink-0">
                                            <div className="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span className="text-2xl font-medium text-gray-700">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="text-lg font-medium text-gray-900">{user.name}</h3>
                                            <p className="text-sm text-gray-500">{user.email}</p>
                                            <div className="mt-2">
                                                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                    user.role === 'admin' 
                                                        ? 'bg-purple-100 text-purple-800' 
                                                        : 'bg-blue-100 text-blue-800'
                                                }`}>
                                                    {user.role === 'admin' ? 'Administrateur' : 'Assistant'}
                                                </span>
                                                <span className={`ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                    user.is_active 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-red-100 text-red-800'
                                                }`}>
                                                    {user.is_active ? 'Actif' : 'Inactif'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm text-gray-500">Membre depuis</p>
                                            <p className="text-sm font-medium text-gray-900">
                                                {new Date(user.created_at).toLocaleDateString('fr-FR')}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Statistiques */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div className="p-6">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0">
                                                <div className="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                                    <span className="text-white text-sm font-bold">📊</span>
                                                </div>
                                            </div>
                                            <div className="ml-4">
                                                <p className="text-sm font-medium text-gray-500">RDV traités</p>
                                                <p className="text-2xl font-semibold text-gray-900">{stats.total_processed}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div className="p-6">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0">
                                                <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                                    <span className="text-white text-sm font-bold">✅</span>
                                                </div>
                                            </div>
                                            <div className="ml-4">
                                                <p className="text-sm font-medium text-gray-500">Taux d'acceptation</p>
                                                <p className="text-2xl font-semibold text-gray-900">
                                                    {stats.total_processed > 0 
                                                        ? Math.round((stats.accepted / stats.total_processed) * 100)
                                                        : 0}%
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div className="p-6">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0">
                                                <div className="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                                    <span className="text-white text-sm font-bold">⏱️</span>
                                                </div>
                                            </div>
                                            <div className="ml-4">
                                                <p className="text-sm font-medium text-gray-500">Temps moyen</p>
                                                <p className="text-2xl font-semibold text-gray-900">
                                                    {stats.avg_processing_time || 0} min
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Détails des statistiques */}
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">Détails des rendez-vous traités</h3>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div className="text-center">
                                            <p className="text-2xl font-semibold text-blue-600">{stats.pending}</p>
                                            <p className="text-sm text-gray-500">En attente</p>
                                        </div>
                                        <div className="text-center">
                                            <p className="text-2xl font-semibold text-green-600">{stats.accepted}</p>
                                            <p className="text-sm text-gray-500">Acceptés</p>
                                        </div>
                                        <div className="text-center">
                                            <p className="text-2xl font-semibold text-red-600">{stats.rejected}</p>
                                            <p className="text-sm text-gray-500">Refusés</p>
                                        </div>
                                        <div className="text-center">
                                            <p className="text-2xl font-semibold text-gray-600">{stats.canceled}</p>
                                            <p className="text-sm text-gray-500">Annulés</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Onglet Activités */}
                    {activeTab === 'activities' && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Activités récentes</h3>
                                <div className="space-y-4">
                                    {user.recent_activities && user.recent_activities.length > 0 ? (
                                        user.recent_activities.map((activity, index) => (
                                            <div key={index} className="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                                <div className="flex-shrink-0">
                                                    <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <span className="text-blue-600 text-sm">📝</span>
                                                    </div>
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium text-gray-900">
                                                        {activity.description}
                                                    </p>
                                                    <p className="text-sm text-gray-500">
                                                        {new Date(activity.created_at).toLocaleString('fr-FR')}
                                                    </p>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-gray-500 text-center py-8">Aucune activité récente</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Onglet Paramètres */}
                    {activeTab === 'settings' && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Paramètres du profil</h3>
                                <p className="text-gray-500 mb-6">
                                    Gérez vos paramètres personnels et vos préférences de notification.
                                </p>
                                
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div>
                                            <h4 className="text-sm font-medium text-gray-900">Notifications par email</h4>
                                            <p className="text-sm text-gray-500">Recevoir les notifications par email</p>
                                        </div>
                                        <button className="bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            <span className="translate-x-0 pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                        </button>
                                    </div>

                                    <div className="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div>
                                            <h4 className="text-sm font-medium text-gray-900">Mode sombre</h4>
                                            <p className="text-sm text-gray-500">Activer le thème sombre</p>
                                        </div>
                                        <button className="bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            <span className="translate-x-0 pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                        </button>
                                    </div>
                                </div>

                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <h4 className="text-sm font-medium text-gray-900 mb-4">Actions</h4>
                                    <div className="space-y-2">
                                        <button className="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md">
                                            Changer le mot de passe
                                        </button>
                                        <button className="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">
                                            Supprimer mon compte
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
} 