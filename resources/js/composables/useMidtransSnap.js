import { ref } from 'vue';

export function useMidtransSnap() {
    const isProcessing = ref(false);

    const loadSnap = (clientKey, isProd) => {
        return new Promise((resolve, reject) => {
            const scriptId = 'midtrans-snap-js-sdk';
            const existing = document.getElementById(scriptId);
            if (existing && window.snap) {
                return resolve(window.snap);
            }
            const snapUrl = isProd
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';
            const script = document.createElement('script');
            script.id = scriptId;
            script.src = snapUrl;
            if (clientKey) {
                script.setAttribute('data-client-key', clientKey);
            }
            script.onload = () => resolve(window.snap);
            script.onerror = () => reject(new Error('Gagal memuat SDK Midtrans Snap.js'));
            document.head.appendChild(script);
        });
    };

    const payWithSnap = async (params, callbacks = {}) => {
        isProcessing.value = true;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/midtrans/snap-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(params),
            });

            const data = await res.json();
            if (!data.success || !data.snap_token) {
                throw new Error(data.message || 'Gagal menghasilkan token pembayaran Midtrans.');
            }

            await loadSnap(data.client_key, data.is_production);

            if (window.snap) {
                window.snap.pay(data.snap_token, {
                    onSuccess: callbacks.onSuccess || ((result) => console.log('Snap success', result)),
                    onPending: callbacks.onPending || ((result) => console.log('Snap pending', result)),
                    onError: callbacks.onError || ((result) => console.error('Snap error', result)),
                    onClose: callbacks.onClose || (() => console.log('Snap closed')),
                });
            }
            return data;
        } finally {
            isProcessing.value = false;
        }
    };

    return {
        isProcessing,
        payWithSnap,
        loadSnap,
    };
}
