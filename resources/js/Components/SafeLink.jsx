import { Link } from '@inertiajs/react';

export default function SafeLink({ 
    href, 
    method, 
    as, 
    className = '', 
    children, 
    ...props 
}) {
    // Vérifier que href existe et est valide
    if (!href || href === '#' || href === '') {
        // Retourner un span au lieu de null pour éviter les erreurs
        return (
            <span className={className} {...props}>
                {children}
            </span>
        );
    }

    // Vérification défensive pour method et as
    const safeMethod = method && typeof method === 'string' ? method : null;
    const safeAs = as && typeof as === 'string' ? as : null;

    // Si method et as sont fournis et valides, les utiliser
    if (safeMethod && safeAs) {
        return (
            <Link
                href={href}
                method={safeMethod}
                as={safeAs}
                className={className}
                {...props}
            >
                {children}
            </Link>
        );
    }

    // Sinon, utiliser Link normalement
    return (
        <Link
            href={href}
            className={className}
            {...props}
        >
            {children}
        </Link>
    );
} 