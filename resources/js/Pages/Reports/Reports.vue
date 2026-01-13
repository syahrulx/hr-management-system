<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Card from "@/Components/Card.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";
import NavLink from "@/Components/NavLink.vue";
import UsersIcon from "@/Components/Icons/UsersIcon.vue";
import TableIcon from "@/Components/Icons/TableIcon.vue";
import CalendarIcon from "@/Components/Icons/CalendarIcon.vue";
import RocketIcon from "@/Components/Icons/RocketIcon.vue";
import { __ } from "@/Composables/useTranslations.js";
import { 
  UsersIcon as HeroUsers, 
  ChartBarIcon, 
  ClockIcon, 
  TrophyIcon,
  ChevronDownIcon,
  CalendarDaysIcon,
  FunnelIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

// Props from the controller
const props = defineProps({
  summary: Array,
  staffAttendance: Array,
  staffTasks: Array,
  staffHours: Array,
  staffLeaves: Array,
  staffRanking: Array,
  month: String,
  allStaff: Array,
  selectedStaffId: [String, Number],
});

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

// Parse current month
const currentMonth = new Date(props.month + '-01');
const selectedMonth = ref(currentMonth.getMonth());
const showMonthDropdown = ref(false);

function selectMonth(idx) {
  selectedMonth.value = idx;
  showMonthDropdown.value = false;
  
  // Reload data with new month
  const newMonth = (idx + 1).toString().padStart(2, "0");
  const year = currentMonth.getFullYear();
  router.get(route('reports.index'), { month: `${year}-${newMonth}` }, { preserveState: true });
}

const selectedMonthLabel = computed(() => monthNames[selectedMonth.value]);

// Staff filter
const staffNames = computed(() => {
  const names = ['All Staff'];
  props.allStaff.forEach(staff => {
    names.push(staff.name);
  });
  return names;
});

const selectedStaff = ref(props.selectedStaffId ? 
  props.allStaff.find(s => s.id == props.selectedStaffId)?.name || 'All Staff' : 
  'All Staff'
);

function selectStaff(e) {
  selectedStaff.value = e.target.value;
  
  // Reload data with staff filter
  const staffId = e.target.value === 'All Staff' ? null : 
    props.allStaff.find(s => s.name === e.target.value)?.id;
  
  router.get(route('reports.index'), { 
    month: props.month,
    staff_id: staffId 
  }, { preserveState: true });
}

function exportCSV() {
  const headers = ['Rank','Name','Attendance','Score'];
  const rows = props.staffRanking.map((row, idx) => [
    idx + 1,
    row.name,
    row.attendance + '%',
    row.score
  ]);
  const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `reports-${props.month}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

const summaryIcons = [
    HeroUsers,
    ChartBarIcon,
    ClockIcon,
    TrophyIcon
];

const summaryColors = [
    'text-red-400',
    'text-emerald-400',
    'text-amber-400',
    'text-pink-400'
];

const summaryBgColors = [
    'bg-red-500/10',
    'bg-emerald-500/10',
    'bg-amber-500/10',
    'bg-pink-500/10'
];

// Get initials for profile placeholder
const getInitials = (name) => {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) || '??';
}

</script>

<template>
  <Head :title="__('Reports')" />
  <AuthenticatedLayout>
    <template #tabs>
        <GoBackNavLink/>
        <NavLink :href="route('reports.index')" :active="route().current('reports.index')">
            {{ __('Reports') }}
        </NavLink>
        <div class="ml-auto hidden md:flex items-center gap-4">
            <div class="relative">
                <button @click="showMonthDropdown = !showMonthDropdown" 
                        class="flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 rounded-lg text-sm font-medium text-gray-200 hover:bg-white/10 transition-all focus:outline-none focus:ring-2 focus:ring-red-500/50">
                    <CalendarDaysIcon class="w-4 h-4 text-red-400" />
                    {{ selectedMonthLabel }}
                    <ChevronDownIcon class="w-3 h-3 transition-transform duration-300" :class="{'rotate-180': showMonthDropdown}" />
                </button>
                <div v-if="showMonthDropdown" 
                     class="absolute right-0 mt-2 w-48 bg-[#18181b] border border-white/10 rounded-xl shadow-2xl z-50 overflow-hidden backdrop-blur-xl animate-fade-in-down">
                    <div class="grid grid-cols-1 max-h-64 overflow-y-auto custom-scrollbar">
                        <button v-for="(name, idx) in monthNames" :key="name"
                                @click="selectMonth(idx)"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-red-500/10 hover:text-red-400 transition-colors"
                                :class="{ 'bg-red-500/5 text-red-400 font-bold': selectedMonth === idx }">
                            {{ name }}
                        </button>
                    </div>
                </div>
            </div>
            <button @click="exportCSV" 
                    class="flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold shadow-lg shadow-red-900/20 transition-all active:scale-95">
                <ArrowDownTrayIcon class="w-4 h-4" />
                {{ __('Export CSV') }}
            </button>
        </div>
    </template>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 animate-fade-in-up">
      
        <!-- MOBILE FILTERS (Visible only on small screens) -->
        <div class="md:hidden flex flex-col gap-4 mb-8 px-4">
             <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">{{ __('Analytics Summary') }}</h2>
                <button @click="exportCSV" class="p-2 bg-red-600 rounded-lg shadow-lg">
                    <ArrowDownTrayIcon class="w-5 h-5 text-white" />
                </button>
             </div>
             <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                <button v-for="(name, idx) in monthNames" :key="name"
                        @click="selectMonth(idx)"
                        class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium border transition-all"
                        :class="selectedMonth === idx ? 'bg-red-600 border-red-500 text-white' : 'bg-white/5 border-white/10 text-gray-400 hover:bg-white/10'">
                    {{ name }}
                </button>
             </div>
        </div>

        <!-- SECTION 1: TOP SUMMARY -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <Card v-for="(card, i) in props.summary" :key="card.label" variant="glass" 
                  class="!mt-0 relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-gray-400 text-xs uppercase font-bold tracking-wider mb-1">{{ card.label }}</p>
                        <h3 class="text-3xl font-bold text-white group-hover:text-red-400 transition-colors">{{ card.value }}</h3>
                    </div>
                    <div :class="['p-3 rounded-2xl transition-all duration-500 group-hover:rotate-12', summaryBgColors[i]]">
                        <component :is="summaryIcons[i]" :class="['w-7 h-7', summaryColors[i]]" />
                    </div>
                </div>
                <!-- Decorative background pulse -->
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-red-500/5 rounded-full blur-2xl group-hover:bg-red-500/10 transition-colors duration-700"></div>
            </Card>
        </div>

        <!-- SECTION 2: FILTERS & BREAKDOWN CONTROLS -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4 px-1">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-500/10 rounded-lg">
                    <FunnelIcon class="w-5 h-5 text-red-400" />
                </div>
                <div>
                     <h3 class="text-lg font-bold text-white tracking-tight">{{ __('Employee Statistics') }}</h3>
                     <p class="text-xs text-gray-500">{{ __('Detailed performance breakdown for') }} {{ selectedMonthLabel }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-white/5 border border-white/10 p-1.5 rounded-xl backdrop-blur-sm w-full md:w-auto">
                <span class="text-xs font-bold text-gray-500 uppercase px-3 hidden lg:block">{{ __('Filter:') }}</span>
                <div class="relative w-full">
                    <select v-model="selectedStaff" @change="selectStaff" 
                            class="w-full md:w-64 bg-transparent border-none text-gray-200 text-sm font-semibold focus:ring-0 cursor-pointer appearance-none pr-10">
                        <option v-for="name in staffNames" :key="name" :value="name">{{ name }}</option>
                    </select>
                    <ChevronDownIcon class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                </div>
            </div>
        </div>

        <!-- SECTION 3: CHARTS & METRICS GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Attendance Breakdown -->
            <Card variant="glass" class="!mt-0 lg:col-span-1 border border-white/5 flex flex-col h-full">
                <template #default>
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1.5 h-6 bg-red-500 rounded-full"></div>
                        <h4 class="font-bold text-white">{{ __('Attendance Distribution') }}</h4>
                    </div>
                    <div class="space-y-6 flex-1">
                        <div v-for="staff in props.staffAttendance" :key="staff.name" class="space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-gray-300">{{ staff.name }}</span>
                                <span class="text-red-400 font-mono">{{ ((staff.present / (staff.present + staff.late + staff.absent)) * 100).toFixed(0) }}%</span>
                            </div>
                            <div class="flex h-2.5 rounded-full overflow-hidden bg-white/5 border border-white/5 p-[1px]">
                                <div
                                    class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(16,185,129,0.3)]"
                                    :style="{ width: `${(staff.present / (staff.present + staff.late + staff.absent) * 100).toFixed(1)}%` }"
                                    v-if="staff.present > 0"
                                ></div>
                                <div
                                    class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full transition-all duration-1000 mx-[1px] shadow-[0_0_10px_rgba(245,158,11,0.3)]"
                                    :style="{ width: `${(staff.late / (staff.present + staff.late + staff.absent) * 100).toFixed(1)}%` }"
                                    v-if="staff.late > 0"
                                ></div>
                                <div
                                    class="h-full bg-gradient-to-r from-red-500 to-red-600 rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(239,68,68,0.3)]"
                                    :style="{ width: `${(staff.absent / (staff.present + staff.late + staff.absent) * 100).toFixed(1)}%` }"
                                    v-if="staff.absent > 0"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-8 pt-4 border-t border-white/5 text-[10px] uppercase font-bold tracking-widest text-gray-500">
                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>{{ __('Present') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-amber-500 rounded-full shadow-[0_0_5px_rgba(245,158,11,0.5)]"></span>{{ __('Late') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 bg-red-500 rounded-full shadow-[0_0_5px_rgba(239,68,68,0.5)]"></span>{{ __('Absent') }}</div>
                    </div>
                </template>
            </Card>

            <!-- Working Hours Grid -->
            <Card variant="glass" class="!mt-0 lg:col-span-1 border border-white/5 h-full">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                    <h4 class="font-bold text-white">{{ __('Working Hours') }}</h4>
                </div>
                <div class="grid grid-cols-1 gap-4 overflow-y-auto max-h-[500px] custom-scrollbar pr-2">
                    <div v-for="staff in props.staffHours" :key="staff.name" 
                         class="group bg-white/5 border border-white/5 rounded-2xl p-4 transition-all hover:bg-white/10 hover:border-white/10">
                        <div class="flex items-center gap-3 mb-3">
                             <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center border border-amber-500/20 text-amber-400 text-xs font-bold">
                                {{ getInitials(staff.name) }}
                            </div>
                            <span class="text-sm font-bold text-gray-200 group-hover:text-amber-400 transition-colors">{{ staff.name }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                             <div class="bg-black/20 px-3 py-1.5 rounded-lg border border-white/5">
                                 <span class="text-gray-500 block text-[10px] uppercase mb-0.5">{{ __('Total') }}</span>
                                 <span class="text-white font-mono font-bold">{{ staff.totalHours }}h</span>
                             </div>
                             <div class="bg-black/20 px-3 py-1.5 rounded-lg border border-white/5">
                                 <span class="text-gray-500 block text-[10px] uppercase mb-0.5">{{ __('Average') }}</span>
                                 <span class="text-white font-mono font-bold">{{ staff.avgDailyHours }}h</span>
                             </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Approved Leaves -->
            <Card variant="glass" class="!mt-0 lg:col-span-1 border border-white/5 h-full">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                    <h4 class="font-bold text-white">{{ __('Leaves Management') }}</h4>
                </div>
                <div class="grid grid-cols-1 gap-4 overflow-y-auto max-h-[500px] custom-scrollbar pr-2">
                    <div v-for="staff in props.staffLeaves" :key="staff.name" 
                         class="group bg-white/5 border border-white/5 rounded-2xl p-4 flex items-center justify-between transition-all hover:bg-white/10">
                        <div class="flex items-center gap-3">
                             <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/20 text-emerald-400 text-xs font-bold">
                                {{ getInitials(staff.name) }}
                            </div>
                            <span class="text-sm font-bold text-gray-200 group-hover:text-emerald-400 transition-colors">{{ staff.name }}</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] uppercase font-bold text-gray-500 mb-1 tracking-wider">{{ __('Approved') }}</span>
                            <span class="text-lg font-bold font-mono" :class="staff.approved > 0 ? 'text-emerald-400' : 'text-gray-600'">{{ staff.approved }}</span>
                        </div>
                    </div>
                </div>
            </Card>

        </div>

        <!-- SECTION 4: RANKING TABLE -->
        <Card variant="glass" class="!mt-0 relative overflow-hidden">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                     <h3 class="text-xl font-bold text-white tracking-tight">{{ __('Employee Rankings') }}</h3>
                     <p class="text-sm text-gray-500">{{ __('Calculated based on attendance rate and active participation') }}</p>
                </div>
                <div class="bg-red-500/10 border border-red-500/20 px-4 py-2 rounded-xl">
                    <span class="text-[10px] uppercase font-bold text-red-400 tracking-widest block mb-0.5 text-center">{{ __('Formula') }}</span>
                    <span class="text-xs text-white/80 font-mono">Score = (AttendanceRate / 100) * 100</span>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.2em] font-black text-gray-500 text-left">
                            <th class="px-6 py-4">{{ __('Rank') }}</th>
                            <th class="px-6 py-4">{{ __('Employee') }}</th>
                            <th class="px-6 py-4">{{ __('Attendance Integrity') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Final Score') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, idx) in props.staffRanking" :key="row.name" 
                            class="group rounded-2xl transition-all duration-300 hover:translate-x-2">
                            <!-- RANK -->
                            <td class="px-6 py-4 whitespace-nowrap bg-white/5 border border-white/5 border-r-0 rounded-l-2xl group-hover:bg-red-500/10 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div v-if="idx < 3" class="w-8 h-8 flex items-center justify-center transform group-hover:scale-125 transition-transform duration-500">
                                        <span v-if="idx === 0" class="text-2xl">🥇</span>
                                        <span v-if="idx === 1" class="text-2xl">🥈</span>
                                        <span v-if="idx === 2" class="text-2xl">🥉</span>
                                    </div>
                                    <span v-else class="text-sm font-bold text-gray-400 font-mono ml-3">#{{ idx + 1 }}</span>
                                </div>
                            </td>
                            <!-- EMPLOYEE -->
                            <td class="px-6 py-4 whitespace-nowrap bg-white/5 border-y border-white/5 group-hover:bg-red-500/10 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-800 flex items-center justify-center shadow-lg transform group-hover:rotate-6 transition-transform">
                                        <span class="text-white text-sm font-black">{{ getInitials(row.name) }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-100 group-hover:text-red-300 transition-colors">{{ row.name }}</span>
                                </div>
                            </td>
                            <!-- ATTENDANCE PROGRESS -->
                            <td class="px-6 py-4 whitespace-nowrap bg-white/5 border-y border-white/5 group-hover:bg-red-500/10 transition-colors">
                                <div class="flex items-center gap-4 min-w-[200px]">
                                    <div class="flex-1 h-3 bg-black/40 rounded-full overflow-hidden border border-white/5 p-[2px]">
                                        <div class="h-full rounded-full transition-all duration-1000 shadow-lg"
                                             :style="{ width: row.attendance + '%', background: row.attendance >= 90 ? 'linear-gradient(to right, #10b981, #34d399)' : row.attendance >= 80 ? 'linear-gradient(to right, #f59e0b, #fbbf24)' : 'linear-gradient(to right, #ef4444, #f87171)' }">
                                        </div>
                                    </div>
                                    <span class="text-xs font-black font-mono" :class="row.attendance >= 90 ? 'text-emerald-400' : row.attendance >= 80 ? 'text-amber-400' : 'text-red-400'">
                                        {{ row.attendance }}%
                                    </span>
                                </div>
                            </td>
                            <!-- SCORE -->
                            <td class="px-6 py-4 whitespace-nowrap bg-white/5 border border-white/5 border-l-0 rounded-r-2xl text-center group-hover:bg-red-500/10 transition-colors">
                                <div class="inline-flex items-center justify-center px-4 py-1.5 rounded-full border shadow-lg font-mono font-black text-sm transition-all duration-500 group-hover:px-6"
                                     :class="[
                                         row.score >= 90 ? 'bg-emerald-500/20 border-emerald-500/30 text-emerald-400 shadow-emerald-500/10' : 
                                         row.score >= 80 ? 'bg-amber-500/20 border-amber-500/30 text-amber-400 shadow-amber-500/10' : 
                                         'bg-red-500/20 border-red-500/30 text-red-400 shadow-red-500/10'
                                     ]">
                                    {{ row.score }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
             <!-- Empty State for Search/Filter -->
             <div v-if="props.staffRanking.length === 0" class="py-20 flex flex-col items-center justify-center text-center">
                <div class="p-6 bg-white/5 rounded-full mb-4 border border-white/10">
                    <RocketIcon class="w-12 h-12 text-gray-600" />
                </div>
                <h3 class="text-xl font-bold text-gray-300 mb-2">{{ __('No analytics data found') }}</h3>
                <p class="text-gray-500 max-w-xs">{{ __('Try adjusting your filters or selecting a different month to view performance records.') }}</p>
            </div>
        </Card>

    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
  height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(239, 68, 68, 0.2);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(239, 68, 68, 0.4);
}

@keyframes dropdown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-down {
    animation: dropdown 0.3s ease-out forwards;
}
</style>