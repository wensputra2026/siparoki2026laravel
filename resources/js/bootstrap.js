import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Safe route fallback in case any template calls route()
if (typeof window !== 'undefined' && typeof window.route === 'undefined') {
    window.route = function (name, params) {
        if (!name) return '#';
        // Common fallback mappings
        if (typeof name === 'string') {
            if (name.includes('.umat.mutasi') && Array.isArray(params) && params[0]) {
                const prefix = name.split('.')[1] || 'superadmin';
                return `/${prefix}/umat/${params[0]}/mutasi`;
            }
            if (name.includes('.umat.pisah') && Array.isArray(params) && params[0]) {
                const prefix = name.split('.')[1] || 'superadmin';
                return `/${prefix}/umat/${params[0]}/pisah-kk`;
            }
            if (name.includes('.umat.riwayat') && Array.isArray(params) && params[0]) {
                const prefix = name.split('.')[1] || 'superadmin';
                return `/${prefix}/umat/${params[0]}/riwayat`;
            }
            if (name.includes('.umat')) {
                const prefix = name.split('.')[1] || 'superadmin';
                return `/${prefix}/umat`;
            }
        }
        return '#';
    };
}
