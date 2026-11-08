<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { Crown, Sparkles, Target, TrendingUp, Swords, Brain, Flame } from 'lucide-vue-next'

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
    total?: number
  }
}

const authStore = useAuthStore()
const logs = ref<ExperienceLog[]>([])
const isLoading = ref(true)
const error = ref('')

const character = computed(() => authStore.user?.character)

const totalXP = computed(() => logs.value.reduce((sum, log) => sum + log.experience, 0))

const totalStrength = computed(() => logs.value.reduce((sum, log) => sum + log.strength_change, 0))

const totalIntelligence = computed(() =>
  logs.value.reduce((sum, log) => sum + log.intelligence_change, 0),
)

const totalDiscipline = computed(() =>
  logs.value.reduce((sum, log) => sum + log.discipline_change, 0),
)

const activityCount = computed(() => logs.value.length)

const averageXP = computed(() =>
  activityCount.value ? Math.round(totalXP.value / activityCount.value) : 0,
)

const chartScale = computed(() => {
  const max = Math.max(...dailyXP.value.map((day) => day.experience), 1)

  const roughStep = max / 4
  const magnitude = Math.pow(10, Math.floor(Math.log10(roughStep)))
  const normalized = roughStep / magnitude

  let niceStep: number

  if (normalized <= 1) {
    niceStep = 1
  } else if (normalized <= 2) {
    niceStep = 2
  } else if (normalized <= 5) {
    niceStep = 5
  } else {
    niceStep = 10
  }

  const step = niceStep * magnitude

  return {
    max: step * 4,
    ticks: [4, 3, 2, 1, 0].map((value) => value * step),
  }
})

const dailyXP = computed(() => {
  const grouped = new Map<string, number>()

  logs.value.forEach((log) => {
    const date = new Date(log.created_at)

    const dayKey = [
      date.getFullYear(),
      String(date.getMonth() + 1).padStart(2, '0'),
      String(date.getDate()).padStart(2, '0'),
    ].join('-')

    grouped.set(dayKey, (grouped.get(dayKey) ?? 0) + log.experience)
  })

  return Array.from(grouped.entries())
    .sort(([a], [b]) => a.localeCompare(b))
    .slice(-7)
    .map(([date, experience]) => ({
      date,
      experience,
    }))
})

function barHeight(xp: number) {
  return `${(xp / chartScale.value.max) * 100}%`
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('uk-UA', {
    day: '2-digit',
    month: 'short',
  })
}

async function loadStatistics() {
  isLoading.value = true
  error.value = ''

  try {
    await authStore.fetchUser()

    const response = await api.get<HistoryResponse>('/api/experience-logs')

    logs.value = response.data.data.data
  } catch {
    error.value = 'Не вдалося завантажити статистику.'
  } finally {
    isLoading.value = false
  }
}

onMounted(loadStatistics)
</script>

<template>
  <main class="statistics-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">АНАЛІТИКА ПЕРСОНАЖА</p>
        <h1>Статистика</h1>
        <p class="subtitle">Аналізуй свій розвиток та відстежуй прогрес.</p>
      </div>

      <RouterLink to="/character" class="back-link"> ← До персонажа </RouterLink>
    </header>

    <div v-if="isLoading" class="message">Завантаження статистики...</div>

    <div v-else-if="error" class="error-message">
      {{ error }}
      <button @click="loadStatistics">Спробувати ще раз</button>
    </div>

    <template v-else>
      <section class="overview-grid">
        <article class="stat-card">
          <Crown class="stat-icon" :size="25" />
          <p>Поточний рівень</p>
          <strong>{{ character?.level ?? 1 }}</strong>
          <small>Рівень персонажа</small>
        </article>

        <article class="stat-card">
          <Sparkles class="stat-icon" :size="25" />
          <p>Загальний XP</p>
          <strong>{{ totalXP.toLocaleString('uk-UA') }}</strong>
          <small>Отримано за історію активності</small>
        </article>

        <article class="stat-card">
          <Target class="stat-icon" :size="25" />
          <p>Активності</p>
          <strong>{{ activityCount }}</strong>
          <small>Записів в історії</small>
        </article>

        <article class="stat-card">
          <TrendingUp class="stat-icon" :size="25" />
          <p>Середній XP</p>
          <strong>{{ averageXP }}</strong>
          <small>Досвіду за активність</small>
        </article>
      </section>

      <section class="content-grid">
        <article class="panel character-panel">
          <div class="panel-header">
            <div>
              <p class="eyebrow">РОЗВИТОК</p>
              <h2>Характеристики</h2>
            </div>
            <Swords class="panel-icon" :size="24" />
          </div>

          <div class="character-name">
            {{ character?.name ?? 'Персонаж' }}
          </div>

          <div class="attribute">
            <div class="attribute-info">
              <span><Swords :size="16" /> Сила</span>
              <strong>{{ character?.strength ?? 0 }}</strong>
            </div>
            <div class="progress-track">
              <div
                class="progress-fill strength"
                :style="{ width: `${Math.min((character?.strength ?? 0) / 10, 100)}%` }"
              ></div>
            </div>
            <small>+{{ totalStrength }} за історію</small>
          </div>

          <div class="attribute">
            <div class="attribute-info">
              <span><Brain :size="16" /> Інтелект</span>
              <strong>{{ character?.intelligence ?? 0 }}</strong>
            </div>
            <div class="progress-track">
              <div
                class="progress-fill intelligence"
                :style="{ width: `${Math.min((character?.intelligence ?? 0) / 10, 100)}%` }"
              ></div>
            </div>
            <small>+{{ totalIntelligence }} за історію</small>
          </div>

          <div class="attribute">
            <div class="attribute-info">
              <span><Flame :size="16" /> Дисципліна</span>
              <strong>{{ character?.discipline ?? 0 }}</strong>
            </div>
            <div class="progress-track">
              <div
                class="progress-fill discipline"
                :style="{ width: `${Math.min((character?.discipline ?? 0) / 10, 100)}%` }"
              ></div>
            </div>
            <small>+{{ totalDiscipline }} за історію</small>
          </div>
        </article>

        <article class="panel chart-panel">
          <div class="panel-header">
            <div>
              <p class="eyebrow">ДОСВІД</p>
              <h2>Останні активності</h2>
            </div>
            <TrendingUp class="panel-icon" :size="24" />
          </div>

          <div v-if="dailyXP.length" class="chart-container">
            <div class="chart">
              <div class="chart-grid">
                <div
                  v-for="value in chartScale.ticks"
                  :key="value"
                  class="chart-grid-line"
                  :style="{
                    bottom: `${(value / chartScale.max) * 100}%`,
                  }"
                >
                  <span class="chart-scale">{{ value }} XP</span>
                </div>
              </div>

              <div class="chart-columns">
                <div v-for="day in dailyXP" :key="day.date" class="chart-column">
                  <div class="bar-wrapper">
                    <div class="bar" :style="{ height: barHeight(day.experience) }"></div>
                  </div>

                  <small>{{ formatDate(day.date) }}</small>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">Поки немає активностей для побудови графіка.</div>
        </article>
      </section>

      <section class="panel activity-panel">
        <div class="panel-header">
          <div>
            <p class="eyebrow">ІСТОРІЯ</p>
            <h2>Останній прогрес</h2>
          </div>

          <RouterLink to="/history" class="all-link"> Уся історія → </RouterLink>
        </div>

        <div v-if="logs.length" class="activity-list">
          <article v-for="log in logs.slice(0, 5)" :key="log.id" class="activity-item">
            <div class="activity-info">
              <strong>{{ log.description }}</strong>
              <small>{{ formatDate(log.created_at) }}</small>
            </div>

            <div class="activity-rewards">
              <strong>+{{ log.experience }} XP</strong>
              <small class="reward-icons">
                <Swords :size="13" /> +{{ log.strength_change }} <Brain :size="13" /> +{{
                  log.intelligence_change
                }}
                <Flame :size="13" /> +{{ log.discipline_change }}
              </small>
            </div>
          </article>
        </div>

        <div v-else class="empty-state">Виконай перший квест, щоб побачити свій прогрес.</div>
      </section>
    </template>
  </main>
</template>

<style scoped>
.statistics-page {
  min-height: 100vh;
  padding: 40px;
  background: #100d1c;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.page-header,
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.page-header {
  margin-bottom: 32px;
}

.eyebrow {
  color: #a78bfa;
  font-size: 11px;
  letter-spacing: 2px;
  font-weight: bold;
}

h1 {
  margin: 10px 0;
  font-size: 36px;
}

h2 {
  margin: 8px 0 0;
  font-size: 21px;
}

.subtitle,
.stat-card small,
.attribute small,
.activity-info small {
  color: #aaa3bd;
}

.back-link,
.all-link {
  color: #c4b5fd;
  text-decoration: none;
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-bottom: 24px;
}

.stat-card,
.panel {
  background: #1b172b;
  border: 1px solid #342b4c;
  border-radius: 16px;
}

.stat-card {
  padding: 22px;
}

.stat-icon {
  color: #a78bfa;
  stroke-width: 1.8;
}

.panel-icon {
  color: #a78bfa;
  stroke-width: 1.8;
}

.attribute-info span {
  display: flex;
  align-items: center;
  gap: 8px;
}

.attribute-info span svg {
  color: #a78bfa;
  flex-shrink: 0;
}

.reward-icons {
  display: flex !important;
  align-items: center;
  justify-content: flex-end;
  gap: 5px;
}

.reward-icons svg {
  color: #a78bfa;
}

.stat-card p {
  color: #aaa3bd;
  margin: 16px 0 8px;
}

.stat-card strong {
  display: block;
  font-size: 30px;
}

.stat-card small {
  display: block;
  margin-top: 8px;
  font-size: 12px;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 20px;
  margin-bottom: 24px;
}

.panel {
  padding: 26px;
}

.character-name {
  margin: 28px 0;
  color: #c4b5fd;
  font-size: 18px;
}

.attribute {
  margin-bottom: 26px;
}

.attribute-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.progress-track {
  height: 9px;
  background: #302942;
  border-radius: 20px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 20px;
}

.strength {
  background: #fb7185;
}

.intelligence {
  background: #60a5fa;
}

.discipline {
  background: #a78bfa;
}

.attribute small {
  display: block;
  margin-top: 8px;
}

.chart-container {
  width: 100%;
  padding: 20px 4px 0;
  box-sizing: border-box;
}

.chart {
  height: 260px;
  position: relative;
  box-sizing: border-box;
}

.chart-grid {
  position: absolute;
  top: 0;
  bottom: 30px;
  left: 65px;
  right: 10px;
  pointer-events: none;
}

.chart-grid-line {
  position: absolute;
  left: 0;
  right: 0;
  border-top: 1px dashed #39324c;
}

.chart-scale {
  position: absolute;
  right: calc(100% + 10px);
  top: 0;
  transform: translateY(-50%);
  color: #aaa3bd;
  font-size: 11px;
  white-space: nowrap;
}

.chart-columns {
  position: absolute;
  top: 0;
  left: 65px;
  right: 10px;
  height: 260px;
  display: flex;
  justify-content: center;
  gap: 28px;
  z-index: 1;
}

.chart-column {
  flex: 0 0 64px;
  width: 64px;
  height: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.bar-wrapper {
  width: 36px;
  height: 230px;
  flex-shrink: 0;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.bar {
  width: 100%;
  min-height: 4px;
  background: linear-gradient(180deg, #c4b5fd, #7c3aed);
  border-radius: 8px 8px 3px 3px;
  transition: height 0.4s ease;
}

.chart-column small {
  height: 30px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #aaa3bd;
  font-size: 11px;
  white-space: nowrap;
}

.chart-column:hover .bar {
  filter: brightness(1.25);
}

.activity-panel {
  margin-bottom: 30px;
}

.activity-list {
  margin-top: 24px;
}

.activity-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 18px 0;
  border-bottom: 1px solid #342b4c;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-info strong,
.activity-info small,
.activity-rewards strong,
.activity-rewards small {
  display: block;
}

.activity-info small,
.activity-rewards small {
  margin-top: 7px;
  color: #aaa3bd;
  font-size: 12px;
}

.activity-rewards {
  text-align: right;
  white-space: nowrap;
}

.activity-rewards strong {
  color: #86efac;
}

.message,
.error-message,
.empty-state {
  padding: 24px;
  color: #aaa3bd;
}

.error-message {
  color: #fca5a5;
}

.error-message button {
  display: block;
  margin-top: 12px;
  padding: 10px 16px;
  border: 0;
  border-radius: 8px;
  background: #8b5cf6;
  color: white;
  cursor: pointer;
}

@media (max-width: 1000px) {
  .overview-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .statistics-page {
    padding: 24px 16px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .overview-grid {
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .stat-card,
  .panel {
    padding: 16px;
  }

  .stat-card strong {
    font-size: 23px;
  }

  .activity-item {
    align-items: flex-start;
    flex-direction: column;
  }

  .activity-rewards {
    text-align: left;
  }
}
</style>
