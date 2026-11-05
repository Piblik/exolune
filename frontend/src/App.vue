<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppNavbar from './Components/AppNavBar.vue'

const route = useRoute()

const showNavbar = computed(() => route.meta.requiresAuth === true)
</script>

<template>
  <AppNavbar v-if="showNavbar" />

  <RouterView />
</template>
