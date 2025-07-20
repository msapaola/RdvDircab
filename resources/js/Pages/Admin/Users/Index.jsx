import React from 'react';
import { Head } from '@inertiajs/react';

export default function Index({ users, stats, filters }) {
    console.log('Users component rendered with:', { users, stats, filters });
    
    return (
        <>
            <Head title="Gestion des utilisateurs - Administration" />
            
            <div className="min-h-screen bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-8">
                        Gestion des utilisateurs
                    </h1>
                    
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">
                            Test de la page
                        </h2>
                        
                        <div className="space-y-4">
                            <div>
                                <strong>Stats:</strong> {JSON.stringify(stats)}
                            </div>
                            
                            <div>
                                <strong>Filters:</strong> {JSON.stringify(filters)}
                            </div>
                            
                            <div>
                                <strong>Users count:</strong> {users?.data?.length || 0}
                            </div>
                            
                            <div>
                                <strong>Users data:</strong>
                                <pre className="text-xs bg-gray-100 p-2 mt-2 rounded">
                                    {JSON.stringify(users, null, 2)}
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
} 