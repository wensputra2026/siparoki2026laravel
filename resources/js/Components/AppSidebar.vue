<script setup>
import { inject, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { RoleMenuKey } from '../composables/useRoleMenu.js';
import { roleMenus } from '../menu/roleMenus.js';

const {
    isSidebarOpen,
    isMobileOpen,
    showLogoutModal,
    activeRole,
    currentMenuTree,
    openGroups,
    toggleGroup,
    getHref,
    isItemActive,
    isGroupActive,
} = inject(RoleMenuKey);

const isMounted = ref(false);
onMounted(() => {
    requestAnimationFrame(() => {
        isMounted.value = true;
    });
});
</script>

<template>
    <!-- MOBILE SIDEBAR DRAWER (OFF-CANVAS) -->
    <Transition
        enter-active-class="transition-opacity ease-linear duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity ease-linear duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isMobileOpen"
            class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-xs lg:hidden"
            @click="isMobileOpen = false"
        ></div>
    </Transition>

    <Transition
        enter-active-class="transition ease-in-out duration-300 transform"
        enter-from-class="-translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition ease-in-out duration-300 transform"
        leave-from-class="translate-x-0"
        leave-to-class="-translate-x-full"
    >
        <aside
            v-if="isMobileOpen"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl flex flex-col lg:hidden border-r border-slate-200"
        >
            <!-- Mobile Drawer Header -->
            <div class="h-16 px-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white font-black text-lg shadow-md shadow-amber-500/25 shrink-0">
                        <i class="fa-solid fa-church text-sm"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-sm tracking-tight text-slate-900 leading-none">
                            SIPAROKI
                        </h1>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ activeRole }}</p>
                    </div>
                </div>
                <button
                    @click="isMobileOpen = false"
                    class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 flex items-center justify-center hover:bg-slate-100 transition cursor-pointer"
                >
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Mobile Role Switcher -->
            <div class="p-3 bg-slate-50 border-b border-slate-100">
                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Ganti Peran Pengguna:</label>
                <select
                    v-model="activeRole"
                    class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-amber-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs"
                >
                    <option v-for="r in Object.keys(roleMenus)" :key="r" :value="r">
                        {{ r }}
                    </option>
                </select>
            </div>

            <!-- Nav Scrollable Menu Area -->
            <div class="flex-1 py-3 px-3 space-y-4 overflow-y-auto custom-scrollbar">
                <div v-for="section in currentMenuTree" :key="section.section" class="space-y-1">
                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        {{ section.section }}
                    </div>

                    <div v-for="item in section.menus" :key="item.name">
                        <!-- Logout Action Item -->
                        <button
                            v-if="item.action === 'logout'"
                            type="button"
                            @click="showLogoutModal = true; isMobileOpen = false;"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-all duration-150 group text-left cursor-pointer"
                        >
                            <i class="fa-solid fa-arrow-right-from-bracket text-center w-4 text-sm text-rose-500 group-hover:scale-110 transition-transform"></i>
                            <span class="truncate">{{ item.name }}</span>
                        </button>

                        <!-- Single Menu Item (No Submenus) -->
                        <Link
                            v-else-if="!item.submenus"
                            :href="getHref(item.href)"
                            :data-active="isItemActive(item.href)"
                            @click="isMobileOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 group',
                                isItemActive(item.href)
                                    ? 'bg-amber-500 text-white font-bold shadow-sm shadow-amber-500/30'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                            ]"
                        >
                            <i :class="[
                                'fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110',
                                isItemActive(item.href) ? 'text-white' : 'text-slate-400 group-hover:text-slate-600',
                                item.icon
                            ]"></i>
                            <span class="truncate">{{ item.name }}</span>
                        </Link>

                        <!-- Menu Item with Submenus -->
                        <div v-else class="space-y-0.5">
                            <button
                                type="button"
                                @click="toggleGroup(item.name)"
                                :class="[
                                    'w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 text-left group',
                                    isGroupActive(item)
                                        ? 'text-amber-900 font-bold bg-amber-50/60'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                                ]"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <i :class="['fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110', isGroupActive(item) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600', item.icon]"></i>
                                    <span class="truncate">{{ item.name }}</span>
                                </div>
                                <i
                                    :class="[
                                        'fa-solid fa-chevron-right text-[10px] text-slate-400 transition-transform duration-200',
                                        openGroups[item.name] ? 'rotate-90 text-amber-600' : '',
                                    ]"
                                ></i>
                            </button>

                            <div
                                v-show="openGroups[item.name]"
                                class="pl-7 pr-1 space-y-0.5 border-l-2 border-slate-100 ml-5 my-1"
                            >
                                <Link
                                    v-for="sub in item.submenus"
                                    :key="sub.name"
                                    :href="getHref(sub.href)"
                                    :data-active="isItemActive(sub.href)"
                                    @click="isMobileOpen = false"
                                    :class="[
                                        'flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 group',
                                        isItemActive(sub.href)
                                            ? 'bg-amber-50 text-amber-800 font-bold border border-amber-200'
                                            : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70',
                                    ]"
                                >
                                    <i :class="[
                                        'fa-solid text-[11px] w-3.5 text-center',
                                        isItemActive(sub.href) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                        sub.icon
                                    ]"></i>
                                    <span class="truncate">{{ sub.name }}</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Drawer Footer -->
            <div class="p-3 border-t border-slate-200/80 bg-slate-50/50 space-y-2">
                <a
                    href="/"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                >
                    <i class="fa-solid fa-house text-center w-4 text-amber-600"></i>
                    <span>Situs Paroki Publik</span>
                </a>
                <button
                    type="button"
                    @click="showLogoutModal = true; isMobileOpen = false;"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket text-center w-4"></i>
                    <span>Keluar Aplikasi</span>
                </button>
            </div>
        </aside>
    </Transition>

    <!-- 2. FIXED SIDEBAR DESKTOP -->
    <aside
        :class="[
            'hidden lg:flex flex-col border-r border-slate-200 bg-white shrink-0 select-none shadow-xs h-full overflow-hidden',
            isMounted ? 'transition-all duration-300 ease-in-out' : '',
            isSidebarOpen ? 'w-64' : 'w-20',
        ]"
    >
        <!-- Nav Scrollable Menu Area -->
        <div class="flex-1 py-3 px-3 space-y-4 overflow-y-auto custom-scrollbar">
            <div v-for="section in currentMenuTree" :key="section.section" class="space-y-1">
                <!-- Section Heading -->
                <div
                    v-show="isSidebarOpen"
                    class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400"
                >
                    {{ section.section }}
                </div>

                <!-- Menu Items in Section -->
                <div v-for="item in section.menus" :key="item.name" class="space-y-0.5">
                    <!-- Logout Action Item -->
                    <button
                        v-if="item.action === 'logout'"
                        type="button"
                        @click="showLogoutModal = true"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-all duration-150 group text-left cursor-pointer"
                        title="Keluar / Logout"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket text-center w-4 text-sm text-rose-500 group-hover:scale-110 transition-transform"></i>
                        <span v-show="isSidebarOpen" class="truncate">{{ item.name }}</span>
                    </button>

                    <!-- Single Link (No Submenu) -->
                    <Link
                        v-else-if="!item.submenus"
                        :href="getHref(item.href)"
                        :data-active="isItemActive(item.href)"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 group',
                            isItemActive(item.href)
                                ? 'bg-amber-50 text-amber-800 border border-amber-300/80 shadow-xs font-bold'
                                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                        ]"
                    >
                        <i :class="[
                            'fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110',
                            isItemActive(item.href) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                            item.icon
                        ]"></i>
                        <span v-show="isSidebarOpen" class="truncate">{{ item.name }}</span>
                    </Link>

                    <!-- Collapsible Parent with Submenus -->
                    <div v-else class="space-y-0.5">
                        <button
                            @click="toggleGroup(item.name)"
                            :class="[
                                'w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 text-left group',
                                isGroupActive(item)
                                    ? 'text-amber-900 font-bold bg-amber-50/60'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                            ]"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <i :class="[
                                    'fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110',
                                    isGroupActive(item) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                    item.icon
                                ]"></i>
                                <span v-show="isSidebarOpen" class="truncate">{{ item.name }}</span>
                            </div>
                            <i
                                v-show="isSidebarOpen"
                                :class="[
                                    'fa-solid fa-chevron-right text-[10px] text-slate-400 transition-transform duration-200',
                                    openGroups[item.name] ? 'rotate-90 text-amber-600' : '',
                                ]"
                            ></i>
                        </button>

                        <!-- Submenu Items -->
                        <div
                            v-show="isSidebarOpen && openGroups[item.name]"
                            class="pl-7 pr-1 space-y-0.5 border-l-2 border-slate-100 ml-5 my-1"
                        >
                            <Link
                                v-for="sub in item.submenus"
                                :key="sub.name"
                                :href="getHref(sub.href)"
                                :data-active="isItemActive(sub.href)"
                                :class="[
                                    'flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 group',
                                    isItemActive(sub.href)
                                        ? 'bg-amber-50 text-amber-800 font-bold border border-amber-200'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70',
                                ]"
                            >
                                <i :class="[
                                    'fa-solid text-[11px] w-3.5 text-center',
                                    isItemActive(sub.href) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                    sub.icon
                                ]"></i>
                                <span class="truncate">{{ sub.name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Sidebar Footer -->
        <div class="p-3 border-t border-slate-200/80 shrink-0 bg-slate-50/50 space-y-1">
            <a
                href="/"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors"
            >
                <i class="fa-solid fa-globe text-center w-4 text-slate-400"></i>
                <span v-show="isSidebarOpen">Lihat Website Publik</span>
            </a>
            <button
                type="button"
                @click="showLogoutModal = true"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors cursor-pointer"
                title="Keluar Aplikasi"
            >
                <i class="fa-solid fa-arrow-right-from-bracket text-center w-4"></i>
                <span v-show="isSidebarOpen">Keluar Aplikasi</span>
            </button>
        </div>
    </aside>
</template>
