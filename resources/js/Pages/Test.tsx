import React from 'react';
import FullCalendarComponent from '@/Components/Calendar/FullCalendar';

const Test: React.FC = () => {
  // Sample events for testing
  const sampleEvents = [
    {
      id: '1',
      title: 'Rendez-vous Test 1',
      start: '2024-01-15T10:00:00',
      end: '2024-01-15T11:00:00',
      color: '#3B82F6'
    },
    {
      id: '2',
      title: 'Rendez-vous Test 2',
      start: '2024-01-16T14:00:00',
      end: '2024-01-16T15:30:00',
      color: '#10B981'
    },
    {
      id: '3',
      title: 'Rendez-vous Test 3',
      start: '2024-01-17T09:00:00',
      end: '2024-01-17T10:00:00',
      color: '#F59E0B'
    }
  ];

  const handleDateSelect = (selectInfo: any) => {
    console.log('Date selected:', selectInfo);
    alert(`Date sélectionnée: ${selectInfo.startStr} à ${selectInfo.endStr}`);
  };

  const handleEventClick = (clickInfo: any) => {
    console.log('Event clicked:', clickInfo);
    alert(`Événement cliqué: ${clickInfo.event.title}`);
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h1 className="text-2xl font-bold text-gray-900 mb-6">
            Test FullCalendar
          </h1>
          
          <div className="mb-4">
            <p className="text-gray-600">
              Ceci est une page de test pour vérifier que FullCalendar fonctionne correctement.
            </p>
          </div>

          <FullCalendarComponent
            events={sampleEvents}
            onDateSelect={handleDateSelect}
            onEventClick={handleEventClick}
            selectable={true}
            height={600}
            initialView="dayGridMonth"
          />
        </div>
      </div>
    </div>
  );
};

export default Test; 