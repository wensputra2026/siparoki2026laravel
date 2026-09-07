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
const toastIsPersistent = ref(false);
let toastTimer = null;

const activeRole = ref('Super Admin');

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const isChildRelationOrCriticalMessage = (msg, type) => {
    if (!msg) return false;
    const lower = String(msg).toLowerCase();
    return (
        type === 'warning' ||
        type === 'error' ||
        lower.includes('terhubung dengan') ||
        lower.includes('data terkait') ||
        lower.includes('data turunan') ||
        lower.includes('relasi data') ||
        lower.includes('anggota keluarga') ||
        lower.includes('tidak dapat dihapus') ||
        lower.includes('dilewati karena') ||
        lower.includes('konten aktif') ||
        lower.includes('masih memiliki') ||
        lower.includes('pindahkan atau') ||
        lower.includes('dibatalkan karena') ||
        lower.includes('foreign key') ||
        lower.includes('integritas data') ||
        msg.length > 110
    );
};

const triggerToast = (msg, type = 'success', options = {}) => {
    if (!msg) return;
    toastMessage.value = msg;
    toastType.value = type;
    showToast.value = true;
    if (toastTimer) {
        clearTimeout(toastTimer);
        toastTimer = null;
    }

    const persistent =
        options.persistent === true ||
        options.autoHide === false ||
        isChildRelationOrCriticalMessage(msg, type);

    toastIsPersistent.value = persistent;

    // Jika pesan adalah info data turunan / peringatan relasi / error / long text, JANGAN autohide sehingga bisa dibaca santai dan lama
    if (!persistent) {
        const duration = typeof options === 'number' ? options : (options.duration || 6000);
        toastTimer = setTimeout(() => {
            showToast.value = false;
            toastIsPersistent.value = false;
        }, duration);
    }
};

// (Flash watcher is initialized inside init() with the reactive page instance)

const confirmLogout = () => {
    showLogoutModal.value = false;
    router.post('/logout');
};

const userAvatar = computed(() => {
    const foto = _page?.props.auth?.user?.foto;
    if (!foto) return '/images/avatar-default.jpg';
    if (foto.startsWith('http://') || foto.startsWith('https://') || foto.startsWith('data:')) {
        return foto;
    }
    const clean = foto.replace(/^\/?(public\/)?/, '').replace(/^\//, '');
    if (!clean.includes('/')) {
        return `/uploads/users/${clean}`;
    }
    return '/' + clean;
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
    const roleVal = _page?.props.auth?.user?.role;
    let r = typeof roleVal === 'string' ? roleVal : (roleVal?.nama_role || roleVal?.slug || 'Super Admin');
    if (r === 'Ketua KUB') return 'Admin KUB';
    return r;
});


const isSuperAdmin = computed(() => {
    if (Boolean(_page?.props.auth?.user?.is_super_admin)) return true;
    if (Number(_page?.props.auth?.user?.role_id) === 1) return true;
    const r = (userActualRole.value || '').toLowerCase();
    return r.includes('super') || r.includes('admin paroki');
});

const onScopeChange = (type, val) => {
    if (typeof window === 'undefined') return;
    router.post('/api/set-active-scope', { scope_type: type, scope_id: val }, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            const currentPath = _page?.url?.split('?')[0] || window.location.pathname;
            const url = new URL(window.location.origin + currentPath);
            if (val) {
                url.searchParams.set(type, val);
            } else {
                url.searchParams.delete(type);
            }
            router.visit(url.pathname + url.search, { preserveState: false, preserveScroll: true });
        },
    });
};

const leaveImpersonation = () => {
    router.post('/impersonate/leave');
};

const resolveRoleFromPath = () => {
    const rawUrl = _page?.url || (typeof window !== 'undefined' ? window.location.pathname : '') || '';
    const path = rawUrl.toLowerCase();
    if (path.startsWith('/wilayah')) return 'Admin Wilayah';
    if (path.startsWith('/kapela') || path.startsWith('/stasi')) return 'Admin Kapela / Stasi';
    if (path.startsWith('/kub')) return 'Admin KUB';
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
    if (_page?.props.role) {
        if (_page.props.role === 'Ketua KUB') return 'Admin KUB';
        if (roleMenus[_page.props.role]) return _page.props.role;
    }
    const userRole = _page?.props.auth?.user?.role;
    const r = typeof userRole === 'string' ? userRole : (userRole?.nama_role || userRole?.slug || '');
    if (r === 'Ketua KUB' || r === 'Admin KUB') return 'Admin KUB';
    if (r && roleMenus[r]) {
        return r;
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

    let activeGroupName = null;
    currentMenuTree.value.forEach((section) => {
        section.menus.forEach((menu) => {
            if (menu.submenus && isGroupActive(menu)) {
                activeGroupName = menu.name;
            }
        });
    });

    Object.keys(openGroups.value).forEach((key) => {
        openGroups.value[key] = (key === activeGroupName);
    });
};

const toggleGroup = (groupName) => {
    const isCurrentlyOpen = !!openGroups.value[groupName];
    // Tutup grup lain agar rapi (single accordion)
    Object.keys(openGroups.value).forEach((key) => {
        openGroups.value[key] = false;
    });
    openGroups.value[groupName] = !isCurrentlyOpen;
};

// Inisialisasi watcher & lifecycle HANYA SEKALI (singleton), agar remount
// akibat router.reload() tidak memicu syncActiveGroup() atau me-reset state.
const init = (page) => {
    if (_initialized) return;
    _initialized = true;

    // 1. Router event listener: langsung menangkap respon Inertia beserta flash message
    router.on('success', (event) => {
        const flash = event.detail.page?.props?.flash || usePage().props?.flash;
        if (flash?.warning) {
            triggerToast(flash.warning, 'warning');
        } else if (flash?.error) {
            triggerToast(flash.error, 'error');
        } else if (flash?.info) {
            triggerToast(flash.info, 'info');
        } else if (flash?.success) {
            triggerToast(flash.success, 'success');
        } else if (flash?.status) {
            triggerToast(flash.status, 'success');
        }
    });

    router.on('error', (errors) => {
        const firstError = typeof errors === 'object' ? Object.values(errors)[0] : null;
        if (firstError) {
            triggerToast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
        } else {
            triggerToast('Terjadi kesalahan validasi atau sistem.', 'error');
        }
    });

    // 2. Watch for reactive flash messages from Laravel
    watch(
        () => page.props.flash,
        (flash) => {
            if (flash?.warning) triggerToast(flash.warning, 'warning');
            else if (flash?.error) triggerToast(flash.error, 'error');
            else if (flash?.info) triggerToast(flash.info, 'info');
            else if (flash?.success) triggerToast(flash.success, 'success');
            else if (flash?.status) triggerToast(flash.status, 'success');
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

    watch(
        () => page.props.activeScope,
        (scope) => {
            if (scope) {
                if (scope.kub_id) selectedKubId.value = scope.kub_id;
                if (scope.wilayah_id) selectedWilayahId.value = scope.wilayah_id;
                if (scope.kapela_id) selectedKapelaId.value = scope.kapela_id;
                if (scope.pastor_id) selectedPastorId.value = scope.pastor_id;
            }
        },
        { immediate: true, deep: true }
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

export { triggerToast, toastIsPersistent };

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
        toastIsPersistent,
        triggerToast,
        userAvatar,
        userName,
        pastorsList,
        selectedPastorId,
        wilayahList,
        selectedWilayahId,
        kapelaList,
        selectedKapelaId,
        kubList,
        selectedKubId,
        onScopeChange,
        leaveImpersonation,
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
