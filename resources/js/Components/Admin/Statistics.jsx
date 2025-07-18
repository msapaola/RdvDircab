import React from 'react';
import ReactApexChart from 'react-apexcharts';

export default function Statistics({ data, type = 'line', height = 350 }) {
    // Safety check for data
    if (!data) {
        return (
            <div className="bg-white rounded-lg shadow p-6">
                <div className="text-gray-500 text-center">Aucune donnée disponible</div>
            </div>
        );
    }

    const getChartOptions = () => {
        const baseOptions = {
            chart: {
                type: type,
                toolbar: {
                    show: false,
                },
                fontFamily: 'Inter, sans-serif',
            },
            colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
            grid: {
                borderColor: '#E5E7EB',
                strokeDashArray: 4,
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    style: {
                        colors: '#6B7280',
                        fontSize: '12px',
                    },
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#6B7280',
                        fontSize: '12px',
                    },
                },
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '14px',
                fontFamily: 'Inter, sans-serif',
                labels: {
                    colors: '#374151',
                },
            },
            tooltip: {
                theme: 'light',
                x: {
                    format: 'dd/MM/yyyy',
                },
            },
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 250,
                        },
                        legend: {
                            position: 'bottom',
                        },
                    },
                },
            ],
        };

        switch (type) {
            case 'pie':
                return {
                    ...baseOptions,
                    chart: {
                        ...baseOptions.chart,
                        type: 'pie',
                    },
                    labels: data.labels || [],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                            },
                        },
                    },
                };
            case 'bar':
                return {
                    ...baseOptions,
                    chart: {
                        ...baseOptions.chart,
                        type: 'bar',
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 4,
                        },
                    },
                };
            default:
                return baseOptions;
        }
    };

    const getChartSeries = () => {
        if (type === 'pie') {
            return data.series || [];
        }
        
        return data.series || [];
    };

    return (
        <div className="bg-white rounded-lg shadow p-6">
            {data.title && (
                <h3 className="text-lg font-semibold text-gray-900 mb-4">{data.title}</h3>
            )}
            <ReactApexChart
                options={getChartOptions()}
                series={getChartSeries()}
                type={type}
                height={height}
            />
        </div>
    );
}

// Composants spécialisés pour différents types de statistiques
export function AppointmentStats({ data }) {
    const chartData = {
        title: 'Évolution des rendez-vous',
        series: [
            {
                name: 'Rendez-vous',
                data: data.appointments || [],
            },
        ],
    };

    return <Statistics data={chartData} type="line" />;
}

export function StatusDistribution({ data }) {
    const chartData = {
        title: 'Répartition par statut',
        series: data.series || [],
        labels: data.labels || [],
    };

    return <Statistics data={chartData} type="pie" height={300} />;
}

export function MonthlyComparison({ data }) {
    const chartData = {
        title: 'Comparaison mensuelle',
        series: [
            {
                name: 'Ce mois',
                data: data.currentMonth || [],
            },
            {
                name: 'Mois précédent',
                data: data.previousMonth || [],
            },
        ],
    };

    return <Statistics data={chartData} type="bar" />;
}

export function PriorityStats({ data }) {
    const chartData = {
        title: 'Répartition par priorité',
        series: data.series || [],
        labels: data.labels || [],
    };

    return <Statistics data={chartData} type="pie" height={250} />;
} 