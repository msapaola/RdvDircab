import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    method,
    as,
    ...props
}) {
    // Filtrer les props pour éviter de passer method/as si ils sont null/undefined
    const linkProps = { ...props };
    
    // Si method et as sont fournis et valides, les ajouter aux props
    if (method && as && typeof method === 'string' && typeof as === 'string') {
        linkProps.method = method;
        linkProps.as = as;
    }

    return (
        <Link
            {...linkProps}
            className={`flex w-full items-start border-l-4 py-2 pe-4 ps-3 ${
                active
                    ? 'border-indigo-400 bg-indigo-50 text-indigo-700 focus:border-indigo-700 focus:bg-indigo-100 focus:text-indigo-800'
                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800'
            } text-base font-medium transition duration-150 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
