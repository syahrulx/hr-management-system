<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import dayjs from 'dayjs';
import Card from '@/Components/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import ScheduleTabs from '@/Components/Tabs/ScheduleTabs.vue';
import FlexButton from '@/Components/FlexButton.vue';

const props = defineProps({
  staffList: {
    type: Array,
    default: () => [],
  },
  leaveList: {
    type: Array,
    default: () => [],
  }
});

console.log('staffList:', props.staffList);

const shiftNames = ['Morning', 'Night'];
const shiftTimes = [
  { label: 'Morning', start: '06:00', end: '15:00' },
  { label: 'Night', start: '15:00', end: '00:00' }
];

const shiftApiNames = ['morning', 'evening'];

// State for the selected week (start on Monday)
const today = dayjs();
const thisMonday = today.startOf('week').add(1, 'day');
// const currentMonday = ref(today.isBefore(thisMonday, 'day') ? thisMonday : thisMonday.add(7, 'day'));
// FIX: Always show this week's Monday by default
const currentMonday = ref(thisMonday);

// State for the selected day and shift
const selectedDay = ref(null);
const selectedShiftIdx = ref(null);

// Loading state for validation
const isValidating = ref(false);

// Assignments: Each day holds two shifts, each shift holds staffId or null
const assignments = ref({});

// Local state for the modal selection
const selectedStaffId = ref('');

// Fetch assignments for the current week from backend
const isSubmitted = ref(false);

async function fetchAssignments() {
  const weekStart = currentMonday.value.format('YYYY-MM-DD');
  const { data } = await axios.get('/schedule/week', { params: { week_start: weekStart } });
  assignments.value = data.assignments || {};
  isSubmitted.value = data.submitted || false;
}

// Fetch assignments on page load and when week changes
onMounted(fetchAssignments);
watch(currentMonday, fetchAssignments);

// Calculate the days for the selected week
const weekDays = computed(() => {
  return Array.from({ length: 7 }, (_, i) => currentMonday.value.add(i, 'day'));
});

// Open modal for a specific day and shift
function openDayModal(day, shiftIdx) {
  selectedDay.value = day;
  selectedShiftIdx.value = shiftIdx;
  if (!assignments.value[day.format('YYYY-MM-DD')]) {
    assignments.value[day.format('YYYY-MM-DD')] = [null, null];
  }
  selectedStaffId.value = assignments.value[day.format('YYYY-MM-DD')][shiftIdx] || '';
}

// Close modal
function closeDayModal() {
  if (isValidating.value) return;
  selectedDay.value = null;
  selectedShiftIdx.value = null;
}

// Assign staff to a shift with loading animation and backend call
async function assignStaff() {
  isValidating.value = true;
  // Removed artificial delay
  // await new Promise(resolve => setTimeout(resolve, 1000));

  const staffId = selectedStaffId.value;
  const dayKey = selectedDay.value.format('YYYY-MM-DD');
  const weekStart = currentMonday.value.format('YYYY-MM-DD');

  // 1. Check if staff is on leave that day
  // leaveList should be an array of { user_id, start_date, end_date }
  if (props.leaveList && props.leaveList.some(l => l.user_id == staffId && l.start_date <= dayKey && (!l.end_date || l.end_date >= dayKey))) {
    isValidating.value = false;
    alert('This staff is on leave for the selected day. Please pick another staff.');
    return;
  }

  // 2. Check if staff is already assigned to another shift on the same day
  const assignmentsForDay = assignments.value[dayKey] || [null, null];
  if (assignmentsForDay.includes(staffId)) {
    isValidating.value = false;
    alert('This staff is already assigned to another shift on this day. Please pick another staff.');
    return;
  }

  // 3. Check if staff is assigned to more than 6 days in the week
  let daysAssigned = 0;
  for (const [date, shifts] of Object.entries(assignments.value)) {
    if (date >= weekStart && date <= dayjs(weekStart).add(6, 'day').format('YYYY-MM-DD')) {
      if (shifts.includes(staffId)) daysAssigned++;
    }
  }
  if (daysAssigned >= 6) {
    isValidating.value = false;
    alert('This staff is already assigned to 6 days in this week. Please pick another staff.');
    return;
  }

  // If all checks pass, proceed to save
  await axios.post('/schedule/assign', {
    employee_id: staffId,
    shift_type: shiftApiNames[selectedShiftIdx.value],
    day: dayKey,
  });
  await fetchAssignments();
  isValidating.value = false;
  closeDayModal();
}

// Navigate to previous week
function prevWeek() {
  currentMonday.value = currentMonday.value.subtract(1, 'week');
  fetchAssignments();
}

// Navigate to next week
function nextWeek() {
  currentMonday.value = currentMonday.value.add(1, 'week');
  fetchAssignments();
}

// Submit the schedule
async function submitSchedule() {
  // //If today is not Sunday (the day before the selected week starts), show an alert
  // //"Supervisor can only submit the schedule one day before the week starts (on Sunday)."
  // const today = dayjs();
  // const nextMonday = currentMonday.value;
  // const sundayBeforeWeek = nextMonday.subtract(1, 'day');
  // //Only allow submit if today is the Sunday before the selected week
  // if (!today.isSame(sundayBeforeWeek, 'day')) {
  //   alert('Supervisor can only submit the schedule one day before the week starts (on Sunday).');
  //   return;
  // }
  // alert('Weekly schedule submitted successfully!');
  // Move to next week
  await axios.post('/schedule/submit-week', {
    week_start: currentMonday.value.format('YYYY-MM-DD'),
  });
  await fetchAssignments();
  alert('Weekly schedule submitted successfully!');
  // TEMP DISABLED: Do not move to next week automatically after submit
  // currentMonday.value = currentMonday.value.add(7, 'day');
}

// Get staff name for a shift
function getStaffName(day, shiftIdx) {
  const staffId = assignments.value[day.format('YYYY-MM-DD')]?.[shiftIdx];
  const staff = props.staffList.find(s => s.id == staffId);
  return staff ? staff.name : '';
}

// Check if a shift is assigned
function isShiftAssigned(day, shiftIdx) {
  return !!assignments.value[day.format('YYYY-MM-DD')]?.[shiftIdx];
}

// Reset all assignments for the current week
async function resetAssignments() {
  if (!confirm('Are you sure you want to reset all assignments for this week?')) return;
  // Optionally, call backend to delete all assignments for this week
  await axios.post('/schedule/reset', {
    week_start: currentMonday.value.format('YYYY-MM-DD'),
  });
  assignments.value = {};
  await fetchAssignments();
}
</script>

<template>
  <Head title="Weekly Schedule" />
  <AuthenticatedLayout>
    <template #tabs>
      <ScheduleTabs />
    </template>
    <div class="py-6">
      <div class="max-w-5xl mx-auto sm:px-4 lg:px-6">
        <Card variant="glass" class="!mt-0">
          <div class="flex justify-between items-center px-2">
            <FlexButton :text="'Previous'" @click="prevWeek" class="!bg-white/5 !border-white/10 hover:!bg-white/10 text-white px-6 py-2 rounded-full font-bold text-sm transition-all" />
            <span class="font-bold text-lg text-white tracking-wide shadow-red-500/50 drop-shadow-lg">{{ currentMonday.format('MMM D, YYYY') }} - {{ currentMonday.add(6, 'day').format('MMM D, YYYY') }}</span>
            <FlexButton :text="'Next'" @click="nextWeek" class="!bg-white/5 !border-white/10 hover:!bg-white/10 text-white px-6 py-2 rounded-full font-bold text-sm transition-all" />
          </div>
        </Card>
        
        <Card variant="glass" class="mt-6">
          <div class="px-2 pb-4 overflow-x-auto">
            <table class="w-full border-separate border-spacing-y-2">
              <thead>
                <tr>
                  <th class="text-xs font-bold text-gray-400 uppercase tracking-widest py-4 px-4 text-left">Day</th>
                  <th class="text-xs font-bold text-gray-400 uppercase tracking-widest py-4 text-center">Morning<br><span class="text-[10px] text-gray-500">6:00 - 15:00</span></th>
                  <th class="text-xs font-bold text-gray-400 uppercase tracking-widest py-4 text-center">Night<br><span class="text-[10px] text-gray-500">15:00 - 00:00</span></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(day, dayIdx) in weekDays" :key="dayIdx" class="group">
                  <td class="py-2 px-2 align-middle">
                    <div
                      class="w-full h-16 flex flex-col items-center justify-center rounded-xl transition-all duration-300
                             bg-white/5 border border-white/5 text-gray-200 group-hover:bg-white/10 group-hover:border-white/20"
                      style="width: 100px;"
                    >
                      <span class="text-xs font-bold uppercase text-red-400">{{ day.format('ddd') }}</span>
                      <span class="text-xl font-bold">{{ day.format('D') }}</span>
                    </div>
                  </td>
                  <td v-for="shiftIdx in [0,1]" :key="shiftIdx" class="py-2 px-2 align-middle">
                    <div
                      :class="[
                        'w-full h-16 flex items-center justify-center rounded-xl transition-all duration-300 border cursor-pointer relative backdrop-blur-md',
                        isShiftAssigned(day, shiftIdx)
                          ? 'bg-gradient-to-br from-green-500/20 to-emerald-900/40 border-green-500/30 hover:border-green-400/50 hover:shadow-lg hover:shadow-green-900/20'
                          : 'bg-white/5 border-white/5 hover:bg-white/10 hover:border-white/20 text-gray-400',
                        isSubmitted ? 'opacity-50 cursor-not-allowed grayscale' : ''
                      ]"
                      @click="isSubmitted ? null : openDayModal(day, shiftIdx)"
                      :title="isShiftAssigned(day, shiftIdx) ? getStaffName(day, shiftIdx) : 'Click to assign staff'"
                    >
                      <template v-if="isShiftAssigned(day, shiftIdx)">
                        <div class="flex flex-col items-center justify-center w-full px-2">
                          <span class="text-white font-semibold text-xs text-center line-clamp-2 leading-tight">
                            {{ getStaffName(day, shiftIdx) }}
                          </span>
                        </div>
                      </template>
                      <template v-else>
                        <div class="flex flex-col items-center justify-center w-full group-hover:scale-110 transition-transform duration-300">
                           <span class="text-2xl text-white/20 font-light mb-0 leading-none">+</span>
                           <span class="text-[10px] uppercase tracking-wider text-white/40 font-bold">Assign</span>
                        </div>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <div class="flex justify-end mt-8 space-x-4 border-t border-white/10 pt-6">
              <button @click="resetAssignments" 
                      :disabled="isSubmitted"
                      class="px-6 py-2 rounded-full font-bold text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all disabled:opacity-50">
                  Reset Week
              </button>
              
              <button v-if="!isSubmitted"
                      @click="submitSchedule"
                      :disabled="isSubmitted"
                      class="px-8 py-3 rounded-full font-bold text-sm text-white uppercase tracking-wider bg-gradient-to-r from-red-600 to-red-800 shadow-lg shadow-red-900/40 hover:shadow-red-600/40 hover:scale-105 active:scale-95 transition-all duration-300">
                  Submit Schedule
              </button>
            </div>
          </div>
        </Card>
        <!-- Modal -->
        <transition name="fade">
          <div v-if="selectedDay !== null && selectedShiftIdx !== null && !isSubmitted" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-2xl shadow-2xl p-8 w-full max-w-md relative animate-fadeIn border border-gray-700">
              <button @click="closeDayModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-200 text-xl font-bold" :disabled="isValidating">&times;</button>
              <h2 class="text-2xl font-bold mb-6 text-gray-100 text-center">
                Assign {{ shiftNames[selectedShiftIdx] }} Shift<br>
                <span class="text-base font-medium text-gray-400">for {{ selectedDay.format('ddd, MMM D') }}</span>
              </h2>
              <div v-if="isValidating" class="flex flex-col items-center justify-center py-8">
                <svg class="animate-spin h-10 w-10 text-red-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-red-400 font-semibold text-lg">Validating staff...</span>
              </div>
              <div v-else class="mb-6">
                <label class="block text-sm font-semibold mb-2 text-gray-200">Select Staff</label>
                <select
                  v-model="selectedStaffId"
                  class="border-2 border-gray-700 focus:border-red-500 focus:ring-2 focus:ring-red-200 rounded-md px-3 py-2 w-full text-base transition shadow-sm outline-none bg-gray-900 text-gray-100"
                  :disabled="isValidating"
                  autofocus
                >
                  <option value="">Select Staff</option>
                  <option v-for="staff in props.staffList.filter(s => s.id !== 1)" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
                </select>
                <FlexButton
                  :text="'Assign'"
                  @click="assignStaff"
                  :class="'w-full text-white font-semibold py-2 rounded-md mt-4'"
                  :disabled="isValidating || !selectedStaffId"
                />
              </div>
              <FlexButton @click="closeDayModal" :text="'Cancel'" :class="'w-full text-gray-200 font-semibold py-2 rounded-md transition mt-2'" :disabled="isValidating" />
            </div>
          </div>
        </transition>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.animate-fadeIn {
  animation: fadeIn 0.3s;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
</style> 