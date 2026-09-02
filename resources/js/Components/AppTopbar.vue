<script setup>
import { ref, inject, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { RoleMenuKey } from '../composables/useRoleMenu.js';
import { roleMenus } from '../menu/roleMenus.js';
import AppNotificationBell from './AppNotificationBell.vue';
import AppChatWidget from './AppChatWidget.vue';

defineProps({
    title: { type: String, default: 'Dashboard' },
});

const {
    page,
    isSidebarOpen,
    toggleSidebar,
    isMobileOpen,
    isSuperAdmin,
    activeRole,
    onRoleChange,
    pastorsList,
    selectedPastorId,
    wilayahList,
    selectedWilayahId,
    kapelaList,
    selectedKapelaId,
    kubList,
    selectedKubId,
    onScopeChange,
    getHref,
    isItemActive,
    userAvatar,
    userName,
    showLogoutModal,
} = inject(RoleMenuKey);

const isUserMenuOpen = ref(false);

const returnToSuperAdmin = () => {
    activeRole.value = 'Super Admin';
    onRoleChange();
};

const closeUserMenu = (e) => {
    if (!e.target.closest('#user-menu-container')) {
        isUserMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeUserMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeUserMenu);
});
</script>

<template>
    <!-- 1. FIXED TOPBAR / HEADER (FULLY RESPONSIVE) -->
    <header class="h-16 w-full shrink-0 border-b border-slate-200/80 bg-white/95 backdrop-blur-md z-30 shadow-xs flex items-center justify-between px-2.5 sm:px-4 md:px-6 gap-2">
        <!-- Left: Toggle & Brand (Never Squished) -->
        <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
            <!-- Sidebar toggle desktop -->
            <button
                @click="toggleSidebar"
                type="button"
                class="hidden lg:flex items-center justify-center w-8.5 h-8.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer shrink-0 border border-slate-200/70"
                title="Toggle Sidebar"
            >
                <i class="fa-solid fa-bars text-sm"></i>
            </button>

            <!-- Sidebar toggle mobile -->
            <button
                @click="isMobileOpen = !isMobileOpen"
                type="button"
                class="lg:hidden flex items-center justify-center w-8.5 h-8.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer shrink-0 border border-slate-200/70"
                title="Buka Menu"
            >
                <i class="fa-solid fa-bars text-sm"></i>
            </button>

            <!-- Logo & Title -->
            <div class="flex items-center gap-2 shrink-0">
                <div class="w-8 h-8 sm:w-8.5 sm:h-8.5 rounded-xl bg-amber-500/10 p-0.5 border border-amber-400/40 shrink-0 overflow-hidden flex items-center justify-center">
                    <img
                        :src="page.props.app?.logo || '/images/church-logo.png'"
                        :alt="page.props.app?.nama_paroki || 'Logo'"
                        class="w-full h-full object-cover rounded-lg"
                        @error="(e) => { e.target.onerror = null; e.target.src = '/images/church-logo.png'; }"
                    />
                </div>
                <div class="min-w-0">
                    <h1 class="font-black text-xs sm:text-sm md:text-base tracking-tight text-slate-900 leading-none">
                        SIPAROKI
                    </h1>
                    <p class="text-[10px] text-slate-500 hidden 2xl:block mt-0.5 truncate max-w-[200px]">
                        {{ page.props.app?.nama_paroki || title }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Role Switcher, Chat, Notif & User Actions -->
        <div class="flex items-center gap-1 sm:gap-1.5 md:gap-2 shrink-0 justify-end">
            <!-- Role Switcher (for Super Admin) -->
            <div v-if="isSuperAdmin" class="flex items-center gap-1 bg-slate-100/90 p-0.5 sm:p-1 rounded-xl border border-slate-200 text-xs shrink-0">
                <select
                    v-model="activeRole"
                    @change="onRoleChange"
                    class="bg-white border border-slate-200 rounded-lg px-1 sm:px-2 py-1 text-[11px] sm:text-xs font-bold text-amber-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs w-auto max-w-[80px] xs:max-w-[100px] sm:max-w-[125px] md:max-w-[140px] truncate"
                    title="Ganti Peran Aktif (Simulasi Hak Akses)"
                >
                    <option v-for="r in Object.keys(roleMenus)" :key="r" :value="r">
                        {{ r }}
                    </option>
                </select>

                <!-- Dynamic Pastor Selector (Ultrawide Desktop only) -->
                <template v-if="activeRole === 'Pastor' && pastorsList.length > 0">
                    <select
                        v-model="selectedPastorId"
                        @change="onScopeChange('pastor_id', selectedPastorId)"
                        class="hidden 2xl:block bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[120px] truncate"
                    >
                        <option value="">-- Semua Pastor --</option>
                        <option v-for="p in pastorsList" :key="p.id" :value="p.id">
                            {{ p.nama_pastor }}
                        </option>
                    </select>
                </template>

                <!-- Dynamic Wilayah Selector (Ultrawide Desktop only) -->
                <template v-if="activeRole === 'Admin Wilayah' && wilayahList.length > 0">
                    <select
                        v-model="selectedWilayahId"
                        @change="onScopeChange('wilayah_id', selectedWilayahId)"
                        class="hidden 2xl:block bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[120px] truncate"
                    >
                        <option value="">-- Semua Wilayah --</option>
                        <option v-for="w in wilayahList" :key="w.id" :value="w.id">
                            {{ w.nama_wilayah }}
                        </option>
                    </select>
                </template>

                <!-- Dynamic Kapela Selector (Ultrawide Desktop only) -->
                <template v-if="activeRole === 'Admin Kapela / Stasi' && kapelaList.length > 0">
                    <select
                        v-model="selectedKapelaId"
                        @change="onScopeChange('kapela_id', selectedKapelaId)"
                        class="hidden 2xl:block bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[120px] truncate"
                    >
                        <option value="">-- Semua Stasi / Kapela --</option>
                        <option v-for="k in kapelaList" :key="k.id" :value="k.id">
                            {{ k.nama_kapela }}
                        </option>
                    </select>
                </template>

                <!-- Dynamic KUB Selector (Ultrawide Desktop only) -->
                <template v-if="activeRole === 'Ketua KUB' && kubList.length > 0">
                    <select
                        v-model="selectedKubId"
                        @change="onScopeChange('kub_id', selectedKubId)"
                        class="hidden 2xl:block bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[120px] truncate"
                    >
                        <option value="">-- Semua KUB --</option>
                        <option v-for="kb in kubList" :key="kb.id" :value="kb.id">
                            {{ kb.nama_kub }}
                        </option>
                    </select>
                </template>

                <!-- Quick Button: Return To Super Admin -->
                <button
                    v-if="activeRole !== 'Super Admin'"
                    type="button"
                    @click="returnToSuperAdmin"
                    class="inline-flex items-center justify-center gap-1 px-1.5 sm:px-2 py-1 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-[10px] sm:text-[11px] font-bold shadow-xs transition cursor-pointer shrink-0"
                    title="Kembali ke Mode Penuh Super Admin"
                >
                    <i class="fa-solid fa-arrow-rotate-left text-[9px]"></i>
                    <span class="hidden 2xl:inline">Ke Super Admin</span>
                </button>
            </div>

            <!-- Static Role Badge for Non-Super Admin -->
            <div v-else class="hidden sm:flex items-center gap-1 px-2 py-1 rounded-xl bg-amber-50 border border-amber-200/80 text-[11px] sm:text-xs font-bold text-amber-900 shadow-2xs shrink-0">
                <i class="fa-solid fa-user-shield text-amber-600 text-[10px]"></i>
                <span class="truncate max-w-[100px] sm:max-w-[120px]">{{ activeRole }}</span>
            </div>

            <!-- Panduan RBAC (Ultrawide Desktop only) -->
            <Link
                :href="getHref('/panduan-hak-akses')"
                class="hidden 2xl:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold hover:bg-amber-100 transition shrink-0"
            >
                <i class="fa-solid fa-shield-halved text-amber-600"></i>
                <span>Panduan RBAC</span>
            </Link>

            <!-- Real-time Internal Chat & Messaging -->
            <AppChatWidget
                v-if="$page.props.auth?.user?.is_super_admin || $page.props.fitur_chat_aktif !== false"
                class="shrink-0"
            />

            <!-- Real-time Notification Bell -->
            <AppNotificationBell class="shrink-0" />

            <!-- Situs Paroki Link (Desktop) -->
            <a
                href="/"
                target="_blank"
                class="hidden xl:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-xs transition-all shrink-0"
                title="Lihat Situs Publik Paroki"
            >
                <i class="fa-solid fa-house text-[11px]"></i>
                <span>Situs Paroki</span>
            </a>

            <!-- User Menu Container (Dropdown on Mobile & Desktop) -->
            <div id="user-menu-container" class="relative shrink-0">
                <!-- User Profile Trigger Button -->
                <button
                    type="button"
                    @click="isUserMenuOpen = !isUserMenuOpen"
                    class="inline-flex items-center gap-1.5 p-0.5 sm:p-1 md:px-2 md:py-1 rounded-xl bg-slate-100 hover:bg-amber-50 hover:text-amber-900 border border-slate-200/80 text-slate-700 text-xs font-semibold transition cursor-pointer shadow-2xs shrink-0"
                    title="Menu Pengguna"
                >
                    <img
                        :src="userAvatar || '/images/avatar-default.jpg'"
                        alt="Foto Profil"
                        class="w-7 h-7 sm:w-7.5 sm:h-7.5 rounded-lg object-cover border border-amber-500/40 shadow-2xs shrink-0"
                        @error="(e) => { e.target.onerror = null; e.target.src = '/images/avatar-default.jpg'; }"
                    />
                    <span class="hidden 2xl:inline font-bold truncate max-w-[110px]">{{ userName }}</span>
                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 hidden 2xl:inline ml-0.5"></i>
                </button>

                <!-- User Dropdown Popover Menu -->
                <transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <div
                        v-if="isUserMenuOpen"
                        class="absolute right-0 mt-2 w-56 rounded-2xl bg-white border border-slate-200 shadow-2xl z-50 overflow-hidden py-1.5 text-xs divide-y divide-slate-100"
                    >
                        <!-- Header User Info -->
                        <div class="px-3.5 py-2.5 bg-slate-50/80 flex items-center gap-2.5">
                            <img
                                :src="userAvatar || '/images/avatar-default.jpg'"
                                alt="Foto Profil"
                                class="w-8 h-8 rounded-lg object-cover border border-amber-500/40 shrink-0"
                            />
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900 truncate">{{ userName }}</p>
                                <p class="text-[10px] text-amber-700 font-semibold truncate">{{ activeRole }}</p>
                            </div>
                        </div>

                        <!-- Menu Links -->
                        <div class="py-1">
                            <Link
                                :href="getHref('/profil-saya')"
                                @click="isUserMenuOpen = false"
                                class="w-full px-3.5 py-2 hover:bg-amber-50 hover:text-amber-900 text-slate-700 flex items-center gap-2.5 font-medium transition cursor-pointer"
                            >
                                <i class="fa-solid fa-user-gear text-slate-400 w-4"></i>
                                <span>Profil Saya</span>
                            </Link>

                            <a
                                href="/"
                                target="_blank"
                                class="xl:hidden w-full px-3.5 py-2 hover:bg-amber-50 hover:text-amber-900 text-slate-700 flex items-center gap-2.5 font-medium transition cursor-pointer"
                            >
                                <i class="fa-solid fa-house text-amber-500 w-4"></i>
                                <span>Situs Paroki</span>
                            </a>

                            <Link
                                :href="getHref('/panduan-hak-akses')"
                                @click="isUserMenuOpen = false"
                                class="2xl:hidden w-full px-3.5 py-2 hover:bg-amber-50 hover:text-amber-900 text-slate-700 flex items-center gap-2.5 font-medium transition cursor-pointer"
                            >
                                <i class="fa-solid fa-shield-halved text-amber-600 w-4"></i>
                                <span>Panduan RBAC</span>
                            </Link>
                        </div>

                        <!-- Logout -->
                        <div class="pt-1">
                            <button
                                type="button"
                                @click="isUserMenuOpen = false; showLogoutModal = true;"
                                class="w-full px-3.5 py-2 hover:bg-rose-50 text-rose-700 flex items-center gap-2.5 font-bold transition cursor-pointer text-left"
                            >
                                <i class="fa-solid fa-arrow-right-from-bracket w-4 text-rose-500"></i>
                                <span>Keluar Aplikasi</span>
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </header>
</template>
