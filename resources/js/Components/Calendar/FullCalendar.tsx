import React from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

interface FullCalendarProps {
  events?: any[];
  onDateSelect?: (selectInfo: any) => void;
  onEventClick?: (clickInfo: any) => void;
  onEventDrop?: (dropInfo: any) => void;
  onEventResize?: (resizeInfo: any) => void;
  height?: string | number;
  initialView?: string;
  editable?: boolean;
  selectable?: boolean;
  selectMirror?: boolean;
  dayMaxEvents?: boolean | number;
  weekends?: boolean;
  headerToolbar?: {
    left?: string;
    center?: string;
    right?: string;
  };
  locale?: string;
  firstDay?: number;
  slotMinTime?: string;
  slotMaxTime?: string;
  slotDuration?: string;
  allDaySlot?: boolean;
  slotLabelFormat?: {
    hour?: string;
    minute?: string;
    hour12?: boolean;
  };
}

const FullCalendarComponent: React.FC<FullCalendarProps> = ({
  events = [],
  onDateSelect,
  onEventClick,
  onEventDrop,
  onEventResize,
  height = 'auto',
  initialView = 'dayGridMonth',
  editable = false,
  selectable = false,
  selectMirror = true,
  dayMaxEvents = true,
  weekends = true,
  headerToolbar = {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  locale = 'fr',
  firstDay = 1, // Monday
  slotMinTime = '08:00:00',
  slotMaxTime = '18:00:00',
  slotDuration = '00:30:00',
  allDaySlot = false,
  slotLabelFormat = {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  }
}) => {
  return (
    <div className="fullcalendar-container">
      <FullCalendar
        plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
        headerToolbar={headerToolbar}
        initialView={initialView}
        editable={editable}
        selectable={selectable}
        selectMirror={selectMirror}
        dayMaxEvents={dayMaxEvents}
        weekends={weekends}
        events={events}
        height={height}
        locale={locale}
        firstDay={firstDay}
        slotMinTime={slotMinTime}
        slotMaxTime={slotMaxTime}
        slotDuration={slotDuration}
        allDaySlot={allDaySlot}
        slotLabelFormat={slotLabelFormat}
        select={onDateSelect}
        eventClick={onEventClick}
        eventDrop={onEventDrop}
        eventResize={onEventResize}
        eventColor="#3B82F6"
        eventTextColor="#FFFFFF"
        dayHeaderFormat={{ weekday: 'long' }}
        titleFormat={{ 
          year: 'numeric', 
          month: 'long',
          day: 'numeric'
        }}
        buttonText={{
          today: 'Aujourd\'hui',
          month: 'Mois',
          week: 'Semaine',
          day: 'Jour'
        }}
        noEventsText="Aucun événement"
        loadingText="Chargement..."
        moreLinkText="+{count} autres"
        eventTimeFormat={{
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        }}
      />
    </div>
  );
};

export default FullCalendarComponent; 