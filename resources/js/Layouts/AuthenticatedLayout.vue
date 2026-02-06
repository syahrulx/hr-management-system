<script setup>
import { ref } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import SidebarListItem from "@/Components/SidebarListItem.vue";
import { useDark, useToggle } from "@vueuse/core";
import EmployeeIcon from "@/Components/Icons/UsersIcon.vue";
import OrganizationIcon from "@/Components/Icons/OrganizationIcon.vue";
import MessageIcon from "@/Components/Icons/MessageIcon.vue";
import CalendarIcon from "@/Components/Icons/CalendarIcon.vue";
import TableIcon from "@/Components/Icons/TableIcon.vue";
import MoneyIcon from "@/Components/Icons/MoneyIcon.vue";
import RocketIcon from "@/Components/Icons/RocketIcon.vue";
import UserIcon from "@/Components/Icons/UserIcon.vue";

import { router, usePage } from "@inertiajs/vue3";
import { watch } from "vue";
import { useToast } from "vue-toastification";

const page = usePage();
const toast = useToast();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
        if (flash?.message) {
            toast.info(flash.message);
        }
    },
    { deep: true }
);

const showingNavigationDropdown = ref(false);
const isDark = useDark();
const toggleDark = useToggle(isDark);
</script>

<template>
    <div
        class="min-h-screen bg-black relative overflow-hidden font-sans text-gray-100"
    >
        <!-- Ambient Background Glow - BOOSTED RED -->
        <div
            class="fixed top-[-20%] left-[-10%] w-[60%] h-[60%] bg-red-800/40 rounded-full blur-[180px] pointer-events-none z-0"
        ></div>
        <div
            class="fixed bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-red-900/30 rounded-full blur-[180px] pointer-events-none z-0"
        ></div>

        <aside
            id="separator-sidebar"
            class="fixed top-4 left-4 z-40 w-64 h-[calc(100vh-2rem)] transition-transform ltr:-translate-x-full ltr:sm:translate-x-0 rtl:translate-x-full rtl:sm:-translate-x-0 rounded-2xl shadow-2xl shadow-black/50"
            :class="$page.props.locale == 'ar' ? 'right-4' : 'left-4'"
            aria-label="Sidebar"
        >
            <div
                class="h-full px-3 py-4 overflow-y-auto flex flex-col center bg-[#0f0505]/90 backdrop-blur-xl border border-red-900/30 rounded-2xl shadow-[0_0_20px_rgba(255,0,0,0.05)]"
            >
                <!-- Logo at the top -->
                <div class="flex flex-col items-center mt-2 mb-6">
                    <img
                        src="/images/gymlogo.png"
                        alt="Gym Logo"
                        class="h-20 object-contain drop-shadow-lg"
                    />
                </div>
                <ul
                    v-if="$page.props.auth.user.role === 'admin'"
                    class="space-y-2 font-medium mb-4"
                >
                    <SidebarListItem
                        :item-name="__('Dashboard')"
                        link="dashboard.index"
                        :active-links="['dashboard.index']"
                    >
                        <RocketIcon class="text-gray-100" />
                    </SidebarListItem>
                    <SidebarListItem
                        :item-name="__('My Profile')"
                        :hasBadge="false"
                        link="my-profile"
                        :active-links="['my-profile']"
                    >
                        <UserIcon class="text-gray-500 dark:text-gray-100" />
                    </SidebarListItem>
                    <SidebarListItem
                        :item-name="__('Employees')"
                        link="employees.index"
                        :active-links="[
                            'employees.index',
                            'employees.create',
                            'employees.show',
                            'employees.find',
                            'employees.edit',
                        ]"
                    >
                        <EmployeeIcon
                            class="text-gray-500 dark:text-gray-100"
                        />
                    </SidebarListItem>
                    <SidebarListItem
                        :item-name="__('Leave')"
                        link="requests.index"
                        :active-links="[
                            'requests.index',
                            'requests.create',
                            'requests.show',
                            'requests.edit',
                        ]"
                    >
                        <MessageIcon class="text-gray-500 dark:text-gray-100" />
                    </SidebarListItem>
                    <SidebarListItem
                        :item-name="__('Assign Schedule')"
                        link="schedule.admin"
                        :activeLinks="['schedule.admin']"
                    >
                        <CalendarIcon
                            class="text-gray-500 dark:text-gray-100"
                        />
                    </SidebarListItem>
                </ul>
                <ul
                    v-else-if="$page.props.auth.user.role === 'owner'"
                    class="space-y-2 font-medium mb-4"
                >
                    <SidebarListItem
                        :item-name="__('Reports')"
                        :hasBadge="false"
                        link="reports.index"
                        :active-links="['reports.index']"
                    >
                        <TableIcon class="text-gray-500 dark:text-gray-100" />
                    </SidebarListItem>
                    <SidebarListItem
                        :item-name="__('Employees')"
                        link="employees.index"
                        :active-links="[
                            'employees.index',
                            'employees.create',
                            'employees.show',
                            'employees.find',
                            'employees.edit',
                        ]"
                    >
                        <EmployeeIcon
                            class="text-gray-500 dark:text-gray-100"
                        />
                    </SidebarListItem>
                    <SidebarListItem
                        :item-name="__('Leave')"
                        link="requests.index"
                        :active-links="[
                            'requests.index',
                            'requests.create',
                            'requests.show',
                            'requests.edit',
                        ]"
                    >
                        <MessageIcon class="text-gray-500 dark:text-gray-100" />
                    </SidebarListItem>
                </ul>

                <ul
                    v-else-if="$page.props.auth.user.role === 'employee'"
                    class="space-y-2 font-medium mb-4"
                >
                    <SidebarListItem
                        :item-name="__('My Dashboard')"
                        :hasBadge="false"
                        link="dashboard.index"
                        :active-links="['dashboard.index']"
                    >
                        <RocketIcon class="text-gray-500 dark:text-gray-100" />
                    </SidebarListItem>

                    <SidebarListItem
                        :item-name="__('My Profile')"
                        :hasBadge="false"
                        link="my-profile"
                        :active-links="['my-profile']"
                    >
                        <UserIcon class="text-gray-500 dark:text-gray-100" />
                    </SidebarListItem>

                    <SidebarListItem
                        :item-name="__('My Leave')"
                        :hasBadge="false"
                        link="requests.index"
                        :active-links="[
                            'requests.index',
                            'requests.show',
                            'requests.create',
                        ]"
                    >
                        <MessageIcon class="text-gray-500 dark:text-gray-100" />
                    </SidebarListItem>

                    <SidebarListItem
                        :item-name="__('My Schedule')"
                        link="schedule.employee"
                        :active-links="['schedule.employee']"
                    >
                        <CalendarIcon
                            class="text-gray-500 dark:text-gray-100"
                        />
                    </SidebarListItem>
                </ul>
            </div>
        </aside>

        <div
            :class="$page.props.locale === 'ar' ? 'sm:mr-72' : 'sm:ml-72'"
            class="relative z-10 p-4 transition-all duration-300"
        >
            <div>
                <!-- Header/Top Bar -->
                <nav
                    class="relative z-50 mb-6 flex justify-between items-center rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 p-4 shadow-lg"
                >
                    <!-- Primary Navigation Menu -->
                    <div class="flex justify-between w-full">
                        <div class="flex">
                            <div
                                class="block my-auto space-x-8 rtl:space-x-reverse sm:-my-px sm:flex"
                            >
                                <slot name="tabs"></slot>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div></div>
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 hover:text-white focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}
                                                <svg
                                                    class="mx-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            {{ __("Log Out") }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Responsive Navigation Menu -->
                    <div
                        :class="{
                            block: showingNavigationDropdown,
                            hidden: !showingNavigationDropdown,
                        }"
                        class="sm:hidden absolute top-16 left-0 right-0 bg-gray-900 border border-gray-700 rounded-b-lg shadow-xl z-50 p-4"
                    >
                        <div class="pt-2 pb-3 space-y-1">
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('dashboard.index')"
                                :active="route().current('dashboard.index')"
                            >
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('employees.index')"
                                >Employees</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('requests.index')"
                                >Leave</ResponsiveNavLink
                            >

                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'owner'"
                                :href="route('employees.index')"
                                >Employees</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'owner'"
                                :href="route('requests.index')"
                                >Leave</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'employee'"
                                :href="route('dashboard.index')"
                                :active="route().current('dashboard.index')"
                            >
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'employee'"
                                :href="route('employees.index')"
                                >Employees</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'employee'"
                                :href="route('requests.index')"
                                >Leave</ResponsiveNavLink
                            >
                        </div>

                        <!-- Responsive Settings Options -->
                        <div class="pt-4 pb-1 border-t border-gray-700">
                            <div class="px-4">
                                <div
                                    class="font-medium text-base text-gray-200"
                                >
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
        <!-- Closing the main wrapper div we added in the previous step -->
    </div>
</template>

<style>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
