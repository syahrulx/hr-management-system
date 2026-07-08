<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Card from "@/Components/Card.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";
import NavLink from "@/Components/NavLink.vue";
import { __ } from "@/Composables/useTranslations.js";
import { 
  ChevronDownIcon,
  CalendarDaysIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  staffAttendance: Array,
  month: String,
  totalPresent: Number,
  totalLate: Number,
  totalAbsent: Number,
});

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

const currentMonth = new Date(props.month + '-01');
const selectedMonth = ref(currentMonth.getMonth());
const selectedYear = ref(currentMonth.getFullYear());
const showMonthDropdown = ref(false);
const showYearDropdown = ref(false);

const years = computed(() => {
    const current = new Date().getFullYear();
    return [current, current - 1, current - 2, current - 3];
});

function selectMonth(idx) {
  selectedMonth.value = idx;
  showMonthDropdown.value = false;
  updateReport();
}

function selectYear(year) {
  selectedYear.value = year;
  showYearDropdown.value = false;
  updateReport();
}

function updateReport() {
  const newMonth = (selectedMonth.value + 1).toString().padStart(2, "0");
  router.get(route('reports.index'), { month: `${selectedYear.value}-${newMonth}` }, { preserveState: true });
}

const selectedMonthLabel = computed(() => monthNames[selectedMonth.value]);

// Wrap a value for CSV output, quoting it if it contains a comma, quote or newline.
function csvField(value) {
  const str = String(value ?? '');
  if (/[",\n\r]/.test(str)) {
    return '"' + str.replace(/"/g, '""') + '"';
  }
  return str;
}

function csvRow(fields) {
  return fields.map(csvField).join(',');
}

function exportCSV() {
  const headers = ['No', 'Staff Name', 'On Time', 'Late Count', 'Late Minutes', 'Absent', 'Total Shifts', 'On Time Rate (%)'];

  const dataRows = props.staffAttendance.map((staff, idx) => {
    const total = staff.present + staff.late + staff.absent;
    const rate = total > 0 ? (staff.present / total * 100).toFixed(0) : 0;

    return [
      idx + 1,
      staff.name,
      staff.present,
      staff.late,
      staff.late_minutes || 0,
      staff.absent,
      total,
      rate,
    ];
  });

  const overallTotalShifts = props.totalPresent + props.totalLate + props.totalAbsent;
  const overallRatePct = overallTotalShifts > 0 ? ((props.totalPresent / overallTotalShifts) * 100).toFixed(0) : 0;

  const lines = [
    csvRow(['Attendance Report']),
    csvRow([`Period: ${selectedMonthLabel.value} ${selectedYear.value}`]),
    csvRow([`Generated: ${new Date().toLocaleString()}`]),
    csvRow([`Overall On Time Rate: ${overallRatePct}%`]),
    '',
    csvRow(headers),
    ...dataRows.map(csvRow),
    '',
    csvRow(['', 'TOTAL', props.totalPresent, props.totalLate, '', props.totalAbsent, overallTotalShifts, overallRatePct]),
  ];

  // Prepend a UTF-8 BOM so Excel opens special characters correctly, and use
  // CRLF line endings for broad spreadsheet-app compatibility.
  const csv = '﻿' + lines.join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `attendance-report-${selectedYear.value}-${String(selectedMonth.value + 1).padStart(2, '0')}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

const totalRecords = computed(() => props.totalPresent + props.totalLate + props.totalAbsent);
const overallRate = computed(() => {
  return totalRecords.value > 0 ? ((props.totalPresent / totalRecords.value) * 100).toFixed(0) : 0;
});
</script>

<template>
  <Head :title="__('Attendance Report')" />
  <AuthenticatedLayout>
    <template #tabs>
        <GoBackNavLink/>
        <NavLink :href="route('reports.index')" :active="route().current('reports.index')">
            {{ __('Reports') }}
        </NavLink>
        <div class="ml-auto hidden md:flex items-center gap-3">
            <!-- Year Dropdown -->
            <div class="relative">
                <button @click="showYearDropdown = !showYearDropdown" 
                        class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-sm font-medium text-gray-200 hover:bg-white/10 transition-all">
                    <CalendarDaysIcon class="w-4 h-4 text-emerald-400" />
                    {{ selectedYear }}
                    <ChevronDownIcon class="w-3 h-3" :class="{'rotate-180': showYearDropdown}" />
                </button>
                <div v-if="showYearDropdown" 
                     class="absolute right-0 mt-2 w-32 bg-[#18181b] border border-white/10 rounded-xl shadow-2xl z-50 overflow-hidden">
                    <button v-for="year in years" :key="year"
                            @click="selectYear(year)"
                            class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition-colors"
                            :class="{ 'bg-emerald-500/10 text-emerald-400 font-bold': selectedYear === year }">
                        {{ year }}
                    </button>
                </div>
            </div>

            <!-- Month Dropdown -->
            <div class="relative">
                <button @click="showMonthDropdown = !showMonthDropdown" 
                        class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-sm font-medium text-gray-200 hover:bg-white/10 transition-all">
                    <CalendarDaysIcon class="w-4 h-4 text-red-400" />
                    {{ selectedMonthLabel }}
                    <ChevronDownIcon class="w-3 h-3" :class="{'rotate-180': showMonthDropdown}" />
                </button>
                <div v-if="showMonthDropdown" 
                     class="absolute right-0 mt-2 w-44 bg-[#18181b] border border-white/10 rounded-xl shadow-2xl z-50 overflow-hidden">
                    <button v-for="(name, idx) in monthNames" :key="name"
                            @click="selectMonth(idx)"
                            class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-red-500/10 hover:text-red-400 transition-colors"
                            :class="{ 'bg-red-500/10 text-red-400 font-bold': selectedMonth === idx }">
                        {{ name }}
                    </button>
                </div>
            </div>
            <button @click="exportCSV" 
                    class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold transition-all">
                <ArrowDownTrayIcon class="w-4 h-4" />
                Export
            </button>
        </div>
    </template>

    <div class="py-8 max-w-full mx-auto sm:px-6 lg:px-8">

        <!-- HEADER -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Attendance Report</h1>
                <p class="text-sm text-gray-500">{{ selectedMonthLabel }} {{ selectedYear }}</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold" :class="overallRate >= 90 ? 'text-emerald-400' : overallRate >= 80 ? 'text-amber-400' : 'text-red-400'">{{ overallRate }}%</p>
                <p class="text-xs text-gray-500 uppercase">Overall On Time Rate</p>
            </div>
        </div>

        <!-- SUMMARY ROW -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-emerald-400">{{ props.totalPresent }}</p>
                <p class="text-xs text-gray-400 uppercase mt-1">On Time</p>
            </div>
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-amber-400">{{ props.totalLate }}</p>
                <p class="text-xs text-gray-400 uppercase mt-1">Late</p>
            </div>
            <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-red-400">{{ props.totalAbsent }}</p>
                <p class="text-xs text-gray-400 uppercase mt-1">Absent</p>
            </div>
        </div>

        <!-- TABLE -->
        <Card variant="glass" class="!mt-0 !p-0 overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr class="text-xs uppercase tracking-wider font-bold text-gray-500">
                        <th class="px-5 py-4 text-left w-12">#</th>
                        <th class="px-5 py-4 text-left">Staff Name</th>
                        <th class="px-5 py-4 text-center w-20">On Time</th>
                        <th class="px-5 py-4 text-center w-28">Late</th>
                        <th class="px-5 py-4 text-center w-20">Absent</th>
                        <th class="px-5 py-4 text-right w-28">On Time Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="(staff, idx) in props.staffAttendance" :key="staff.name" 
                        class="hover:bg-white/5 transition-colors">
                        <td class="px-5 py-4 text-sm text-gray-500">{{ idx + 1 }}</td>
                        <td class="px-5 py-4 text-sm font-medium text-white">{{ staff.name }}</td>
                        <td class="px-5 py-4 text-center text-sm font-semibold text-emerald-400">{{ staff.present }}</td>
                        
                        <!-- Late Column -->
                        <td class="px-5 py-4 text-center text-sm font-semibold text-amber-400">
                            {{ staff.late }}
                            <span v-if="staff.late > 0 && staff.late_minutes > 0" class="text-xs font-normal text-amber-500/70 ml-1">
                                ({{ staff.late_minutes }}m)
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center text-sm font-semibold text-red-400">{{ staff.absent }}</td>
                        <td class="px-5 py-4 text-right">
                            <span class="inline-block min-w-[50px] px-2 py-1 rounded text-xs font-bold text-center"
                                  :class="(staff.present + staff.late + staff.absent) > 0 && ((staff.present) / (staff.present + staff.late + staff.absent) * 100) >= 90 
                                    ? 'bg-emerald-500/20 text-emerald-400' 
                                    : (staff.present + staff.late + staff.absent) > 0 && ((staff.present) / (staff.present + staff.late + staff.absent) * 100) >= 80 
                                    ? 'bg-amber-500/20 text-amber-400' 
                                    : 'bg-red-500/20 text-red-400'">
                                {{ (staff.present + staff.late + staff.absent) > 0 ? ((staff.present / (staff.present + staff.late + staff.absent)) * 100).toFixed(0) : 0 }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div v-if="props.staffAttendance.length === 0" class="py-16 text-center">
                <p class="text-gray-500">No attendance records for {{ selectedMonthLabel }}</p>
            </div>
        </Card>

    </div>
  </AuthenticatedLayout>
</template>