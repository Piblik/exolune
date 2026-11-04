<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errorMessage = ref('')
const validationErrors = ref<Record<string, string[]>>({})

async function handleRegister() {
  errorMessage.value = ''
  validationErrors.value = {}

  if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Паролі не співпадають'
    return
  }

  try {
    await authStore.register(form)
    await router.push({ name: 'dashboard' })
  } catch (error: unknown) {
    const apiError = error as {
      response?: {
        data?: {
          message?: string
          errors?: Record<string, string[]>
        }
      }
    }

    errorMessage.value = apiError.response?.data?.message ?? 'Не вдалося зареєструватися'

    validationErrors.value = apiError.response?.data?.errors ?? {}
  }
}
</script>

<template>
  <main class="register-page">
    <section class="register-card">
      <h1>exolune RPG</h1>
      <p class="subtitle">Створи свого персонажа та почни власну пригоду</p>

      <form @submit.prevent="handleRegister">
        <label for="name">Ім'я персонажа</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          placeholder="Введи своє ім'я"
          required
          maxlength="50"
        />
        <small v-if="validationErrors.name">
          {{ validationErrors.name[0] }}
        </small>

        <label for="email">Електронна пошта</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          placeholder="example@email.com"
          required
        />
        <small v-if="validationErrors.email">
          {{ validationErrors.email[0] }}
        </small>

        <label for="password">Пароль</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          placeholder="Мінімум 8 символів"
          required
          minlength="8"
        />
        <small v-if="validationErrors.password">
          {{ validationErrors.password[0] }}
        </small>

        <label for="password_confirmation">Підтвердження пароля</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          placeholder="Повтори пароль"
          required
        />

        <p v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="authStore.isLoading">
          {{ authStore.isLoading ? 'Створення акаунта...' : 'Створити акаунт' }}
        </button>
      </form>

      <p class="login-link">
        Уже маєш акаунт?
        <RouterLink to="/login">Увійти</RouterLink>
      </p>
    </section>
  </main>
</template>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
  background: #10111b;
  color: #ffffff;
  font-family: Arial, sans-serif;
}

.register-card {
  width: 100%;
  max-width: 420px;
  padding: 36px;
  border: 1px solid #303248;
  border-radius: 16px;
  background: #191a29;
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
  line-height: 1.5;
}

form {
  display: flex;
  flex-direction: column;
  gap: 10px;
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

small {
  color: #ff7777;
}

.error-message {
  color: #ff7777;
  font-size: 14px;
}

button {
  margin-top: 16px;
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

.login-link {
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
