<script setup>
import { inject } from 'vue';
import { Link } from '@inertiajs/vue3';
import { RoleMenuKey } from '../composables/useRoleMenu.js';
import { roleMenus } from '../menu/roleMenus.js';

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
    getHref,
    isItemActive,
    userAvatar,
    userName,
    showLogoutModal,
} = inject(RoleMenuKey);
</script>

<template>
    <!-- 1. FIXED TOPBAR / HEADER -->
    <header class="h-16 w-full shrink-0 border-b border-slate-200/80 bg-white/95 backdrop-blur-md z-30 shadow-xs flex items-center justify-between px-4 sm:px-6">
        <!-- Left: Toggle & Brand -->
        <div class="flex items-center gap-3">
            <button
                @click="toggleSidebar"
                class="hidden lg:flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
            >
                <i class="fa-solid fa-bars text-sm"></i>
            </button>
            <button
                @click="isMobileOpen = !isMobileOpen"
                class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
            >
                <i class="fa-solid fa-bars text-sm"></i>
            </button>

            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white font-black text-lg shadow-md shadow-amber-500/25 shrink-0 overflow-hidden border border-amber-300/40">
                    <img
                        v-if="page.props.app?.logo"
                        :src="page.props.app.logo"
                        :alt="page.props.app?.nama_paroki || 'Logo'"
                        class="w-full h-full object-cover"
                    />
                    <i v-else class="fa-solid fa-church text-sm"></i>
                </div>
                <div>
                    <h1 class="font-bold text-sm sm:text-base tracking-tight text-slate-900 leading-none">
                        SIPAROKI
                    </h1>
                    <p class="text-[11px] text-slate-500 hidden sm:block mt-0.5 truncate max-w-[280px]">
                        {{ page.props.app?.nama_paroki || title }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Role Switcher & Action Buttons -->
        <div class="flex items-center gap-2.5">
            <!-- Role Preview Selector (ONLY for Super Admin) -->
            <div v-if="isSuperAdmin" class="hidden md:flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200 text-xs">
                <span class="text-[10px] font-bold uppercase text-slate-500 pl-1.5 pr-0.5">Peran:</span>
                <select
                    v-model="activeRole"
                    @change="onRoleChange"
                    class="bg-white border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold text-amber-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs"
                >
                    <option v-for="r in Object.keys(roleMenus)" :key="r" :value="r">
                        {{ r }}
                    </option>
                </select>

                <!-- Dynamic Pastor Selector -->
                <template v-if="activeRole === 'Pastor' && pastorsList.length > 0">
                    <span class="text-slate-300">|</span>
                    <select
                        v-model="selectedPastorId"
                        class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                    >
                        <option value="">-- Pilih Pastor --</option>
                        <option v-for="p in pastorsList" :key="p.id" :value="p.id">
                            {{ p.nama_pastor }} ({{ p.jabatan || 'Pastor' }})
                        </option>
                    </select>
                </template>

                <!-- Dynamic Wilayah Selector -->
                <template v-if="activeRole === 'Admin Wilayah' && wilayahList.length > 0">
                    <span class="text-slate-300">|</span>
                    <select
                        v-model="selectedWilayahId"
                        class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                    >
                        <option value="">-- Pilih Wilayah --</option>
                        <option v-for="w in wilayahList" :key="w.id" :value="w.id">
                            {{ w.nama_wilayah }}
                        </option>
                    </select>
                </template>

                <!-- Dynamic Kapela / Stasi Selector -->
                <template v-if="activeRole === 'Admin Kapela / Stasi' && kapelaList.length > 0">
                    <span class="text-slate-300">|</span>
                    <select
                        v-model="selectedKapelaId"
                        class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                    >
                        <option value="">-- Pilih Stasi / Kapela --</option>
                        <option v-for="k in kapelaList" :key="k.id" :value="k.id">
                            {{ k.nama_kapela }}
                        </option>
                    </select>
                </template>

                <!-- Dynamic KUB Selector -->
                <template v-if="activeRole === 'Ketua KUB' && kubList.length > 0">
                    <span class="text-slate-300">|</span>
                    <select
                        v-model="selectedKubId"
                        class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                    >
                        <option value="">-- Pilih KUB --</option>
                        <option v-for="kb in kubList" :key="kb.id" :value="kb.id">
                            {{ kb.nama_kub }}
                        </option>
                    </select>
                </template>
            </div>

            <!-- Static Role Badge for Non-Super Admin (Admin Paroki, Pastor, Wilayah, etc.) -->
            <div v-else class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-xs font-bold text-amber-900 shadow-2xs">
                <i class="fa-solid fa-user-shield text-amber-600 text-[11px]"></i>
                <span>{{ activeRole }}</span>
            </div>

            <Link
                :href="getHref('/panduan-hak-akses')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold hover:bg-amber-100 transition"
            >
                <i class="fa-solid fa-shield-halved text-amber-600"></i>
                <span class="hidden xl:inline">Panduan RBAC</span>
            </Link>

            <a
                href="/"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-sm shadow-amber-500/20 transition-all"
            >
                <i class="fa-solid fa-house"></i>
                <span class="hidden sm:inline">Situs Paroki</span>
            </a>

            <Link
                :href="getHref('/profil-saya')"
                :class="[
                    'inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer',
                    isItemActive('/v2/profil-saya')
                        ? 'bg-amber-100 text-amber-900 border border-amber-300'
                        : 'bg-slate-100 hover:bg-amber-50 hover:text-amber-800 text-slate-700'
                ]"
                title="Profil Saya"
            >
                <img
                    v-if="userAvatar"
                    :src="userAvatar"
                    alt="Foto Profil"
                    class="w-5 h-5 rounded-full object-cover border border-amber-500/50 shadow-2xs shrink-0"
                />
                <i v-else class="fa-solid fa-circle-user text-amber-600 text-sm"></i>
                <span class="hidden sm:inline font-bold truncate max-w-[120px]">{{ userName }}</span>
            </Link>

            <button
                type="button"
                @click="showLogoutModal = true"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-700 font-semibold text-xs transition cursor-pointer"
            >
                <i class="fa-solid fa-arrow-right-from-bracket text-slate-500"></i>
                <span class="hidden sm:inline">Keluar</span>
            </button>
        </div>
    </header>
</template>
