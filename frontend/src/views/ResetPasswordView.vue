<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const router = useRouter()

const email = ref(String(route.query.email || ''))
const token = ref(String(route.query.token || ''))
const password = ref('')
const passwordConfirmation = ref('')

const isLoading = ref(false)
const message = ref('')
const error = ref('')

const submitForm = async () => {
  message.value = ''
  error.value = ''

  if (!token.value || !email.value) {
    error.value = 'Посилання для відновлення пароля недійсне.'
    return
  }

  isLoading.value = true

  try {
    const response = await api.post('/api/reset-password', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    const data = response.data

    message.value = data.message

    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Сталася помилка. Спробуйте ще раз.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <div class="auth-card">
      <RouterLink to="/login" class="back-link"> ← Назад до входу </RouterLink>

      <div class="logo">EXOLUNE</div>

      <h1>Новий пароль</h1>

      <p class="description">Створіть новий пароль для свого облікового запису.</p>

      <form @submit.prevent="submitForm">
        <label for="password">Новий пароль</label>

        <input
          id="password"
          v-model="password"
          type="password"
          placeholder="Мінімум 8 символів"
          minlength="8"
          required
        />

        <label for="password-confirmation">Підтвердження пароля</label>

        <input
          id="password-confirmation"
          v-model="passwordConfirmation"
          type="password"
          placeholder="Повторіть новий пароль"
          minlength="8"
          required
        />

        <p v-if="message" class="success-message">
          {{ message }} Перенаправляємо на сторінку входу...
        </p>

        <p v-if="error" class="error-message">
          {{ error }}
        </p>

        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Збереження...' : 'Змінити пароль' }}
        </button>
      </form>
    </div>
  </main>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
  background: #090d18;
  color: #f5f2ff;
  font-family: 'Manrope', sans-serif;
}

.auth-card {
  width: 100%;
  max-width: 460px;
  padding: 40px;
  border: 1px solid #252d41;
  border-radius: 15px;
  background: #101625;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
}

.back-link {
  display: inline-block;
  color: #a78bfa;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: color 0.2s;
}

.back-link:hover {
  color: #c4a8ff;
}

.logo {
  margin: 35px 0 24px;
  color: #a78bfa;
  font-size: 22px;
  font-weight: 800;
  letter-spacing: 4px;
}

h1 {
  margin: 0 0 12px;
  color: #f5f2ff;
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.7px;
}

.description {
  margin: 0 0 28px;
  color: #929bb2;
  font-size: 13px;
  line-height: 1.7;
}

form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

label {
  margin-top: 5px;
  color: #d8d2e6;
  font-size: 12px;
  font-weight: 600;
}

input {
  width: 100%;
  padding: 13px 14px;
  border: 1px solid #252d41;
  border-radius: 9px;
  outline: none;
  background: #151d2d;
  color: #f5f2ff;
  font-family: inherit;
  font-size: 13px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

input::placeholder {
  color: #778198;
}

input:focus {
  border-color: #8b6bd1;
  box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
}

button {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 10px;
  padding: 14px;
  border: none;
  border-radius: 9px;
  background: linear-gradient(135deg, #8b5cf6, #7041d9);
  color: #fff;
  font-family: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition:
    transform 0.2s,
    box-shadow 0.2s,
    opacity 0.2s;
}

button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-message,
.error-message {
  margin: 5px 0;
  padding: 11px 13px;
  border-radius: 8px;
  font-size: 12px;
  line-height: 1.6;
}

.success-message {
  border: 1px solid rgba(37, 198, 154, 0.2);
  background: rgba(37, 198, 154, 0.08);
  color: #25c69a;
}

.error-message {
  border: 1px solid rgba(251, 113, 133, 0.2);
  background: rgba(251, 113, 133, 0.08);
  color: #fb7185;
}

@media (max-width: 480px) {
  .auth-page {
    padding: 16px;
  }

  .auth-card {
    padding: 26px 22px;
  }

  .logo {
    margin-top: 28px;
  }

  h1 {
    font-size: 25px;
  }
}

@media (max-width: 360px) {
  .auth-card {
    padding: 23px 18px;
  }
}
</style>
