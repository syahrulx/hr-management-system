<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import dayjs from 'dayjs';
import axios from 'axios';
import MyScheduleTabs from '@/Components/Tabs/MyScheduleTabs.vue';

const today = dayjs();
const thisMonday = today.startOf('week').add(1, 'day');
// const currentMonday = ref(today.isBefore(thisMonday, 'day') ? thisMonday : thisMonday.add(7, 'day'));
// FIX: Always show this week's Monday by default
const currentMonday = ref(thisMonday);

const assignments = ref({});

async function fetchAssignments() {
  const weekStart = currentMonday.value.format('YYYY-MM-DD');
  const { data } = await axios.get('/my-schedule/week', { params: { week_start: weekStart } });
  assignments.value = data.assignments || {};
}

onMounted(fetchAssignments);
watch(currentMonday, fetchAssignments);

function prevWeek() {
  currentMonday.value = currentMonday.value.subtract(1, 'week');
}
function nextWeek() {
  currentMonday.value = currentMonday.value.add(1, 'week');
}

const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

// Tab state
const activeTab = ref('weekly');
</script>
<template>
  <AuthenticatedLayout>
    <template #tabs>
      <MyScheduleTabs />
    </template>
    <div class="py-8  min-h-screen">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <template v-if="activeTab === 'weekly'">
          <Card variant="glass" class="!mt-0">
             <div class="flex justify-between items-center px-2">
                 <button @click="prevWeek" class="bg-white/5 hover:bg-white/10 text-white px-6 py-2 rounded-full font-bold text-sm transition-all border border-white/10">Previous</button>
                 <span class="font-bold text-lg text-white tracking-wide shadow-red-500/50 drop-shadow-lg">{{ currentMonday.format('MMM D, YYYY') }} - {{ currentMonday.add(6, 'day').format('MMM D, YYYY') }}</span>
                 <button @click="nextWeek" class="bg-white/5 hover:bg-white/10 text-white px-6 py-2 rounded-full font-bold text-sm transition-all border border-white/10">Next</button>
             </div>
          </Card>

          <Card variant="glass" class="mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                <thead class="text-xs text-gray-400 uppercase tracking-widest bg-transparent">
                    <tr>
                    <th class="px-6 py-4 font-bold">Day</th>
                    <th class="px-6 py-4 font-bold text-center">Morning<br><span class="text-[10px] text-gray-500 normal-case">6:00 AM - 3:00 PM</span></th>
                    <th class="px-6 py-4 font-bold text-center">Evening<br><span class="text-[10px] text-gray-500 normal-case">3:00 PM - 12:00 AM</span></th>
                    </tr>
                </thead>
                <tbody class="text-gray-300">
                    <tr v-for="i in 7" :key="i" class="group hover:bg-white/5 transition-colors rounded-xl">
                    <td class="px-6 py-4 whitespace-nowrap text-base font-bold text-white border-b border-white/5 group-hover:border-transparent rounded-l-xl">{{ days[i-1] }} <span class="text-xs text-gray-500 font-normal ml-2">{{ currentMonday.add(i-1, 'day').format('D MMM') }}</span></td>
                    <td class="px-6 py-4 whitespace-nowrap text-center border-b border-white/5 group-hover:border-transparent">
                        <span v-if="assignments[currentMonday.add(i-1, 'day').format('YYYY-MM-DD')]?.morning" class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-green-500/20 text-green-300 border border-green-500/30 shadow-sm shadow-green-900/20">
                        Morning
                        </span>
                        <span v-else class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-white/5 text-gray-500 border border-white/5">
                        -
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center border-b border-white/5 group-hover:border-transparent rounded-r-xl">
                        <span v-if="assignments[currentMonday.add(i-1, 'day').format('YYYY-MM-DD')]?.evening" class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-red-500/20 text-red-300 border border-red-500/30 shadow-sm shadow-red-900/20">
                        Evening
                        </span>
                        <span v-else class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-white/5 text-gray-500 border border-white/5">
                        -
                        </span>
                    </td>
                    </tr>
                </tbody>
                </table>
            </div>
          </Card>
          <div class="mt-8 text-center text-gray-500 text-xs uppercase tracking-widest opacity-60">
            <span class="">Contact your supervisor for schedule changes</span>
          </div>
        </template>
        <template v-else-if="activeTab === 'task'">
          <div class="flex flex-col items-center justify-center h-64">
            <span class="text-xl text-gray-400">View Task feature coming soon...</span>
          </div>
        </template>
      </div>
    </div>
  </AuthenticatedLayout>
</template>