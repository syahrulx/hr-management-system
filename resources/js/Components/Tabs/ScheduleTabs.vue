<script setup>
import NavLink from "@/Components/NavLink.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";
</script>

<template>
    <GoBackNavLink />
    <div class="space-x-8 rtl:space-x-reverse sm:flex">
        <NavLink
            :href="route('schedule.admin')"
            :active="route().current('schedule.admin')"
        >
            Schedule
        </NavLink>
    </div>
</template>
