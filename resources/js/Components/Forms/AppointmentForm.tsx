import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

interface AppointmentFormProps {
  onSubmit: (data: AppointmentFormData) => void;
  onCancel: () => void;
  initialData?: Partial<AppointmentFormData>;
  loading?: boolean;
  title?: string;
}

interface AppointmentFormData {
  name: string;
  email: string;
  phone: string;
  subject: string;
  message: string;
  preferred_date: string;
  preferred_time: string;
  priority: 'normal' | 'urgent' | 'official';
}

const AppointmentForm: React.FC<AppointmentFormProps> = ({
  onSubmit,
  onCancel,
  initialData = {},
  loading = false,
  title = 'Demande de Rendez-vous'
}) => {
  const [formData, setFormData] = useState<AppointmentFormData>({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
    preferred_date: '',
    preferred_time: '',
    priority: 'normal',
    ...initialData
  });

  const [errors, setErrors] = useState<Partial<AppointmentFormData>>({});

  const handleChange = (field: keyof AppointmentFormData, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({ ...prev, [field]: undefined }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Partial<AppointmentFormData> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Le nom est requis';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'L\'email est requis';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'L\'email n\'est pas valide';
    }

    if (!formData.phone.trim()) {
      newErrors.phone = 'Le téléphone est requis';
    }

    if (!formData.subject.trim()) {
      newErrors.subject = 'Le sujet est requis';
    }

    if (!formData.preferred_date) {
      newErrors.preferred_date = 'La date préférée est requise';
    }

    if (!formData.preferred_time) {
      newErrors.preferred_time = 'L\'heure préférée est requise';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm()) {
      onSubmit(formData);
    }
  };

  const timeSlots = [
    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
    '11:00', '11:30', '14:00', '14:30', '15:00', '15:30',
    '16:00', '16:30', '17:00', '17:30'
  ];

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold text-gray-900 mb-4">{title}</h2>
      </div>

      {/* Informations personnelles */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label htmlFor="name" className="block text-sm font-medium text-gray-700">
            Nom complet *
          </label>
          <input
            type="text"
            id="name"
            className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
            value={formData.name}
            onChange={(e) => handleChange('name', e.target.value)}
            required
          />
          {errors.name && <p className="mt-2 text-sm text-red-600">{errors.name}</p>}
        </div>

        <div>
          <label htmlFor="email" className="block text-sm font-medium text-gray-700">
            Email *
          </label>
          <input
            type="email"
            id="email"
            className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
            value={formData.email}
            onChange={(e) => handleChange('email', e.target.value)}
            required
          />
          {errors.email && <p className="mt-2 text-sm text-red-600">{errors.email}</p>}
        </div>
      </div>

      <div>
        <label htmlFor="phone" className="block text-sm font-medium text-gray-700">
          Téléphone *
        </label>
        <input
          type="tel"
          id="phone"
          className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
          value={formData.phone}
          onChange={(e) => handleChange('phone', e.target.value)}
          required
        />
        {errors.phone && <p className="mt-2 text-sm text-red-600">{errors.phone}</p>}
      </div>

      <div>
        <label htmlFor="subject" className="block text-sm font-medium text-gray-700">
          Sujet de la demande *
        </label>
        <input
          type="text"
          id="subject"
          className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
          value={formData.subject}
          onChange={(e) => handleChange('subject', e.target.value)}
          required
        />
        {errors.subject && <p className="mt-2 text-sm text-red-600">{errors.subject}</p>}
      </div>

      <div>
        <label htmlFor="message" className="block text-sm font-medium text-gray-700">
          Message détaillé
        </label>
        <textarea
          id="message"
          className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
          rows={4}
          value={formData.message}
          onChange={(e) => handleChange('message', e.target.value)}
        />
        {errors.message && <p className="mt-2 text-sm text-red-600">{errors.message}</p>}
      </div>

      {/* Préférences de rendez-vous */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label htmlFor="preferred_date" className="block text-sm font-medium text-gray-700">
            Date préférée *
          </label>
          <input
            type="date"
            id="preferred_date"
            className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
            value={formData.preferred_date}
            onChange={(e) => handleChange('preferred_date', e.target.value)}
            min={new Date().toISOString().split('T')[0]}
            required
          />
          {errors.preferred_date && <p className="mt-2 text-sm text-red-600">{errors.preferred_date}</p>}
        </div>

        <div>
          <label htmlFor="preferred_time" className="block text-sm font-medium text-gray-700">
            Heure préférée *
          </label>
          <select
            id="preferred_time"
            className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
            value={formData.preferred_time}
            onChange={(e) => handleChange('preferred_time', e.target.value)}
            required
          >
            <option value="">Sélectionner une heure</option>
            {timeSlots.map(time => (
              <option key={time} value={time}>{time}</option>
            ))}
          </select>
          {errors.preferred_time && <p className="mt-2 text-sm text-red-600">{errors.preferred_time}</p>}
        </div>

        <div>
          <label htmlFor="priority" className="block text-sm font-medium text-gray-700">
            Priorité
          </label>
          <select
            id="priority"
            className="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-institutional shadow-sm"
            value={formData.priority}
            onChange={(e) => handleChange('priority', e.target.value as any)}
          >
            <option value="normal">Normale</option>
            <option value="urgent">Urgente</option>
            <option value="official">Officielle</option>
          </select>
        </div>
      </div>

      {/* Boutons */}
      <div className="flex justify-end space-x-3 pt-4">
        <SecondaryButton
          type="button"
          onClick={onCancel}
          disabled={loading}
        >
          Annuler
        </SecondaryButton>
        <PrimaryButton
          type="submit"
          disabled={loading}
        >
          {loading ? 'Envoi en cours...' : 'Envoyer la demande'}
        </PrimaryButton>
      </div>
    </form>
  );
};

export default AppointmentForm; 