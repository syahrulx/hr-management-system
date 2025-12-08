<script setup>
import NavLink from "@/Components/NavLink.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";
</script>

<template>
    <GoBackNavLink/>

    <NavLink :href="route('shifts.index')" :active="route().current('shifts.index') || route().current('shifts.edit') || route().current('shifts.show') ||route().current('shifts.create')">
        {{__('Shifts')}}
    </NavLink>

</template>

