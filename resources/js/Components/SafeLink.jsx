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

    // Si method et as sont fournis et valides, les utiliser
    if (method && as && typeof method === 'string' && typeof as === 'string') {
        return (
            <Link
                href={href}
                method={method}
                as={as}
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