<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import FlexButton from "@/Components/FlexButton.vue";
import ReqTabs from "@/Components/Tabs/ReqTabs.vue";
import Card from "@/Components/Card.vue";
import {
    PlusIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowRightIcon,
    CheckIcon,
    XMarkIcon,
    PaperClipIcon,
    ArrowTopRightOnSquareIcon,
} from "@heroicons/vue/24/outline";
import { __ } from "@/Composables/useTranslations.js";
import { request_status_types } from "@/Composables/useRequestStatusTypes.js";
import { useToast } from "vue-toastification";
import dayjs from "dayjs";

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

// Approve/Reject functions
const approveRequest = (requestId) => {
    const form = useForm({ status: 1 });
    form.put(route('requests.update', { request: requestId }), {
        preserveScroll: true,
        onSuccess: () => {
            useToast().success(__('Request Approved'));
        },
        onError: (errors) => {
            useToast().error(errors.status || __('Error Approving Request'));
        },
    });
};

const rejectRequest = (requestId) => {
    const form = useForm({ status: 2 });
    form.put(route('requests.update', { request: requestId }), {
        preserveScroll: true,
        onSuccess: () => {
            useToast().success(__('Request Rejected'));
        },
        onError: (errors) => {
            useToast().error(errors.status || __('Error Rejecting Request'));
        },
    });
};

// Check if current user can approve/reject this request
const canApprove = (request) => {
    const userRole = usePage().props.auth.user.role;
    const userId = usePage().props.auth.user.id;
    
    // Can't approve own requests
    if (request.user_id === userId) {
        return false;
    }
    
    // Owner can approve admin requests
    if (userRole === 'owner' && request.employee_role === 'admin') {
        return true;
    }
    
    // Admin can only approve employee requests (not other admins)
    if (userRole === 'admin' && request.employee_role === 'employee') {
        return true;
    }
    
    return false;
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
            <!-- MAIN CONTENT: TABLE -->
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
                                __("Manage and track your time-off applications.")
                            }}
                        </p>
                    </div>

                    <FlexButton
                        v-if="!['owner'].includes($page.props.auth.user.role)"
                        :href="route('requests.create')"
                        :class="'!bg-red-600 hover:!bg-red-700 !rounded-xl !px-6 !py-3 !text-white flex items-center gap-2 transition-all shadow-xl hover:shadow-red-500/20 group'"
                    >
                        <PlusIcon
                            class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"
                        />
                        <span class="font-bold">{{ __("New Request") }}</span>
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
                                    class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5 text-center"
                                >
                                    <PaperClipIcon class="w-4 h-4 mx-auto" />
                                </th>
                                <th
                                    v-if="!['admin', 'owner'].includes($page.props.auth.user.role)"
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
                                    #{{ request.id.toString().padStart(4, "0") }}
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-white font-bold group-hover:text-red-400 transition-colors"
                                        >{{ request.employee_name }}</span>
                                        <span class="text-xs text-gray-500">{{ request.type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center justify-center gap-3 text-sm">
                                        <div class="text-center">
                                            <p class="text-white font-medium">
                                                {{ dayjs(request.start_date).format('DD/MM/YYYY') }}
                                            </p>
                                            <p class="text-[10px] text-gray-500 uppercase">
                                                {{ __("Start") }}
                                            </p>
                                        </div>
                                        <ArrowRightIcon class="w-3 h-3 text-red-500/40" />
                                        <div class="text-center">
                                            <p class="text-white font-medium">
                                                {{ request.end_date ? dayjs(request.end_date).format('DD/MM/YYYY') : __("N/A") }}
                                            </p>
                                            <p class="text-[10px] text-gray-500 uppercase">
                                                {{ __("End") }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        :class="getStatusClass(request.status)"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm"
                                    >
                                        {{ request_status_types[request.status.toLowerCase()] || request.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <a
                                        v-if="request.support_doc"
                                        :href="request.support_doc"
                                        target="_blank"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/5 text-gray-400 hover:text-white hover:bg-white/10 transition-colors border border-white/10"
                                        :title="__('View Document')"
                                    >
                                        <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                                    </a>
                                    <span v-else class="text-gray-600">-</span>
                                </td>
                                <!-- Reason Column (Employee View) -->
                                <td
                                    v-if="!['admin', 'owner'].includes($page.props.auth.user.role)"
                                    class="px-6 py-5 max-w-[200px]"
                                >
                                    <span class="text-sm text-gray-400 line-clamp-2">
                                        {{ request.remark || __("No reason provided") }}
                                    </span>
                                </td>
                                <!-- Action Column (Admin/Owner View) -->
                                <td v-else class="px-6 py-5 text-right">
                                    <div v-if="request.status === 'Pending' && canApprove(request)" class="flex items-center justify-end gap-2">
                                        <button
                                            @click="approveRequest(request.id)"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 hover:text-white bg-emerald-500/10 hover:bg-emerald-600 px-3 py-2 rounded-lg border border-emerald-500/20 hover:border-emerald-600 transition-all shadow-sm"
                                            :title="__('Approve')"
                                        >
                                            <CheckIcon class="w-4 h-4" />
                                            {{ __("Approve") }}
                                        </button>
                                        <button
                                            @click="rejectRequest(request.id)"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-600 px-3 py-2 rounded-lg border border-rose-500/20 hover:border-rose-600 transition-all shadow-sm"
                                            :title="__('Reject')"
                                        >
                                            <XMarkIcon class="w-4 h-4" />
                                            {{ __("Reject") }}
                                        </button>
                                    </div>
                                    <span v-else-if="request.status === 'Pending'" class="text-xs text-gray-500 italic">
                                        {{ __("Awaiting approval") }}
                                    </span>
                                    <span v-else class="text-xs text-gray-500 italic">
                                        {{ __("Already processed") }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="requests.data.length === 0">
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4 opacity-20">
                                        <CalendarDaysIcon class="w-16 h-16 text-white" />
                                        <p class="text-xl font-bold text-white">
                                            {{ __("No requests found") }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
