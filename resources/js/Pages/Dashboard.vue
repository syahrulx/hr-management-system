<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { daysUntilNthDayOfMonth } from "@/Composables/daysUntilNthDayOfMonthCalculator.js";
import { daysBetweenNthDates } from "@/Composables/daysBetweenNthDatesCalculator.js";
import NavLink from "@/Components/NavLink.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";
import Card from "@/Components/Card.vue";
import BlockQuote from "@/Components/BlockQuote.vue";
import IconCard from "@/Components/IconCard.vue";
import ProgressBar from "@/Components/ProgressBar.vue";
import ToolTip from "@/Components/ToolTip.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
// TextInput not needed for compact UI
import Swal from "sweetalert2";
import HorizontalRule from "@/Components/HorizontalRule.vue";
import MoneyIcon from "@/Components/Icons/MoneyIcon.vue";
import CalendarIcon from "@/Components/Icons/CalendarIcon.vue";
import TableIcon from "@/Components/Icons/TableIcon.vue";
import MessageIcon from "@/Components/Icons/MessageIcon.vue";
import { ClipboardDocumentListIcon, BriefcaseIcon, HeartIcon, ChartBarIcon } from "@heroicons/vue/24/outline";
import { __ } from "@/Composables/useTranslations.js";
import { useToast } from "vue-toastification";
import { CallQuoteAPI } from "@/Composables/useCallQuoteAPI.js";

const props = defineProps({
    employee_stats: Object,
    attendance_status: Number,
    is_today_off: Boolean,
    is_owner: Boolean,
    owner_stats: Object,
    sign_in_time: String,
    leaveBalances: Array,
    sign_off_time: String,
    has_schedule_today: Boolean,
});

const toast = useToast();
const today = new Date().toLocaleDateString("en-GB", {
    weekday: "short",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
});

const form = useForm({});

const msg = computed(() => {
    return props.attendance_status === 0 ? __("Clock in") : __("Clock out");
});

let isSignIn = props.attendance_status === 0;
watch(
    () => props.attendance_status,
    () => {
        isSignIn = props.attendance_status === 0;
    }
);
const submit = () => {
    const postRoute = isSignIn
        ? "attendance.dashboardSignIn"
        : "attendance.dashboardSignOff";

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            cancelButton:
                "mx-4 text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900",
            confirmButton:
                "text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900",
        },
        buttonsStyling: false,
    });
    swalWithBootstrapButtons
        .fire({
            title: __("Confirm :signType for attendance for :today?", {
                signType: isSignIn ? __("Sign in") : __("Sign off"),
                today: today,
            }),

            html: isSignIn.value
                ? "<b>" +
                  __("Notes") +
                  "</b><br>" +
                  __(
                      "1. Attendance for non-remote employees can only be taken from inside the organization."
                  ) +
                  "<br>" +
                  __(
                      "2. You need to register sign-off here again before leaving, otherwise, your attendance will not be accounted."
                  )
                : "",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: __("Confirm"),
            cancelButtonText: __("Cancel"),
            reverseButtons: true,
        })
        .then((result) => {
            if (result.isConfirmed) {
                form.post(route(postRoute), {
                    preserveScroll: true,
                    onError: () => {
                        if (usePage().props.errors.ip_error) {
                            Swal.fire(
                                __("Attendance Error"),
                                usePage().props.errors.ip_error,
                                "error"
                            );
                        } else if (usePage().props.errors.no_ip) {
                            Swal.fire(
                                __("IP Error"),
                                usePage().props.errors.no_ip,
                                "error"
                            );
                        } else if (usePage().props.errors.authorization_error) {
                            Swal.fire(
                                __("Authorization Error"),
                                usePage().props.errors.authorization_error,
                                "error"
                            );
                        } else if (usePage().props.errors.day_off) {
                            Swal.fire(
                                __("Today is OFF!"),
                                usePage().props.errors.day_off,
                                "error"
                            );
                        } else if (usePage().props.errors.schedule_error) {
                            Swal.fire(
                                __("Schedule Error"),
                                usePage().props.errors.schedule_error,
                                "error"
                            );
                        } else {
                            Swal.fire(
                                __("Error"),
                                __("Something went wrong.") +
                                    "</br>" +
                                    __(
                                        "Please contact your administrator of this error"
                                    ),
                                "error"
                            );
                        }
                    },
                    onSuccess: () => {
                        Swal.fire(
                            __("Action Registerd"),
                            isSignIn
                                ? __(
                                      "Don't forget to come here and sign-off before you leave so that the attendance gets registered!"
                                  )
                                : "",
                            "success"
                        );
                    },
                });
            }
        });
};

const quote = ref(null);

const formatTime = (time) => {
    if (!time) return "";
    const [hours, minutes] = time.split(":");
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
};

// Live clock showing current time
const currentTime = ref("");
let timerInterval = null;

const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

onMounted(() => {
    CallQuoteAPI(quote);
    updateClock();
    timerInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head :title="__('Dashboard')" />
    <AuthenticatedLayout>
        <template #tabs>
            <GoBackNavLink />
            <NavLink
                :href="route('dashboard.index')"
                :active="route().current('dashboard.index')"
            >
                {{ __("Dashboard") }}
            </NavLink>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 animate-fade-in-up">
            <!-- BENTO GRID CONTAINER -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 auto-rows-min">
                <!-- 1. HERO SECTION (Spans 3 Columns for Staff, 4 for Owner) -->
                <Card
                    variant="glass"
                    class="!mt-0 relative overflow-hidden group"
                    :class="
                        is_owner
                            ? 'lg:col-span-4 min-h-[250px] flex flex-col justify-center'
                            : 'lg:col-span-3'
                    "
                >
                    <!-- Decorative Background Elements -->
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-red-600/20 to-transparent rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"
                    ></div>

                    <div
                        class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center h-full"
                    >
                        <div>
                            <h2
                                class="text-sm font-medium text-red-400 uppercase tracking-wider mb-1"
                            >
                                {{ today }}
                            </h2>
                            <h1 class="text-4xl font-bold text-white mb-2">
                                {{ __("Welcome back,") }}
                                <span
                                    class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-red-200"
                                    >{{ $page.props.auth.user.name }}</span
                                >!
                            </h1>
                            <p
                                class="text-gray-400 max-w-xl text-sm leading-relaxed"
                                v-if="quote"
                            >
                                "{{ quote["content"] }}" —
                                <span class="text-red-400">{{
                                    quote["author"]
                                }}</span>
                            </p>
                            <p
                                class="text-gray-400 max-w-xl text-sm leading-relaxed"
                                v-else
                            >
                                "{{
                                    __(
                                        "Success is not the key to happiness. Happiness is the key to success."
                                    )
                                }}"
                            </p>
                        </div>
                        <!-- Hero Icon/Illustration placeholder -->
                        <div
                            class="hidden md:block opacity-50 group-hover:opacity-100 transition-opacity duration-700"
                        >
                            <RocketIcon class="w-24 h-24 text-red-500/20" />
                        </div>
                    </div>
                </Card>

                <!-- 2. ACTION TOWER (Clock In/Out) - Spans 1 Col, 2 Rows -->
                <Card
                    v-if="!is_owner"
                    variant="glass"
                    class="lg:col-span-1 lg:row-span-2 !mt-0 flex flex-col justify-between relative overflow-hidden h-full min-h-[300px]"
                    :noPadding="true"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-b from-red-900/10 to-transparent pointer-events-none"
                    ></div>

                    <div
                        class="p-6 flex-1 flex flex-col justify-center items-center text-center relative z-10"
                    >
                        <div
                            class="w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(220,38,38,0.3)] transition-all duration-500"
                            :class="
                                attendance_status === 0
                                    ? 'bg-red-500/20 text-red-400 border border-red-500/50'
                                    : attendance_status === 1
                                    ? 'bg-green-500/20 text-green-400 border border-green-500/50'
                                    : 'bg-amber-500/20 text-amber-400 border border-amber-500/50'
                            "
                        >
                            <CalendarIcon class="w-10 h-10" />
                        </div>

                        <h3 class="text-xl font-bold text-white mb-2">
                            {{
                                !has_schedule_today && attendance_status === 0
                                    ? __("Day Off")
                                    : attendance_status === 0
                                    ? __("Not Signed In")
                                    : attendance_status === 1
                                    ? __("Currently Working")
                                    : __("Completed")
                            }}
                        </h3>

                        <!-- Live Clock -->
                        <div class="mb-3">
                            <div
                                class="text-3xl font-mono font-bold tracking-wider px-4 py-2 rounded-lg text-white bg-white/5 border border-white/10"
                            >
                                {{ currentTime }}
                            </div>
                        </div>

                        <!-- Timestamp Display -->
                        <div
                            class="mb-6 space-y-2 text-sm font-medium w-full px-4"
                        >
                            <div
                                v-if="sign_in_time"
                                class="flex items-center justify-center gap-2 text-emerald-400 bg-emerald-400/10 py-1.5 px-3 rounded-lg border border-emerald-400/20 shadow-sm backdrop-blur-sm"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"
                                ></span>
                                <span
                                    >{{ __("In") }}:
                                    {{ formatTime(sign_in_time) }}</span
                                >
                            </div>
                            <div
                                v-if="sign_off_time"
                                class="flex items-center justify-center gap-2 text-amber-400 bg-amber-400/10 py-1.5 px-3 rounded-lg border border-amber-400/20 shadow-sm backdrop-blur-sm"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-amber-400"
                                ></span>
                                <span
                                    >{{ __("Out") }}:
                                    {{ formatTime(sign_off_time) }}</span
                                >
                            </div>
                        </div>

                        <form
                            @submit.prevent="submit"
                            class="w-full"
                            v-if="has_schedule_today || attendance_status !== 0"
                        >
                            <button
                                v-if="attendance_status !== 2"
                                class="w-full py-4 rounded-xl font-bold text-white shadow-lg transition-all duration-300 transform hover:scale-105 active:scale-95 group relative overflow-hidden"
                                :class="
                                    attendance_status === 0
                                        ? 'bg-gradient-to-br from-red-600 to-red-800 shadow-red-900/40'
                                        : 'bg-gradient-to-br from-gray-700 to-gray-900 shadow-black/40 border border-white/10'
                                "
                            >
                                <span
                                    class="relative z-10 group-hover:tracking-widest transition-all duration-300"
                                >
                                    {{
                                        attendance_status === 0
                                            ? __("CLOCK IN")
                                            : __("CLOCK OUT")
                                    }}
                                </span>
                            </button>
                        </form>

                        <div
                            v-else
                            class="w-full py-4 rounded-xl font-bold text-white bg-white/5 border border-white/10 text-center cursor-not-allowed opacity-70"
                        >
                            <span>{{ __("No Shift Today 🛌") }}</span>
                        </div>
                    </div>
                </Card>

                <!-- 3. STATS GRID (Owner vs Employee) -->

                <!-- OWNER STATS -->
                <template v-if="is_owner">
                    <!-- Row 2: Main Stats -->
                    <Card
                        variant="glass"
                        class="lg:col-span-1 !mt-0 h-full flex flex-col justify-center"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-gray-400 text-xs uppercase font-bold tracking-wider"
                                >
                                    {{ __("Total Staff") }}
                                </p>
                                <p class="text-3xl font-bold text-white mt-1">
                                    {{ owner_stats?.staffCount ?? 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-red-500/10 rounded-lg">
                                <UserIcon class="w-6 h-6 text-red-400" />
                            </div>
                        </div>
                    </Card>
                    <Card
                        variant="glass"
                        class="lg:col-span-1 !mt-0 h-full flex flex-col justify-center"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-gray-400 text-xs uppercase font-bold tracking-wider"
                                >
                                    {{ __("Present") }}
                                </p>
                                <p
                                    class="text-3xl font-bold text-emerald-400 mt-1"
                                >
                                    {{ owner_stats?.presentToday ?? 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-emerald-500/20 rounded-lg">
                                <TableIcon class="w-6 h-6 text-emerald-400" />
                            </div>
                        </div>
                    </Card>
                    <Card
                        variant="glass"
                        class="lg:col-span-1 !mt-0 h-full flex flex-col justify-center"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-gray-400 text-xs uppercase font-bold tracking-wider"
                                >
                                    {{ __("Late") }}
                                </p>
                                <p
                                    class="text-3xl font-bold text-amber-400 mt-1"
                                >
                                    {{ owner_stats?.lateToday ?? 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-amber-500/20 rounded-lg">
                                <CalendarIcon class="w-6 h-6 text-amber-400" />
                            </div>
                        </div>
                    </Card>
                    <Card
                        variant="glass"
                        class="lg:col-span-1 !mt-0 h-full flex flex-col justify-center"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-gray-400 text-xs uppercase font-bold tracking-wider"
                                >
                                    {{ __("Leave Requests") }}
                                </p>
                                <p
                                    class="text-3xl font-bold text-purple-400 mt-1"
                                >
                                    {{ owner_stats?.pendingRequests ?? 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-purple-500/20 rounded-lg">
                                <ClipboardDocumentListIcon class="w-6 h-6 text-purple-400" />
                            </div>
                        </div>
                    </Card>
                </template>

                <!-- EMPLOYEE STATS & LEAVES (Combined 2x2 Grid) -->
                <template v-else>
                    <Card
                        variant="glass"
                        class="lg:col-span-3 !mt-0 relative overflow-hidden"
                    >
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-100 tracking-tight">
                                {{ __("My Status") }}
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <!-- 2. LEAVE BALANCES (Annual, Sick, Emergency) -->
                            <div
                                v-for="leave in leaveBalances"
                                :key="leave.leave_type"
                                class="relative group bg-white/5 border border-white/10 rounded-xl p-6 overflow-hidden transition-all duration-300 hover:bg-white/10 hover:border-white/20"
                            >
                                <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-red-500/10 to-transparent rounded-full blur-2xl group-hover:bg-red-500/20 transition-all duration-500"></div>
                                <div class="relative z-10 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                            {{ __(leave.leave_type) }}
                                        </p>
                                        <div class="flex items-end gap-2">
                                            <span class="text-3xl font-bold" :class="leave.balance > 0 ? 'text-white' : 'text-red-400'">
                                                {{ leave.balance }}
                                            </span>
                                            <span class="text-sm text-gray-500 mb-1">/ {{ leave.total }} {{ __("Days") }}</span>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white/5 rounded-lg border border-white/5 group-hover:scale-110 transition-transform duration-300">
                                        <BriefcaseIcon v-if="leave.leave_type === 'Annual Leave'" class="w-6 h-6 text-red-500" />
                                        <HeartIcon v-else-if="leave.leave_type === 'Sick Leave'" class="w-6 h-6 text-red-500" />
                                        <CalendarIcon v-else class="w-6 h-6 text-red-500" />
                                    </div>
                                </div>
                                <div class="w-full bg-gray-700/50 rounded-full h-1.5 mt-4 overflow-hidden">
                                    <div
                                        class="h-1.5 rounded-full bg-red-500 transition-all duration-1000 ease-out"
                                        :style="{ width: Math.min((leave.balance / leave.total) * 100, 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </Card>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes glowing {
    0% {
        box-shadow: 0 0 5px indigo;
    }
    50% {
        box-shadow: 0 0 10px indigo, 0 0 15px indigo;
    }
    100% {
        box-shadow: 0 0 5px indigo;
    }
}

.glow-element {
    animation: glowing 7s infinite;
}
</style>
