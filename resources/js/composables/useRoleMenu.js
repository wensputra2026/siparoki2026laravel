// Composable yang mengelola seluruh state & logika panel kontrol backend
// (sidebar, role switcher, scope selector, toast, active menu).
// Dipisah dari AppLayout.vue agar komponen layout hanya fokus pada tampilan.
//
// PENTING: state dibuat di level modul (singleton) sehingga PERSISTEN meski
// komponen AppLayout/AppSidebar di-remount ulang (mis. saat router.reload()).
// Tanpa ini, klik tombol Reload memicu ulang inisialisasi openGroups/isSidebarOpen
// dan syncActiveGroup(), sehingga menu sidebar "bergoyang"/bergeser.

import { ref, computed, watch, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { roleMenus, rolePrefixMap } from '../menu/roleMenus.js';

// Kunci inject bersama agar AppLayout, AppTopbar, dan AppSidebar
// berbagi satu instance state (bukan membuat state terpisah).
export const RoleMenuKey = Symbol('roleMenu');

let _page = null;
let _initialized = false;

const layoutStorageKey = 'siparoki.backend.layout';

// Baca state layout SEBELUM paint agar lebar sidebar tidak berubah
// mendadak saat mount (penyebab halaman "lompat" saat refresh).
const initialLayout = (() => {
    try {
        return JSON.parse(sessionStorage.getItem(layoutStorageKey) || '{}');
    } catch (e) {
        return {};
    }
})();

const isSidebarOpen = ref(
    typeof initialLayout.isSidebarOpen === 'boolean' ? initialLayout.isSidebarOpen : true
);
const isMobileOpen = ref(false);
const showLogoutModal = ref(false);
// Flag untuk menunda transisi lebar sidebar sampai setelah paint pertama.
const isMounted = ref(false);

const openGroups = ref({
    'Data Gerejawi': false,
    'Wilayah Sipil': false,
    'Data Referensi': false,
    'Pelayanan Paroki': false,
    'Pelayanan Pastoral': false,
    'Data Sakramen': false,
    'Lapak & Toko': false,
    'Sekretariat Paroki': false,
    'Registrasi Surat': false,
    'Keuangan & Aset': false,
    'Iuran & Persembahan': false,
    'Website Paroki': false,
    'Warta & Konten': false,
    'Pengaturan Web': false,
    'Wilayah Paroki': false,
    'Wilayah & KUB': false,
    ...(initialLayout.openGroups && typeof initialLayout.openGroups === 'object' ? initialLayout.openGroups : {}),
});

// Scope / Target Context Selection State
const selectedPastorId = ref('');
const selectedWilayahId = ref('');
const selectedKapelaId = ref('');
const selectedKubId = ref('');

// Toast notification state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');
let toastTimer = null;

const activeRole = ref('Super Admin');

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const triggerToast = (msg, type = 'success') => {
    if (!msg) return;
    toastMessage.value = msg;
    toastType.value = type;
    showToast.value = true;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        showToast.value = false;
    }, 4000);
};

// (Flash watcher is initialized inside init() with the reactive page instance)

const confirmLogout = () => {
    showLogoutModal.value = false;
    window.location.href = '/logout';
};

const userAvatar = computed(() => {
    const foto = _page?.props.auth?.user?.foto;
    if (!foto) return null;
    if (foto.startsWith('http://') || foto.startsWith('https://') || foto.startsWith('data:')) {
        return foto;
    }
    if (foto.startsWith('/')) {
        return foto;
    }
    return '/' + foto;
});

const userName = computed(() => {
    return _page?.props.auth?.user?.name || 'Profil';
});

const pastorsList = computed(() => _page?.props.scopeOptions?.pastors || []);
const wilayahList = computed(() => _page?.props.scopeOptions?.wilayah || []);
const kapelaList = computed(() => _page?.props.scopeOptions?.kapela || []);
const kubList = computed(() => _page?.props.scopeOptions?.kub || []);

// Current authenticated user role or active preview role
const userActualRole = computed(() => {
    return _page?.props.auth?.user?.role || '';
});

const isSuperAdmin = computed(() => {
    const r = (userActualRole.value || '').toLowerCase();
    return r.includes('super');
});

const resolveRoleFromPath = () => {
    const rawUrl = _page?.url || (typeof window !== 'undefined' ? window.location.pathname : '') || '';
    const path = rawUrl.toLowerCase();
    if (path.startsWith('/wilayah')) return 'Admin Wilayah';
    if (path.startsWith('/kapela') || path.startsWith('/stasi')) return 'Admin Kapela / Stasi';
    if (path.startsWith('/kub')) return 'Ketua KUB';
    if (path.startsWith('/bendahara')) return 'Bendahara';
    if (path.startsWith('/penulis')) return 'Penulis';
    if (path.startsWith('/umat')) return 'Umat';
    if (path.startsWith('/pastor')) return 'Pastor';
    if (path.startsWith('/paroki')) return 'Admin Paroki';
    if (path.startsWith('/superadmin') || path.startsWith('/v2')) return 'Super Admin';
    return '';
};

const resolveActiveRole = () => {
    const fromPath = resolveRoleFromPath();
    if (fromPath && roleMenus[fromPath]) {
        return fromPath;
    }
    if (_page?.props.role && roleMenus[_page.props.role]) {
        return _page.props.role;
    }
    const userRole = _page?.props.auth?.user?.role;
    if (userRole && roleMenus[userRole]) {
        return userRole;
    }
    return 'Super Admin';
};

const onRoleChange = () => {
    const targetUrl = rolePrefixMap[activeRole.value] || '/superadmin';
    router.visit(targetUrl);
};

// Compute current active menu tree based on selected role
const currentMenuTree = computed(() => {
    return roleMenus[activeRole.value] || roleMenus['Super Admin'];
});

const rolePrefix = computed(() => {
    return rolePrefixMap[activeRole.value] || '/superadmin';
});

const getHref = (rawHref) => {
    if (!rawHref) return '#';
    if (rawHref.startsWith('http') || rawHref === '/') return rawHref;
    if (rawHref.startsWith('/admin')) return rawHref;

    const clean = rawHref.replace(/^\/(v2|superadmin|paroki|pastor|wilayah|kapela|kub|bendahara|penulis|umat)/, '');

    if (clean === '/dashboard' || clean === '') {
        return `${rolePrefix.value}/dashboard`;
    }

    return `${rolePrefix.value}${clean.startsWith('/') ? clean : '/' + clean}`;
};

const isItemActive = (rawHref) => {
    if (!rawHref) return false;
    const targetHref = getHref(rawHref);
    const current = _page.url.split('?')[0].replace(/\/$/, '') || '/';
    const target = targetHref.split('?')[0].replace(/\/$/, '') || '/';
    const currentQuery = new URLSearchParams(_page.url.split('?')[1] || '');
    const targetQuery = new URLSearchParams(targetHref.split('?')[1] || '');

    if (targetQuery.size > 0) {
        if (current !== target) return false;
        return Array.from(targetQuery.entries()).every(([key, value]) => currentQuery.get(key) === value);
    }

    if (target.endsWith('/konten') && currentQuery.has('tipe')) {
        return false;
    }

    if (current === target) return true;

    if (target !== '/' && target !== rolePrefix.value) {
        return current.startsWith(target + '/');
    }

    return false;
};

const isGroupActive = (menu) => {
    if (!menu.submenus) return isItemActive(menu.href);
    return menu.submenus.some((sub) => isItemActive(sub.href));
};

const closeAllGroups = () => {
    Object.keys(openGroups.value).forEach((key) => {
        openGroups.value[key] = false;
    });
};

const isDashboardPage = () => {
    const current = _page.url.split('?')[0].replace(/\/$/, '') || '/';
    return current === rolePrefix.value;
};

const syncActiveGroup = () => {
    if (isDashboardPage()) {
        closeAllGroups();
        return;
    }

    currentMenuTree.value.forEach((section) => {
        section.menus.forEach((menu) => {
            if (menu.submenus && isGroupActive(menu)) {
                openGroups.value[menu.name] = true;
            }
        });
    });
};

const toggleGroup = (groupName) => {
    openGroups.value[groupName] = openGroups.value[groupName] === false ? true : false;
};

// Inisialisasi watcher & lifecycle HANYA SEKALI (singleton), agar remount
// akibat router.reload() tidak memicu syncActiveGroup() atau me-reset state.
const init = (page) => {
    if (_initialized) return;
    _initialized = true;

    // Watch for incoming flash messages from Laravel (e.g. login success alert)
    watch(
        () => page.props.flash,
        (flash) => {
            if (flash?.success) triggerToast(flash.success, 'success');
            else if (flash?.status) triggerToast(flash.status, 'success');
            else if (flash?.error) triggerToast(flash.error, 'error');
        },
        { deep: true, immediate: true }
    );

    watch(
        () => [page.props.role, page.url],
        () => {
            activeRole.value = resolveActiveRole();
        },
        { immediate: true }
    );

    onMounted(() => {
        // Tunda satu frame agar transisi lebar tidak animasi saat paint pertama.
        requestAnimationFrame(() => {
            isMounted.value = true;
        });
        syncActiveGroup();
    });

    watch(
        [isSidebarOpen, openGroups],
        () => {
            try {
                sessionStorage.setItem(layoutStorageKey, JSON.stringify({
                    isSidebarOpen: isSidebarOpen.value,
                    openGroups: openGroups.value,
                }));
            } catch (error) {
                // Storage can fail in private mode; navigation still works normally.
            }
        },
        { deep: true }
    );

    watch(
        () => [page.url, activeRole.value],
        () => {
            syncActiveGroup();
        }
    );
};

export function useRoleMenu(props) {
    const page = usePage();
    _page = page;
    init(page);
    return {
        page,
        isSidebarOpen,
        isMounted,
        toggleSidebar,
        isMobileOpen,
        showLogoutModal,
        confirmLogout,
        showToast,
        toastMessage,
        toastType,
        userAvatar,
        userName,
        selectedPastorId,
        selectedWilayahId,
        selectedKapelaId,
        selectedKubId,
        isSuperAdmin,
        activeRole,
        onRoleChange,
        currentMenuTree,
        openGroups,
        toggleGroup,
        getHref,
        isItemActive,
        isGroupActive,
    };
}
