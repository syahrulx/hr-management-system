<script setup>
import NavLink from "@/Components/NavLink.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";
</script>

<template>
    <GoBackNavLink/>
    <div v-if="$page.props.auth.user.role === 'admin'" class="space-x-8 rtl:space-x-reverse sm:flex">
        <NavLink :href="route('employees.index')" :active="route().current('employees.index') || route().current('employees.show')">
            {{__('All Employees')}}
        </NavLink>
        <NavLink :href="route('employees.create')" :active="route().current('employees.create')">
            {{__('Add An Employee')}}
        </NavLink>
    </div>
    <div v-else class="space-x-8 sm:flex">
        <NavLink :href="route('my-profile', {id: $page.props.auth.user.id})" active>
            {{__('My Profile')}}
        </NavLink>
    </div>
</template>
