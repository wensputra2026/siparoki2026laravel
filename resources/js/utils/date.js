/**
 * Utility functions for date formatting in Indonesian locale (dd/mm/yyyy)
 */

/**
 * Format any ISO/SQL date string or Date object to 'DD/MM/YYYY'
 * Example: '1995-12-31' -> '31/12/1995'
 */
export function formatDateId(d, fallback = '—') {
    if (!d) return fallback;
    try {
        const str = String(d).trim();
        // If already in YYYY-MM-DD format, split directly to prevent timezone shift
        const clean = str.substring(0, 10);
        const parts = clean.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            const y = parts[0];
            const m = parts[1].padStart(2, '0');
            const day = parts[2].padStart(2, '0');
            return `${day}/${m}/${y}`;
        }

        const date = new Date(d);
        if (isNaN(date.getTime())) return fallback;
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    } catch {
        return fallback;
    }
}

/**
 * Format datetime to 'DD/MM/YYYY HH:mm'
 */
export function formatDateTimeId(d, fallback = '—') {
    if (!d) return fallback;
    try {
        const date = new Date(d);
        if (isNaN(date.getTime())) return fallback;
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    } catch {
        return fallback;
    }
}
