import React from 'react';
import ReactApexChart from 'react-apexcharts';

interface ChartData {
  name: string;
  data: number[];
}

interface StatisticsProps {
  title: string;
  type: 'line' | 'area' | 'bar' | 'pie' | 'donut';
  series: ChartData[] | number[];
  categories?: string[];
  height?: number | string;
  colors?: string[];
  options?: any;
}

const Statistics: React.FC<StatisticsProps> = ({
  title,
  type,
  series,
  categories = [],
  height = 350,
  colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
  options = {}
}) => {
  const defaultOptions = {
    chart: {
      type: type,
      toolbar: {
        show: true,
        tools: {
          download: true,
          selection: false,
          zoom: false,
          zoomin: false,
          zoomout: false,
          pan: false,
          reset: false
        }
      },
      fontFamily: 'Inter, sans-serif',
    },
    colors: colors,
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 2
    },
    grid: {
      borderColor: '#E5E7EB',
      strokeDashArray: 4,
    },
    xaxis: {
      categories: categories,
      labels: {
        style: {
          colors: '#6B7280',
          fontSize: '12px',
          fontFamily: 'Inter, sans-serif',
        }
      },
      axisBorder: {
        color: '#E5E7EB',
      },
      axisTicks: {
        color: '#E5E7EB',
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: '#6B7280',
          fontSize: '12px',
          fontFamily: 'Inter, sans-serif',
        }
      }
    },
    legend: {
      position: 'top',
      horizontalAlign: 'right',
      fontSize: '14px',
      fontFamily: 'Inter, sans-serif',
      labels: {
        colors: '#374151'
      }
    },
    tooltip: {
      theme: 'light',
      style: {
        fontSize: '12px',
        fontFamily: 'Inter, sans-serif',
      }
    },
    plotOptions: {
      bar: {
        borderRadius: 4,
        columnWidth: '60%',
      },
      pie: {
        donut: {
          size: '60%',
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
    },
    ...options
  };

  return (
    <div className="bg-white rounded-lg shadow-lg p-6">
      <h3 className="text-lg font-semibold text-gray-900 mb-4">{title}</h3>
      <ReactApexChart
        options={defaultOptions}
        series={series}
        type={type}
        height={height}
      />
    </div>
  );
};

export default Statistics; 