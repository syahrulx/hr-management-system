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
const showMonthDropdown = ref(false);

function selectMonth(idx) {
  selectedMonth.value = idx;
  showMonthDropdown.value = false;
  const newMonth = (idx + 1).toString().padStart(2, "0");
  const year = currentMonth.getFullYear();
  router.get(route('reports.index'), { month: `${year}-${newMonth}` }, { preserveState: true });
}

const selectedMonthLabel = computed(() => monthNames[selectedMonth.value]);

function exportCSV() {
  const headers = ['No', 'Name', 'Present', 'Late', 'Early Leave', 'Absent', 'Attendance Rate'];
  const rows = props.staffAttendance.map((staff, idx) => {
    const total = staff.present + staff.late + staff.absent;
    const rate = total > 0 ? ((staff.present + staff.late) / total * 100).toFixed(0) : 0;
    
    // Format Late: "2 (30m)" or "0"
    let lateStr = staff.late;
    if (staff.late > 0 && staff.late_minutes > 0) {
      lateStr += ` (${staff.late_minutes}m)`;
    }

    // Format Early: "1 (15m)" or "-"
    let earlyStr = '-';
    if (staff.early_count > 0) {
      earlyStr = `${staff.early_count} (${staff.early_minutes}m)`;
    }

    return [
      idx + 1, 
      staff.name, 
      staff.present, 
      lateStr, 
      earlyStr, 
      staff.absent, 
      rate + '%'
    ];
  });
  const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `attendance-report-${props.month}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

const totalRecords = computed(() => props.totalPresent + props.totalLate + props.totalAbsent);
const overallRate = computed(() => {
  return totalRecords.value > 0 ? (((props.totalPresent + props.totalLate) / totalRecords.value) * 100).toFixed(0) : 0;
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
                <p class="text-sm text-gray-500">{{ selectedMonthLabel }} {{ currentMonth.getFullYear() }}</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold" :class="overallRate >= 90 ? 'text-emerald-400' : overallRate >= 80 ? 'text-amber-400' : 'text-red-400'">{{ overallRate }}%</p>
                <p class="text-xs text-gray-500 uppercase">Overall Attendance</p>
            </div>
        </div>

        <!-- SUMMARY ROW -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-emerald-400">{{ props.totalPresent }}</p>
                <p class="text-xs text-gray-400 uppercase mt-1">Present</p>
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
                        <th class="px-5 py-4 text-center w-20">Present</th>
                        <th class="px-5 py-4 text-center w-28">Late</th>
                        <th class="px-5 py-4 text-center w-28 text-orange-500/80">Early Leave</th>
                        <th class="px-5 py-4 text-center w-20">Absent</th>
                        <th class="px-5 py-4 text-right w-28">Rate</th>
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

                        <!-- Early Leave Column -->
                        <td class="px-5 py-4 text-center text-sm font-medium text-orange-500/80">
                            <span v-if="staff.early_count > 0">
                                {{ staff.early_count }} 
                                <span class="text-xs font-normal text-orange-500/60 ml-1">({{ staff.early_minutes }}m)</span>
                            </span>
                            <span v-else>-</span>
                        </td>

                        <td class="px-5 py-4 text-center text-sm font-semibold text-red-400">{{ staff.absent }}</td>
                        <td class="px-5 py-4 text-right">
                            <span class="inline-block min-w-[50px] px-2 py-1 rounded text-xs font-bold text-center"
                                  :class="(staff.present + staff.late + staff.absent) > 0 && ((staff.present + staff.late) / (staff.present + staff.late + staff.absent) * 100) >= 90 
                                    ? 'bg-emerald-500/20 text-emerald-400' 
                                    : (staff.present + staff.late + staff.absent) > 0 && ((staff.present + staff.late) / (staff.present + staff.late + staff.absent) * 100) >= 80 
                                    ? 'bg-amber-500/20 text-amber-400' 
                                    : 'bg-red-500/20 text-red-400'">
                                {{ (staff.present + staff.late + staff.absent) > 0 ? (((staff.present + staff.late) / (staff.present + staff.late + staff.absent)) * 100).toFixed(0) : 0 }}%
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