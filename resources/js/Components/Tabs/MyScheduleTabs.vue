<script setup>
import NavLink from "@/Components/NavLink.vue";
</script>

<template>
  <div class="space-x-8 rtl:space-x-reverse sm:flex">
    <NavLink :href="route('schedule.employee')" :active="route().current('schedule.employee')">
      Weekly Schedule
    </NavLink>
  </div>
</template>
