<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppNavbar from './Components/AppNavbar.vue'
import AppHeader from './Components/AppHeader.vue'

const route = useRoute()

const showLayout = computed(() => route.meta.requiresAuth === true)
</script>

<template>
  <div v-if="showLayout" class="app-layout">
    <AppNavbar />

    <div class="main-area">
      <AppHeader />

      <main class="page-content">
        <RouterView />
      </main>
    </div>
  </div>

  <RouterView v-else />
</template>

<style>
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  background: #090d18;
}

.app-layout {
  min-height: 100vh;
  background: #090d18;
}

.main-area {
  min-height: 100vh;
  margin-left: 186px;
}

.page-content {
  min-height: calc(100vh - 66px);
}

@media (max-width: 650px) {
  .main-area {
    margin-left: 62px;
  }
}
</style>
