import React, { useState } from 'react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

export default function AppointmentForm({ form, onSubmit, isSubmitting, selectedSlot, onCancel, errors }) {
    const [attachments, setAttachments] = useState([]);

    const handleSubmit = (e) => {
        e.preventDefault();
        
        // Créer un FormData pour gérer les fichiers
        const formData = new FormData();
        
        // Ajouter les données du formulaire
        Object.keys(form.data).forEach(key => {
            if (key !== 'attachments') {
                formData.append(key, form.data[key]);
            }
        });
        
        // Ajouter les fichiers
        attachments.forEach((file, index) => {
            formData.append(`attachments[${index}]`, file);
        });
        
        onSubmit(formData);
    };

    const handleFileChange = (e) => {
        const files = Array.from(e.target.files);
        
        // Vérifier le nombre de fichiers (max 5)
        if (attachments.length + files.length > 5) {
            alert('Vous ne pouvez pas ajouter plus de 5 fichiers.');
            return;
        }
        
        // Vérifier la taille des fichiers (max 5MB chacun)
        const maxSize = 5 * 1024 * 1024; // 5MB
        const validFiles = files.filter(file => {
            if (file.size > maxSize) {
                alert(`Le fichier ${file.name} est trop volumineux (max 5MB).`);
                return false;
            }
            return true;
        });
        
        setAttachments([...attachments, ...validFiles]);
    };

    const removeFile = (index) => {
        setAttachments(attachments.filter((_, i) => i !== index));
    };

    const getPriorityDescription = (priority) => {
        const descriptions = {
            normal: 'Demande standard - Traitement dans les délais habituels',
            urgent: 'Demande urgente - Traitement prioritaire (justification requise)',
            official: 'Demande officielle - Institution ou organisation officielle',
        };
        return descriptions[priority] || '';
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {/* Erreurs générales */}
            {(errors.slot || errors.timing || errors.rate_limit) && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div className="flex">
                        <svg className="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                        </svg>
                        <div>
                            <h3 className="text-sm font-medium text-red-800">Erreur</h3>
                            <div className="text-sm text-red-700 mt-1">
                                {errors.slot && <p>{errors.slot}</p>}
                                {errors.timing && <p>{errors.timing}</p>}
                                {errors.rate_limit && <p>{errors.rate_limit}</p>}
                            </div>
                        </div>
                    </div>
                </div>
            )}
            
            {/* Informations personnelles */}
            <div className="bg-gray-50 rounded-lg p-4">
                <h3 className="text-sm font-medium text-gray-900 mb-4">Informations personnelles</h3>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel htmlFor="name" value="Nom complet *" />
                        <TextInput
                            id="name"
                            type="text"
                            className="mt-1 block w-full"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            required
                        />
                        <InputError message={errors.name || form.errors.name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="email" value="Email *" />
                        <TextInput
                            id="email"
                            type="email"
                            className="mt-1 block w-full"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                            required
                        />
                        <InputError message={errors.email || form.errors.email} className="mt-2" />
                    </div>
                </div>

                <div className="mt-6">
                    <InputLabel htmlFor="phone" value="Téléphone *" />
                    <TextInput
                        id="phone"
                        type="tel"
                        className="mt-1 block w-full"
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                        placeholder="+243 XXX XXX XXX"
                        required
                    />
                                            <InputError message={errors.phone || form.errors.phone} className="mt-2" />
                </div>
            </div>

            {/* Détails de la demande */}
            <div className="bg-gray-50 rounded-lg p-4">
                <h3 className="text-sm font-medium text-gray-900 mb-4">Détails de la demande</h3>
                
                <div>
                    <InputLabel htmlFor="subject" value="Objet de la visite *" />
                    <TextInput
                        id="subject"
                        type="text"
                        className="mt-1 block w-full"
                        value={form.data.subject}
                        onChange={(e) => form.setData('subject', e.target.value)}
                        placeholder="Ex: Demande d'audience pour projet urbain"
                        required
                    />
                                            <InputError message={errors.subject || form.errors.subject} className="mt-2" />
                </div>

                <div className="mt-6">
                    <InputLabel htmlFor="message" value="Message détaillé" />
                    <textarea
                        id="message"
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="4"
                        value={form.data.message}
                        onChange={(e) => form.setData('message', e.target.value)}
                        placeholder="Décrivez en détail l'objet de votre visite..."
                    />
                                            <InputError message={errors.message || form.errors.message} className="mt-2" />
                </div>
            </div>

            {/* Créneau sélectionné */}
            {selectedSlot && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 className="text-sm font-medium text-blue-900 mb-2">Créneau sélectionné</h3>
                    <p className="text-sm text-blue-800">
                        <strong>Date :</strong> {selectedSlot.date} <br />
                        <strong>Heure :</strong> {selectedSlot.start_time} - {selectedSlot.end_time}
                    </p>
                </div>
            )}

            {/* Date et heure */}
            <div className="bg-gray-50 rounded-lg p-4">
                <h3 className="text-sm font-medium text-gray-900 mb-4">Date et heure souhaitées</h3>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel htmlFor="preferred_date" value="Date souhaitée *" />
                        <TextInput
                            id="preferred_date"
                            type="date"
                            className="mt-1 block w-full"
                            value={form.data.preferred_date}
                            onChange={(e) => form.setData('preferred_date', e.target.value)}
                            min={new Date().toISOString().split('T')[0]}
                            required
                        />
                        <InputError message={errors.preferred_date || form.errors.preferred_date} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="preferred_time" value="Heure souhaitée *" />
                        <TextInput
                            id="preferred_time"
                            type="time"
                            className="mt-1 block w-full"
                            value={form.data.preferred_time}
                            onChange={(e) => form.setData('preferred_time', e.target.value)}
                            min="08:00"
                            max="17:00"
                            required
                        />
                        <InputError message={errors.preferred_time || form.errors.preferred_time} className="mt-2" />
                    </div>
                </div>
            </div>

            {/* Type de demande */}
            <div className="bg-gray-50 rounded-lg p-4">
                <h3 className="text-sm font-medium text-gray-900 mb-4">Type de demande</h3>
                
                <div>
                    <InputLabel htmlFor="priority" value="Type de demande *" />
                    <select
                        id="priority"
                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        value={form.data.priority}
                        onChange={(e) => form.setData('priority', e.target.value)}
                        required
                    >
                        <option value="normal">Demande standard</option>
                        <option value="urgent">Demande urgente</option>
                        <option value="official">Demande officielle</option>
                    </select>
                                            <InputError message={errors.priority || form.errors.priority} className="mt-2" />
                    
                    <p className="mt-2 text-sm text-gray-600">
                        {getPriorityDescription(form.data.priority)}
                    </p>
                </div>
            </div>

            {/* Pièces jointes */}
            <div className="bg-gray-50 rounded-lg p-4">
                <h3 className="text-sm font-medium text-gray-900 mb-4">Pièces jointes (optionnel)</h3>
                
                <div>
                    <InputLabel htmlFor="attachments" value="Ajouter des fichiers" />
                    <input
                        id="attachments"
                        type="file"
                        multiple
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                        className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        onChange={handleFileChange}
                    />
                    <p className="mt-2 text-xs text-gray-500">
                        Formats acceptés : PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (max 5MB par fichier, max 5 fichiers)
                    </p>
                                            <InputError message={errors.attachments || form.errors.attachments} className="mt-2" />
                </div>

                {/* Liste des fichiers sélectionnés */}
                {attachments.length > 0 && (
                    <div className="mt-4">
                        <h4 className="text-sm font-medium text-gray-700 mb-2">Fichiers sélectionnés :</h4>
                        <div className="space-y-2">
                            {attachments.map((file, index) => (
                                <div key={index} className="flex items-center justify-between bg-white p-2 rounded border">
                                    <div className="flex items-center">
                                        <svg className="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
                                        </svg>
                                        <span className="text-sm text-gray-700">{file.name}</span>
                                        <span className="text-xs text-gray-500 ml-2">
                                            ({(file.size / 1024 / 1024).toFixed(2)} MB)
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => removeFile(index)}
                                        className="text-red-600 hover:text-red-800 text-sm"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Avertissements */}
            {form.data.priority === 'urgent' && (
                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div className="flex">
                        <svg className="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                        <div>
                            <h3 className="text-sm font-medium text-yellow-800">Demande urgente</h3>
                            <p className="text-sm text-yellow-700 mt-1">
                                Les demandes urgentes nécessitent une justification valable. 
                                Les fausses déclarations peuvent entraîner le refus de votre demande.
                            </p>
                        </div>
                    </div>
                </div>
            )}

            {form.data.priority === 'official' && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div className="flex">
                        <svg className="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                        </svg>
                        <div>
                            <h3 className="text-sm font-medium text-blue-800">Demande officielle</h3>
                            <p className="text-sm text-blue-700 mt-1">
                                Les demandes officielles sont réservées aux institutions et organisations officielles.
                            </p>
                        </div>
                    </div>
                </div>
            )}

            {/* Boutons de soumission */}
            <div className="flex justify-end space-x-3 pt-6">
                {onCancel && (
                    <SecondaryButton type="button" onClick={onCancel}>
                        Annuler
                    </SecondaryButton>
                )}
                <PrimaryButton type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Envoi en cours...' : 'Soumettre la demande'}
                </PrimaryButton>
            </div>
        </form>
    );
} 