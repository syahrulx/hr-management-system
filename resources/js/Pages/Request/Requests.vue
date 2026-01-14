<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import FlexButton from "@/Components/FlexButton.vue";
import ReqTabs from "@/Components/Tabs/ReqTabs.vue";
import Card from "@/Components/Card.vue";
import {
    PlusIcon,
    CalendarDaysIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ChartBarIcon,
    ArrowRightIcon,
} from "@heroicons/vue/24/outline";
import { __ } from "@/Composables/useTranslations.js";
import { request_status_types } from "@/Composables/useRequestStatusTypes.js";

const props = defineProps({
    requests: Object,
    leaveBalances: Array,
    leaveTotals: Object,
});

const getStatusClass = (status) => {
    switch (status) {
        case "Pending":
            return "text-amber-400 bg-amber-400/10 border-amber-400/20";
        case "Approved":
            return "text-emerald-400 bg-emerald-400/10 border-emerald-400/20";
        case "Rejected":
            return "text-rose-400 bg-rose-400/10 border-rose-400/20";
        default:
            return "text-gray-400 bg-gray-400/10 border-gray-400/20";
    }
};
</script>

<template>
    <Head :title="__('Requests')" />
    <AuthenticatedLayout>
        <template #tabs>
            <ReqTabs />
        </template>

        <div
            class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in-up"
        >
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- SIDEBAR: LEAVE BALANCES -->
                <aside class="lg:col-span-3 space-y-6">
                    <Card
                        variant="glass"
                        class="!mt-0 relative overflow-hidden group"
                    >
                        <div
                            class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity"
                        >
                            <ChartBarIcon class="w-32 h-32 text-white" />
                        </div>

                        <div
                            v-if="
                                ['admin', 'owner'].includes(
                                    $page.props.auth.user.role
                                )
                            "
                        >
                            <h2
                                class="text-lg font-bold text-white mb-6 flex items-center gap-2"
                            >
                                <CheckCircleIcon
                                    class="w-5 h-5 text-emerald-500"
                                />
                                {{ __("Total Approved") }}
                            </h2>
                            <div class="space-y-5">
                                <div
                                    v-for="type in [
                                        'Annual Leave',
                                        'Emergency Leave',
                                        'Sick Leave',
                                    ]"
                                    :key="type"
                                    class="group/item"
                                >
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-300"
                                            >{{ __(type) }}</span
                                        >
                                        <span
                                            class="text-sm font-bold text-white bg-red-500/10 px-2 py-0.5 rounded border border-red-500/20"
                                        >
                                            {{
                                                leaveTotals && leaveTotals[type]
                                                    ? leaveTotals[type]
                                                    : 0
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden"
                                    >
                                        <div
                                            class="h-full bg-red-500/40 rounded-full group-hover/item:bg-red-500/60 transition-all"
                                            :style="{
                                                width: `${Math.min(
                                                    (leaveTotals &&
                                                    leaveTotals[type]
                                                        ? leaveTotals[type]
                                                        : 0) * 5,
                                                    100
                                                )}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else>
                            <h2
                                class="text-lg font-bold text-white mb-6 flex items-center gap-2"
                            >
                                <CalendarDaysIcon
                                    class="w-5 h-5 text-red-500"
                                />
                                {{ __("Leave Balances") }}
                            </h2>
                            <div class="space-y-6">
                                <div
                                    v-for="type in [
                                        'Annual Leave',
                                        'Emergency Leave',
                                        'Sick Leave',
                                    ]"
                                    :key="type"
                                    class="group/item"
                                >
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-white"
                                                >{{ __(type) }}</span
                                            >
                                            <span
                                                class="text-[10px] uppercase tracking-wider text-gray-500"
                                                >{{
                                                    __("Remaining Days")
                                                }}</span
                                            >
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="text-lg font-black text-red-500"
                                            >
                                                {{
                                                    leaveBalances &&
                                                    leaveBalances.find(
                                                        (l) =>
                                                            l.leave_type ===
                                                            type
                                                    )
                                                        ? leaveBalances.find(
                                                              (l) =>
                                                                  l.leave_type ===
                                                                  type
                                                          ).balance
                                                        : 0
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <div
                                        class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden"
                                    >
                                        <div
                                            class="h-full bg-gradient-to-r from-red-600 to-red-400 rounded-full group-hover/item:opacity-80 transition-all shadow-[0_0_10px_rgba(239,68,68,0.3)]"
                                            :style="{
                                                width: `${Math.min(
                                                    ((leaveBalances &&
                                                        leaveBalances.find(
                                                            (l) =>
                                                                l.leave_type ===
                                                                type
                                                        )?.balance) ||
                                                        0) * 7,
                                                    100
                                                )}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- QUICK GUIDE / TIPS -->
                    <div
                        class="p-6 rounded-2xl bg-gradient-to-br from-red-600/10 to-transparent border border-red-500/20 backdrop-blur-sm"
                    >
                        <h4
                            class="text-white font-bold mb-2 flex items-center gap-2 uppercase tracking-tighter text-sm"
                        >
                            <ClockIcon class="w-4 h-4 text-red-500" />
                            {{ __("Processing Time") }}
                        </h4>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            {{
                                __(
                                    "Leave requests are typically reviewed within 24-48 hours by your supervisor."
                                )
                            }}
                        </p>
                    </div>
                </aside>

                <!-- MAIN CONTENT: TABLE -->
                <main class="lg:col-span-9">
                    <Card variant="glass" class="!mt-0 !p-0 overflow-hidden">
                        <div
                            class="p-8 border-b border-white/5 flex flex-col md:flex-row md:justify-between md:items-center gap-6 bg-white/[0.02]"
                        >
                            <div>
                                <h1
                                    class="text-3xl font-black text-white tracking-tight flex items-center gap-3"
                                >
                                    {{ __("Leave Requests") }}
                                    <span
                                        class="text-sm font-medium text-gray-500 bg-white/5 px-2.5 py-1 rounded-lg border border-white/10 uppercase tracking-widest"
                                    >
                                        {{ requests.data.length }}
                                        {{ __("Total") }}
                                    </span>
                                </h1>
                                <p class="text-gray-400 text-sm mt-1">
                                    {{
                                        __(
                                            "Manage and track your time-off applications."
                                        )
                                    }}
                                </p>
                            </div>

                            <FlexButton
                                v-if="
                                    !['owner'].includes(
                                        $page.props.auth.user.role
                                    )
                                "
                                :href="route('requests.create')"
                                :class="'!bg-red-600 hover:!bg-red-700 !rounded-xl !px-6 !py-3 !text-white flex items-center gap-2 transition-all shadow-xl hover:shadow-red-500/20 group'"
                            >
                                <PlusIcon
                                    class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"
                                />
                                <span class="font-bold">{{
                                    __("New Request")
                                }}</span>
                            </FlexButton>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-black/20">
                                        <th
                                            class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5"
                                        >
                                            {{ __("ID") }}
                                        </th>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5"
                                        >
                                            {{ __("Employee / Type") }}
                                        </th>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5 text-center"
                                        >
                                            {{ __("Duration") }}
                                        </th>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5 text-center"
                                        >
                                            {{ __("Status") }}
                                        </th>
                                        <th
                                            v-if="
                                                !['admin', 'owner'].includes(
                                                    $page.props.auth.user.role
                                                )
                                            "
                                            class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5"
                                        >
                                            {{ __("Reason") }}
                                        </th>
                                        <th
                                            v-else
                                            class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5 text-right"
                                        >
                                            {{ __("Action") }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr
                                        v-for="(request, idx) in requests.data"
                                        :key="request.id"
                                        class="group hover:bg-white/[0.02] transition-colors"
                                    >
                                        <td
                                            class="px-6 py-5 font-mono text-sm font-bold text-red-500/80"
                                        >
                                            #{{
                                                request.id
                                                    .toString()
                                                    .padStart(4, "0")
                                            }}
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-white font-bold group-hover:text-red-400 transition-colors"
                                                    >{{
                                                        request.employee_name
                                                    }}</span
                                                >
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >{{ request.type }}</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div
                                                class="flex items-center justify-center gap-3 text-sm"
                                            >
                                                <div class="text-center">
                                                    <p
                                                        class="text-white font-medium"
                                                    >
                                                        {{ request.start_date }}
                                                    </p>
                                                    <p
                                                        class="text-[10px] text-gray-500 uppercase"
                                                    >
                                                        {{ __("Start") }}
                                                    </p>
                                                </div>
                                                <ArrowRightIcon
                                                    class="w-3 h-3 text-red-500/40"
                                                />
                                                <div class="text-center">
                                                    <p
                                                        class="text-white font-medium"
                                                    >
                                                        {{
                                                            request.end_date ??
                                                            __("N/A")
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-[10px] text-gray-500 uppercase"
                                                    >
                                                        {{ __("End") }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span
                                                :class="
                                                    getStatusClass(
                                                        request.status
                                                    )
                                                "
                                                class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm"
                                            >
                                                {{
                                                    request_status_types[
                                                        request.status.toLowerCase()
                                                    ] || request.status
                                                }}
                                            </span>
                                        </td>
                                        <!-- Reason Column (Employee View) -->
                                        <td
                                            v-if="
                                                !['admin', 'owner'].includes(
                                                    $page.props.auth.user.role
                                                )
                                            "
                                            class="px-6 py-5 max-w-[200px]"
                                        >
                                            <span
                                                class="text-sm text-gray-400 line-clamp-2"
                                                >{{
                                                    request.remark ||
                                                    __("No reason provided")
                                                }}</span
                                            >
                                        </td>
                                        <!-- Action Column (Admin/Owner View) -->
                                        <td v-else class="px-6 py-5 text-right">
                                            <a
                                                :href="
                                                    route('requests.show', {
                                                        request: request.id,
                                                    })
                                                "
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-white bg-white/5 hover:bg-red-600 px-4 py-2 rounded-lg border border-white/10 transition-all shadow-sm"
                                            >
                                                {{ __("View Details") }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr v-if="requests.data.length === 0">
                                        <td
                                            colspan="5"
                                            class="px-6 py-20 text-center"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-4 opacity-20"
                                            >
                                                <CalendarDaysIcon
                                                    class="w-16 h-16 text-white"
                                                />
                                                <p
                                                    class="text-xl font-bold text-white"
                                                >
                                                    {{
                                                        __("No requests found")
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
