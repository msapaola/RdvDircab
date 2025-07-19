import React, { useState } from 'react';

export default function AttachmentViewer({ attachments, appointmentId }) {
    const [previewUrl, setPreviewUrl] = useState(null);
    const [previewFileName, setPreviewFileName] = useState(null);

    const getFileIcon = (fileName) => {
        const isImage = /\.(jpg|jpeg|png|gif|webp|svg|bmp)$/i.test(fileName);
        const isPdf = /\.pdf$/i.test(fileName);
        const isText = /\.(txt|md|log|csv|json|xml|html|css|js)$/i.test(fileName);
        const isWord = /\.(doc|docx)$/i.test(fileName);
        const isExcel = /\.(xls|xlsx|csv)$/i.test(fileName);
        const isArchive = /\.(zip|rar|7z|tar|gz)$/i.test(fileName);
        const isVideo = /\.(mp4|avi|mov|wmv|flv|webm)$/i.test(fileName);
        const isAudio = /\.(mp3|wav|ogg|flac|aac)$/i.test(fileName);

        if (isImage) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clipRule="evenodd" />
                    </svg>
                ),
                color: 'text-blue-500',
                bgColor: 'bg-blue-50',
                borderColor: 'border-blue-200'
            };
        }
        if (isPdf) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
                    </svg>
                ),
                color: 'text-red-500',
                bgColor: 'bg-red-50',
                borderColor: 'border-red-200'
            };
        }
        if (isWord) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
                    </svg>
                ),
                color: 'text-blue-600',
                bgColor: 'bg-blue-50',
                borderColor: 'border-blue-200'
            };
        }
        if (isExcel) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
                    </svg>
                ),
                color: 'text-green-600',
                bgColor: 'bg-green-50',
                borderColor: 'border-green-200'
            };
        }
        if (isText) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
                    </svg>
                ),
                color: 'text-green-500',
                bgColor: 'bg-green-50',
                borderColor: 'border-green-200'
            };
        }
        if (isArchive) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z" />
                        <path d="M4 3h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                ),
                color: 'text-purple-500',
                bgColor: 'bg-purple-50',
                borderColor: 'border-purple-200'
            };
        }
        if (isVideo) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                        <path d="M8 11l3-1.5v3L8 14v-3z" />
                    </svg>
                ),
                color: 'text-red-600',
                bgColor: 'bg-red-50',
                borderColor: 'border-red-200'
            };
        }
        if (isAudio) {
            return {
                icon: (
                    <svg className="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.793L4.5 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.5l3.883-3.793a1 1 0 011.617.793zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                ),
                color: 'text-yellow-500',
                bgColor: 'bg-yellow-50',
                borderColor: 'border-yellow-200'
            };
        }
        
        return {
            icon: (
                <svg className="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
                </svg>
            ),
            color: 'text-gray-500',
            bgColor: 'bg-gray-50',
            borderColor: 'border-gray-200'
        };
    };

    const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    const canPreview = (fileName) => {
        const isImage = /\.(jpg|jpeg|png|gif|webp|svg|bmp)$/i.test(fileName);
        const isPdf = /\.pdf$/i.test(fileName);
        const isText = /\.(txt|md|log|csv|json|xml|html|css|js)$/i.test(fileName);
        return isImage || isPdf || isText;
    };

    const openPreview = (attachment) => {
        const previewUrl = route('admin.appointments.attachments.preview', [appointmentId, attachment.name]);
        setPreviewUrl(previewUrl);
        setPreviewFileName(attachment.name);
    };

    const closePreview = () => {
        setPreviewUrl(null);
        setPreviewFileName(null);
    };

    if (!attachments || attachments.length === 0) {
        return (
            <div className="mt-6">
                <h3 className="text-sm font-medium text-gray-500 mb-2">Pièces jointes</h3>
                <div className="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p className="mt-2 text-sm text-gray-500">Aucune pièce jointe</p>
                </div>
            </div>
        );
    }

    return (
        <div className="mt-6">
            <h3 className="text-sm font-medium text-gray-500 mb-3">
                Pièces jointes ({attachments.length})
            </h3>
            
            <div className="space-y-3">
                {attachments.map((attachment, index) => {
                    const fileInfo = getFileIcon(attachment.name);
                    const fileSize = attachment.size ? formatFileSize(attachment.size) : 'Taille inconnue';
                    
                    return (
                        <div 
                            key={index} 
                            className={`${fileInfo.bgColor} ${fileInfo.borderColor} border rounded-lg p-4 hover:shadow-md transition-shadow`}
                        >
                            <div className="flex items-center justify-between mb-3">
                                <div className="flex items-center space-x-3">
                                    <div className={`${fileInfo.color} flex-shrink-0`}>
                                        {fileInfo.icon}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-medium text-gray-900 truncate">
                                            {attachment.name}
                                        </p>
                                        <p className="text-xs text-gray-500">
                                            {fileSize}
                                            {attachment.mime_type && ` • ${attachment.mime_type}`}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="flex space-x-2">
                                <a
                                    href={route('admin.appointments.attachments.download', [appointmentId, attachment.name])}
                                    className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                >
                                    <svg className="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clipRule="evenodd" />
                                    </svg>
                                    Télécharger
                                </a>
                                
                                {canPreview(attachment.name) && (
                                    <button
                                        onClick={() => openPreview(attachment)}
                                        className="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                    >
                                        <svg className="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fillRule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clipRule="evenodd" />
                                        </svg>
                                        {/\.(jpg|jpeg|png|gif|webp|svg|bmp)$/i.test(attachment.name) ? 'Voir' : 'Lire'}
                                    </button>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Modal de prévisualisation */}
            {previewUrl && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div className="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div className="absolute inset-0 bg-gray-500 opacity-75" onClick={closePreview}></div>
                        </div>

                        <div className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                            <div className="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-medium text-gray-900">
                                        Prévisualisation : {previewFileName}
                                    </h3>
                                    <button
                                        onClick={closePreview}
                                        className="text-gray-400 hover:text-gray-600 transition-colors"
                                    >
                                        <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div className="max-h-96 overflow-auto">
                                    {/\.(jpg|jpeg|png|gif|webp|svg|bmp)$/i.test(previewFileName) ? (
                                        <img 
                                            src={previewUrl} 
                                            alt={previewFileName}
                                            className="max-w-full h-auto mx-auto"
                                            onError={(e) => {
                                                e.target.style.display = 'none';
                                                e.target.nextSibling.style.display = 'block';
                                            }}
                                        />
                                    ) : /\.pdf$/i.test(previewFileName) ? (
                                        <div className="relative">
                                            <iframe
                                                src={previewUrl}
                                                className="w-full h-96 border-0"
                                                title={previewFileName}
                                                onError={(e) => {
                                                    console.error('Erreur de chargement PDF:', e);
                                                }}
                                            />
                                            <div className="absolute top-2 right-2">
                                                <a
                                                    href={route('admin.appointments.attachments.download', [appointmentId, previewFileName])}
                                                    className="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                                                    title="Télécharger"
                                                >
                                                    <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fillRule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clipRule="evenodd" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    ) : /\.(txt|md|log|csv|json|xml|html|css|js)$/i.test(previewFileName) ? (
                                        <iframe
                                            src={previewUrl}
                                            className="w-full h-96 border-0"
                                            title={previewFileName}
                                        />
                                    ) : (
                                        <div className="text-center py-8">
                                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p className="mt-2 text-sm text-gray-500">
                                                Prévisualisation non disponible pour ce type de fichier
                                            </p>
                                            <a
                                                href={route('admin.appointments.attachments.download', [appointmentId, previewFileName])}
                                                className="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                                            >
                                                Télécharger le fichier
                                            </a>
                                        </div>
                                    )}
                                </div>
                            </div>
                            
                            <div className="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="button"
                                    onClick={closePreview}
                                    className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Fermer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
} 