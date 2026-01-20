<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import EmployeeTabs from "@/Components/Tabs/EmployeeTabs.vue";
import Card from "@/Components/Card.vue";
import {
    AtSymbolIcon,
    UserIcon,
    PhoneIcon,
    MapPinIcon,
    IdentificationIcon,
    CalendarIcon,
    ShieldCheckIcon,
    PencilSquareIcon,
} from "@heroicons/vue/24/solid";
import { CheckBadgeIcon } from "@heroicons/vue/24/outline";

defineProps({
    user: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="__('Employee Profile')" />

    <AuthenticatedLayout>
        <template #tabs>
            <EmployeeTabs />
        </template>

        <div
            v-if="user"
            class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 animate-fade-in-up"
        >
            <!-- HERO SECTION -->
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

                    <!-- ID Card Style Details + Edit Button -->
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
                                        ? new Date(
                                              user.hired_on
                                          ).toLocaleDateString(undefined, {
                                              year: "numeric",
                                              month: "long",
                                          })
                                        : "—"
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- EMPLOYEE DETAILS CARD (Read-Only) -->
            <Card variant="glass" class="!mt-0 relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity"
                >
                    <UserIcon class="w-24 h-24 text-white" />
                </div>

                <div class="p-8 md:p-10 space-y-10">
                    <!-- Header with Edit Button -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-medium text-red-500 mb-2">
                                {{ __("Employee Details") }}
                            </h2>
                            <p class="text-sm text-gray-300">
                                {{ __("View employee profile information and organizational details") }}.
                            </p>
                        </div>
                        <Link
                            :href="route('employees.edit', { employee: user.user_id })"
                            class="flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-xs uppercase tracking-wide transition-all shadow-lg shadow-red-600/20"
                        >
                            <PencilSquareIcon class="w-4 h-4" />
                            {{ __("Edit Employee") }}
                        </Link>
                    </div>

                    <!-- Identity Section -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/5">
                            <IdentificationIcon class="w-5 h-5 text-red-500" />
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">
                                {{ __("Identity Information") }}
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em]">
                                    {{ __("Full Name") }}
                                </p>
                                <p class="text-base font-medium text-gray-200">
                                    {{ user.name }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em]">
                                    {{ __("National ID (IC)") }}
                                </p>
                                <p class="text-base font-medium text-gray-200">
                                    {{ user.ic_number || "—" }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/5">
                            <PhoneIcon class="w-5 h-5 text-red-500" />
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">
                                {{ __("Contact Details") }}
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em]">
                                    {{ __("Phone Number") }}
                                </p>
                                <p class="text-base font-medium text-gray-200">
                                    {{ user.phone || "—" }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em]">
                                    {{ __("Email Address") }}
                                </p>
                                <p class="text-base font-medium text-gray-200">
                                    {{ user.email }}
                                </p>
                            </div>
                            <div class="space-y-1 md:col-span-2">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em]">
                                    {{ __("Residential Address") }}
                                </p>
                                <p class="text-base font-medium text-gray-200">
                                    {{ user.address || "—" }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Organizational Section -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/5">
                            <ShieldCheckIcon class="w-5 h-5 text-red-500" />
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">
                                {{ __("Organizational Context") }}
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em]">
                                    {{ __("Hire Date") }}
                                </p>
                                <p class="text-base font-medium text-gray-200 flex items-center gap-2">
                                    <CalendarIcon class="w-4 h-4 text-red-500/60" />
                                    {{
                                        user.hired_on
                                            ? new Date(user.hired_on).toLocaleDateString(undefined, {
                                                  year: "numeric",
                                                  month: "long",
                                                  day: "numeric",
                                              })
                                            : "—"
                                    }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em]">
                                    {{ __("Permissions Level") }}
                                </p>
                                <p class="text-base font-medium text-gray-200 flex items-center gap-2">
                                    <CheckBadgeIcon class="w-4 h-4 text-red-500/60" />
                                    <span class="capitalize">{{ user.user_role || "Employee" }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
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
