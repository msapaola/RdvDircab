import { Head } from '@inertiajs/react';

export default function LoginSimple() {
    return (
        <>
            <Head title="Connexion" />
            
            <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
                <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                    <div className="mb-6 text-center">
                        <h2 className="text-2xl font-bold text-gray-900">
                            Connexion
                        </h2>
                        <p className="text-gray-600 mt-2">
                            Cabinet du Gouverneur de Kinshasa
                        </p>
                    </div>

                    <form method="POST" action="/login">
                        <div className="mb-4">
                            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                required
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>

                        <div className="mb-4">
                            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                                Mot de passe
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>

                        <div className="flex items-center justify-between">
                            <button
                                type="submit"
                                className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            >
                                Se connecter
                            </button>
                        </div>
                    </form>

                    <div className="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h3 className="text-sm font-medium text-blue-800 mb-2">
                            Identifiants de test :
                        </h3>
                        <div className="text-xs text-blue-700 space-y-1">
                            <p><strong>Admin:</strong> admin@gouvernorat-kinshasa.cd / Admin@2024!</p>
                            <p><strong>Assistant:</strong> assistant@gouvernorat-kinshasa.cd / Assistant@2024!</p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
} 