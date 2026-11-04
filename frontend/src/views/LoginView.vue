<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.ts'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const errorMessage = ref('')

async function handleLogin() {
  errorMessage.value = ''

  try {
    await authStore.login(form)
    await router.push({ name: 'dashboard' })
  } catch (error: unknown) {
    const apiError = error as {
      response?: {
        data?: {
          message?: string
        }
      }
    }

    errorMessage.value =
      apiError.response?.data?.message ?? 'Не вдалося увійти. Перевір правильність даних'
  }
}
</script>

<template>
  <main class="login-page">
    <section class="login-card">
      <h1>exolune RPG</h1>
      <p class="subtitle">Повернись до своєї пригоди</p>

      <form @submit.prevent="handleLogin">
        <label for="email">Електронна пошта</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          placeholder="example@email.com"
          autocomplete="email"
          required
        />

        <label for="password">пароль</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          placeholder="Введи пароль"
          autocomplete="current-password"
          required
        />

        <p v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="authStore.isLoading">
          {{ authStore.isLoading ? 'Вхід...' : 'Увійти' }}
        </button>
      </form>

      <p class="register-link">
        Ще не має акаунта?
        <RouterLink to="/register">зареєструватися</RouterLink>
      </p>
    </section>
  </main>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
  box-sizing: border-box;
  background: #10111b;
  color: #ffffff;
  font-family: Arial, sans-serif;
}

.login-card {
  width: 100%;
  max-width: 420px;
  padding: 36px;
  border: 1px solid #303248;
  border-radius: 16px;
  background: #191a29;
  box-sizing: border-box;
}

h1 {
  margin: 0;
  text-align: center;
  font-size: 32px;
  color: #a78bfa;
}

.subtitle {
  margin: 12px 0 28px;
  text-align: center;
  color: #a1a1b5;
}

form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

label {
  margin-top: 8px;
  font-size: 14px;
  color: #d5d5e2;
}

input {
  width: 100%;
  box-sizing: border-box;
  padding: 13px;
  border: 1px solid #393b52;
  border-radius: 8px;
  outline: none;
  background: #11121d;
  color: white;
}

input:focus {
  border-color: #a78bfa;
}

.error-message {
  color: #ff7777;
  font-size: 14px;
}

button {
  margin-top: 12px;
  padding: 14px;
  border: none;
  border-radius: 8px;
  background: #8b5cf6;
  color: white;
  font-size: 15px;
  font-weight: bold;
  cursor: pointer;
}

button:hover {
  background: #7c3aed;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.register-link {
  margin-top: 24px;
  text-align: center;
  color: #a1a1b5;
  font-size: 14px;
}

a {
  color: #a78bfa;
  text-decoration: none;
}
</style>
