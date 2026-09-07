<script setup>
import { provide } from 'vue';
import { useRoleMenu, RoleMenuKey } from '../composables/useRoleMenu.js';
import AppTopbar from '../Components/AppTopbar.vue';
import AppSidebar from '../Components/AppSidebar.vue';
import AppToast from '../Components/AppToast.vue';
import AppLogoutModal from '../Components/AppLogoutModal.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
    role: {
        type: String,
        default: '',
    },
    prefix: {
        type: String,
        default: '',
    },
    fullWidth: {
        type: Boolean,
        default: false,
    },
});

const menu = useRoleMenu(props);
provide(RoleMenuKey, menu);

// Destructure ke top-level agar ref ter-auto-unwrap di template.
// (Akses via `menu.xxx` tidak di-unwrap karena menu adalah plain object.)
const {
    showToast,
    toastMessage,
    toastType,
    toastIsPersistent,
    showLogoutModal,
    confirmLogout,
    page,
    activeRole,
    leaveImpersonation,
} = menu;
</script>

<template>
    <!-- Fixed Full Height Shell -->
    <div class="h-screen w-full overflow-hidden bg-slate-50 text-slate-800 flex flex-col font-sans selection:bg-amber-500 selection:text-white">
        <!-- 1. FIXED TOPBAR / HEADER -->
        <AppTopbar :title="title" />

        <!-- IMPERSONATION SIMULATION BANNER -->
        <div
            v-if="page.props.impersonating"
            class="w-full bg-gradient-to-r from-amber-700 via-amber-600 to-amber-700 text-white px-3 sm:px-6 py-2 text-xs flex items-center justify-between shadow-md z-40 shrink-0 border-b border-amber-500/40"
        >
            <div class="flex items-center gap-2 truncate">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-white/20 text-[11px] shrink-0">
                    <i class="fa-solid fa-user-gear"></i>
                </span>
                <span class="truncate">
                    Mode Simulasi Akun: Anda sedang masuk sebagai <strong class="text-amber-100 underline decoration-amber-300 underline-offset-2">{{ page.props.auth?.user?.name }}</strong> 
                    ({{ page.props.auth?.user?.role }}{{ page.props.auth?.user?.nama_kub ? ' • ' + page.props.auth?.user?.nama_kub : '' }})
                </span>
            </div>
            <button
                type="button"
                @click="leaveImpersonation"
                class="ml-3 shrink-0 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white text-amber-900 hover:bg-amber-50 font-bold text-[11px] shadow-sm transition cursor-pointer"
            >
                <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                <span>Kembali ke Super Admin</span>
            </button>
        </div>

        <!-- FLOATING TOAST NOTIFICATION -->
        <AppToast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            :persistent="toastIsPersistent"
            @close="showToast = false"
        />

        <!-- LOGOUT CONFIRMATION MODAL -->
        <AppLogoutModal
            :show="showLogoutModal"
            @confirm="confirmLogout"
            @cancel="showLogoutModal = false"
        />

        <!-- Body Area: Fixed Sidebar + Scrollable Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- 2. SIDEBAR (DESKTOP + MOBILE DRAWER) -->
            <AppSidebar />

            <!-- 3. FULL HEIGHT MAIN CONTENT AREA -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden h-full">
                <!-- Page Full Body -->
                <main class="flex-1 overflow-y-auto flex flex-col min-h-0 custom-scrollbar">
                    <div :class="['flex-1 flex flex-col min-h-0', fullWidth ? 'p-2 sm:p-3' : 'px-3 sm:px-4 lg:px-5 py-3 sm:py-4']">
                        <slot />
                    </div>
                </main>

                <!-- 4. FIXED BACKEND FOOTER -->
                <footer class="h-10 w-full shrink-0 border-t border-slate-200/80 bg-white px-4 sm:px-6 flex items-center justify-between text-xs text-slate-500 z-20">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ page.props.app?.name || 'SIPAROKI' }} &copy; {{ new Date().getFullYear() }} {{ page.props.app?.nama_paroki || 'Paroki St. Vincentius a Paulo' }}</span>
                    </div>
                    <div class="hidden sm:flex items-center gap-4 text-[11px]">
                        <span class="font-medium text-slate-600">Peran Aktif: <b class="text-amber-800">{{ activeRole }}</b></span>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.4);
    border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.7);
}
</style>
