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
  width: 100%;
  max-width: 1200px;
  min-height: 100vh;
  margin: 0 auto;
  padding: 30px;
  box-sizing: border-box;
  color: #f5f2ff;
}

.page-header,
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.page-header {
  margin-bottom: 28px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #a78bfa;
  font-size: 11px;
  letter-spacing: 1.8px;
  font-weight: 700;
}

h1 {
  margin: 0 0 8px;
  font-size: 28px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

h2 {
  margin: 0;
  color: #f5f2ff;
  font-size: 19px;
  font-weight: 650;
}

.subtitle {
  margin: 0;
  color: #929bb2;
  font-size: 14px;
  line-height: 1.5;
}

.back-link,
.all-link {
  color: #c4a8ff;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.back-link:hover,
.all-link:hover {
  color: #fff;
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.stat-card,
.panel {
  background: #101625;
  border: 1px solid #252d41;
  border-radius: 14px;
}

.stat-card {
  min-width: 0;
  padding: 20px;
  transition:
    border-color 0.2s,
    transform 0.2s;
}

.stat-card:hover {
  border-color: #49386e;
  transform: translateY(-2px);
}

.stat-icon,
.panel-icon {
  color: #a78bfa;
  stroke-width: 1.8;
}

.stat-card p {
  margin: 15px 0 7px;
  color: #929bb2;
  font-size: 13px;
}

.stat-card strong {
  display: block;
  color: #f5f2ff;
  font-size: 28px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.stat-card small {
  display: block;
  margin-top: 7px;
  color: #77829b;
  font-size: 11px;
  line-height: 1.5;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  gap: 18px;
  margin-bottom: 20px;
}

.panel {
  min-width: 0;
  padding: 24px;
}

.panel-header {
  margin-bottom: 22px;
}

.panel-header .eyebrow {
  margin-bottom: 7px;
}

.character-name {
  margin: 25px 0;
  padding: 13px 15px;
  color: #c4a8ff;
  background: #211b36;
  border: 1px solid #35294f;
  border-radius: 9px;
  font-size: 15px;
  font-weight: 600;
}

.attribute {
  margin-bottom: 24px;
}

.attribute:last-child {
  margin-bottom: 0;
}

.attribute-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.attribute-info span {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #cbd0df;
  font-size: 13px;
}

.attribute-info span svg {
  color: #a78bfa;
  flex-shrink: 0;
}

.attribute-info strong {
  color: #f5f2ff;
  font-size: 14px;
}

.progress-track {
  height: 8px;
  overflow: hidden;
  background: #202a3d;
  border-radius: 20px;
}

.progress-fill {
  height: 100%;
  border-radius: 20px;
  transition: width 0.4s ease;
}

.strength {
  background: linear-gradient(90deg, #e85d7a, #fb7185);
}

.intelligence {
  background: linear-gradient(90deg, #478bdc, #60a5fa);
}

.discipline {
  background: linear-gradient(90deg, #7658c9, #a78bfa);
}

.attribute small {
  display: block;
  margin-top: 8px;
  color: #25c69a;
  font-size: 11px;
}

.chart-panel {
  overflow: hidden;
}

.chart-container {
  width: 100%;
  padding: 18px 0 0;
  box-sizing: border-box;
}

.chart {
  position: relative;
  height: 260px;
  box-sizing: border-box;
}

.chart-grid {
  position: absolute;
  top: 0;
  bottom: 30px;
  left: 55px;
  right: 8px;
  pointer-events: none;
}

.chart-grid-line {
  position: absolute;
  left: 0;
  right: 0;
  border-top: 1px dashed #303950;
}

.chart-scale {
  position: absolute;
  top: 0;
  right: calc(100% + 9px);
  transform: translateY(-50%);
  color: #77829b;
  font-size: 10px;
  white-space: nowrap;
}

.chart-columns {
  position: absolute;
  top: 0;
  left: 55px;
  right: 8px;
  height: 260px;
  display: flex;
  justify-content: center;
  gap: 20px;
  z-index: 1;
}

.chart-column {
  flex: 0 1 64px;
  min-width: 0;
  height: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.bar-wrapper {
  width: 34px;
  height: 230px;
  flex-shrink: 0;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.bar {
  width: 100%;
  min-height: 4px;
  background: linear-gradient(180deg, #c4a8ff 0%, #7658c9 100%);
  border-radius: 7px 7px 3px 3px;
  box-shadow: 0 0 12px rgba(167, 139, 250, 0.12);
  transition:
    height 0.4s ease,
    filter 0.2s;
}

.chart-column:hover .bar {
  filter: brightness(1.25);
}

.chart-column small {
  width: 100%;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #929bb2;
  font-size: 10px;
  white-space: nowrap;
}

.activity-panel {
  margin-bottom: 30px;
}

.activity-list {
  margin-top: 8px;
}

.activity-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 16px 0;
  border-bottom: 1px solid #252d41;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-info {
  min-width: 0;
}

.activity-info strong {
  display: block;
  overflow-wrap: anywhere;
  color: #e8e5f2;
  font-size: 13px;
  font-weight: 600;
}

.activity-info small {
  display: block;
  margin-top: 6px;
  color: #77829b;
  font-size: 11px;
}

.activity-rewards {
  flex-shrink: 0;
  text-align: right;
}

.activity-rewards strong {
  display: block;
  color: #25c69a;
  font-size: 14px;
  font-weight: 700;
}

.activity-rewards small {
  display: block;
  margin-top: 7px;
  color: #929bb2;
  font-size: 11px;
}

.reward-icons {
  display: flex !important;
  align-items: center;
  justify-content: flex-end;
  gap: 5px;
}

.reward-icons svg {
  color: #a78bfa;
  flex-shrink: 0;
}

.empty-state,
.message,
.error-message {
  padding: 24px 12px;
  color: #929bb2;
  font-size: 13px;
  line-height: 1.6;
  text-align: center;
}

.error-message {
  color: #fb7185;
  background: rgba(251, 113, 133, 0.06);
  border: 1px solid rgba(251, 113, 133, 0.2);
  border-radius: 10px;
}

.error-message button {
  display: block;
  margin: 14px auto 0;
  padding: 10px 16px;
  color: #fff;
  background: #7658c9;
  border: 1px solid transparent;
  border-radius: 8px;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.error-message button:hover {
  background: #9270e8;
}

@media (max-width: 1000px) {
  .overview-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .statistics-page {
    padding: 22px 15px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 22px;
  }

  h1 {
    font-size: 24px;
  }

  .overview-grid {
    gap: 10px;
    margin-bottom: 14px;
  }

  .stat-card {
    padding: 15px;
    border-radius: 11px;
  }

  .stat-card strong {
    font-size: 23px;
  }

  .stat-card p {
    margin-top: 12px;
    font-size: 12px;
  }

  .stat-card small {
    font-size: 10px;
  }

  .content-grid {
    gap: 14px;
    margin-bottom: 14px;
  }

  .panel {
    padding: 17px;
    border-radius: 12px;
  }

  .panel-header {
    gap: 12px;
  }

  h2 {
    font-size: 17px;
  }

  .chart-container {
    padding-top: 12px;
  }

  .chart-columns {
    gap: 10px;
  }

  .chart-column {
    flex: 1 1 0;
  }

  .bar-wrapper {
    width: 28px;
  }

  .chart-scale {
    font-size: 9px;
  }

  .activity-item {
    align-items: flex-start;
    flex-direction: column;
    gap: 10px;
  }

  .activity-rewards {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
  }

  .activity-rewards small {
    margin-top: 0;
  }

  .reward-icons {
    justify-content: flex-start;
  }

  .all-link {
    white-space: nowrap;
  }
}

@media (max-width: 380px) {
  .statistics-page {
    padding: 18px 12px;
  }

  .overview-grid {
    gap: 8px;
  }

  .stat-card {
    padding: 12px;
  }

  .stat-card strong {
    font-size: 20px;
  }

  .panel {
    padding: 14px;
  }

  .chart-grid {
    left: 45px;
  }

  .chart-columns {
    left: 45px;
    gap: 5px;
  }

  .chart-scale {
    font-size: 8px;
  }

  .bar-wrapper {
    width: 23px;
  }
}
</style>
