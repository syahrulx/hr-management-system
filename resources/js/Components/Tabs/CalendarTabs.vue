<script setup>
import NavLink from "@/Components/NavLink.vue";
import GoBackNavLink from "@/Components/GoBackNavLink.vue";
</script>

<template>
    <GoBackNavLink/>

    <div class="space-x-8 sm:flex">
        <NavLink href="#" active>
            {{__('Calendar Feature Removed')}}
        </NavLink>
    </div>

</template>

