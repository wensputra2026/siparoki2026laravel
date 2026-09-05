<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'dd/mm/yyyy',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    inputClass: {
        type: String,
        default: '',
    },
    min: {
        type: String,
        default: '',
    },
    max: {
        type: String,
        default: '',
    },
    iconColor: {
        type: String,
        default: 'text-amber-600',
    },
    clearable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const containerRef = ref(null);
const nativeInputRef = ref(null);
const isOpen = ref(false);

// Month names in Indonesian
const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Current viewed month & year in calendar popup
const currentViewYear = ref(new Date().getFullYear());
const currentViewMonth = ref(new Date().getMonth());

// Text display value in format DD/MM/YYYY
const displayValue = ref('');

// Helper: Convert YYYY-MM-DD -> DD/MM/YYYY
const isoToDisplay = (isoStr) => {
    if (!isoStr) return '';
    const clean = String(isoStr).trim().substring(0, 10);
    const parts = clean.split('-');
    if (parts.length === 3 && parts[0].length === 4) {
        return `${parts[2].padStart(2, '0')}/${parts[1].padStart(2, '0')}/${parts[0]}`;
    }
    return '';
};

// Helper: Convert DD/MM/YYYY -> YYYY-MM-DD
const displayToIso = (dStr) => {
    if (!dStr) return '';
    const clean = String(dStr).trim();
    const parts = clean.split('/');
    if (parts.length === 3) {
        const d = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10);
        const y = parseInt(parts[2], 10);
        if (y >= 1000 && y <= 9999 && m >= 1 && m <= 12 && d >= 1 && d <= 31) {
            // Validate days in that specific month/year
            const daysInMonth = new Date(y, m, 0).getDate();
            if (d <= daysInMonth) {
                return `${String(y).padStart(4, '0')}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            }
        }
    }
    return '';
};

// Sync display from props
watch(
    () => props.modelValue,
    (newVal) => {
        const expected = isoToDisplay(newVal);
        if (displayValue.value !== expected) {
            displayValue.value = expected;
        }
        if (newVal) {
            const parts = String(newVal).substring(0, 10).split('-');
            if (parts.length === 3 && parts[0].length === 4) {
                currentViewYear.value = parseInt(parts[0], 10);
                currentViewMonth.value = parseInt(parts[1], 10) - 1;
            }
        }
    },
    { immediate: true }
);

// Handle manual text input with automatic slash insertion
const handleTextInput = (e) => {
    let val = e.target.value;

    // Filter non-digits and non-slashes
    val = val.replace(/[^0-9/]/g, '');

    // Auto format DD/MM/YYYY if typing pure digits or without slashes
    const rawDigits = val.replace(/\D/g, '');
    let formatted = '';

    if (rawDigits.length > 0) {
        if (rawDigits.length <= 2) {
            formatted = rawDigits;
            if (rawDigits.length === 2 && e.inputType !== 'deleteContentBackward') {
                formatted += '/';
            }
        } else if (rawDigits.length <= 4) {
            formatted = `${rawDigits.substring(0, 2)}/${rawDigits.substring(2)}`;
            if (rawDigits.length === 4 && e.inputType !== 'deleteContentBackward') {
                formatted += '/';
            }
        } else {
            formatted = `${rawDigits.substring(0, 2)}/${rawDigits.substring(2, 4)}/${rawDigits.substring(4, 8)}`;
        }
    }

    displayValue.value = formatted;

    if (!formatted) {
        emit('update:modelValue', '');
        emit('change', '');
        return;
    }

    if (formatted.length === 10) {
        const iso = displayToIso(formatted);
        if (iso) {
            emit('update:modelValue', iso);
            emit('change', iso);
            const parts = iso.split('-');
            currentViewYear.value = parseInt(parts[0], 10);
            currentViewMonth.value = parseInt(parts[1], 10) - 1;
        }
    }
};

// On blur, re-verify or restore valid display
const handleBlur = () => {
    if (!displayValue.value) {
        emit('update:modelValue', '');
        emit('change', '');
        return;
    }
    const iso = displayToIso(displayValue.value);
    if (iso) {
        emit('update:modelValue', iso);
        emit('change', iso);
        displayValue.value = isoToDisplay(iso);
    } else {
        // If invalid, revert to existing modelValue or clear
        displayValue.value = isoToDisplay(props.modelValue);
    }
};

// Calendar Grid Computations
const calendarDays = computed(() => {
    const year = currentViewYear.value;
    const month = currentViewMonth.value;

    const firstDayIndex = new Date(year, month, 1).getDay(); // 0 = Sun, 1 = Mon ...
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    const days = [];

    // Previous month padding
    for (let i = firstDayIndex - 1; i >= 0; i--) {
        days.push({
            day: daysInPrevMonth - i,
            month: month - 1,
            year: month === 0 ? year - 1 : year,
            isCurrentMonth: false,
        });
    }

    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        days.push({
            day: i,
            month: month,
            year: year,
            isCurrentMonth: true,
        });
    }

    // Next month padding to fill complete weeks (rows of 7)
    const totalSlots = Math.ceil(days.length / 7) * 7;
    const remainingSlots = totalSlots - days.length;
    for (let i = 1; i <= remainingSlots; i++) {
        days.push({
            day: i,
            month: month + 1,
            year: month === 11 ? year + 1 : year,
            isCurrentMonth: false,
        });
    }

    return days;
});

// Check if a day cell matches the selected date
const isSelectedDate = (cell) => {
    if (!props.modelValue) return false;
    const parts = props.modelValue.split('-');
    if (parts.length !== 3) return false;
    const y = parseInt(parts[0], 10);
    const m = parseInt(parts[1], 10) - 1;
    const d = parseInt(parts[2], 10);

    const actualMonth = cell.month < 0 ? 11 : cell.month > 11 ? 0 : cell.month;
    return cell.year === y && actualMonth === m && cell.day === d;
};

// Check if a day cell is today
const isToday = (cell) => {
    const today = new Date();
    const actualMonth = cell.month < 0 ? 11 : cell.month > 11 ? 0 : cell.month;
    return cell.year === today.getFullYear() && actualMonth === today.getMonth() && cell.day === today.getDate();
};

// Select a date from the calendar
const selectDate = (cell) => {
    if (props.disabled) return;
    let actualYear = cell.year;
    let actualMonth = cell.month;

    if (actualMonth < 0) {
        actualMonth = 11;
        actualYear--;
    } else if (actualMonth > 11) {
        actualMonth = 0;
        actualYear++;
    }

    const iso = `${String(actualYear).padStart(4, '0')}-${String(actualMonth + 1).padStart(2, '0')}-${String(cell.day).padStart(2, '0')}`;
    emit('update:modelValue', iso);
    emit('change', iso);
    displayValue.value = isoToDisplay(iso);
    isOpen.value = false;
};

// Navigation controls
const prevMonth = () => {
    if (currentViewMonth.value === 0) {
        currentViewMonth.value = 11;
        currentViewYear.value--;
    } else {
        currentViewMonth.value--;
    }
};

const nextMonth = () => {
    if (currentViewMonth.value === 11) {
        currentViewMonth.value = 0;
        currentViewYear.value++;
    } else {
        currentViewMonth.value++;
    }
};

const setToday = () => {
    const today = new Date();
    const y = today.getFullYear();
    const m = today.getMonth();
    const d = today.getDate();
    const iso = `${String(y).padStart(4, '0')}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    emit('update:modelValue', iso);
    emit('change', iso);
    displayValue.value = isoToDisplay(iso);
    currentViewYear.value = y;
    currentViewMonth.value = m;
    isOpen.value = false;
};

const clearDate = () => {
    emit('update:modelValue', '');
    emit('change', '');
    displayValue.value = '';
    isOpen.value = false;
};

const togglePicker = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value && props.modelValue) {
        const parts = props.modelValue.split('-');
        if (parts.length === 3) {
            currentViewYear.value = parseInt(parts[0], 10);
            currentViewMonth.value = parseInt(parts[1], 10) - 1;
        }
    }
};

// Generate list of selectable years (from 1920 to current year + 10)
const selectableYears = computed(() => {
    const start = 1920;
    const end = new Date().getFullYear() + 15;
    const list = [];
    for (let y = end; y >= start; y--) {
        list.push(y);
    }
    return list;
});

// Close popup when clicking outside
const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <!-- Visible input displaying dd/mm/yyyy -->
        <div class="relative flex items-center">
            <input
                type="text"
                :value="displayValue"
                @input="handleTextInput"
                @blur="handleBlur"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                maxlength="10"
                :class="[
                    inputClass || 'w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white placeholder:text-slate-400 placeholder:italic font-medium transition',
                    disabled ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'cursor-text',
                    'pr-10'
                ]"
            />

            <!-- Calendar toggle button -->
            <button
                type="button"
                @click="togglePicker"
                :disabled="disabled"
                title="Pilih tanggal dari kalender"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition cursor-pointer disabled:cursor-not-allowed"
            >
                <i :class="['fa-solid fa-calendar-days text-sm', iconColor]"></i>
            </button>
        </div>

        <!-- Popover Calendar (Indonesian Locale & dd/mm/yyyy format) -->
        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1.5 w-72 bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-4 select-none font-sans text-xs left-0 sm:left-auto"
            >
                <!-- Header with Month and Year Selectors -->
                <div class="flex items-center justify-between gap-1 pb-3 mb-3 border-b border-slate-100">
                    <button
                        type="button"
                        @click="prevMonth"
                        class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer"
                        title="Bulan Sebelumnya"
                    >
                        <i class="fa-solid fa-chevron-left text-[11px]"></i>
                    </button>

                    <div class="flex items-center gap-1.5">
                        <!-- Month Dropdown -->
                        <select
                            v-model="currentViewMonth"
                            class="px-2 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-800 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500 cursor-pointer"
                        >
                            <option
                                v-for="(mName, idx) in monthNames"
                                :key="idx"
                                :value="idx"
                            >
                                {{ mName }}
                            </option>
                        </select>

                        <!-- Year Dropdown -->
                        <select
                            v-model="currentViewYear"
                            class="px-2 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-800 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500 cursor-pointer"
                        >
                            <option
                                v-for="y in selectableYears"
                                :key="y"
                                :value="y"
                            >
                                {{ y }}
                            </option>
                        </select>
                    </div>

                    <button
                        type="button"
                        @click="nextMonth"
                        class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer"
                        title="Bulan Berikutnya"
                    >
                        <i class="fa-solid fa-chevron-right text-[11px]"></i>
                    </button>
                </div>

                <!-- Days of week names -->
                <div class="grid grid-cols-7 gap-1 text-center font-bold text-[11px] text-slate-400 mb-1.5">
                    <span v-for="(dayName, dIdx) in dayNames" :key="dIdx" :class="dIdx === 0 ? 'text-rose-500' : ''">
                        {{ dayName }}
                    </span>
                </div>

                <!-- Calendar Days Grid -->
                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                    <button
                        v-for="(cell, cIdx) in calendarDays"
                        :key="cIdx"
                        type="button"
                        @click="selectDate(cell)"
                        :class="[
                            'h-8 w-8 mx-auto rounded-xl flex items-center justify-center text-xs font-semibold transition cursor-pointer',
                            isSelectedDate(cell)
                                ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/30'
                                : cell.isCurrentMonth
                                    ? isToday(cell)
                                        ? 'bg-amber-50 text-amber-900 font-black border border-amber-300'
                                        : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                                    : 'text-slate-300 hover:bg-slate-50'
                        ]"
                    >
                        {{ cell.day }}
                    </button>
                </div>

                <!-- Footer Quick Actions: Hari Ini, Bersihkan, Tutup -->
                <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 text-[11px]">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="setToday"
                            class="px-2.5 py-1 rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 font-bold transition cursor-pointer"
                        >
                            Hari Ini
                        </button>
                        <button
                            v-if="clearable && modelValue"
                            type="button"
                            @click="clearDate"
                            class="px-2.5 py-1 rounded-lg text-rose-600 hover:bg-rose-50 font-bold transition cursor-pointer"
                        >
                            Hapus
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="isOpen = false"
                        class="px-2.5 py-1 rounded-lg text-slate-500 hover:bg-slate-100 font-bold transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>
