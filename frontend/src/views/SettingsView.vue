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
  color: #f5f2ff;
  box-sizing: border-box;
}

.page-header {
  margin-bottom: 28px;
}

.page-header h1 {
  margin: 0 0 8px;
  font-size: 28px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.page-header p,
.section-heading p {
  margin: 0;
  color: #929bb2;
  font-size: 14px;
  line-height: 1.5;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.settings-card {
  padding: 24px;
  background: #101625;
  border: 1px solid #252d41;
  border-radius: 14px;
}

.section-heading {
  margin-bottom: 24px;
}

.section-heading h2,
.password-card h2 {
  margin: 0 0 7px;
  font-size: 19px;
  font-weight: 650;
  color: #f5f2ff;
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
  color: #d9d5e8;
  font-size: 13px;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 14px;
  color: #f5f2ff;
  background: #090f1d;
  border: 1px solid #303950;
  border-radius: 9px;
  outline: none;
  font: inherit;
  font-size: 14px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.form-group input::placeholder {
  color: #68738c;
}

.form-group input:focus {
  border-color: #a78bfa;
  box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
}

.form-group input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.avatar-section {
  display: flex;
  align-items: center;
  gap: 22px;
  margin-bottom: 26px;
}

.avatar-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 104px;
  height: 104px;
  flex-shrink: 0;
  overflow: hidden;
  background: linear-gradient(145deg, #29213d, #171d30);
  border: 2px solid #7658b8;
  border-radius: 50%;
  font-size: 36px;
}

.avatar-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  min-width: 0;
}

.avatar-info input {
  max-width: 100%;
  color: #c5c9d8;
  font: inherit;
  font-size: 13px;
}

.avatar-info input::file-selector-button {
  padding: 8px 12px;
  margin-right: 10px;
  color: #c4a8ff;
  background: #211b36;
  border: 1px solid #49386e;
  border-radius: 7px;
  cursor: pointer;
}

.avatar-info small {
  color: #929bb2;
  font-size: 12px;
}

.delete-avatar-btn,
.delete-avatar-btn:focus,
.delete-avatar-btn:active {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-top: 4px;
  padding: 9px 13px;
  color: #fb7185 !important;
  background: rgba(251, 113, 133, 0.08) !important;
  border: 1px solid rgba(251, 113, 133, 0.25) !important;
  border-radius: 8px;
  font: inherit;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.delete-avatar-btn:hover {
  color: #ff9aaa !important;
  background: rgba(251, 113, 133, 0.15) !important;
  border-color: rgba(251, 113, 133, 0.55) !important;
}

.password-card {
  width: 100%;
  box-sizing: border-box;
}

.password-card h2 {
  margin-bottom: 7px;
}

.password-card > p {
  margin: 0;
  color: #929bb2;
  font-size: 14px;
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
  padding: 12px 14px;
  color: #f5f2ff;
  background: #090f1d;
  border: 1px solid #303950;
  border-radius: 9px;
  outline: none;
  font: inherit;
  font-size: 14px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.password-card .form-group input:focus {
  border-color: #a78bfa;
  box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
}

.success-message {
  margin: 0;
  color: #25c69a !important;
  font-size: 13px;
  line-height: 1.5;
}

.error-message {
  margin: 0;
  color: #fb7185 !important;
  font-size: 13px;
  line-height: 1.5;
}

.message {
  padding: 13px 16px;
  border: 1px solid transparent;
  border-radius: 9px;
  font-size: 14px;
  line-height: 1.5;
}

.message.success {
  color: #25c69a;
  background: rgba(37, 198, 154, 0.08);
  border-color: rgba(37, 198, 154, 0.2);
}

.message.error {
  color: #fb7185;
  background: rgba(251, 113, 133, 0.08);
  border-color: rgba(251, 113, 133, 0.2);
}

button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 20px;
  color: #fff;
  background: linear-gradient(135deg, #7658c9, #9270e8);
  border: 1px solid transparent;
  border-radius: 9px;
  font: inherit;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

button:hover:not(:disabled) {
  background: linear-gradient(135deg, #896be0, #a78bfa);
  box-shadow: 0 4px 16px rgba(167, 139, 250, 0.18);
  transform: translateY(-1px);
}

button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none;
}

.secondary-btn {
  align-self: flex-start;
  color: #c4a8ff !important;
  background: #211b36 !important;
  border: 1px solid #49386e !important;
}

.secondary-btn:hover:not(:disabled) {
  color: #fff !important;
  background: #30264b !important;
  border-color: #7658b8 !important;
}

.settings-form > button[type='submit'] {
  align-self: flex-start;
  min-width: 170px;
}

@media (max-width: 900px) {
  .settings-page {
    padding: 24px;
  }
}

@media (max-width: 600px) {
  .settings-page {
    padding: 20px 15px;
  }

  .page-header {
    margin-bottom: 22px;
  }

  .page-header h1 {
    font-size: 24px;
  }

  .settings-form {
    gap: 15px;
  }

  .settings-card {
    padding: 18px;
    border-radius: 12px;
  }

  .section-heading {
    margin-bottom: 20px;
  }

  .section-heading h2,
  .password-card h2 {
    font-size: 17px;
  }

  .avatar-section {
    align-items: flex-start;
    flex-direction: column;
    gap: 16px;
  }

  .avatar-preview {
    width: 88px;
    height: 88px;
    font-size: 30px;
  }

  .avatar-info {
    width: 100%;
  }

  .avatar-info input {
    width: 100%;
  }

  .password-card form {
    margin-top: 20px;
  }

  .settings-form > button[type='submit'] {
    width: 100%;
  }

  .secondary-btn {
    width: 100%;
  }
}

@media (max-width: 380px) {
  .settings-page {
    padding: 16px 12px;
  }

  .settings-card {
    padding: 15px;
  }

  .form-group input,
  .password-card .form-group input {
    padding: 11px 12px;
  }
}
</style>
