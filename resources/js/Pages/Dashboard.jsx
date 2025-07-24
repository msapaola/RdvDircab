import React from 'react';
import { Head, Link } from '@inertiajs/react';
import Statistics from '@/Components/Admin/Statistics';
import StatusBadge from '@/Components/UI/StatusBadge';
import DashboardMenu from '@/Components/DashboardMenu';

export default function Dashboard({ stats = {}, nextAppointments = [], statsByDay = [] }) {
    return (
        <>
            <Head title="Tableau de bord - Administration" />
            <div className="min-h-screen bg-gray-50">
                <DashboardMenu />
                <header className="bg-white shadow-sm border-b mb-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                        <p className="text-gray-600 mt-2">Vue synthétique de l'activité des rendez-vous</p>
                    </div>
                </header>
                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* KPIs */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-orange-500">{stats.pending}</div>
                            <div className="text-sm text-gray-600 mt-2">En attente</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-green-500">{stats.accepted}</div>
                            <div className="text-sm text-gray-600 mt-2">Acceptés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-red-500">{stats.rejected}</div>
                            <div className="text-sm text-gray-600 mt-2">Refusés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-gray-400">{stats.canceled}</div>
                            <div className="text-sm text-gray-600 mt-2">Annulés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-blue-500">{stats.completed}</div>
                            <div className="text-sm text-gray-600 mt-2">Terminés</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6 text-center">
                            <div className="text-2xl font-bold text-gray-500">{stats.expired}</div>
                            <div className="text-sm text-gray-600 mt-2">Expirés</div>
                        </div>
                    </div>

                    {/* Statistiques graphiques */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Statistiques sur 30 jours</h2>
                        <Statistics 
                            data={{
                                title: 'Évolution des rendez-vous sur 30 jours',
                                series: [
                                    {
                                        name: 'Rendez-vous',
                                        data: Array.isArray(statsByDay) ? statsByDay.map(item => ({
                                            x: new Date(item.day).getTime(),
                                            y: item.count
                                        })) : []
                                    }
                                ]
                            }}
                            type="line"
                        />
                    </div>

                    {/* Prochains rendez-vous acceptés */}
                    <div className="bg-white rounded-lg shadow p-6 mb-8">
                        <div className="flex justify-between items-center mb-4">
                            <h2 className="text-lg font-semibold text-gray-900">Prochains rendez-vous acceptés</h2>
                            <Link href={route('admin.appointments.index')} className="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Gérer les rendez-vous →
                            </Link>
                        </div>
                        {nextAppointments && nextAppointments.length > 0 ? (
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th className="text-left py-2">Date</th>
                                        <th className="text-left py-2">Heure</th>
                                        <th className="text-left py-2">Demandeur</th>
                                        <th className="text-left py-2">Objet</th>
                                        <th className="text-left py-2">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {nextAppointments.map((rdv) => (
                                        <tr key={rdv.id} className="border-b">
                                            <td className="py-2">{rdv.preferred_date}</td>
                                            <td className="py-2">{rdv.preferred_time}</td>
                                            <td className="py-2">{rdv.name}</td>
                                            <td className="py-2">{rdv.subject}</td>
                                            <td className="py-2">
                                                <StatusBadge status={rdv.formatted_status} color="green" />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <div className="text-gray-500 text-sm">Aucun rendez-vous à venir.</div>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
