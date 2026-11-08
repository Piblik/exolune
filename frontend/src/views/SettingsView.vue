<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { passwordService } from '../services/password'
import type { ChangePasswordData } from '../services/password'
import { profileService } from '../services/profile'

const authStore = useAuthStore()

const user = computed(() => authStore.user)
const character = computed(() => authStore.user?.character)

const name = ref('')
const email = ref('')
const characterName = ref('')
const avatar = ref<File | null>(null)
const avatarPreview = ref<string | null>(null)

const successMessage = ref('')
const errorMessage = ref('')

const passwordForm = ref<ChangePasswordData>({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const passwordMessage = ref('')
const passwordError = ref('')
const isChangingPassword = ref(false)

onMounted(() => {
  if (user.value && character.value) {
    name.value = user.value.name
    email.value = user.value.email
    characterName.value = character.value.name
  }
})

function handleAvatarChange(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'Будь ласка, вибери зображення.'
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    errorMessage.value = 'Розмір зображення не повинен перевищувати 2 МБ.'
    return
  }

  avatar.value = file
  avatarPreview.value = URL.createObjectURL(file)
  errorMessage.value = ''
}

async function saveProfile() {
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await authStore.updateProfile({
      name: name.value,
      email: email.value,
      character_name: characterName.value,
      avatar: avatar.value,
    })

    successMessage.value = 'Профіль успішно оновлено!'
    avatar.value = null
  } catch (error: unknown) {
    const apiError = error as {
      response?: {
        data?: {
          message?: string
        }
      }
    }

    errorMessage.value = apiError.response?.data?.message ?? 'Не вдалося оновити профіль.'
  }
}

async function changePassword() {
  passwordMessage.value = ''
  passwordError.value = ''

  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    passwordError.value = 'Нові паролі не збігаються.'
    return
  }

  isChangingPassword.value = true

  try {
    passwordMessage.value = await passwordService.changePassword(passwordForm.value)

    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    }
  } catch (err: unknown) {
    const error = err as {
      response?: {
        data?: {
          message?: string
          errors?: Record<string, string[]>
        }
      }
    }

    console.log('Password change error:', error.response?.data)

    passwordError.value =
      Object.values(error.response?.data?.errors ?? {})
        .flat()
        .join(' ') ||
      error.response?.data?.message ||
      'Не вдалося змінити пароль.'
  } finally {
    isChangingPassword.value = false
  }
}

async function deleteAvatar() {
  passwordError.value = ''
  passwordMessage.value = ''

  try {
    const updatedUser = await profileService.deleteAvatar()

    authStore.user = updatedUser
    avatarPreview.value = null

    passwordMessage.value = 'Аватар успішно видалено!'
  } catch (err: unknown) {
    const error = err as {
      response?: {
        data?: {
          message?: string
        }
      }
    }

    passwordError.value = error.response?.data?.message ?? 'Не вдалося видалити аватар.'
  }
}

const currentAvatar = computed(() => {
  if (avatarPreview.value) return avatarPreview.value

  const path = character.value?.avatar

  if (!path) return null

  return `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/storage/${path}`
})
</script>

<template>
  <div class="settings-page">
    <div class="page-header">
      <div>
        <h1>Налаштування</h1>
        <p>Керуй своїм акаунтом та персонажем</p>
      </div>
    </div>

    <form class="settings-form" @submit.prevent="saveProfile">
      <section class="settings-card">
        <div class="section-heading">
          <h2>Особисті дані</h2>
          <p>Інформація твого облікового запису</p>
        </div>

        <div class="form-group">
          <label for="name">Ім'я користувача</label>
          <input
            id="name"
            v-model="name"
            type="text"
            maxlength="50"
            required
            placeholder="Введи своє ім'я"
          />
        </div>

        <div class="form-group">
          <label for="email">Електронна пошта</label>
          <input id="email" v-model="email" type="email" required placeholder="example@email.com" />
        </div>
      </section>

      <section class="settings-card">
        <div class="section-heading">
          <h2>Мій персонаж</h2>
          <p>Налаштуй зовнішній вигляд свого героя</p>
        </div>

        <div class="avatar-section">
          <div class="avatar-preview">
            <img v-if="currentAvatar" :src="currentAvatar" alt="Аватар персонажа" />
            <span v-else>⚔️</span>
          </div>

          <div class="avatar-info">
            <label for="avatar">Аватар персонажа</label>
            <input
              id="avatar"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              @change="handleAvatarChange"
            />
            <small>JPG, PNG або WEBP. Максимум 2 МБ.</small>
            <button
              v-if="character?.avatar || avatarPreview"
              type="button"
              class="delete-avatar-btn"
              @click="deleteAvatar"
            >
              Видалити аватар
            </button>
          </div>
        </div>

        <div class="form-group">
          <label for="characterName">Ім'я персонажа</label>
          <input
            id="characterName"
            v-model="characterName"
            type="text"
            maxlength="50"
            required
            placeholder="Введи ім'я персонажа"
          />
        </div>
      </section>

      <section class="settings-card password-card">
        <h2>Безпека акаунта</h2>
        <p>Зміна пароля для захисту твого акаунта.</p>

        <form @submit.prevent="changePassword">
          <div class="form-group">
            <label for="current-password">Поточний пароль</label>
            <input
              id="current-password"
              v-model="passwordForm.current_password"
              type="password"
              autocomplete="current-password"
              required
            />
          </div>

          <div class="form-group">
            <label for="new-password">Новий пароль</label>
            <input
              id="new-password"
              v-model="passwordForm.password"
              type="password"
              autocomplete="new-password"
              minlength="8"
              required
            />
          </div>

          <div class="form-group">
            <label for="confirm-password">Підтвердження нового пароля</label>
            <input
              id="confirm-password"
              v-model="passwordForm.password_confirmation"
              type="password"
              autocomplete="new-password"
              minlength="8"
              required
            />
          </div>

          <p v-if="passwordMessage" class="success-message">
            {{ passwordMessage }}
          </p>

          <p v-if="passwordError" class="error-message">
            {{ passwordError }}
          </p>

          <button type="submit" class="secondary-btn" :disabled="isChangingPassword">
            {{ isChangingPassword ? 'Збереження...' : 'Змінити пароль' }}
          </button>
        </form>
      </section>

      <div v-if="successMessage" class="message success">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="message error">
        {{ errorMessage }}
      </div>

      <button type="submit" :disabled="authStore.isLoading">
        {{ authStore.isLoading ? 'Збереження...' : 'Зберегти зміни' }}
      </button>
    </form>
  </div>
</template>

<style scoped>
.settings-page {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: 30px;
  color: #fff;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  margin: 0 0 8px;
  font-size: 28px;
}

.page-header p,
.section-heading p {
  margin: 0;
  color: #9292a8;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.settings-card {
  padding: 25px;
  background: #191927;
  border: 1px solid #303047;
  border-radius: 16px;
}

.section-heading {
  margin-bottom: 24px;
}

.section-heading h2 {
  margin: 0 0 8px;
  font-size: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 9px;
  margin-bottom: 20px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label,
.avatar-info label {
  font-size: 14px;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  box-sizing: border-box;
  padding: 13px 15px;
  color: #fff;
  background: #10101b;
  border: 1px solid #35354d;
  border-radius: 9px;
  outline: none;
}

.form-group input:focus {
  border-color: #9270ff;
}

.avatar-section {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 25px;
}

.avatar-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 100px;
  flex-shrink: 0;
  overflow: hidden;
  background: #29233f;
  border: 2px solid #7657d9;
  border-radius: 50%;
  font-size: 35px;
}

.avatar-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-info {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
}

.avatar-info input {
  max-width: 100%;
  font-size: 13px;
}

.avatar-info small {
  color: #9292a8;
}

.delete-avatar-btn,
.delete-avatar-btn:focus,
.delete-avatar-btn:active {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 12px;
  padding: 10px 16px;

  border: 1px solid rgba(255, 107, 129, 0.3) !important;
  border-radius: 10px;

  background: rgba(255, 107, 129, 0.06) !important;
  color: #ff8b9b !important;

  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.delete-avatar-btn:hover {
  border-color: rgba(255, 107, 129, 0.8) !important;
  background: rgba(255, 107, 129, 0.18) !important;
  color: #ff647d !important;
  box-shadow: 0 4px 14px rgba(255, 107, 129, 0.12);
  transform: translateY(-1px);
}

.secondary-btn {
  background: #29233f !important;
  border: 1px solid #7657d9 !important;
  color: #c9b8ff !important;
}

.secondary-btn:hover:not(:disabled) {
  background: #352b50 !important;
  border-color: #9270ff !important;
  color: #fff !important;
}

.message {
  padding: 13px 16px;
  border-radius: 9px;
  font-size: 14px;
}

.success {
  color: #72e6a3;
  background: #163426;
}

.error {
  color: #ff8585;
  background: #3b2025;
}

button {
  padding: 14px 22px;
  color: #fff;
  background: #7657d9;
  border: none;
  border-radius: 9px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

button:hover:not(:disabled) {
  background: #8969ed;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 600px) {
  .settings-page {
    padding: 20px 15px;
  }

  .settings-card {
    padding: 18px;
  }

  .avatar-section {
    align-items: flex-start;
    flex-direction: column;
  }
}

.password-card {
  width: 100%;
  box-sizing: border-box;
}

.password-card h2 {
  margin: 0 0 8px;
  font-size: 20px;
}

.password-card > p {
  margin: 0;
  color: #9292a8;
}

.password-card form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 24px;
}

.password-card .form-group {
  margin-bottom: 0;
}

.password-card .form-group input {
  width: 100%;
  box-sizing: border-box;
  padding: 13px 15px;
  color: #fff;
  background: #10101b;
  border: 1px solid #35354d;
  border-radius: 9px;
  outline: none;
}

.password-card .form-group input:focus {
  border-color: #9270ff;
}

.success-message {
  color: #65d99b !important;
}

.error-message {
  color: #ff7373 !important;
}
</style>
