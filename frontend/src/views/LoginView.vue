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

        <label for="password">Пароль</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          placeholder="Введи пароль"
          autocomplete="current-password"
          required
        />

        <RouterLink to="/forgot-password" class="forgot-password-link"> Забули пароль? </RouterLink>

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
  background: #090d18;
  color: #f5f2ff;
  font-family: inherit;
}

.login-card {
  width: 100%;
  max-width: 420px;
  padding: 36px;
  border: 1px solid #252d41;
  border-radius: 15px;
  background: #101625;
  box-sizing: border-box;
  box-shadow: 0 16px 45px rgba(0, 0, 0, 0.2);
}

h1 {
  margin: 0;
  text-align: center;
  font-size: 32px;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: #a78bfa;
}

.subtitle {
  margin: 12px 0 28px;
  text-align: center;
  color: #929bb2;
  font-size: 14px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

label {
  margin-top: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #c5cbe0;
}

input {
  width: 100%;
  box-sizing: border-box;
  padding: 13px 14px;
  border: 1px solid #303a52;
  border-radius: 9px;
  outline: none;
  background: #0b1120;
  color: #f5f2ff;
  font-size: 14px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

input::placeholder {
  color: #68738d;
}

input:focus {
  border-color: #a78bfa;
  box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.12);
}

.error-message {
  margin: 4px 0 0;
  padding: 11px 13px;
  border: 1px solid rgba(251, 113, 133, 0.25);
  border-radius: 8px;
  background: rgba(251, 113, 133, 0.08);
  color: #fb7185;
  font-size: 13px;
  line-height: 1.5;
}

button {
  margin-top: 12px;
  padding: 14px;
  border: 1px solid transparent;
  border-radius: 9px;
  background: #8b5cf6;
  color: #ffffff;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition:
    background 0.2s,
    transform 0.2s,
    box-shadow 0.2s;
}

button:hover:not(:disabled) {
  background: #7c3aed;
  box-shadow: 0 5px 18px rgba(139, 92, 246, 0.2);
  transform: translateY(-1px);
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.register-link {
  margin-top: 24px;
  text-align: center;
  color: #929bb2;
  font-size: 13px;
  line-height: 1.6;
}

a {
  color: #a78bfa;
  text-decoration: none;
  transition: color 0.2s;
}

a:hover {
  color: #c4a8ff;
}

.forgot-password-link {
  align-self: flex-end;
  margin-top: 2px;
  font-size: 13px;
  color: #a78bfa;
  text-decoration: none;
}

.forgot-password-link:hover {
  color: #c4a8ff;
  text-decoration: underline;
}

@media (max-width: 480px) {
  .login-page {
    padding: 16px;
  }

  .login-card {
    padding: 28px 22px;
  }

  h1 {
    font-size: 28px;
  }
}

@media (max-width: 360px) {
  .login-card {
    padding: 24px 18px;
  }

  h1 {
    font-size: 25px;
  }
}
</style>
