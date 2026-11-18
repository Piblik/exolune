<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../services/api'

const email = ref('')
const isLoading = ref(false)
const message = ref('')
const error = ref('')

const submitForm = async () => {
  message.value = ''
  error.value = ''
  isLoading.value = true

  try {
    const response = await api.post('/api/forgot-password', {
      email: email.value,
    })

    const data = response.data

    message.value = data.message
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

      <h1>Забули пароль?</h1>

      <p class="description">
        Введіть електронну пошту, яку використовували під час реєстрації. Ми надішлемо вам посилання
        для відновлення пароля.
      </p>

      <form @submit.prevent="submitForm">
        <label for="email">Електронна пошта</label>

        <input id="email" v-model="email" type="email" placeholder="example@email.com" required />

        <p v-if="message" class="success-message">
          {{ message }}
        </p>

        <p v-if="error" class="error-message">
          {{ error }}
        </p>

        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Надсилання...' : 'Надіслати посилання' }}
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
  box-sizing: border-box;
  padding: 24px;
  background: #090d18;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.auth-card {
  width: 100%;
  max-width: 440px;
  box-sizing: border-box;
  padding: 36px;
  border: 1px solid #252d41;
  border-radius: 14px;
  background: #101625;
  box-shadow: 0 15px 45px #00000030;
}

.back-link {
  color: #a78bfa;
  text-decoration: none;
  font-size: 12px;
  transition: color 0.2s;
}

.back-link:hover {
  color: #c4a8ff;
}

.logo {
  margin: 34px 0 24px;
  color: #c4a8ff;
  font-size: 21px;
  font-weight: 800;
  letter-spacing: 4px;
}

h1 {
  margin: 0 0 12px;
  color: #f5f2ff;
  font-size: 25px;
}

.description {
  margin: 0 0 26px;
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
  color: #d8dced;
  font-size: 12px;
  font-weight: bold;
}

input {
  width: 100%;
  box-sizing: border-box;
  padding: 13px 14px;
  border: 1px solid #252d41;
  border-radius: 8px;
  outline: none;
  background: #090d18;
  color: #f5f2ff;
  font-family: inherit;
  font-size: 13px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

input::placeholder {
  color: #737d96;
}

input:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 2px #8b5cf622;
}

button {
  margin-top: 8px;
  padding: 13px;
  border: 1px solid #9b79ed;
  border-radius: 8px;
  background: linear-gradient(135deg, #8b5cf6, #7044cb);
  color: #fff;
  font-size: 12px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
}

button:hover:not(:disabled) {
  background: linear-gradient(135deg, #9b70ff, #8055df);
  box-shadow: 0 4px 16px #8b5cf633;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-message {
  margin: 4px 0;
  padding: 11px;
  border: 1px solid #245b4d;
  border-radius: 8px;
  background: #142b28;
  color: #25c69a;
  font-size: 12px;
  line-height: 1.5;
}

.error-message {
  margin: 4px 0;
  padding: 11px;
  border: 1px solid #653044;
  border-radius: 8px;
  background: #281722;
  color: #fb7185;
  font-size: 12px;
  line-height: 1.5;
}

@media (max-width: 600px) {
  .auth-page {
    padding: 16px;
  }

  .auth-card {
    padding: 26px 22px;
    border-radius: 12px;
  }

  .logo {
    margin-top: 28px;
    font-size: 19px;
  }

  h1 {
    font-size: 23px;
  }

  .description {
    font-size: 12px;
  }

  input {
    font-size: 16px;
  }
}

@media (max-width: 360px) {
  .auth-card {
    padding: 22px 17px;
  }
}
</style>
