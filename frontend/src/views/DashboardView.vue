<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const character = computed(() => user.value?.character)

async function handleLogout() {
  try {
    await authStore.logout()
    await router.push({ name: 'login' })
  } catch {
    alert('Не вдалося вийти з акаунта. Спробуй ще раз.')
  }
}
</script>

<template>
  <main class="dashboard">
    <header class="header">
      <h1>exolune RPG</h1>

      <button class="logout-button" @click="handleLogout">Вийти</button>
    </header>

    <section v-if="character" class="content">
      <div class="welcome">
        <p>ТВОЯ ПРИГОДА ПРОДОВЖУЄТЬСЯ</p>
        <h2>Вітаємо, {{ user?.name }}!</h2>
        <span>Твій персонаж готовий до нових випробувань.</span>
      </div>

      <section class="character-card">
        <div class="character-info">
          <div class="avatar">
            {{ character.name.charAt(0).toUpperCase() }}
          </div>

          <div>
            <h2>{{ character.name }}</h2>
            <p>Рівень {{ character.level }}</p>
          </div>
        </div>

        <div class="experience">
          <div class="experience-label">
            <span>Досвід</span>
            <span>{{ character.experience }} XP</span>
          </div>

          <div class="progress-bar">
            <div class="progress-fill"></div>
          </div>

          <small>Поточний досвід персонажа</small>
        </div>
      </section>

      <section class="stats-section">
        <h2>Характеристики персонажа</h2>

        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-icon">⚔️</span>
            <h3>Сила</h3>
            <strong>{{ character.strength }}</strong>
          </div>

          <div class="stat-card">
            <span class="stat-icon">🧠</span>
            <h3>Інтелект</h3>
            <strong>{{ character.intelligence }}</strong>
          </div>

          <div class="stat-card">
            <span class="stat-icon">🔥</span>
            <h3>Дисципліна</h3>
            <strong>{{ character.discipline }}</strong>
          </div>
        </div>
      </section>
    </section>

    <section v-else class="loading">Завантаження персонажа...</section>
  </main>
</template>

<style scoped>
.dashboard {
  min-height: 100vh;
  padding: 30px 7%;
  box-sizing: border-box;
  background: #10111b;
  color: white;
  font-family: Arial, sans-serif;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #303248;
  padding-bottom: 20px;
}

.header h1 {
  color: #a78bfa;
  font-size: 28px;
}

.logout-button {
  padding: 10px 22px;
  border: 1px solid #8b5cf6;
  border-radius: 8px;
  background: transparent;
  color: white;
  cursor: pointer;
}

.logout-button:hover {
  background: #8b5cf6;
}

.content {
  max-width: 1000px;
  margin: 50px auto;
}

.welcome {
  margin-bottom: 35px;
}

.welcome p {
  color: #a78bfa;
  font-size: 12px;
  letter-spacing: 2px;
}

.welcome h2 {
  font-size: 32px;
  margin: 12px 0;
}

.welcome span {
  color: #a1a1b5;
}

.character-card {
  padding: 28px;
  border: 1px solid #393b52;
  border-radius: 16px;
  background: #191a29;
}

.character-info {
  display: flex;
  align-items: center;
  gap: 18px;
}

.avatar {
  width: 65px;
  height: 65px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  background: #8b5cf6;
  font-size: 28px;
  font-weight: bold;
}

.character-info h2 {
  margin: 0 0 8px;
}

.character-info p {
  margin: 0;
  color: #a78bfa;
}

.experience {
  margin-top: 30px;
}

.experience-label {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.progress-bar {
  height: 10px;
  border-radius: 10px;
  background: #303248;
  overflow: hidden;
}

.progress-fill {
  width: 0%;
  height: 100%;
  background: #a78bfa;
}

.experience small {
  display: block;
  margin-top: 10px;
  color: #a1a1b5;
}

.stats-section {
  margin-top: 40px;
}

.stats-section h2 {
  margin-bottom: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.stat-card {
  padding: 24px;
  border: 1px solid #393b52;
  border-radius: 14px;
  background: #191a29;
}

.stat-icon {
  font-size: 26px;
}

.stat-card h3 {
  color: #a1a1b5;
  font-size: 15px;
}

.stat-card strong {
  font-size: 30px;
  color: #a78bfa;
}

.loading {
  margin-top: 100px;
  text-align: center;
  color: #a1a1b5;
}

@media (max-width: 600px) {
  .dashboard {
    padding: 20px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .welcome h2 {
    font-size: 25px;
  }
}
</style>
