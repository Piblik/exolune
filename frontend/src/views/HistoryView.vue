<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
  Activity,
  Zap,
  CalendarDays,
  Search,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Dumbbell,
  Trophy,
  Sparkles,
  Brain,
  Flame,
  Target,
  Filter,
  Check,
} from 'lucide-vue-next'
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

const searchQuery = ref('')
const selectedPeriod = ref('all')
const selectedCategory = ref('all')
const currentPage = ref(1)

const pageSize = 8

async function loadHistory() {
  isLoading.value = true
  error.value = ''

  try {
    const firstResponse = await api.get<HistoryResponse>('/api/experience-logs')

    const firstPage = firstResponse.data.data

    const allLogs = [...firstPage.data]

    for (let page = 2; page <= firstPage.last_page; page++) {
      const response = await api.get<HistoryResponse>('/api/experience-logs', { params: { page } })

      allLogs.push(...response.data.data.data)
    }

    logs.value = allLogs
  } catch {
    error.value = 'Не вдалося завантажити історію активності.'
  } finally {
    isLoading.value = false
  }
}

function getLogCategory(log: ExperienceLog) {
  const source = log.source.toLowerCase()
  const description = log.description.toLowerCase()

  if (
    source.includes('achievement') ||
    source.includes('досягнення') ||
    description.includes('досягнення')
  ) {
    return 'achievements'
  }

  if (source.includes('quest') || source.includes('квест')) {
    return 'quests'
  }

  if (log.strength_change > 0) return 'strength'
  if (log.intelligence_change > 0) return 'intelligence'
  if (log.discipline_change > 0) return 'discipline'

  return 'other'
}

function getLogIcon(log: ExperienceLog) {
  const category = getLogCategory(log)

  const icons = {
    quests: Target,
    achievements: Trophy,
    strength: Dumbbell,
    intelligence: Brain,
    discipline: Flame,
    other: Sparkles,
  }

  return icons[category]
}

function getLogIconClass(log: ExperienceLog) {
  return `icon-${getLogCategory(log)}`
}

function hasPageGap(index: number, page: number) {
  const previousPage = visiblePages.value[index - 1]

  return previousPage !== undefined && page - previousPage > 1
}

const totalXP = computed(() => logs.value.reduce((sum, log) => sum + log.experience, 0))

const activeDays = computed(() => {
  return new Set(
    logs.value.map((log) => {
      const date = new Date(log.created_at)
      return `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`
    }),
  ).size
})

const filteredLogs = computed(() => {
  const now = new Date()

  return logs.value.filter((log) => {
    const logDate = new Date(log.created_at)

    const query = searchQuery.value.trim().toLowerCase()

    const matchesSearch =
      !query ||
      log.description.toLowerCase().includes(query) ||
      log.source.toLowerCase().includes(query)

    let matchesPeriod = true

    if (selectedPeriod.value !== 'all') {
      const days = Number(selectedPeriod.value)
      const startDate = new Date(now)

      startDate.setDate(startDate.getDate() - days)

      matchesPeriod = logDate >= startDate && logDate <= now
    }

    const matchesCategory =
      selectedCategory.value === 'all' || getLogCategory(log) === selectedCategory.value

    return matchesSearch && matchesPeriod && matchesCategory
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredLogs.value.length / pageSize)))

const paginatedGroups = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  const pageLogs = filteredLogs.value.slice(start, start + pageSize)

  const groups = new Map<string, ExperienceLog[]>()

  for (const log of pageLogs) {
    const date = new Date(log.created_at)
    const key = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`

    if (!groups.has(key)) groups.set(key, [])

    groups.get(key)!.push(log)
  }

  return Array.from(groups.entries())
    .map(([key, items]) => {
      const firstItem = items[0]

      if (!firstItem) return null

      const date = new Date(firstItem.created_at)

      const today = new Date()
      const yesterday = new Date()
      yesterday.setDate(today.getDate() - 1)

      let label = date.toLocaleDateString('uk-UA', {
        day: '2-digit',
        month: 'long',
      })

      if (date.toDateString() === today.toDateString()) {
        label = 'СЬОГОДНІ · ' + label
      } else if (date.toDateString() === yesterday.toDateString()) {
        label = 'ВЧОРА · ' + label
      }

      return {
        date: key,
        label,
        logs: items,
        totalXP: items.reduce((sum, item) => sum + item.experience, 0),
      }
    })
    .filter((group): group is NonNullable<typeof group> => group !== null)
})

const visiblePages = computed(() => {
  const pages = new Set<number>([1, currentPage.value, totalPages.value])

  if (currentPage.value > 1) pages.add(currentPage.value - 1)
  if (currentPage.value < totalPages.value) {
    pages.add(currentPage.value + 1)
  }

  return Array.from(pages).sort((a, b) => a - b)
})

function changePage(page: number) {
  if (page < 1 || page > totalPages.value) return

  currentPage.value = page
}

function formatTime(date: string) {
  return new Date(date).toLocaleTimeString('uk-UA', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatFullDate(date: string) {
  return new Date(date).toLocaleDateString('uk-UA', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

onMounted(loadHistory)

import { watch } from 'vue'

watch([searchQuery, selectedPeriod, selectedCategory], () => {
  currentPage.value = 1
})

const periodMenu = ref<HTMLDetailsElement | null>(null)
const categoryMenu = ref<HTMLDetailsElement | null>(null)

const periodLabel = computed(() => {
  const labels: Record<string, string> = {
    '7': 'За 7 днів',
    '30': 'За 30 днів',
    all: 'Увесь час',
  }

  return labels[selectedPeriod.value]
})

const categoryLabel = computed(() => {
  const labels: Record<string, string> = {
    all: 'Усі типи',
    quests: 'Квести',
    achievements: 'Досягнення',
    strength: 'Сила',
    intelligence: 'Інтелект',
    discipline: 'Дисципліна',
    other: 'Інше',
  }

  return labels[selectedCategory.value]
})

function choosePeriod(value: string) {
  selectedPeriod.value = value
  periodMenu.value?.removeAttribute('open')
}

function chooseCategory(value: string) {
  selectedCategory.value = value
  categoryMenu.value?.removeAttribute('open')
}
</script>

<template>
  <main class="history-page">
    <section class="summary-grid">
      <article class="summary-card">
        <div class="summary-icon purple">
          <Activity :size="21" />
        </div>

        <div class="summary-content">
          <span>Усього активностей</span>
          <strong>{{ logs.length }}</strong>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon teal">
          <Zap :size="21" />
        </div>

        <div class="summary-content">
          <span>Здобуто досвіду</span>
          <strong>{{ totalXP.toLocaleString('uk-UA') }} XP</strong>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon green">
          <CalendarDays :size="21" />
        </div>

        <div class="summary-content">
          <span>Активних днів</span>
          <strong>{{ activeDays }}</strong>
        </div>
      </article>
    </section>

    <section class="filters">
      <div class="search-box">
        <Search :size="15" />

        <input v-model="searchQuery" type="text" placeholder="Пошук в історії..." />
      </div>

      <div class="filter-controls">
        <!-- Період -->
        <details ref="periodMenu" class="custom-dropdown">
          <summary>
            <CalendarDays :size="15" />
            <span>{{ periodLabel }}</span>
            <ChevronDown :size="14" class="dropdown-arrow" />
          </summary>

          <div class="dropdown-menu">
            <button
              v-for="option in [
                { value: '7', label: 'За 7 днів' },
                { value: '30', label: 'За 30 днів' },
                { value: 'all', label: 'Увесь час' },
              ]"
              :key="option.value"
              :class="{ selected: selectedPeriod === option.value }"
              @click="choosePeriod(option.value)"
            >
              {{ option.label }}
              <Check v-if="selectedPeriod === option.value" :size="14" />
            </button>
          </div>
        </details>

        <!-- Категорія -->
        <details ref="categoryMenu" class="custom-dropdown">
          <summary>
            <Filter :size="15" />
            <span>{{ categoryLabel }}</span>
            <ChevronDown :size="14" class="dropdown-arrow" />
          </summary>

          <div class="dropdown-menu">
            <button
              v-for="option in [
                { value: 'all', label: 'Усі типи' },
                { value: 'quests', label: 'Квести' },
                { value: 'achievements', label: 'Досягнення' },
                { value: 'strength', label: 'Сила' },
                { value: 'intelligence', label: 'Інтелект' },
                { value: 'discipline', label: 'Дисципліна' },
                { value: 'other', label: 'Інше' },
              ]"
              :key="option.value"
              :class="{ selected: selectedCategory === option.value }"
              @click="chooseCategory(option.value)"
            >
              {{ option.label }}
              <Check v-if="selectedCategory === option.value" :size="14" />
            </button>
          </div>
        </details>
      </div>
    </section>

    <div v-if="isLoading" class="message">Завантаження історії активності...</div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else-if="filteredLogs.length === 0" class="empty-state">
      <div class="empty-icon">
        <Activity :size="30" />
      </div>

      <h2>Активностей не знайдено</h2>

      <p>Спробуй змінити параметри пошуку або виконай новий квест.</p>
    </div>

    <section v-else class="history-container">
      <div v-for="group in paginatedGroups" :key="group.date" class="history-group">
        <div class="group-header">
          <span>{{ group.label }}</span>

          <strong :class="group.totalXP >= 0 ? 'positive-xp' : 'negative-xp'">
            {{ group.totalXP >= 0 ? '+' : '' }}{{ group.totalXP }} XP
          </strong>
        </div>

        <article v-for="log in group.logs" :key="log.id" class="history-row">
          <div class="activity-icon" :class="getLogIconClass(log)">
            <component :is="getLogIcon(log)" :size="19" />
          </div>

          <div class="activity-info">
            <h3>{{ log.description }}</h3>

            <span>
              {{ formatFullDate(log.created_at) }},
              {{ formatTime(log.created_at) }}
            </span>
          </div>

          <div class="activity-rewards">
            <strong>+{{ log.experience }} XP</strong>

            <span v-if="log.strength_change > 0"> +{{ log.strength_change }} Сили </span>

            <span v-if="log.intelligence_change > 0">
              +{{ log.intelligence_change }} Інтелекту
            </span>

            <span v-if="log.discipline_change > 0"> +{{ log.discipline_change }} Дисципліни </span>
          </div>
        </article>
      </div>
    </section>

    <nav v-if="!isLoading && !error && totalPages > 1" class="pagination">
      <button
        class="page-button arrow"
        :disabled="currentPage === 1"
        @click="changePage(currentPage - 1)"
      >
        <ChevronLeft :size="17" />
      </button>

      <template v-for="(page, index) in visiblePages" :key="page">
        <span v-if="index > 0 && hasPageGap(index, page)" class="page-dots"> ... </span>

        <button
          class="page-button"
          :class="{ active: currentPage === page }"
          @click="changePage(page)"
        >
          {{ page }}
        </button>
      </template>

      <button
        class="page-button arrow"
        :disabled="currentPage === totalPages"
        @click="changePage(currentPage + 1)"
      >
        <ChevronRight :size="17" />
      </button>
    </nav>
  </main>
</template>

<style scoped>
.history-page {
  min-height: calc(100vh - 66px);
  padding: 28px clamp(16px, 3vw, 36px) 45px;
  background: #090d18;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.summary-card {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  padding: 13px 15px;
  background: #101625;
  border: 1px solid #20283b;
  border-radius: 12px;
}

.summary-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 11px;
}

.summary-icon svg {
  stroke-width: 1.9;
}

.summary-icon.purple {
  color: #a78bfa;
  background: #211b39;
}

.summary-icon.teal {
  color: #2dd4bf;
  background: #102b30;
}

.summary-icon.green {
  color: #34d399;
  background: #12302c;
}

.summary-content {
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 0;
}

.summary-content span {
  color: #929bb2;
  font-size: 11px;
}

.summary-content strong {
  font-size: 19px;
  color: #f5f2ff;
}

.filters {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 16px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 9px;
  width: 280px;
  height: 38px;
  padding: 0 12px;
  border: 1px solid #252d41;
  border-radius: 9px;
  background: #101625;
  color: #8993aa;
}

.search-box:focus-within {
  border-color: #8055bc;
}

.search-box input {
  width: 100%;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  color: #f5f2ff;
  font-size: 11px;
}

.search-box input::placeholder {
  color: #68738b;
}

.filter-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.select-wrapper {
  display: flex;
  align-items: center;
  gap: 7px;
  height: 38px;
  padding: 0 10px;
  border: 1px solid #252d41;
  border-radius: 9px;
  background: #101625;
  color: #8993aa;
}

.select-wrapper select {
  max-width: 125px;
  border: none;
  outline: none;
  appearance: none;
  background: transparent;
  color: #c4cbe0;
  font-size: 11px;
  cursor: pointer;
}

.select-wrapper option {
  background: #101625;
  color: #f5f2ff;
}

.history-container {
  overflow: hidden;
  border: 1px solid #20283b;
  border-radius: 13px;
  background: #101625;
}

.history-group {
  padding: 0 15px;
}

.group-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 43px;
  border-bottom: 1px solid #252d41;
  color: #8993aa;
  font-size: 10px;
  font-weight: 600;
}

.group-header strong {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 10px;
}

.positive-xp {
  color: #a78bfa;
  background: #211b39;
  border: 1px solid #38295c;
}

.negative-xp {
  color: #fca5a5;
  background: #351d2a;
  border: 1px solid #573044;
}

.history-row {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 62px;
  border-bottom: 1px solid #252d41;
  transition: background 0.2s ease;
}

.history-row:last-child {
  border-bottom: none;
}

.history-row:hover {
  background: #151d2e;
}

.activity-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 37px;
  height: 37px;
  border: 1px solid;
  border-radius: 9px;
}

.activity-icon svg {
  stroke-width: 1.9;
}

.icon-quests {
  color: #a78bfa;
  background: #211b39;
  border-color: #38295c;
}

.icon-achievements {
  color: #fbbf24;
  background: #30291b;
  border-color: #554324;
}

.icon-strength {
  color: #a78bfa;
  background: #211b39;
  border-color: #38295c;
}

.icon-intelligence {
  color: #2dd4bf;
  background: #102b30;
  border-color: #174c4b;
}

.icon-discipline {
  color: #34d399;
  background: #12302c;
  border-color: #195345;
}

.icon-other {
  color: #a78bfa;
  background: #211b39;
  border-color: #38295c;
}

.activity-info {
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 5px;
  min-width: 0;
}

.activity-info h3 {
  overflow: hidden;
  margin: 0;
  color: #e5e7f1;
  font-size: 12px;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.activity-info span {
  color: #8290aa;
  font-size: 10px;
}

.activity-rewards {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  flex-shrink: 0;
  text-align: right;
}

.activity-rewards strong {
  color: #a78bfa;
  font-size: 11px;
}

.activity-rewards span {
  color: #8290aa;
  font-size: 9px;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  margin-top: 13px;
}

.page-button {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
  padding: 0 8px;
  border: 1px solid #252d41;
  border-radius: 8px;
  background: #101625;
  color: #929bb2;
  font-size: 11px;
  cursor: pointer;
  transition: 0.2s ease;
}

.page-button:hover:not(:disabled) {
  border-color: #8055bc;
  color: #c4b5fd;
}

.page-button.active {
  border-color: #8b5cf6;
  background: #8b5cf6;
  color: white;
}

.page-button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-dots {
  padding: 0 3px;
  color: #929bb2;
  font-size: 11px;
}

.message,
.error-message {
  padding: 30px;
  border: 1px solid #252d41;
  border-radius: 12px;
  background: #101625;
  color: #929bb2;
  font-size: 12px;
  text-align: center;
}

.error-message {
  color: #fca5a5;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 55px 20px;
  border: 1px solid #252d41;
  border-radius: 12px;
  background: #101625;
  text-align: center;
}

.empty-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  margin-bottom: 15px;
  border-radius: 15px;
  background: #211b39;
  color: #a78bfa;
}

.empty-state h2 {
  margin: 0 0 8px;
  font-size: 17px;
}

.empty-state p {
  margin: 0;
  color: #929bb2;
  font-size: 12px;
}

@media (max-width: 700px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }

  .filters {
    align-items: stretch;
    flex-direction: column;
  }

  .search-box {
    width: auto;
  }

  .filter-controls {
    flex-wrap: wrap;
  }

  .history-group {
    padding: 0 10px;
  }

  .history-row {
    gap: 9px;
  }

  .activity-icon {
    width: 34px;
    height: 34px;
  }

  .activity-info h3 {
    font-size: 11px;
  }

  .activity-rewards strong {
    font-size: 10px;
  }
}

/* Кастомні випадаючі меню */

.custom-dropdown {
  position: relative;
  min-width: 125px;
}

.custom-dropdown summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 9px;
  height: 38px;
  padding: 0 11px;
  border: 1px solid #252d41;
  border-radius: 9px;
  background: #101625;
  color: #aeb8d0;
  font-size: 11px;
  cursor: pointer;
  list-style: none;
  transition: all 0.2s ease;
}

.custom-dropdown summary::-webkit-details-marker {
  display: none;
}

.custom-dropdown summary:hover {
  border-color: #554078;
  background: #141b2c;
}

.custom-dropdown[open] summary {
  border-color: #8055bc;
  color: #c4b5fd;
  background: #17152a;
}

.dropdown-arrow {
  transition: transform 0.2s ease;
}

.custom-dropdown[open] .dropdown-arrow {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 7px);
  right: 0;
  z-index: 20;
  min-width: 100%;
  width: max-content;
  padding: 5px;
  border: 1px solid #34304b;
  border-radius: 10px;
  background: #151a2a;
  box-shadow: 0 12px 30px #00000066;
  animation: dropdown-appear 0.16s ease;
}

.dropdown-menu button {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  width: 100%;
  padding: 10px 11px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #aeb8d0;
  font-family: inherit;
  font-size: 11px;
  text-align: left;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.15s ease;
}

.dropdown-menu button:hover {
  background: #252039;
  color: #e5ddff;
}

.dropdown-menu button.selected {
  background: #29203e;
  color: #c4a8ff;
}

.dropdown-menu button.selected svg {
  color: #a78bfa;
}

@keyframes dropdown-appear {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

</style>
