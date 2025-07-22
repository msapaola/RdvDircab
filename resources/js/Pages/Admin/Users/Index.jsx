import React from 'react';
import { Head } from '@inertiajs/react';

export default function Index(props) {
    console.log('Props reçues Users/Index.jsx :', props);
    const { users, stats, filters } = props;

    if (!users || !Array.isArray(users.data)) {
        return (
            <div style={{ padding: 40, color: 'red', fontWeight: 'bold' }}>
                Erreur critique : Données utilisateurs invalides ou absentes.<br/>
                users = {JSON.stringify(users)}
            </div>
        );
    }
    if (!stats) {
        return (
            <div style={{ padding: 40, color: 'red', fontWeight: 'bold' }}>
                Erreur critique : Données stats absentes.<br/>
                stats = {JSON.stringify(stats)}
            </div>
        );
    }
    if (!filters) {
        return (
            <div style={{ padding: 40, color: 'red', fontWeight: 'bold' }}>
                Erreur critique : Données filters absentes.<br/>
                filters = {JSON.stringify(filters)}
            </div>
        );
    }

    return (
        <>
            <Head title="Test ultra-défensif - Utilisateurs" />
            <div style={{ padding: 40, color: 'green', fontWeight: 'bold' }}>
                Données valides reçues.<br/>
                Nombre d'utilisateurs : {users.data.length}
            </div>
        </>
    );
} 