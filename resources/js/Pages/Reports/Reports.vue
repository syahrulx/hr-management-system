<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

// Icon SVGs for summary cards
const cardIcons = [
  `<svg class='w-7 h-7 text-blue-400' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87M16 3.13a4 4 0 0 1 0 7.75M8 3.13a4 4 0 0 0 0 7.75'/></svg>`, // Employees
  `<svg class='w-7 h-7 text-green-400' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M5 13l4 4L19 7'/></svg>`, // Attendance Rate
  `<svg class='w-7 h-7 text-yellow-400' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>`, // Avg Daily Hours
  `<svg class='w-7 h-7 text-pink-400' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M12 20l9-5-9-5-9 5 9 5z'/><path d='M12 12V4'/></svg>` // Top Staff
];

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
  const newMonth = (idx + 1).toString().padStart(2, "end_date");
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

// Helper for donut SVG
function donutPath(percent, r) {
  const c = 2 * Math.PI * r;
  return {
    strokeDasharray: `${(percent / 100) * c} ${c}`,
    strokeDashoffset: 0,
  };
}

// Medal icons for top 3
const medalIcons = [
  `<svg class='w-5 h-5 text-yellow-400 inline' fill='currentColor' viewBox='0 0 20 20'><path d='M10 2a1 1 0 01.894.553l1.382 2.8 3.09.45a1 1 0 01.554 1.706l-2.236 2.18.528 3.08a1 1 0 01-1.451 1.054L10 12.347l-2.771 1.456a1 1 0 01-1.451-1.054l.528-3.08-2.236-2.18a1 1 0 01.554-1.706l3.09-.45L9.106 2.553A1 1 0 0110 2z'/></svg>`,
  `<svg class='w-5 h-5 text-gray-300 inline' fill='currentColor' viewBox='0 0 20 20'><path d='M10 2a1 1 0 01.894.553l1.382 2.8 3.09.45a1 1 0 01.554 1.706l-2.236 2.18.528 3.08a1 1 0 01-1.451 1.054L10 12.347l-2.771 1.456a1 1 0 01-1.451-1.054l.528-3.08-2.236-2.18a1 1 0 01.554-1.706l3.09-.45L9.106 2.553A1 1 0 0110 2z'/></svg>`,
  `<svg class='w-5 h-5 text-yellow-700 inline' fill='currentColor' viewBox='0 0 20 20'><path d='M10 2a1 1 0 01.894.553l1.382 2.8 3.09.45a1 1 0 01.554 1.706l-2.236 2.18.528 3.08a1 1 0 01-1.451 1.054L10 12.347l-2.771 1.456a1 1 0 01-1.451-1.054l.528-3.08-2.236-2.18a1 1 0 01.554-1.706l3.09-.45L9.106 2.553A1 1 0 0110 2z'/></svg>`
];
</script>

<template>
  <Head title="Reports" />
  <AuthenticatedLayout>
    <div class="py-8 px-2 md:px-0">
      <div class="max-w-7xl mx-auto">
        <!-- Month filter: compact, top-right above summary cards -->
        <div class="flex justify-end items-center mb-4">
          <span class="text-gray-400 text-xs font-semibold mr-2">Filter by month</span>
          <div class="relative">
            <button @click="showMonthDropdown = !showMonthDropdown" class="bg-gray-900 border border-gray-700 text-gray-200 px-3 py-1 rounded-md text-sm font-semibold flex items-center gap-1 focus:outline-none focus:ring-2 focus:ring-red-500">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3M16 7V3M4 11h16M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/></svg>
              {{ selectedMonthLabel }}
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div v-if="showMonthDropdown" class="absolute right-0 mt-2 w-36 bg-gray-900 border border-gray-700 rounded-md shadow-lg z-50">
              <ul>
                <li v-for="(name, idx) in monthNames" :key="name">
                  <button @click="selectMonth(idx)" class="w-full text-left px-4 py-2 text-gray-200 hover:bg-gray-800 focus:bg-gray-800 focus:outline-none" :class="{ 'bg-gray-800': selectedMonth.value === idx }">
                    {{ name }}
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <!-- Section 1: 4 summary cards in a row -->
        <div class="flex flex-row gap-4 mb-4">
          <div v-for="(card, i) in props.summary" :key="card.label" class="flex-1 bg-gray-900 border border-gray-700 rounded-lg p-4 flex flex-col items-center shadow transition hover:shadow-lg hover:bg-gray-800 cursor-pointer group">
            <span v-html="cardIcons[i]" class="mb-2"></span>
            <div class="text-2xl font-bold text-white">{{ card.value }}</div>
            <div class="text-gray-300 text-sm mt-1 text-center">{{ card.label }}</div>
          </div>
        </div>
        <hr class="my-6 border-0 h-0.5 bg-gray-800 rounded" />
        <!-- Staff filter above charts (second section) -->
        <div class="flex flex-row items-center mb-2">
          <label for="staff" class="text-gray-400 text-xs font-semibold mr-2">Filter by staff</label>
          <select id="staff" v-model="selectedStaff" @change="selectStaff" class="bg-gray-900 border border-gray-700 text-gray-200 px-3 py-1 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            <option v-for="name in staffNames" :key="name" :value="name">{{ name }}</option>
          </select>
        </div>
        <!-- Section 2: Attendance, Working Hours, Approved Leaves -->
        <div class="flex flex-row gap-4 mb-4">
          <!-- Attendance Breakdown Card -->
          <div class="flex-1 bg-gray-800 border border-gray-700 rounded-lg p-6 shadow flex flex-col">
            <div class="font-semibold text-white mb-4">Attendance Breakdown (This Month)</div>
            <div class="flex flex-col gap-7">
              <div v-for="staff in props.staffAttendance" :key="staff.name" class="flex flex-col gap-2">
                <div class="text-sm text-gray-200 mb-1">{{ staff.name }}</div>
                <div class="flex items-center h-8 rounded overflow-hidden w-full" style="min-width: 200px;">
                  <div
                    class="flex items-center justify-center h-full bg-green-500 text-white text-xs font-bold"
                    :style="{ width: `${(staff.present / (staff.present + staff.late + staff.absent) * 100).toFixed(1)}%` }"
                    v-if="staff.present > 0"
                  >
                    {{ staff.present }}
                  </div>
                  <div
                    class="flex items-center justify-center h-full bg-yellow-400 text-gray-900 text-xs font-bold"
                    :style="{ width: `${(staff.late / (staff.present + staff.late + staff.absent) * 100).toFixed(1)}%` }"
                    v-if="staff.late > 0"
                  >
                    {{ staff.late }}
                  </div>
                  <div
                    class="flex items-center justify-center h-full bg-red-500 text-white text-xs font-bold"
                    :style="{ width: `${(staff.absent / (staff.present + staff.late + staff.absent) * 100).toFixed(1)}%` }"
                    v-if="staff.absent > 0"
                  >
                    {{ staff.absent }}
                  </div>
                </div>
              </div>
            </div>
            <div class="flex gap-6 mt-8 text-sm text-gray-300">
              <span class="flex items-center"><span class="inline-block w-4 h-4 bg-green-500 rounded-full mr-2"></span>Present</span>
              <span class="flex items-center"><span class="inline-block w-4 h-4 bg-yellow-400 rounded-full mr-2"></span>Late</span>
              <span class="flex items-center"><span class="inline-block w-4 h-4 bg-red-500 rounded-full mr-2"></span>Absent</span>
            </div>
          </div>
          <!-- Working Hours Card -->
          <div class="flex-1 bg-gray-800 border border-gray-700 rounded-lg p-6 shadow flex flex-col">
            <div class="font-semibold text-white mb-4">Working Hours (This Month)</div>
            <div class="grid grid-cols-2 gap-6">
              <div v-for="staff in props.staffHours" :key="staff.name" class="bg-gray-900 border border-gray-800 rounded p-4">
                <div class="text-sm text-gray-200 font-semibold mb-1">{{ staff.name }}</div>
                <div class="text-xs text-gray-400">Total: {{ staff.totalHours }} h</div>
                <div class="text-xs text-gray-400">Avg/Day: {{ staff.avgDailyHours }} h</div>
              </div>
            </div>
          </div>
          <!-- Approved Leaves Card -->
          <div class="flex-1 bg-gray-800 border border-gray-700 rounded-lg p-6 shadow flex flex-col">
            <div class="font-semibold text-white mb-4">Approved Leaves (This Month)</div>
            <div class="grid grid-cols-2 gap-6">
              <div v-for="staff in props.staffLeaves" :key="staff.name" class="bg-gray-900 border border-gray-800 rounded p-4">
                <div class="text-sm text-gray-200 font-semibold mb-1">{{ staff.name }}</div>
                <div class="text-xs text-gray-400">Approved: {{ staff.approved }}</div>
              </div>
            </div>
          </div>
        </div>
        <hr class="my-6 border-0 h-0.5 bg-gray-800 rounded" />
        <!-- Section 3: Table -->
        <div class="bg-gray-900 border border-gray-700 rounded-lg shadow p-6">
          <div class="font-semibold text-white mb-4">Ranking (Attendance)</div>
            <div class="text-xs text-gray-400 mb-2">Score = AttendanceRate*0.7</div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-gray-200">
              <thead class="sticky top-0 z-10 bg-gray-900 border-b border-gray-800">
                <tr>
                  <th class="px-4 py-2">Rank</th>
                  <th class="px-4 py-2">Name</th>
                  <th class="px-4 py-2">Attendance</th>
                  <th class="px-4 py-2">Score</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, idx) in props.staffRanking" :key="row.name" class="border-b border-gray-800 hover:bg-gray-800 transition">
                  <td class="px-4 py-2 font-bold text-center">
                    <span v-if="idx < 3" v-html="medalIcons[idx]"></span>
                    <span v-else>{{ idx + 1 }}</span>
                  </td>
                  <td class="px-4 py-2">{{ row.name }}</td>
                  <td class="px-4 py-2 w-40">
                    <div class="w-full h-4 bg-gray-800 rounded-full overflow-hidden">
                      <div class="h-4 rounded-full"
                        :style="{ width: row.attendance + '%', background: row.attendance >= 90 ? '#3ECF8E' : row.attendance >= 80 ? '#FFB946' : '#FF5C5C' }">
                      </div>
                    </div>
                    <span class="text-xs text-gray-300 ml-2 align-middle">{{ row.attendance }}%</span>
                  </td>
                  <td class="px-4 py-2 font-bold">
                    <span :class="[row.score >= 90 ? 'bg-green-700 text-green-200' : row.score >= 80 ? 'bg-yellow-700 text-yellow-200' : 'bg-red-700 text-red-200', 'px-3 py-1 rounded-full text-xs']">
                      {{ row.score }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.sticky { position: sticky; }
</style>