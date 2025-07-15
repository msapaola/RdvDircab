import React from 'react';
import Statistics from '@/Components/Admin/Statistics';

const TestCharts: React.FC = () => {
  // Sample data for testing charts
  const appointmentData = [
    { name: 'Rendez-vous', data: [30, 40, 35, 50, 49, 60, 70, 91, 125] }
  ];

  const statusData = [44, 55, 13, 33];
  const statusLabels = ['Acceptés', 'En attente', 'Refusés', 'Annulés'];

  const weeklyData = [
    { name: 'Lundi', data: [10, 15, 12, 18, 20] },
    { name: 'Mardi', data: [8, 12, 10, 15, 18] },
    { name: 'Mercredi', data: [12, 18, 15, 22, 25] },
    { name: 'Jeudi', data: [9, 14, 11, 16, 19] },
    { name: 'Vendredi', data: [7, 11, 9, 13, 16] }
  ];

  const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep'];

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">
          Test des Graphiques ApexCharts
        </h1>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Line Chart */}
          <Statistics
            title="Évolution des Rendez-vous (9 mois)"
            type="line"
            series={appointmentData}
            categories={months}
            height={300}
          />

          {/* Bar Chart */}
          <Statistics
            title="Rendez-vous par Jour de la Semaine"
            type="bar"
            series={weeklyData}
            categories={['8h', '10h', '12h', '14h', '16h']}
            height={300}
          />

          {/* Donut Chart */}
          <Statistics
            title="Répartition par Statut"
            type="donut"
            series={statusData}
            height={300}
            options={{
              labels: statusLabels,
              plotOptions: {
                pie: {
                  donut: {
                    labels: {
                      show: true,
                      name: {
                        show: true,
                        fontSize: '14px',
                        fontFamily: 'Inter, sans-serif',
                        color: '#374151',
                      },
                      value: {
                        show: true,
                        fontSize: '16px',
                        fontFamily: 'Inter, sans-serif',
                        color: '#111827',
                        fontWeight: 600,
                      },
                      total: {
                        show: true,
                        label: 'Total',
                        fontSize: '14px',
                        fontFamily: 'Inter, sans-serif',
                        color: '#374151',
                      }
                    }
                  }
                }
              }
            }}
          />

          {/* Area Chart */}
          <Statistics
            title="Tendances Mensuelles"
            type="area"
            series={[
              { name: 'Nouveaux RDV', data: [31, 40, 28, 51, 42, 109, 100] },
              { name: 'RDV Confirmés', data: [11, 32, 45, 32, 34, 52, 41] }
            ]}
            categories={['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']}
            height={300}
          />
        </div>

        <div className="mt-8 bg-white rounded-lg shadow-lg p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">
            Informations sur les Graphiques
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
              <h3 className="font-medium text-gray-900 mb-2">Types de Graphiques Disponibles:</h3>
              <ul className="list-disc list-inside space-y-1">
                <li>Line Chart - Pour les tendances temporelles</li>
                <li>Bar Chart - Pour les comparaisons</li>
                <li>Area Chart - Pour les volumes</li>
                <li>Donut Chart - Pour les répartitions</li>
                <li>Pie Chart - Pour les proportions</li>
              </ul>
            </div>
            <div>
              <h3 className="font-medium text-gray-900 mb-2">Fonctionnalités:</h3>
              <ul className="list-disc list-inside space-y-1">
                <li>Export en PNG/SVG</li>
                <li>Tooltips interactifs</li>
                <li>Légendes personnalisables</li>
                <li>Couleurs adaptatives</li>
                <li>Responsive design</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TestCharts; 