<script setup lang="ts">
import { ref } from 'vue'
import api from './services/api'

const message = ref('')
const loading = ref(false)

async function testRegister() {
  loading.value = true
  message.value = ''

  try {
    // Отримуємо CSRF-cookie
    await api.get('/sanctum/csrf-cookie')

    // Реєструємо тестового користувача
    const response = await api.post('/api/register', {
      name: 'Test Player',
      email: `test${Date.now()}@exolune.com`,
      password: 'Password123!',
      password_confirmation: 'Password123!',
    })

    message.value = JSON.stringify(response.data, null, 2)
  } catch (error: any) {
    message.value = JSON.stringify(error.response?.data ?? error.message, null, 2)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main>
    <h1>exolune RPG — API Test</h1>

    <button :disabled="loading" @click="testRegister">
      {{ loading ? 'Реєстрація...' : 'Тест реєстрації' }}
    </button>

    <pre v-if="message">{{ message }}</pre>
  </main>
</template>
