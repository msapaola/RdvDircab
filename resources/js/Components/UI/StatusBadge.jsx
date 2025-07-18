import React from 'react';

export default function StatusBadge({ status, color = 'gray' }) {
    const colorClasses = {
        gray: 'bg-gray-100 text-gray-800',
        red: 'bg-red-100 text-red-800',
        green: 'bg-green-100 text-green-800',
        blue: 'bg-blue-100 text-blue-800',
        orange: 'bg-orange-100 text-orange-800',
        yellow: 'bg-yellow-100 text-yellow-800',
        purple: 'bg-purple-100 text-purple-800',
        pink: 'bg-pink-100 text-pink-800',
    };

    // Mapping des statuts vers les couleurs et labels
    const statusConfig = {
        'pending': { color: 'orange', label: 'En attente' },
        'accepted': { color: 'green', label: 'Accepté' },
        'rejected': { color: 'red', label: 'Refusé' },
        'canceled': { color: 'gray', label: 'Annulé' },
        'canceled_by_requester': { color: 'gray', label: 'Annulé par le demandeur' },
        'expired': { color: 'gray', label: 'Expiré' },
        'completed': { color: 'blue', label: 'Terminé' },
    };

    const config = statusConfig[status] || { color: 'gray', label: status };

    return (
        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClasses[config.color]}`}>
            {config.label}
        </span>
    );
} 