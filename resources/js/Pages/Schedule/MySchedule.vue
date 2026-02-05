<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch } from "vue";
import dayjs from "dayjs";
import axios from "axios";
import Card from "@/Components/Card.vue";
import MyScheduleTabs from "@/Components/Tabs/MyScheduleTabs.vue";

const today = dayjs();
const thisMonday = today.startOf("week").add(1, "day");
const currentMonday = ref(thisMonday);

const assignments = ref({});

async function fetchAssignments() {
    const weekStart = currentMonday.value.format("YYYY-MM-DD");
    const { data } = await axios.get("/my-schedule/week", {
        params: { week_start: weekStart },
    });
    assignments.value = data.assignments || {};
}

onMounted(fetchAssignments);
watch(currentMonday, fetchAssignments);

function prevWeek() {
    currentMonday.value = currentMonday.value.subtract(1, "week");
}
function nextWeek() {
    currentMonday.value = currentMonday.value.add(1, "week");
}

// Calculate the days for the selected week
const weekDays = computed(() => {
    return Array.from({ length: 7 }, (_, i) =>
        currentMonday.value.add(i, "day")
    );
});

// Check if a shift is assigned
function isShiftAssigned(day, shiftType) {
    const dayKey = day.format("YYYY-MM-DD");
    return !!assignments.value[dayKey]?.[shiftType];
}
</script>

<template>
    <Head title="My Schedule" />
    <AuthenticatedLayout>
        <template #tabs>
            <MyScheduleTabs />
        </template>
        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-4 lg:px-6">
                <Card variant="glass" class="!mt-0">
                    <div class="flex justify-between items-center px-2">
                        <button
                            @click="prevWeek"
                            class="bg-white/5 border border-white/10 hover:bg-white/10 text-white px-6 py-2 rounded-full font-bold text-sm transition-all shadow-none"
                        >
                            {{ __("Previous") }}
                        </button>
                        <span
                            class="font-bold text-lg text-white tracking-wide text-center"
                        >
                            {{ currentMonday.format("MMM D, YYYY") }} -
                            {{
                                currentMonday
                                    .add(6, "day")
                                    .format("MMM D, YYYY")
                            }}
                        </span>
                        <button
                            @click="nextWeek"
                            class="bg-white/5 border border-white/10 hover:bg-white/10 text-white px-6 py-2 rounded-full font-bold text-sm transition-all shadow-none"
                        >
                            {{ __("Next") }}
                        </button>
                    </div>
                </Card>

                <Card variant="glass" class="mt-6">
                    <div class="px-2 pb-4 overflow-x-auto">
                        <table
                            class="w-full border-separate border-spacing-y-2"
                        >
                            <thead>
                                <tr>
                                    <th
                                        class="text-xs font-bold text-gray-400 uppercase tracking-widest py-4 px-4 text-left"
                                    >
                                        Day
                                    </th>
                                    <th
                                        class="text-xs font-bold text-gray-400 uppercase tracking-widest py-4 text-center"
                                    >
                                        Morning<br /><span
                                            class="text-[10px] text-gray-500"
                                                                                        >6:00 AM - 3:00 PM</span
                                        >
                                    </th>
                                    <th
                                        class="text-xs font-bold text-gray-400 uppercase tracking-widest py-4 text-center"
                                    >
                                        Night<br /><span
                                            class="text-[10px] text-gray-500"
                                                                                        >3:00 PM - 12:00 AM</span
                                        >
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(day, dayIdx) in weekDays"
                                    :key="dayIdx"
                                    class="group"
                                >
                                    <td class="py-2 px-2 align-middle">
                                        <div
                                            class="w-full h-16 flex flex-col items-center justify-center rounded-xl transition-all duration-300 bg-white/5 border border-white/5 text-gray-200 group-hover:bg-white/10 group-hover:border-white/20"
                                            style="width: 100px"
                                        >
                                            <span
                                                class="text-xs font-bold uppercase text-red-400"
                                                >{{ day.format("ddd") }}</span
                                            >
                                            <span class="text-xl font-bold">{{
                                                day.format("D")
                                            }}</span>
                                        </div>
                                    </td>
                                    <td
                                        v-for="shiftType in ['morning', 'evening']"
                                        :key="shiftType"
                                        class="py-2 px-2 align-middle"
                                    >
                                        <div
                                            :class="[
                                                'min-w-[180px] h-16 flex items-center justify-center rounded-xl transition-all duration-300 border backdrop-blur-md',
                                                isShiftAssigned(day, shiftType)
                                                    ? 'bg-gradient-to-br from-green-500/20 to-emerald-900/40 border-green-500/30'
                                                    : 'bg-white/5 border-white/5 text-gray-400',
                                            ]"
                                        >
                                            <template
                                                v-if="isShiftAssigned(day, shiftType)"
                                            >
                                                <div
                                                    class="flex items-center justify-center w-full h-full px-3"
                                                >
                                                    <span
                                                        class="text-green-400 font-bold text-xs uppercase tracking-wider"
                                                    >
                                                        ✓ Assigned
                                                    </span>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div
                                                    class="flex flex-col items-center justify-center w-full h-full"
                                                >
                                                    <span
                                                        class="text-xs text-white/30 font-medium"
                                                        >—</span
                                                    >
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
                
                <div
                    class="mt-6 text-center text-gray-500 text-xs uppercase tracking-widest opacity-60"
                >
                    <span>Contact your supervisor for schedule changes</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
