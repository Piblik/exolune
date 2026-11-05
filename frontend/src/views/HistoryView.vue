<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '../services/api'

interface ExperienceLog {
  id: number
  source: string
  description: string
  experience: number
  strength_change: number
  intelligence_change: number
  discipline_change: number
  created_at: string
}

interface HistoryResponse {
  success: boolean
  data: {
    data: ExperienceLog[]
    current_page: number
    last_page: number
  }
}

const logs = ref<ExperienceLog[]>([])
const isLoading = ref(true)
const error = ref('')

async function loadHistory() {
  isLoading.value = true
  error.value = ''

  try {
    const response = await api.get<HistoryResponse>('/api/experience-logs')
    logs.value = response.data.data.data
  } catch {
    error.value = 'Не вдалося завантажити історію активності.'
  } finally {
    isLoading.value = false
  }
}

function formatDate(date: string) {
  return new Date(date).toLocaleString('uk-UA', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(loadHistory)
</script>

<template>
  <main class="history-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">ТВІЙ ПРОГРЕС</p>
        <h1>Історія активності</h1>
        <p class="subtitle">Переглядай свої досягнення та розвиток персонажа.</p>
      </div>

      <RouterLink to="/dashboard" class="back-link"> ← До персонажа </RouterLink>
    </header>

    <div v-if="isLoading" class="message">Завантаження історії...</div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else-if="logs.length === 0" class="message">
      Історія поки порожня. Виконай свій перший квест!
    </div>

    <section v-else class="history-list">
      <article v-for="log in logs" :key="log.id" class="history-card">
        <div class="history-top">
          <div>
            <span class="source">{{ log.source }}</span>
            <h2>{{ log.description }}</h2>
            <p class="date">{{ formatDate(log.created_at) }}</p>
          </div>

          <strong class="experience"> +{{ log.experience }} XP </strong>
        </div>

        <div class="rewards">
          <span>⚔️ Сила: +{{ log.strength_change }}</span>
          <span>🧠 Інтелект: +{{ log.intelligence_change }}</span>
          <span>🔥 Дисципліна: +{{ log.discipline_change }}</span>
        </div>
      </article>
    </section>
  </main>
</template>

<style scoped>
.history-page {
  min-height: 100vh;
  padding: 48px;
  background: #100d1c;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  margin-bottom: 36px;
}

.eyebrow {
  color: #a78bfa;
  font-size: 12px;
  letter-spacing: 2px;
  font-weight: bold;
}

h1 {
  margin: 10px 0;
  font-size: 36px;
}

.subtitle,
.date {
  color: #aaa3bd;
}

.back-link {
  color: #c4b5fd;
  text-decoration: none;
}

.history-list {
  display: grid;
  gap: 18px;
  max-width: 1000px;
}

.history-card {
  padding: 24px;
  background: #1b172b;
  border: 1px solid #342b4c;
  border-radius: 16px;
}

.history-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
}

.source {
  color: #a78bfa;
  font-size: 12px;
  text-transform: uppercase;
}

.history-card h2 {
  margin: 10px 0;
  font-size: 20px;
}

.date {
  font-size: 13px;
}

.experience {
  color: #86efac;
  white-space: nowrap;
}

.rewards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid #342b4c;
  color: #c4b5fd;
  font-size: 14px;
}

.message,
.error-message {
  padding: 20px;
  color: #c4b5fd;
}

.error-message {
  color: #fca5a5;
}

@media (max-width: 600px) {
  .history-page {
    padding: 24px 16px;
  }

  .page-header,
  .history-top {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
