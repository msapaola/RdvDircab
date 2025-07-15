import React from 'react';
import StatusBadge from '@/Components/UI/StatusBadge';

const TestColors: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">
          Test des Couleurs Institutionnelles
        </h1>

        {/* Couleurs principales */}
        <div className="bg-white rounded-institutional shadow-institutional p-6 mb-8">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Couleurs Principales</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Primary Colors */}
            <div>
              <h3 className="font-medium text-gray-900 mb-3">Primary (Bleu)</h3>
              <div className="space-y-2">
                {[50, 100, 200, 300, 400, 500, 600, 700, 800, 900].map((shade) => (
                  <div key={shade} className="flex items-center gap-3">
                    <div className={`w-8 h-8 rounded bg-primary-${shade} border border-gray-200`}></div>
                    <span className="text-sm text-gray-600">primary-{shade}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Secondary Colors */}
            <div>
              <h3 className="font-medium text-gray-900 mb-3">Secondary (Gris)</h3>
              <div className="space-y-2">
                {[50, 100, 200, 300, 400, 500, 600, 700, 800, 900].map((shade) => (
                  <div key={shade} className="flex items-center gap-3">
                    <div className={`w-8 h-8 rounded bg-secondary-${shade} border border-gray-200`}></div>
                    <span className="text-sm text-gray-600">secondary-{shade}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Accent Colors */}
            <div>
              <h3 className="font-medium text-gray-900 mb-3">Accent (Orange)</h3>
              <div className="space-y-2">
                {[50, 100, 200, 300, 400, 500, 600, 700, 800, 900].map((shade) => (
                  <div key={shade} className="flex items-center gap-3">
                    <div className={`w-8 h-8 rounded bg-accent-${shade} border border-gray-200`}></div>
                    <span className="text-sm text-gray-600">accent-{shade}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* Statuts de rendez-vous */}
        <div className="bg-white rounded-institutional shadow-institutional p-6 mb-8">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Badges de Statut</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 className="font-medium text-gray-900 mb-3">Tailles disponibles</h3>
              <div className="space-y-3">
                <div className="flex items-center gap-4">
                  <span className="text-sm text-gray-600 w-20">Petit:</span>
                  <StatusBadge status="pending" size="sm" />
                </div>
                <div className="flex items-center gap-4">
                  <span className="text-sm text-gray-600 w-20">Moyen:</span>
                  <StatusBadge status="accepted" size="md" />
                </div>
                <div className="flex items-center gap-4">
                  <span className="text-sm text-gray-600 w-20">Grand:</span>
                  <StatusBadge status="rejected" size="lg" />
                </div>
              </div>
            </div>

            <div>
              <h3 className="font-medium text-gray-900 mb-3">Tous les statuts</h3>
              <div className="space-y-2">
                <StatusBadge status="pending" />
                <StatusBadge status="accepted" />
                <StatusBadge status="rejected" />
                <StatusBadge status="canceled" />
                <StatusBadge status="canceled_by_requester" />
                <StatusBadge status="expired" />
                <StatusBadge status="completed" />
              </div>
            </div>
          </div>
        </div>

        {/* Boutons avec couleurs institutionnelles */}
        <div className="bg-white rounded-institutional shadow-institutional p-6 mb-8">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Boutons Institutionnels</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 className="font-medium text-gray-900 mb-3">Boutons principaux</h3>
              <div className="space-y-3">
                <button className="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-institutional font-medium transition-colors">
                  Bouton Principal
                </button>
                <button className="bg-secondary-500 hover:bg-secondary-600 text-white px-4 py-2 rounded-institutional font-medium transition-colors">
                  Bouton Secondaire
                </button>
                <button className="bg-accent-500 hover:bg-accent-600 text-white px-4 py-2 rounded-institutional font-medium transition-colors">
                  Bouton Accent
                </button>
              </div>
            </div>

            <div>
              <h3 className="font-medium text-gray-900 mb-3">Boutons d'état</h3>
              <div className="space-y-3">
                <button className="bg-success-500 hover:bg-success-600 text-white px-4 py-2 rounded-institutional font-medium transition-colors">
                  Succès
                </button>
                <button className="bg-warning-500 hover:bg-warning-600 text-white px-4 py-2 rounded-institutional font-medium transition-colors">
                  Avertissement
                </button>
                <button className="bg-danger-500 hover:bg-danger-600 text-white px-4 py-2 rounded-institutional font-medium transition-colors">
                  Danger
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Cartes avec ombres institutionnelles */}
        <div className="bg-white rounded-institutional shadow-institutional p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Ombres et Bordures Institutionnelles</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-primary-50 border border-primary-200 rounded-institutional p-4">
              <h3 className="font-medium text-primary-900 mb-2">Carte Primaire</h3>
              <p className="text-primary-700 text-sm">
                Cette carte utilise les couleurs primaires avec l'ombre institutionnelle.
              </p>
            </div>

            <div className="bg-secondary-50 border border-secondary-200 rounded-institutional p-4">
              <h3 className="font-medium text-secondary-900 mb-2">Carte Secondaire</h3>
              <p className="text-secondary-700 text-sm">
                Cette carte utilise les couleurs secondaires avec l'ombre institutionnelle.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TestColors; 