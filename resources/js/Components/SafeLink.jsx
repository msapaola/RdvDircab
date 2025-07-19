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
    if (!href) {
        console.warn('SafeLink: href is required');
        return null;
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