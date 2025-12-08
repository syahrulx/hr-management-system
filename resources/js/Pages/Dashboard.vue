<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import {computed, onMounted, ref, watch} from 'vue';
import {daysUntilNthDayOfMonth} from "@/Composables/daysUntilNthDayOfMonthCalculator.js";
import {daysBetweenNthDates} from "@/Composables/daysBetweenNthDatesCalculator.js";
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
import {__} from "@/Composables/useTranslations.js";
import {CallQuoteAPI} from "@/Composables/useCallQuoteAPI.js";

const props = defineProps({
    employee_stats: Object,
    attendance_status: Number,
    is_today_off: Boolean,
    is_owner: Boolean,
    owner_stats: Object,
});

const today = (new Date()).toLocaleDateString(usePage().props.locale,
    { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' });

const form = useForm({});

const msg = computed(() => {
    return (props.attendance_status === 0) ? __('Clock in') : __('Clock out')
})

let isSignIn = props.attendance_status === 0;
watch(() => props.attendance_status,
    () => {
        isSignIn = (props.attendance_status === 0);
    }
)
const submit = () => {
    const postRoute = isSignIn ? 'attendance.dashboardSignIn' : 'attendance.dashboardSignOff';

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            cancelButton: 'mx-4 text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900',
            confirmButton: 'text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: __('Confirm :signType for attendance for :today?', {
            signType: isSignIn ? __('Sign in') : __('Sign off'),
            today: today
        }),

        html: isSignIn.value ? "<b>" + __('Notes') + "</b><br>" +
            __('1. Attendance for non-remote employees can only be taken from inside the organization.') + "<br>" +
            __('2. You need to register sign-off here again before leaving, otherwise, your attendance will not be accounted.')
            : '',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: __('Confirm'),
        cancelButtonText: __('Cancel'),
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.post(route(postRoute, {id: usePage().props.auth.user.id}), {
                preserveScroll: true,
                onError: () => {
                    if (usePage().props.errors.ip_error) {
                        Swal.fire(__('Attendance Error'), usePage().props.errors.ip_error, 'error')
                    } else if (usePage().props.errors.no_ip) {
                        Swal.fire(__('IP Error'), usePage().props.errors.no_ip, 'error')
                    } else if (usePage().props.errors.authorization_error) {
                        Swal.fire(__('Authorization Error'), usePage().props.errors.authorization_error, 'error')
                    } else if (usePage().props.errors.day_off) {
                        Swal.fire(__('Today is OFF!'), usePage().props.errors.day_off, 'error')
                    } else {
                        Swal.fire(__('Error'), __('Something went wrong.') + '</br>' + __('Please contact your administrator of this error'), 'error')
                    }
                },
                onSuccess: () => {
                    Swal.fire(__('Action Registerd'),
                        isSignIn ? __('Don\'t forget to come here and sign-off before you leave so that the attendance gets registered!') : '',
                        'success')
                }
            });
        }
    })
};

const quote = ref(null);
onMounted(() => {
    CallQuoteAPI(quote);
});

</script>

<template>
    <Head :title="__('Dashboard')"/>
    <AuthenticatedLayout>
        <template #tabs>
            <GoBackNavLink/>
            <NavLink :href="route('dashboard.index')" :active="route().current('dashboard.index')">
                {{ __('Dashboard') }}
            </NavLink>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between gap-4 ">
                    <Card :class="['!mt-0', is_owner ? 'w-full' : 'w-full md:w-3/4']">
                        <h1 class="!card-header !mb-0">
                            {{ __('Welcome, :name', {name: $page.props.auth.user.name}) }}!</h1>
                    </Card>
                    <Card v-if="!is_owner" class="w-full md:w-1/4 !mt-0" vl :fancy-p="false">
                        <form @submit.prevent="submit" class="w-full h-full"
                              v-if="attendance_status !== 2 && !is_today_off">
                            <PrimaryButton class="w-full h-full flex justify-center">
                            <span class="text-xl">{{ __('Attendance :msg', {msg: msg}) }}
                                <br>
                                <span class="text-xs text-gray-200">{{ __('For') }}  {{ today }}</span>
                            </span>
                            </PrimaryButton>
                        </form>
                        <PrimaryButton v-else
                                       class="w-full h-full flex justify-center !border-0 bg-red-600 cursor-not-allowed"
                                       disabled>
                            <span v-if="is_today_off" class="text-sm">{{ __('Today is off! 🕺🕺') }}<br></span>
                            <span v-else class="text-sm">{{ __('Attendance Taken Today! 🎉') }}<br></span>
                        </PrimaryButton>
                    </Card>
                </div>

                <!-- OWNER OVERVIEW directly below welcome -->
                <div v-if="is_owner" class="mt-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- 1: Red -->
                        <Card class="w-full !mt-0 bg-gradient-to-r from-red-500 to-red-800">
                            <h2 class="text-sm text-white">{{ __('Total Staff') }}</h2>
                            <p class="text-3xl font-bold mt-2 text-white">{{ owner_stats?.staffCount ?? 0 }}</p>
                        </Card>
                        <!-- 2: Default -->
                        <Card class="w-full !mt-0">
                            <h2 class="text-sm text-gray-300">{{ __('Present Today') }}</h2>
                            <p class="text-3xl font-bold mt-2">{{ owner_stats?.presentToday ?? 0 }}</p>
                        </Card>
                        <!-- 3: Red -->
                        <Card class="w-full !mt-0 bg-gradient-to-r from-red-500 to-red-800">
                            <h2 class="text-sm text-white">{{ __('Late Today') }}</h2>
                            <p class="text-3xl font-bold mt-2 text-white">{{ owner_stats?.lateToday ?? 0 }}</p>
                        </Card>
                        <!-- 4: Default -->
                        <Card class="w-full !mt-0">
                            <h2 class="text-sm text-gray-300">{{ __('Absent Today') }}</h2>
                            <p class="text-3xl font-bold mt-2">{{ owner_stats?.absentToday ?? 0 }}</p>
                        </Card>
                        <!-- 5: Red -->
                        <Card class="w-full !mt-0 bg-gradient-to-r from-red-500 to-red-800">
                            <h2 class="text-sm text-white">{{ __('Pending Requests') }}</h2>
                            <p class="text-3xl font-bold mt-2 text-white">{{ owner_stats?.pendingRequests ?? 0 }}</p>
                        </Card>
                    </div>
                </div>

                <!-- QUOTE -->
                <div class="flex flex-col md:flex-row justify-between md:gap-4">
                    <Card class="w-full">
                        <h1 class="text-2xl">{{ __('Quote of the day') }}</h1>
                        <div class="flex justify items-center">
                            <BlockQuote class="mb-0" v-if="quote" :style="2" :quote="quote['content']"
                                        :author="quote['author']"/>
                            <BlockQuote class="mb-0" v-else :style="2" :quote="__('Success is not the key to happiness. Happiness is the key to success. If you love what you are doing, you will be successful.')"
                                        :author="__('Albert Scweitzer')"/>
                        </div>
                    </Card>

                    <Card v-if="!is_owner" class="w-full md:w-1/4 " vl>
                        <h1 class="text-2xl text-center font-semibold mb-4">{{ __('Salary Info') }}</h1>
                        <div class="space-y-4">
                            <p class="text-xl text-center">MYR <span> {{ __('VALUE NEED FROM UNEXIST DB') }}</span></p>
                            <HorizontalRule class="!bg-neutral-300"/>
                            <p class="text-center text-gray-700 text-sm">
                                {{ __('Last Updated: VALUE NEED FROM UNEXIST DB') }}</p>
                        </div>
                    </Card>
                </div>

                <!-- MONTH DATA -->
                <div class="flex flex-col md:flex-row justify-between md:gap-4">

                    <!-- MONTH DATA -->
                    <Card v-if="!is_owner" class="w-full md:w-1/4">
                        <h1 class="text-2xl">{{
                                __('Data of :month', {month: new Date().toLocaleString($page.props.locale, {month: 'long'})})
                            }}</h1>
                        <div class="mt-4 w-full flex flex-col">
                            <div class="flex justify-between align-middle mb-6 sm:mb-2">
                                <p class="font-medium">{{ __('Work Days') }}: </p>
                                <p>{{ employee_stats['attendableThisMonth'] }} {{ __('Days') }}</p>
                            </div>
                            <div class="flex justify-between align-middle mb-6 sm:mb-2">
                                <p class="font-medium">{{ __('Weekends') }}: </p>
                                <p>{{ employee_stats['weekendsThisMonth'] }} {{ __('Days') }}</p>
                            </div>
                            <div class="flex justify-between align-middle mb-6 sm:mb-2">
                                <p class="font-medium">{{ __('Holidays') }}: </p>
                                <p>{{ employee_stats['holidaysThisMonth'] }} {{ __('Days') }}</p>
                            </div>

                        </div>
                    </Card>

                    <Card v-if="!is_owner" class="w-full md:w-2/4  ">
                        <h1 class="text-2xl">{{ __('Your Attendance This Month') }}</h1>
                        <div class="mt-4 grid grid-rows-3">
                            <div class="flex flex-col lg:flex-row justify-between align-middle mb-6 sm:mb-2">
                                <p class="w-full sm:w-1/3">{{ __('Attended') + ' ' + employee_stats['totalAttendanceSoFar']}}</p>
                                <ProgressBar class="col-span-3"  color="bg-green-500" no-text
                                             :percentage="employee_stats['totalAttendanceSoFar'] / employee_stats['attendableThisMonth'] * 100"
                                             :text="employee_stats['totalAbsenceSoFar'] + (employee_stats['totalAbsenceSoFar'] > 0 ? __('Day(s)') : '')"/>

                            </div>


                            <div class="flex flex-col lg:flex-row justify-between align-middle mb-6 sm:mb-2">
                                <p class="w-full sm:w-1/3">{{ __('Absented:') }} {{ employee_stats['absentedThisMonth'] }}</p>
                                <ProgressBar no-text  color="bg-red-500"
                                             :percentage="employee_stats['totalAbsenceSoFar'] / employee_stats['YearStats']['absence_limit'] * 100"
                                             :text="employee_stats['totalAbsenceSoFar'] + (employee_stats['totalAbsenceSoFar'] > 0 ? __('Day(s)') : '')"/>

                            </div>
                            <div class="flex flex-col lg:flex-row justify-between align-middle mb-6 sm:mb-2">
                                <p class="w-full sm:w-1/3">{{ __('Hours:') }}
                                    <ToolTip direction="bottom">
                                        {{ __('Number of Overtime/Undertime Hours (so far).') }}<br/>
                                        {{ __('This value will be accounted for in the payroll, resulting in a reward or a penalty.') }}
                                    </ToolTip>
                                    {{ employee_stats['hoursDifferenceSoFar'].toFixed(0) + __('h') }}</p>
                                <ProgressBar class="col-span-3"
                                             :percentage="employee_stats['hoursDifferenceSoFar']"
                                             :text="employee_stats['hoursDifferenceSoFar'] === 0 ? '' :Math.abs(employee_stats['hoursDifferenceSoFar']).toFixed(2) + ' ' + __('Hours') +' ' + (employee_stats['hoursDifferenceSoFar'] > 0 ? __('extra') : __('late'))"
                                             :color="employee_stats['hoursDifferenceSoFar'] > 0 ? 'bg-green-500' : 'bg-red-500' "/>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Owner overview moved above -->
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
