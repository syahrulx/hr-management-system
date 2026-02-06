<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import ReqTabs from "@/Components/Tabs/ReqTabs.vue";
import Card from "@/Components/Card.vue";
import {
    CalendarDaysIcon,
    UserIcon,
    ClockIcon,
    ChatBubbleLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowLeftIcon,
    PaperClipIcon,
    ArrowTopRightOnSquareIcon,
} from "@heroicons/vue/24/outline";
import { __ } from "@/Composables/useTranslations.js";
import { request_status_types } from "@/Composables/useRequestStatusTypes.js";
import { useToast } from "vue-toastification";

const props = defineProps({
    request: Object,
});

const getStatusClass = (status) => {
    switch (status) {
        case 0:
            return "text-amber-400 bg-amber-400/10 border-amber-400/20";
        case 1:
            return "text-emerald-400 bg-emerald-400/10 border-emerald-400/20";
        case 2:
            return "text-rose-400 bg-rose-400/10 border-rose-400/20";
        default:
            return "text-gray-400 bg-gray-400/10 border-gray-400/20";
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 0:
            return __("Pending");
        case 1:
            return __("Approved");
        case 2:
            return __("Rejected");
        default:
            return __("Unknown");
    }
};

const approveRequest = () => {
    router.put(
        route("requests.update", { request: props.request.request_id }),
        {
            status: 1,
        },
        {
            onSuccess: () => {
                useToast().success(__("Request Approved"));
            },
            onError: () => {
                useToast().error(__("Failed to update request"));
            },
        }
    );
};

const rejectRequest = () => {
    router.put(
        route("requests.update", { request: props.request.request_id }),
        {
            status: 2,
        },
        {
            onSuccess: () => {
                useToast().success(__("Request Rejected"));
            },
            onError: () => {
                useToast().error(__("Failed to update request"));
            },
        }
    );
};
</script>

<template>
    <Head :title="__('Request Details')" />
    <AuthenticatedLayout>
        <template #tabs>
            <ReqTabs />
        </template>

        <div
            class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in-up"
        >
            <!-- BACK BUTTON -->
            <a
                :href="route('requests.index')"
                class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm font-medium"
            >
                <ArrowLeftIcon class="w-4 h-4" />
                {{ __("Back to Requests") }}
            </a>

            <!-- REQUEST DETAILS CARD -->
            <Card variant="glass" class="!mt-4 overflow-hidden">
                <!-- Header -->
                <div class="p-8 border-b border-white/5 bg-white/[0.02]">
                    <div
                        class="flex flex-col md:flex-row md:justify-between md:items-start gap-6"
                    >
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span
                                    class="text-sm font-mono font-bold text-red-500/80"
                                    >#{{
                                        request.request_id
                                            ?.toString()
                                            .padStart(4, "0")
                                    }}</span
                                >
                                <span
                                    :class="getStatusClass(request.status)"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm"
                                >
                                    {{ getStatusText(request.status) }}
                                </span>
                            </div>
                            <h1
                                class="text-3xl font-black text-white tracking-tight"
                            >
                                {{ request.type }}
                            </h1>
                        </div>

                        <!-- ACTION BUTTONS (Admin/Owner only) -->
                        <div
                            v-if="
                                ['admin', 'owner'].includes(
                                    $page.props.auth.user.role
                                ) && request.status === 0 && request.user_id !== $page.props.auth.user.user_id
                            "
                            class="flex gap-3"
                        >
                            <button
                                @click="approveRequest"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-emerald-600/20"
                            >
                                <CheckCircleIcon class="w-5 h-5" />
                                {{ __("Approve") }}
                            </button>
                            <button
                                @click="rejectRequest"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-rose-600/20"
                            >
                                <XCircleIcon class="w-5 h-5" />
                                {{ __("Reject") }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Employee -->
                        <div
                            class="bg-white/5 rounded-xl p-5 border border-white/10"
                        >
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center"
                                >
                                    <UserIcon class="w-5 h-5 text-red-500" />
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] font-bold text-gray-500 uppercase tracking-widest"
                                    >
                                        {{ __("Employee") }}
                                    </p>
                                    <p class="text-lg font-bold text-white">
                                        {{
                                            request.employee?.name ||
                                            __("Unknown")
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div
                            class="bg-white/5 rounded-xl p-5 border border-white/10"
                        >
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center"
                                >
                                    <CalendarDaysIcon
                                        class="w-5 h-5 text-red-500"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] font-bold text-gray-500 uppercase tracking-widest"
                                    >
                                        {{ __("Duration") }}
                                    </p>
                                    <p class="text-lg font-bold text-white">
                                        {{ request.start_date }}
                                        <span
                                            v-if="request.end_date"
                                            class="text-gray-400"
                                        >
                                            → {{ request.end_date }}</span
                                        >
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div
                        v-if="request.remark"
                        class="bg-white/5 rounded-xl p-5 border border-white/10"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center flex-shrink-0"
                            >
                                <ChatBubbleLeftIcon
                                    class="w-5 h-5 text-red-500"
                                />
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2"
                                >
                                    {{ __("Remarks") }}
                                </p>
                                <p class="text-gray-300 leading-relaxed">
                                    {{ request.remark }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Support Document -->
                    <div
                        v-if="request.support_doc"
                        class="bg-white/5 rounded-xl p-5 border border-white/10"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center flex-shrink-0"
                            >
                                <PaperClipIcon
                                    class="w-5 h-5 text-red-500"
                                />
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2"
                                >
                                    {{ __("Supporting Document") }}
                                </p>
                                <a :href="request.support_doc" target="_blank" class="text-red-400 hover:text-red-300 font-bold text-sm underline flex items-center gap-2">
                                    {{ __("View Document") }}
                                    <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- No Remarks Placeholder -->
                    <div
                        v-else-if="!request.remark"
                        class="bg-white/5 rounded-xl p-5 border border-white/10 text-center"
                    >
                        <p class="text-gray-500 text-sm italic">
                            {{ __("No remarks provided") }}
                        </p>
                    </div>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
