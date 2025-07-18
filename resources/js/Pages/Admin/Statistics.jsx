import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Statistics from '@/Components/Admin/Statistics';
import PrimaryButton from '@/Components/PrimaryButton';

export default function AdminStatistics({ auth, stats, chartsData }) {
    return (
        <AdminLayout user={auth.user}>
            <Head title="Statistiques - Administration" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* En-tête */}
                    <div className="mb-8 flex justify-between items-center">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Statistiques</h1>
                            <p className="text-gray-600 mt-2">Vue d'ensemble des performances du système</p>
                        </div>
                        <div className="flex space-x-2">
                            <PrimaryButton onClick={() => window.print()}>
                                Imprimer
                            </PrimaryButton>
                            <Link
                                href={route('admin.statistics.export')}
                                className="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Exporter
                            </Link>
                        </div>
                    </div>

                    {/* KPIs principaux */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white rounded-lg shadow p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-bold">📅</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <div className="text-2xl font-bold text-gray-900">{stats.total_appointments}</div>
                                    <div className="text-sm text-gray-600">Total rendez-vous</div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                            <span className="text-white text-sm font-bold">✅</span>
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                    <div className="text-2xl font-bold text-gray-900">{stats.accepted_rate}%</div>
                                    <div className="text-sm text-gray-600">Taux d'acceptation</div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-bold">⏱️</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <div className="text-2xl font-bold text-gray-900">{stats.avg_response_time}h</div>
                                    <div className="text-sm text-gray-600">Temps de réponse moyen</div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-bold">👥</span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <div className="text-2xl font-bold text-gray-900">{stats.active_users}</div>
                                    <div className="text-sm text-gray-600">Utilisateurs actifs</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Graphiques */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        {/* Évolution des rendez-vous */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Évolution des rendez-vous</h2>
                            <Statistics 
                                data={{
                                    title: 'Rendez-vous par mois',
                                    series: [
                                        {
                                            name: 'Rendez-vous',
                                            data: chartsData.monthly_appointments || []
                                        }
                                    ]
                                }}
                                type="line"
                            />
                                                </div>

                        {/* Répartition par statut */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Répartition par statut</h2>
                            <Statistics 
                                data={{
                                    title: 'Statuts des rendez-vous',
                                    series: [
                                        {
                                            name: 'Rendez-vous',
                                            data: chartsData.status_distribution || []
                                        }
                                    ]
                                }}
                                type="pie"
                            />
                                            </div>
                                        </div>

                    {/* Statistiques détaillées */}
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                        {/* Rendez-vous par priorité */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Par priorité</h3>
                            <div className="space-y-3">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Normale</span>
                                    <span className="text-sm font-semibold text-gray-900">{stats.priority.normal}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Urgente</span>
                                    <span className="text-sm font-semibold text-orange-600">{stats.priority.urgent}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Officielle</span>
                                    <span className="text-sm font-semibold text-blue-600">{stats.priority.official}</span>
                                </div>
                            </div>
                        </div>

                        {/* Performance par utilisateur */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Performance par utilisateur</h3>
                                <div className="space-y-3">
                                {stats.user_performance?.map((user, index) => (
                                    <div key={index} className="flex justify-between items-center">
                                        <span className="text-sm text-gray-600 truncate">{user.name}</span>
                                        <span className="text-sm font-semibold text-gray-900">{user.count}</span>
                                            </div>
                                ))}
                                            </div>
                                        </div>

                        {/* Tendances */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Tendances</h3>
                            <div className="space-y-3">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Cette semaine</span>
                                    <span className="text-sm font-semibold text-green-600">+{stats.trends.this_week}%</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Ce mois</span>
                                    <span className="text-sm font-semibold text-blue-600">+{stats.trends.this_month}%</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Cette année</span>
                                    <span className="text-sm font-semibold text-purple-600">+{stats.trends.this_year}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Tableau des meilleures performances */}
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="text-lg font-semibold text-gray-900">Meilleures performances</h2>
                        </div>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Utilisateur
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rendez-vous traités
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Taux d'acceptation
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Temps moyen
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Score
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                    {stats.top_performers?.map((performer, index) => (
                                        <tr key={index} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center">
                                                        <div className="flex-shrink-0 h-10 w-10">
                                                            <div className="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                                <span className="text-sm font-medium text-gray-700">
                                                                {performer.name.charAt(0).toUpperCase()}
                                                                </span>
                                                            </div>
                                                    </div>
                                                    <div className="ml-4">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {performer.name}
                                                        </div>
                                                        <div className="text-sm text-gray-500">
                                                            {performer.role}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {performer.processed_count}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {performer.acceptance_rate}%
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {performer.avg_response_time}h
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                    performer.score >= 90 ? 'bg-green-100 text-green-800' :
                                                    performer.score >= 70 ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                }`}>
                                                    {performer.score}/100
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 