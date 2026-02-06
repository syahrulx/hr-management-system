<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ReqTabs from "@/Components/Tabs/ReqTabs.vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import dayjs from "dayjs";
import Card from "@/Components/Card.vue";
import { inject, watch, computed } from "vue";
import { __ } from "@/Composables/useTranslations.js";
import {
    CalendarDaysIcon,
    InformationCircleIcon,
    PaperAirplaneIcon,
    ChartBarIcon,
    PaperClipIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    types: Array,
    leaveBalances: Array,
});

const leaveTypes = ["Annual Leave", "Sick Leave", "Emergency Leave"];

const form = useForm({
    type: "",
    date: "",
    remark: "",
    support_doc: null,
});

// Computed Minimum Date
const minDate = computed(() => {
    if (form.type === 'Annual Leave') {
        return dayjs().add(7, 'day').toDate();
    }
    return dayjs().toDate(); // Prevent past dates for others (consistent with backend)
});

// Computed Max Range (in days)
const maxRange = computed(() => {
    return form.type === 'Annual Leave' ? 4 : null; 
});

watch(
    () => form.type,
    (value) => {
        // Reset date when type changes to avoid invalid selections remaining
        form.date = "";
    }
);

const submitForm = () => {
    Object.keys(form.date).forEach(function (key) {
        if (form.date[key] && !/^\d{4}-\d{2}-\d{2}$/.test(form.date[key])) {
            form.date[key] = dayjs(form.date[key]).format("YYYY-MM-DD");
        }
    });
    form.post(route("requests.store"), {
        preserveScroll: true,
        onError: () => {
            if (usePage().props.errors.past_leave) {
                useToast().error(usePage().props.errors.past_leave);
            } else {
                useToast().error(__("Error Creating Request"));
            }
        },
        onSuccess: () => {
            useToast().success(__("Request Created Successfully"));
            form.reset();
        },
    });
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // If it's a PDF, we can't compress it easily, so just use it as is
    if (file.type === "application/pdf") {
        form.support_doc = file;
        return;
    }

    // Checking if file size is small enough (e.g. < 1MB), no need to compress
    if (file.size < 1024 * 1024) {
        form.support_doc = file;
        return;
    }

    // Compress Image
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (e) => {
        const img = new Image();
        img.src = e.target.result;
        img.onload = () => {
            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");

            // Max dimensions
            const MAX_WIDTH = 1200;
            const MAX_HEIGHT = 1200;
            let width = img.width;
            let height = img.height;

            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                }
            }

            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob(
                (blob) => {
                    // Create a new File object from the blob to keep the name
                    const compressedFile = new File([blob], file.name, {
                        type: "image/jpeg",
                        lastModified: Date.now(),
                    });
                    form.support_doc = compressedFile;
                },
                "image/jpeg",
                0.7 // Quality (0.7 is good balance)
            );
        };
    };
};
</script>

<template>
    <Head :title="__('Request Leave')" />
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

                        <h2
                            class="text-lg font-bold text-white mb-6 flex items-center gap-2"
                        >
                            <CalendarDaysIcon class="w-5 h-5 text-red-500" />
                            {{ __("Leave Balances") }}
                        </h2>
                        <div class="space-y-6">
                            <div
                                v-for="type in leaveTypes"
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
                                            >{{ __("Remaining Days") }}</span
                                        >
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="text-lg font-black text-red-500"
                                        >
                                            {{
                                                leaveBalances &&
                                                leaveBalances.find(
                                                    (l) => l.leave_type === type
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
                                                (((leaveBalances &&
                                                    leaveBalances.find(
                                                        (l) => l.leave_type === type
                                                    )?.balance) ||
                                                    0) /
                                                    (type === 'Emergency Leave'
                                                        ? 7
                                                        : 14)) *
                                                    100,
                                                100
                                            )}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <div
                        class="p-6 rounded-2xl bg-emerald-500/5 border border-emerald-500/10 backdrop-blur-sm"
                    >
                        <h4
                            class="text-emerald-400 font-bold mb-2 flex items-center gap-2 uppercase tracking-tighter text-sm font-mono"
                        >
                            <InformationCircleIcon class="w-4 h-4" />
                            {{ __("Important") }}
                        </h4>
                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            {{
                                __(
                                    "Please ensure all required documents are ready if you are applying for Sick Leave or long-term Absence."
                                )
                            }}
                        </p>
                    </div>
                </aside>

                <!-- MAIN CONTENT: FORM -->
                <main class="lg:col-span-9">
                    <Card
                        variant="glass"
                        class="!mt-0 overflow-hidden relative"
                    >
                        <div
                            class="p-8 border-b border-white/5 bg-white/[0.02]"
                        >
                            <h1
                                class="text-3xl font-black text-white tracking-tight"
                            >
                                {{ __("Apply Leave") }}
                            </h1>
                            <p class="text-gray-400 text-sm mt-1">
                                {{
                                    __(
                                        "Apply for your time-off with details below."
                                    )
                                }}
                            </p>
                        </div>

                        <form
                            @submit.prevent="submitForm"
                            class="p-8 space-y-8"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Leave Type -->
                                <div class="space-y-1.5">
                                    <InputLabel
                                        for="type_id"
                                        :value="__('Type of Leave')"
                                        class="!mb-0"
                                    />
                                    <div class="relative group">
                                        <select
                                            id="type_id"
                                            class="w-full bg-white/5 border border-white/10 text-gray-100 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-3 pr-10 appearance-none transition-all"
                                            v-model="form.type"
                                        >
                                            <option
                                                selected
                                                value=""
                                                class="bg-[#18181b]"
                                            >
                                                {{ __("Choose a Leave Type") }}
                                            </option>
                                            <option
                                                v-for="type in leaveTypes"
                                                :key="type"
                                                :value="type"
                                                class="bg-[#18181b]"
                                            >
                                                {{ type }}
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
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.type"
                                    />
                                </div>

                                <!-- Date Picker -->
                                <div class="space-y-1.5">
                                    <InputLabel
                                        for="date"
                                        :value="__('Date Range selection')"
                                        class="!mb-0"
                                    />
                                    <VueDatePicker
                                        id="date"
                                        v-model="form.date"
                                        class="modern-datepicker"
                                        :placeholder="__('Select Date...')"
                                        :enable-time-picker="false"
                                        :min-date="minDate"
                                        :max-range="maxRange"
                                        :dark="true"
                                        range
                                        required
                                    ></VueDatePicker>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.date"
                                    />
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="space-y-1.5">
                                <InputLabel
                                    for="remark"
                                    :value="__('Remark / Reason')"
                                    class="!mb-0"
                                />
                                <textarea
                                    id="remark"
                                    class="w-full bg-white/5 border border-white/10 text-gray-100 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-4 min-h-[120px] transition-all placeholder:text-gray-600"
                                    v-model="form.remark"
                                    autocomplete="off"
                                    :placeholder="
                                        __(
                                            'I will be absent for 3 days because...'
                                        )
                                    "
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.remark"
                                />
                            </div>

                            <!-- Support Document -->
                            <div class="space-y-1.5">
                                <InputLabel
                                    for="support_doc"
                                    :value="['Sick Leave', 'Emergency Leave'].includes(form.type) ? __('Supporting Document (Required)') : __('Supporting Document (Optional)')"
                                    class="!mb-0"
                                />
                                
                                <div class="relative flex items-center gap-3 p-1.5 bg-white/5 border border-white/10 rounded-xl group hover:border-white/20 transition-colors">
                                    <div class="relative shrink-0">
                                        <input
                                            type="file"
                                            id="support_doc"
                                            @change="handleFileUpload"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                        />
                                        <div class="px-4 py-2 bg-white/10 group-hover:bg-white/15 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors flex items-center gap-2">
                                            <PaperClipIcon class="w-4 h-4" />
                                            {{ __("Choose File") }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 pr-3">
                                        <p class="text-sm text-gray-400 truncate font-mono">
                                            {{ form.support_doc ? form.support_doc.name : __("No file selected") }}
                                        </p>
                                    </div>
                                </div>

                                <p class="mt-1 text-xs text-gray-500 pl-1">{{ __("Max 5MB. Formats: JPG, PNG, PDF.") }}</p>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.support_doc"
                                />
                            </div>

                            <div
                                class="flex items-center justify-end pt-4 border-t border-white/5"
                            >
                                <PrimaryButton
                                    class="!bg-red-600 hover:!bg-red-700 !rounded-xl !px-10 !py-4 !text-white flex items-center gap-3 transition-all shadow-xl hover:shadow-red-500/20 group"
                                    :disabled="form.processing"
                                >
                                    <span
                                        class="font-black uppercase tracking-widest text-sm"
                                        >{{ __("Submit Request") }}</span
                                    >
                                    <PaperAirplaneIcon
                                        class="w-5 h-5 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"
                                    />
                                </PrimaryButton>
                            </div>
                        </form>
                    </Card>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.modern-datepicker .dp__input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    color: #f3f4f6;
    font-size: 0.875rem;
}
.modern-datepicker .dp__input:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 1px #ef4444;
}

/* Fix for date picker dropdown being cut off */
.modern-datepicker .dp__menu {
    z-index: 9999 !important;
}

.dp__menu {
    z-index: 9999 !important;
}

.dp__outer_menu_wrap {
    z-index: 9999 !important;
}
</style>
