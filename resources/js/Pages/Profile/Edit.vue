<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";
import EmployeeTabs from "@/Components/Tabs/EmployeeTabs.vue";
import Card from "@/Components/Card.vue";
import {
    AtSymbolIcon,
    UserIcon,
    LockClosedIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/solid";
import dayjs from "dayjs";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    user: {
        type: Object,
    },
});
</script>

<template>
    <Head :title="__('Profile')" />

    <AuthenticatedLayout>
        <template #tabs>
            <EmployeeTabs />
        </template>

        <div
            v-if="user"
            class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 animate-fade-in-up"
        >
            <!-- 1. ENHANCED HERO SECTION -->
            <Card
                variant="glass"
                class="relative overflow-hidden !mt-0 !p-0 border-none shadow-2xl"
            >
                <!-- Modern Abstract Background Decoration -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-red-900/40 via-black to-black"
                ></div>
                <div
                    class="absolute top-0 right-0 w-[500px] h-[500px] bg-red-600/10 rounded-full blur-[120px] -mr-32 -mt-32 pointer-events-none"
                ></div>
                <div
                    class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-red-900/10 rounded-full blur-[100px] -ml-20 -mb-20 pointer-events-none"
                ></div>

                <div
                    class="relative z-10 p-8 md:p-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-8"
                >
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <!-- Avatar with Premium Glow -->
                        <div class="relative group">
                            <div
                                class="absolute -inset-1 bg-gradient-to-r from-red-600 to-red-900 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"
                            ></div>
                            <div
                                class="relative w-32 h-32 rounded-full bg-[#18181b] border-2 border-red-500/30 flex items-center justify-center shadow-inner overflow-hidden"
                            >
                                <span
                                    class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-br from-red-400 to-red-600"
                                >
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div
                                class="absolute bottom-1 right-1 w-8 h-8 rounded-full bg-emerald-500 border-4 border-[#18181b] shadow-lg"
                                title="Active Account"
                            ></div>
                        </div>

                        <div class="text-center md:text-left">
                            <h1
                                class="text-4xl font-extrabold text-white tracking-tight mb-1"
                            >
                                {{ user.name }}
                            </h1>
                            <div
                                class="flex flex-wrap justify-center md:justify-start items-center gap-3"
                            >
                                <span
                                    class="px-3 py-1 text-xs font-bold rounded-lg bg-red-500/20 text-red-500 border border-red-500/30 uppercase tracking-widest"
                                >
                                    {{ user.user_role || "Employee" }}
                                </span>
                                <span
                                    class="flex items-center gap-1.5 text-gray-400 text-sm"
                                >
                                    <AtSymbolIcon
                                        class="w-4 h-4 text-red-500/60"
                                    />
                                    {{ user.email }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ID Card Style Details -->
                    <div
                        class="w-full md:w-auto grid grid-cols-2 gap-4 md:flex md:flex-col md:items-end"
                    >
                        <div
                            class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-4 min-w-[160px]"
                        >
                            <p
                                class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-1"
                            >
                                {{ __("Employee ID") }}
                            </p>
                            <p class="text-lg font-mono font-bold text-white">
                                #{{ user.user_id?.toString().padStart(5, "0") }}
                            </p>
                        </div>
                        <div
                            class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-4 min-w-[160px]"
                        >
                            <p
                                class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-1"
                            >
                                {{ __("Member Since") }}
                            </p>
                            <p class="text-sm font-bold text-gray-200">
                                {{
                                    user.hired_on
                                        ? dayjs(user.hired_on).format('DD/MM/YYYY')
                                        : "—"
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- 2. MAIN FORMS AREA (Left/Middle) -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- PROFILE INFORMATION -->
                    <Card
                        variant="glass"
                        class="!mt-0 relative overflow-hidden group"
                    >
                        <div
                            class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity"
                        >
                            <UserIcon class="w-24 h-24 text-white" />
                        </div>
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            :user="user"
                        />
                    </Card>
                </div>

                <!-- 3. SECONDARY ACTIONS (Right) -->
                <div class="lg:col-span-4 space-y-8">
                    <!-- SECURITY CARD -->
                    <Card
                        variant="glass"
                        class="!mt-0 relative overflow-hidden group"
                    >
                        <div
                            class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity"
                        >
                            <LockClosedIcon class="w-20 h-20 text-white" />
                        </div>
                        <UpdatePasswordForm />
                    </Card>

                    <!-- INFO TIP BOX -->
                    <div
                        class="p-6 rounded-2xl bg-gradient-to-br from-red-600/10 to-transparent border border-red-500/20"
                    >
                        <h4
                            class="text-white font-bold mb-2 flex items-center gap-2"
                        >
                            <InformationCircleIcon
                                class="w-5 h-5 text-red-500"
                            />
                            {{ __("Security Tip") }}
                        </h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            {{
                                __(
                                    "Keep your profile updated to ensure accurate payroll and communication. We recommend changing your password every 90 days."
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Not Found Fallback -->
        <div v-else class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="text-center p-12 bg-white/5 border border-white/10 rounded-2xl"
            >
                <h2 class="text-2xl font-bold text-white mb-2">
                    {{ __("User Not Found") }}
                </h2>
                <p class="text-gray-400">
                    {{ __("The requested user profile could not be found.") }}
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
