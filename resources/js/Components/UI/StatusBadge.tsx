import React from 'react';

interface StatusBadgeProps {
  status: 'pending' | 'accepted' | 'rejected' | 'canceled' | 'expired' | 'completed' | 'canceled_by_requester';
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

const StatusBadge: React.FC<StatusBadgeProps> = ({ 
  status, 
  size = 'md', 
  className = '' 
}) => {
  const statusConfig = {
    pending: {
      label: 'En attente',
      colors: 'bg-warning-100 text-warning-800 border-warning-200',
      dot: 'bg-warning-500'
    },
    accepted: {
      label: 'Accepté',
      colors: 'bg-success-100 text-success-800 border-success-200',
      dot: 'bg-success-500'
    },
    rejected: {
      label: 'Refusé',
      colors: 'bg-danger-100 text-danger-800 border-danger-200',
      dot: 'bg-danger-500'
    },
    canceled: {
      label: 'Annulé',
      colors: 'bg-secondary-100 text-secondary-800 border-secondary-200',
      dot: 'bg-secondary-500'
    },
    canceled_by_requester: {
      label: 'Annulé par le demandeur',
      colors: 'bg-secondary-100 text-secondary-800 border-secondary-200',
      dot: 'bg-secondary-500'
    },
    expired: {
      label: 'Expiré',
      colors: 'bg-gray-100 text-gray-800 border-gray-200',
      dot: 'bg-gray-500'
    },
    completed: {
      label: 'Terminé',
      colors: 'bg-primary-100 text-primary-800 border-primary-200',
      dot: 'bg-primary-500'
    }
  };

  const sizeClasses = {
    sm: 'px-2 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm',
    lg: 'px-4 py-2 text-base'
  };

  const dotSizes = {
    sm: 'w-1.5 h-1.5',
    md: 'w-2 h-2',
    lg: 'w-2.5 h-2.5'
  };

  const config = statusConfig[status];

  return (
    <span
      className={`
        inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium
        rounded-full border
        ${config.colors}
        ${sizeClasses[size]}
        ${className}
      `}
    >
      <span className={`${config.dot} ${dotSizes[size]} rounded-full`}></span>
      {config.label}
    </span>
  );
};

export default StatusBadge; 