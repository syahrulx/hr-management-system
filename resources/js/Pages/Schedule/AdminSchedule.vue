<script setup>
import { ref, computed, onMounted, watch } from "vue";
import dayjs from "dayjs";
import Card from "@/Components/Card.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import axios from "axios";
import ScheduleTabs from "@/Components/Tabs/ScheduleTabs.vue";
import FlexButton from "@/Components/FlexButton.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";

const page = usePage();
const reassignAlert = computed(() => page.props.flash?.reassign_alert);
const showReassignModal = ref(false);

watch(
    () => page.props.flash?.reassign_alert,
    (newVal) => {
        if (newVal) {
            showReassignModal.value = true;
        }
    },
    { immediate: true },
);

function closeReassignModal() {
    showReassignModal.value = false;
    // Optional: Clear flash via Inertia if needed, but simple close is fine
}

const props = defineProps({
    staffList: {
        type: Array,
        default: () => [],
    },
    leaveList: {
        type: Array,
        default: () => [],
    },
});

console.log("staffList:", props.staffList);

const shiftNames = ["Morning", "Night"];
const shiftTimes = [
    { label: "Morning", start: "06:00", end: "15:00" },
    { label: "Night", start: "15:00", end: "00:00" },
];

const shiftApiNames = ["morning", "evening"];

// State for the selected week (start on Monday)
const today = dayjs();
const thisMonday = today.startOf("week").add(1, "day");
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
const selectedStaffId = ref("");

// Fetch assignments for the current week from backend
const isSubmitted = ref(false);

async function fetchAssignments() {
    const weekStart = currentMonday.value.format("YYYY-MM-DD");
    const { data } = await axios.get("/schedule/week", {
        params: { week_start: weekStart },
    });
    assignments.value = data.assignments || {};
    isSubmitted.value = data.submitted || false;
}

// Fetch assignments on page load and when week changes
onMounted(fetchAssignments);
watch(currentMonday, fetchAssignments);

// Calculate the days for the selected week
const weekDays = computed(() => {
    return Array.from({ length: 7 }, (_, i) =>
        currentMonday.value.add(i, "day"),
    );
});

// Open modal for a specific day and shift
function openDayModal(day, shiftIdx) {
    selectedDay.value = day;
    selectedShiftIdx.value = shiftIdx;
    if (!assignments.value[day.format("YYYY-MM-DD")]) {
        assignments.value[day.format("YYYY-MM-DD")] = [null, null];
    }
    selectedStaffId.value =
        assignments.value[day.format("YYYY-MM-DD")][shiftIdx] || "";
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
    const dayKey = selectedDay.value.format("YYYY-MM-DD");
    const weekStart = currentMonday.value.format("YYYY-MM-DD");

    // 1. Check if staff is on leave that day
    // leaveList should be an array of { user_id, start_date, end_date }
    if (
        props.leaveList &&
        props.leaveList.some(
            (l) =>
                l.user_id == staffId &&
                l.start_date <= dayKey &&
                (!l.end_date || l.end_date >= dayKey),
        )
    ) {
        isValidating.value = false;
        alert(
            "This staff is on leave for the selected day. Please pick another staff.",
        );
        return;
    }

    // 2. Check if staff is already assigned to another shift on the same day
    const assignmentsForDay = assignments.value[dayKey] || [null, null];
    if (assignmentsForDay.includes(staffId)) {
        isValidating.value = false;
        alert(
            "This staff is already assigned to another shift on this day. Please pick another staff.",
        );
        return;
    }

    // 3. Check if staff is assigned to more than 6 days in the week
    let daysAssigned = 0;
    for (const [date, shifts] of Object.entries(assignments.value)) {
        if (
            date >= weekStart &&
            date <= dayjs(weekStart).add(6, "day").format("YYYY-MM-DD")
        ) {
            if (shifts.includes(staffId)) daysAssigned++;
        }
    }
    if (daysAssigned >= 6) {
        isValidating.value = false;
        alert(
            "This staff is already assigned to 6 days in this week. Please pick another staff.",
        );
        return;
    }

    // If all checks pass, proceed to save
    try {
        const response = await axios.post("/schedule/assign", {
            employee_id: staffId,
            shift_type: shiftApiNames[selectedShiftIdx.value],
            day: dayKey,
        });

        // Use the returned assignments immediately
        if (response.data.assignments) {
            assignments.value = response.data.assignments;
        }

        isValidating.value = false;
        closeDayModal();
    } catch (error) {
        isValidating.value = false;
        console.error("Assignment error:", error);
        if (error.response && error.response.status === 422) {
            alert(error.response.data.error || "Validation failed");
        } else {
            alert("An error occurred while assigning staff. Please try again.");
        }
    } finally {
        isValidating.value = false;
    }
}

// Navigate to previous week
function prevWeek() {
    currentMonday.value = currentMonday.value.subtract(1, "week");
}

// Navigate to next week
function nextWeek() {
    currentMonday.value = currentMonday.value.add(1, "week");
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
    await axios.post("/schedule/submit-week", {
        week_start: currentMonday.value.format("YYYY-MM-DD"),
    });
    await fetchAssignments();
    alert("Weekly schedule submitted successfully!");
    // TEMP DISABLED: Do not move to next week automatically after submit
    // currentMonday.value = currentMonday.value.add(7, 'day');
}

// Get staff name for a shift
function getStaffName(day, shiftIdx) {
    const staffId = assignments.value[day.format("YYYY-MM-DD")]?.[shiftIdx];
    const staff = props.staffList.find((s) => s.id == staffId);
    return staff ? staff.name : "";
}

// Check if a shift is assigned
function isShiftAssigned(day, shiftIdx) {
    return !!assignments.value[day.format("YYYY-MM-DD")]?.[shiftIdx];
}

// Reset all assignments for the current week
async function resetAssignments() {
    if (
        !confirm(
            "Are you sure you want to reset all assignments for this week?",
        )
    )
        return;
    // Optionally, call backend to delete all assignments for this week
    await axios.post("/schedule/reset", {
        week_start: currentMonday.value.format("YYYY-MM-DD"),
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
                    <!-- Supervisor Schedule Note - Inside Card -->
                    <div class="mb-4 px-2">
                        <div
                            class="flex items-center gap-3 bg-gradient-to-r from-amber-500/10 to-orange-500/5 border border-amber-500/20 rounded-xl px-4 py-3"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500/20 flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-amber-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="text-sm">
                                <span class="text-amber-300 font-medium"
                                    >Your Schedule:</span
                                >
                                <span class="text-gray-300 ml-1"
                                    >Mon – Fri, 9:00 AM – 5:00 PM</span
                                >
                                <span class="text-gray-500 ml-1"
                                    >• Excluded from shift assignments</span
                                >
                            </div>
                        </div>
                    </div>
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
                                        v-for="shiftIdx in [0, 1]"
                                        :key="shiftIdx"
                                        class="py-2 px-2 align-middle"
                                    >
                                        <div
                                            :class="[
                                                'min-w-[180px] h-16 flex items-center justify-center rounded-xl transition-all duration-300 border cursor-pointer relative backdrop-blur-md',
                                                isShiftAssigned(day, shiftIdx)
                                                    ? 'bg-gradient-to-br from-green-500/20 to-emerald-900/40 border-green-500/30 hover:border-green-400/50 hover:shadow-lg hover:shadow-green-900/20'
                                                    : 'bg-white/5 border-white/5 hover:bg-white/10 hover:border-white/20 text-gray-400',
                                                isSubmitted
                                                    ? 'opacity-50 cursor-not-allowed grayscale'
                                                    : '',
                                            ]"
                                            @click="
                                                isSubmitted
                                                    ? null
                                                    : openDayModal(
                                                          day,
                                                          shiftIdx,
                                                      )
                                            "
                                            :title="
                                                isShiftAssigned(day, shiftIdx)
                                                    ? getStaffName(
                                                          day,
                                                          shiftIdx,
                                                      )
                                                    : 'Click to assign staff'
                                            "
                                        >
                                            <template
                                                v-if="
                                                    isShiftAssigned(
                                                        day,
                                                        shiftIdx,
                                                    )
                                                "
                                            >
                                                <div
                                                    class="flex items-center justify-center w-full h-full px-3"
                                                >
                                                    <span
                                                        class="text-white font-semibold text-xs text-center line-clamp-2 leading-tight break-words max-w-full"
                                                    >
                                                        {{
                                                            getStaffName(
                                                                day,
                                                                shiftIdx,
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div
                                                    class="flex flex-col items-center justify-center w-full h-full"
                                                >
                                                    <span
                                                        class="text-2xl text-white/20 font-light mb-0 leading-none"
                                                        >+</span
                                                    >
                                                    <span
                                                        class="text-[10px] uppercase tracking-wider text-white/40 font-bold"
                                                        >Assign</span
                                                    >
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div
                            class="flex justify-end mt-8 border-t border-white/10 pt-6"
                        >
                            <button
                                @click="resetAssignments"
                                :disabled="isSubmitted"
                                class="px-6 py-2 rounded-full font-bold text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all disabled:opacity-50"
                            >
                                Reset Week
                            </button>
                        </div>
                    </div>
                </Card>
                <!-- Modal -->
                <transition name="fade">
                    <div
                        v-if="
                            selectedDay !== null &&
                            selectedShiftIdx !== null &&
                            !isSubmitted
                        "
                        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50"
                        @click.self="closeDayModal"
                    >
                        <div
                            class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-2xl shadow-2xl w-full max-w-sm relative animate-fadeIn border border-white/10 overflow-hidden"
                        >
                            <!-- Header -->
                            <div
                                class="px-6 pt-6 pb-4 border-b border-white/5 bg-white/[0.02]"
                            >
                                <button
                                    @click="closeDayModal"
                                    class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white transition-all"
                                    :disabled="isValidating"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        ></path>
                                    </svg>
                                </button>
                                <div class="flex items-center gap-3 mb-1">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-5 h-5 text-red-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2
                                            class="text-lg font-bold text-white"
                                        >
                                            {{
                                                shiftNames[selectedShiftIdx]
                                            }}
                                            Shift
                                        </h2>
                                        <p class="text-xs text-gray-400">
                                            {{
                                                selectedDay.format(
                                                    "dddd, MMMM D, YYYY",
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="p-6">
                                <div
                                    v-if="isValidating"
                                    class="flex flex-col items-center justify-center py-8"
                                >
                                    <svg
                                        class="animate-spin h-8 w-8 text-red-500 mb-3"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                        ></path>
                                    </svg>
                                    <span
                                        class="text-gray-400 font-medium text-sm"
                                        >Validating...</span
                                    >
                                </div>
                                <div v-else>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2"
                                    >
                                        Select Staff Member
                                    </label>
                                    <div class="relative">
                                        <select
                                            v-model="selectedStaffId"
                                            class="w-full bg-white/5 border border-white/10 text-gray-100 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-3 pr-10 appearance-none transition-all"
                                            :disabled="isValidating"
                                            autofocus
                                        >
                                            <option
                                                value=""
                                                class="bg-gray-900"
                                            >
                                                Choose staff...
                                            </option>
                                            <option
                                                v-for="staff in props.staffList"
                                                :key="staff.id"
                                                :value="staff.id"
                                                class="bg-gray-900"
                                            >
                                                {{ staff.name }}
                                            </option>
                                        </select>
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 9l-7 7-7-7"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 pb-6 flex gap-3">
                                <button
                                    @click="closeDayModal"
                                    :disabled="isValidating"
                                    class="flex-1 px-4 py-3 rounded-xl font-semibold text-sm text-gray-300 bg-white/5 border border-white/10 hover:bg-white/10 hover:text-white transition-all disabled:opacity-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="assignStaff"
                                    :disabled="isValidating || !selectedStaffId"
                                    class="flex-1 px-4 py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 shadow-lg shadow-red-900/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Assign Staff
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Reassignment Alert Modal -->
                <transition name="fade">
                    <div
                        v-if="showReassignModal && reassignAlert"
                        class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[60]"
                    >
                        <div
                            class="bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md border border-red-500/30 overflow-hidden animate-fadeIn"
                        >
                            <div class="p-6 text-center">
                                <div
                                    class="w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4 animate-bounce"
                                >
                                    <svg
                                        class="w-8 h-8 text-red-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        ></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white mb-2">
                                    Schedule Conflict Resolved
                                </h3>
                                <div
                                    class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6"
                                >
                                    <p class="text-gray-300 text-sm mb-1">
                                        Emergency Leave for
                                        <span class="text-white font-bold">{{
                                            reassignAlert.name
                                        }}</span>
                                        was approved.
                                    </p>
                                    <p
                                        class="text-xs text-red-400 font-mono mt-2"
                                    >
                                        CLEARED SHIFTS:
                                        {{ reassignAlert.dates }}
                                    </p>
                                </div>
                                <p class="text-gray-400 text-sm mb-6">
                                    The conflicting shifts have been removed.
                                    Please check the schedule and reassign these
                                    slots if necessary.
                                </p>
                                <button
                                    @click="closeReassignModal"
                                    class="w-full px-4 py-3 rounded-xl font-bold text-sm text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-900/30 transition-all"
                                >
                                    Understood, I'll Reassign
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>
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
.animate-fadeIn {
    animation: fadeIn 0.3s;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.96);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
