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
        <Card class="!mt-0 bg-gray-800 rounded-xl shadow-lg border border-gray-700">
          <div class="flex justify-between items-center mb-4">
            <FlexButton :text="'Previous'" @click="prevWeek" :class="'text-white px-4 py-1 rounded font-semibold text-sm'" />
            <span class="font-semibold text-base text-gray-200">{{ currentMonday.format('MMM D, YYYY') }} - {{ currentMonday.add(6, 'day').format('MMM D, YYYY') }}</span>
            <FlexButton :text="'Next'" @click="nextWeek" :class="'text-white px-4 py-1 rounded font-semibold text-sm'" />
          </div>
        </Card>
        <Card class="mt-6 bg-gray-800 rounded-xl shadow-lg border border-gray-700">
          <div class="px-2 pb-4">
            <table class="w-full border-separate border-spacing-y-1">
              <thead>
                <tr>
                  <th class="text-sm font-bold text-gray-200 py-2 px-12 text-left">Day</th>
                  <th class="text-sm font-bold text-gray-200 py-2 text-center">Morning<br><span class="text-xs text-gray-400">6:00 - 15:00</span></th>
                  <th class="text-sm font-bold text-gray-200 py-2 text-center">Night<br><span class="text-xs text-gray-400">15:00 - 00:00</span></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(day, dayIdx) in weekDays" :key="dayIdx" class="border-b last:border-b-0 bg-gray-800">
                  <td class="py-2 px-1 align-top">
                    <div
                      class="w-full min-h-[48px] flex items-center justify-center rounded transition text-sm font-medium border bg-gray-900 text-gray-100 border-gray-700"
                      style="height: 48px; width: 120px; padding: 0 0.25rem;"
                    >
                      <span class="text-xs text-center" style="width: 100%;">{{ day.format('ddd D') }}</span>
                    </div>
                  </td>
                  <td v-for="shiftIdx in [0,1]" :key="shiftIdx" class="py-2 px-1 align-top">
                    <div
                      :class="[
                        'w-full min-h-[48px] flex items-center justify-center rounded transition text-sm font-medium border cursor-pointer relative',
                        isShiftAssigned(day, shiftIdx)
                          ? 'bg-green-800/30 text-green-100 border-green-500 hover:bg-green-700/60'
                          : 'bg-gray-900 text-gray-400 border-gray-700 hover:bg-blue-900/60',
                        isSubmitted ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                      @click="isSubmitted ? null : openDayModal(day, shiftIdx)"
                      :title="isShiftAssigned(day, shiftIdx) ? getStaffName(day, shiftIdx) : 'Click to assign staff'"
                      style="min-height: 48px; height: 48px; padding: 0 0.25rem; display: flex; align-items: center; justify-content: center;"
                    >
                      <template v-if="isShiftAssigned(day, shiftIdx)">
                        <div class="flex items-center justify-center gap-1 w-full" style="width: 120px;">
                          <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4 text-green-300' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z' /></svg>
                          <span class="text-gray-100 font-semibold text-xs text-center" style="word-break: break-word; max-height: 3.6em; line-height: 1.2; overflow: hidden; white-space: normal; width: 90px; display: inline-block;">
                        {{ getStaffName(day, shiftIdx) }}
                      </span>
                        </div>
                      </template>
                      <template v-else>
                        <div class="flex flex-col items-center justify-center w-full" style="width: 120px;">
                          <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-blue-400 mb-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4' /></svg>
                          <span class="text-xs text-blue-300 text-center">Assign</span>
                        </div>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="flex justify-end mt-6 space-x-2">
              <FlexButton :text="'Reset'" @click="resetAssignments" :class="'text-white px-8 py-2 rounded-md font-semibold text-base'" :disabled="isSubmitted" />
              <FlexButton
                v-if="!isSubmitted"
                :text="'Submit Weekly Schedule'"
                @click="submitSchedule"
                :class="'text-white px-8 py-2 rounded-md font-semibold text-base'"
                :disabled="isSubmitted"
              />
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