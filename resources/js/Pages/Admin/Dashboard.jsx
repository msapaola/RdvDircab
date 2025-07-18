import React from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function AdminDashboard({ auth, stats, nextAppointments, statsByDay, appointments, filters }) {
    return (
        <AdminLayout user={auth.user}>
            <Head title="Tableau de bord - Administration" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* En-tête */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                        <p className="text-gray-600 mt-2">
                            Bienvenue, {auth.user.name} ({auth.user.role === 'admin' ? 'Administrateur' : 'Assistant'})
                        </p>
                    </div>

                    {/* KPIs */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-orange-500">{stats?.pending || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">En attente</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-green-500">{stats?.accepted || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">Acceptés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-red-500">{stats?.rejected || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">Refusés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-gray-400">{stats?.canceled || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">Annulés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-blue-500">{stats?.completed || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">Terminés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-gray-500">{stats?.expired || 0}</div>
                            <div className="text-sm text-gray-600 mt-2">Expirés</div>
                        </div>
                    </div>

                    {/* Informations de debug */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Informations de debug</h2>
                        <div className="space-y-2 text-sm">
                            <p><strong>Stats:</strong> {JSON.stringify(stats)}</p>
                            <p><strong>Next Appointments:</strong> {nextAppointments?.length || 0} rendez-vous</p>
                            <p><strong>Stats By Day:</strong> {statsByDay?.length || 0} jours de données</p>
                            <p><strong>Appointments:</strong> {appointments?.data?.length || 0} rendez-vous</p>
                            <p><strong>Filters:</strong> {JSON.stringify(filters)}</p>
                        </div>
                    </div>

                    {/* Prochains rendez-vous */}
                    {nextAppointments && nextAppointments.length > 0 && (
                        <div className="bg-white rounded-lg shadow p-6 mb-8">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Prochains rendez-vous</h2>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nom
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Heure
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Objet
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {nextAppointments.map((appointment) => (
                                            <tr key={appointment.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {appointment.name}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {new Date(appointment.preferred_date).toLocaleDateString('fr-FR')}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {appointment.preferred_time}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {appointment.subject}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    {/* Message si aucun rendez-vous */}
                    {(!nextAppointments || nextAppointments.length === 0) && (
                        <div className="bg-white rounded-lg shadow p-12 text-center">
                            <div className="text-gray-500">
                                <p className="text-lg mb-2">Aucun rendez-vous à venir</p>
                                <p className="text-sm">Les prochains rendez-vous apparaîtront ici</p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
} 