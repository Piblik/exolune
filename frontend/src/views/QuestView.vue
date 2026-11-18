<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { questService, type Quest, type UserQuest } from '../services/quests'
import { Zap, Sword, Brain, Shield, Check } from 'lucide-vue-next'

const quests = ref<Quest[]>([])
const myQuests = ref<UserQuest[]>([])
const isLoading = ref(true)
const error = ref('')

const difficultyLabels: Record<Quest['difficulty'], string> = {
  easy: 'Легкий',
  medium: 'Середній',
  hard: 'Складний',
  epic: 'Епічний',
}

type QuestTab = 'all' | 'daily' | 'weekly' | 'custom'

const activeTab = ref<QuestTab>('all')
const searchQuery = ref('')
const selectedCategory = ref('all')

const categories = computed(() => {
  return [...new Set(quests.value.map((quest) => quest.category))]
})

const activeQuestCount = computed(() => {
  return myQuests.value.filter((item) => item.status === 'active').length
})

const completedTodayCount = computed(() => {
  return myQuests.value.filter((item) => item.status === 'completed' && isToday(item.completed_at))
    .length
})

const availableXp = computed(() => {
  return displayedQuests.value
    .filter((quest) => getQuestStatus(quest).class !== 'completed')
    .reduce((total, quest) => total + quest.experience_reward, 0)
})

const displayedQuests = computed(() => {
  const assignedDailyIds = new Set(
    myQuests.value
      .filter((item) => item.quest.is_daily && isToday(item.daily_date))
      .map((item) => item.quest_id),
  )

  const assignedWeeklyIds = new Set(
    myQuests.value
      .filter((item) => item.quest.is_weekly && item.daily_date)
      .map((item) => item.quest_id),
  )

  let result = quests.value.filter((quest) => {
    if (quest.is_daily) {
      return assignedDailyIds.has(quest.id)
    }

    if (quest.is_weekly) {
      return assignedWeeklyIds.has(quest.id)
    }

    return true
  })

  if (activeTab.value === 'daily') {
    result = result.filter((quest) => quest.is_daily)
  }

  if (activeTab.value === 'weekly') {
    result = result.filter((quest) => quest.is_weekly)
  }

  if (activeTab.value === 'custom') {
    result = result.filter((quest) => quest.created_by != null)
  }

  if (selectedCategory.value !== 'all') {
    result = result.filter((quest) => quest.category === selectedCategory.value)
  }

  const query = searchQuery.value.trim().toLowerCase()

  if (query) {
    result = result.filter(
      (quest) =>
        quest.title.toLowerCase().includes(query) ||
        quest.description.toLowerCase().includes(query),
    )
  }

  return result
})

const activeQuests = computed(() => {
  return displayedQuests.value.filter((quest) => getQuestStatus(quest).class !== 'completed')
})

const completedQuests = computed(() => {
  return displayedQuests.value.filter((quest) => getQuestStatus(quest).class === 'completed')
})

function getQuestStatus(quest: Quest) {
  const relatedQuests = myQuests.value.filter(
    (item) => item.quest_id === quest.id && (!quest.is_daily || isToday(item.daily_date)),
  )

  if (relatedQuests.some((item) => item.status === 'completed')) {
    return {
      text: '✓ Виконано',
      class: 'completed',
    }
  }

  if (relatedQuests.some((item) => item.status === 'active')) {
    return {
      text: '● Активний',
      class: 'active',
    }
  }

  return {
    text: '○ Доступний',
    class: 'available',
  }
}

async function loadData() {
  isLoading.value = true
  error.value = ''

  try {
    const [allQuests, userQuests] = await Promise.all([
      questService.getQuests(),
      questService.getMyQuests(),
    ])

    quests.value = allQuests
    myQuests.value = userQuests

    console.log('ALL QUESTS:', allQuests)
    console.log('MY QUESTS:', userQuests)
    console.log('DISPLAYED QUESTS:', displayedQuests.value)
  } catch {
    error.value = 'Не вдалося завантажити квести.'
  } finally {
    isLoading.value = false
  }
}

function getTodayDate(): string {
  const date = new Date()
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

function isToday(date: string | null): boolean {
  if (!date) return false

  const parsedDate = new Date(date)

  const year = parsedDate.getFullYear()
  const month = String(parsedDate.getMonth() + 1).padStart(2, '0')
  const day = String(parsedDate.getDate()).padStart(2, '0')

  const localDate = `${year}-${month}-${day}`

  return localDate === getTodayDate()
}

onMounted(loadData)
</script>

<template>
  <main class="quests-page">
    <header class="page-header">
      <div class="header-title">
        <p class="eyebrow">ТВІЙ ШЛЯХ ДО ВЕРШИНИ</p>
        <h1>Квести</h1>
        <p class="subtitle">Виконуй завдання, отримуй досвід та розвивай свого персонажа.</p>
      </div>

      <div class="header-actions">
        <RouterLink to="/quests/create" class="create-link">
          <span>＋</span> Створити квест
        </RouterLink>
      </div>
    </header>

    <section class="stats-grid">
      <article class="stat-card">
        <div class="stat-icon purple"><Sword :size="24" /></div>
        <div>
          <p>Активні квести</p>
          <strong>{{ activeQuestCount }}</strong>
        </div>
      </article>

      <article class="stat-card">
        <div class="stat-icon green"><Check :size="24" /></div>
        <div>
          <p>Виконано сьогодні</p>
          <strong>{{ completedTodayCount }}</strong>
        </div>
      </article>

      <article class="stat-card">
        <div class="stat-icon yellow"><Zap :size="24" /></div>
        <div>
          <p>ХР можна здобути</p>
          <strong>{{ availableXp }}</strong>
        </div>
      </article>
    </section>

    <section class="quest-filters">
      <div class="filter-tabs">
        <button :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">
          Усі ({{ displayedQuests.length }})
        </button>

        <button :class="{ active: activeTab === 'daily' }" @click="activeTab = 'daily'">
          Щоденні
        </button>

        <button :class="{ active: activeTab === 'weekly' }" @click="activeTab = 'weekly'">
          Тижневі
        </button>

        <button :class="{ active: activeTab === 'custom' }" @click="activeTab = 'custom'">
          Власні
        </button>
      </div>

      <div class="filter-controls">
        <input v-model="searchQuery" type="text" placeholder="⌕  Пошук квестів..." />

        <select v-model="selectedCategory">
          <option value="all">Усі категорії</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
      </div>
    </section>

    <p v-if="error" class="error-message">{{ error }}</p>

    <div v-if="isLoading" class="message">Завантаження квестів...</div>

    <div v-else>
      <section class="quest-section">
        <div class="section-heading">
          <h2>Активні квести</h2>
          <span>{{ activeQuests.length }}</span>
        </div>

        <div v-if="activeQuests.length === 0" class="message">Наразі немає активних квестів.</div>

        <div v-else class="quests-grid">
          <article v-for="quest in activeQuests" :key="quest.id" class="quest-card">
            <div class="quest-top">
              <div class="quest-labels">
                <span v-if="quest.is_daily" class="daily-badge">DAILY</span>
                <span v-if="quest.is_weekly" class="weekly-badge">WEEKLY</span>
                <span class="category">{{ quest.category }}</span>
              </div>

              <span class="difficulty" :class="quest.difficulty">
                {{ difficultyLabels[quest.difficulty] }}
              </span>
            </div>

            <div class="quest-heading">
              <h2>{{ quest.title }}</h2>
              <span class="quest-status" :class="getQuestStatus(quest).class">
                {{ getQuestStatus(quest).text }}
              </span>
            </div>

            <p class="description">{{ quest.description }}</p>

            <div class="reward-list">
              <span class="reward-xp"> <Zap :size="15" /> +{{ quest.experience_reward }} XP </span>

              <span v-if="quest.strength_reward > 0">
                <Sword :size="15" /> +{{ quest.strength_reward }} Сили
              </span>

              <span v-if="quest.intelligence_reward > 0">
                <Brain :size="15" /> +{{ quest.intelligence_reward }} Інтелекту
              </span>

              <span v-if="quest.discipline_reward > 0">
                <Shield :size="15" /> +{{ quest.discipline_reward }} Дисципліни
              </span>
            </div>

            <div class="quest-card-footer">
              <RouterLink
                :to="{ name: 'quest-details', params: { id: quest.id } }"
                class="details-link"
              >
                Відкрити квест →
              </RouterLink>
            </div>
          </article>
        </div>
      </section>
      <section class="quest-section completed-section">
        <div class="section-heading">
          <h2>Нещодавно виконані</h2>
          <span>{{ completedQuests.length }}</span>
        </div>

        <div v-if="completedQuests.length === 0" class="message">
          Тут поки немає виконаних квестів.
        </div>

        <div v-else class="quests-grid">
          <article
            v-for="quest in completedQuests"
            :key="quest.id"
            class="quest-card completed-card"
          >
            <div class="quest-top">
              <div class="quest-labels">
                <span v-if="quest.is_daily" class="daily-badge">DAILY</span>
                <span v-if="quest.is_weekly" class="weekly-badge">WEEKLY</span>
                <span class="category">{{ quest.category }}</span>
              </div>

              <span class="quest-status completed">
                <Check :size="14" />
                Виконано
              </span>
            </div>

            <h2>{{ quest.title }}</h2>
            <p class="description">{{ quest.description }}</p>

            <div class="completed-reward">
              <Zap :size="16" />
              +{{ quest.experience_reward }} XP отримано
            </div>

            <div class="quest-card-footer">
              <RouterLink
                :to="{ name: 'quest-details', params: { id: quest.id } }"
                class="details-link"
              >
                Переглянути →
              </RouterLink>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
.quests-page {
  min-height: 100vh;
  padding: 36px;
  box-sizing: border-box;
  background: #090d18;
  color: #f5f2ff;
  font-family: inherit;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  margin-bottom: 30px;
}

.eyebrow {
  margin: 0 0 10px;
  color: #a78bfa;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1.8px;
}

h1 {
  margin: 0 0 9px;
  color: #f5f2ff;
  font-size: 32px;
  font-weight: 750;
}

.subtitle {
  margin: 0;
  color: #929bb2;
  font-size: 14px;
  line-height: 1.6;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.create-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 11px 16px;
  border: 1px solid transparent;
  border-radius: 9px;
  background: #8b5cf6;
  color: #fff;
  font-size: 13px;
  font-weight: 650;
  text-decoration: none;
  transition:
    background 0.2s,
    transform 0.2s;
}

.create-link:hover {
  background: #7c3aed;
  transform: translateY(-1px);
}

.create-link span {
  font-size: 18px;
  line-height: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  margin-bottom: 28px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
  padding: 20px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
  transition:
    border-color 0.2s,
    transform 0.2s;
}

.stat-card:hover {
  border-color: #514078;
  transform: translateY(-2px);
}

.stat-card p {
  margin: 0 0 7px;
  color: #929bb2;
  font-size: 13px;
}

.stat-card strong {
  color: #f5f2ff;
  font-size: 26px;
  font-weight: 750;
  font-variant-numeric: tabular-nums;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  border-radius: 12px;
}

.stat-icon.purple {
  background: rgba(167, 139, 250, 0.13);
  color: #a78bfa;
}

.stat-icon.green {
  background: rgba(37, 198, 154, 0.12);
  color: #25c69a;
}

.stat-icon.yellow {
  background: rgba(251, 191, 36, 0.12);
  color: #fbbf24;
}

.quest-filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 30px;
  padding: 14px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
}

.filter-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.filter-tabs button {
  padding: 9px 14px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: #929bb2;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition:
    background 0.2s,
    color 0.2s,
    border-color 0.2s;
}

.filter-tabs button:hover {
  background: #1b2336;
  color: #e2dcf5;
}

.filter-tabs button.active {
  border-color: rgba(167, 139, 250, 0.25);
  background: #211b36;
  color: #c4a8ff;
}

.filter-controls {
  display: flex;
  justify-content: flex-end;
  flex: 1;
  gap: 10px;
}

.filter-controls input,
.filter-controls select {
  min-width: 0;
  padding: 10px 12px;
  border: 1px solid #303a52;
  border-radius: 8px;
  outline: none;
  background: #0b1120;
  color: #f5f2ff;
  font: inherit;
  font-size: 13px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.filter-controls input {
  width: 220px;
}

.filter-controls select {
  min-width: 145px;
}

.filter-controls input::placeholder {
  color: #68738d;
}

.filter-controls input:focus,
.filter-controls select:focus {
  border-color: #a78bfa;
  box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
}

.quest-section {
  margin-bottom: 36px;
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
}

.section-heading h2 {
  margin: 0;
  color: #f5f2ff;
  font-size: 19px;
  font-weight: 700;
}

.section-heading > span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 25px;
  height: 24px;
  padding: 0 7px;
  border: 1px solid #303a52;
  border-radius: 7px;
  background: #151d30;
  color: #aeb8d0;
  font-size: 12px;
  font-weight: 650;
}

.quests-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  align-items: stretch;
}

.quest-card {
  display: flex;
  flex-direction: column;
  min-width: 0;
  height: 100%;
  padding: 20px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
  transition:
    transform 0.2s,
    border-color 0.2s,
    box-shadow 0.2s;
}

.quest-card:hover {
  transform: translateY(-3px);
  border-color: #514078;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.16);
}

.quest-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 16px;
}

.quest-labels {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 7px;
}

.category,
.difficulty {
  display: inline-flex;
  align-items: center;
  padding: 5px 9px;
  border: 1px solid #303a52;
  border-radius: 7px;
  background: #151d30;
  color: #aeb8d0;
  font-size: 11px;
  font-weight: 600;
}

.difficulty.easy {
  border-color: rgba(37, 198, 154, 0.25);
  background: rgba(37, 198, 154, 0.08);
  color: #25c69a;
}

.difficulty.medium {
  border-color: rgba(251, 191, 36, 0.25);
  background: rgba(251, 191, 36, 0.08);
  color: #fbbf24;
}

.difficulty.hard {
  border-color: rgba(251, 113, 133, 0.25);
  background: rgba(251, 113, 133, 0.08);
  color: #fb7185;
}

.difficulty.epic {
  border-color: rgba(167, 139, 250, 0.3);
  background: rgba(167, 139, 250, 0.1);
  color: #c4a8ff;
}

.daily-badge,
.weekly-badge {
  display: inline-flex;
  align-items: center;
  padding: 5px 9px;
  border-radius: 7px;
  font-size: 10px;
  font-weight: 750;
  letter-spacing: 0.7px;
}

.daily-badge {
  border: 1px solid rgba(167, 139, 250, 0.3);
  background: rgba(167, 139, 250, 0.1);
  color: #c4a8ff;
}

.weekly-badge {
  border: 1px solid rgba(59, 130, 246, 0.3);
  background: rgba(59, 130, 246, 0.1);
  color: #93c5fd;
}

.quest-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 10px;
}

.quest-card h2 {
  display: -webkit-box;
  min-height: 2.7em;
  margin: 0;
  overflow: hidden;
  overflow-wrap: anywhere;
  color: #f5f2ff;
  font-size: 17px;
  font-weight: 700;
  line-height: 1.4;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.quest-heading h2 {
  flex: 1;
}

.quest-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  gap: 5px;
  padding: 6px 9px;
  border: 1px solid transparent;
  border-radius: 7px;
  font-size: 11px;
  font-weight: 650;
}

.quest-status.active {
  border-color: rgba(167, 139, 250, 0.25);
  background: rgba(167, 139, 250, 0.1);
  color: #c4a8ff;
}

.quest-status.completed {
  border-color: rgba(37, 198, 154, 0.25);
  background: rgba(37, 198, 154, 0.1);
  color: #25c69a;
}

.quest-status.available {
  border-color: #303a52;
  background: #151d30;
  color: #929bb2;
}

.description {
  display: -webkit-box;
  min-height: 4.5em;
  margin: 0;
  overflow: hidden;
  overflow-wrap: anywhere;
  color: #929bb2;
  font-size: 13px;
  line-height: 1.5;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}

.reward-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 16px;
}

.reward-list span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 9px;
  border: 1px solid #303a52;
  border-radius: 7px;
  background: #151d30;
  color: #c0c8da;
  font-size: 11px;
  font-weight: 550;
}

.reward-list .reward-xp {
  border-color: rgba(251, 191, 36, 0.2);
  background: rgba(251, 191, 36, 0.08);
  color: #fbbf24;
}

.quest-card-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: auto;
  padding-top: 18px;
}

.details-link {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 12px;
  border: 1px solid rgba(167, 139, 250, 0.25);
  border-radius: 8px;
  background: rgba(167, 139, 250, 0.08);
  color: #c4a8ff;
  font-size: 12px;
  font-weight: 650;
  text-decoration: none;
  transition:
    background 0.2s,
    border-color 0.2s,
    color 0.2s;
}

.details-link:hover {
  border-color: #8b5cf6;
  background: #6d42c7;
  color: #fff;
}

.completed-section {
  padding-top: 28px;
  border-top: 1px solid #252d41;
}

.completed-card {
  border-color: rgba(37, 198, 154, 0.2);
  background: linear-gradient(145deg, #101b24, #101625);
}

.completed-card:hover {
  border-color: rgba(37, 198, 154, 0.45);
}

.completed-card .quest-top {
  margin-bottom: 14px;
}

.completed-card h2 {
  margin-bottom: 10px;
}

.completed-reward {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  color: #25c69a;
  font-size: 13px;
  font-weight: 650;
}

.message {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 110px;
  padding: 24px;
  border: 1px dashed #303a52;
  border-radius: 12px;
  background: rgba(16, 22, 37, 0.6);
  color: #929bb2;
  text-align: center;
  font-size: 13px;
}

.error-message {
  margin: 0 0 22px;
  padding: 13px 16px;
  border: 1px solid rgba(251, 113, 133, 0.25);
  border-radius: 9px;
  background: rgba(251, 113, 133, 0.08);
  color: #fb7185;
  font-size: 13px;
}

/* Планшети */
@media (max-width: 900px) {
  .quests-page {
    padding: 28px 24px;
  }

  .quest-filters {
    align-items: stretch;
  }

  .filter-controls {
    justify-content: flex-start;
  }

  .filter-controls input {
    flex: 1;
    width: auto;
  }
}

/* Телефони */
@media (max-width: 600px) {
  .quests-page {
    padding: 20px 15px 36px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 24px;
  }

  h1 {
    font-size: 28px;
  }

  .header-actions {
    width: 100%;
  }

  .create-link {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: 1fr;
    gap: 10px;
    margin-bottom: 22px;
  }

  .stat-card {
    padding: 15px;
  }

  .stat-icon {
    width: 43px;
    height: 43px;
  }

  .stat-card strong {
    font-size: 23px;
  }

  .quest-filters {
    padding: 12px;
    gap: 13px;
  }

  .filter-tabs {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    width: 100%;
  }

  .filter-tabs button {
    padding: 10px 8px;
  }

  .filter-controls {
    flex-direction: column;
    width: 100%;
  }

  .filter-controls input,
  .filter-controls select {
    width: 100%;
    box-sizing: border-box;
  }

  .quests-grid {
    grid-template-columns: 1fr;
    gap: 13px;
  }

  .quest-card {
    padding: 17px;
  }

  .quest-heading {
    flex-direction: column;
    gap: 8px;
  }

  .quest-heading h2 {
    min-height: auto;
  }

  .quest-status {
    align-self: flex-start;
  }

  .section-heading h2 {
    font-size: 17px;
  }

  .quest-card-footer {
    justify-content: flex-start;
  }

  .details-link {
    width: 100%;
    justify-content: center;
    box-sizing: border-box;
  }
}

@media (max-width: 360px) {
  .quests-page {
    padding-right: 12px;
    padding-left: 12px;
  }

  .quest-top {
    flex-direction: column;
  }

  .quest-labels {
    gap: 5px;
  }

  .reward-list {
    gap: 6px;
  }

  .reward-list span {
    font-size: 10px;
  }
}
</style>
