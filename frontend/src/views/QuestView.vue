<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { questService, type Quest, type UserQuest } from '../services/quests'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const quests = ref<Quest[]>([])
const myQuests = ref<UserQuest[]>([])
const isLoading = ref(true)
const processingId = ref<number | null>(null)
const error = ref('')

const difficultyLabels: Record<Quest['difficulty'], string> = {
  easy: 'Легкий',
  medium: 'Середній',
  hard: 'Складний',
  epic: 'Епічний',
}

const activeQuestIds = computed(
  () =>
    new Set(
      myQuests.value
        .filter(
          (item) => item.status === 'active' && (!item.quest?.is_daily || isToday(item.daily_date)),
        )
        .map((item) => item.quest_id),
    ),
)

const completedQuestIds = computed(
  () =>
    new Set(
      myQuests.value
        .filter(
          (item) =>
            item.status === 'completed' && (!item.quest?.is_daily || isToday(item.daily_date)),
        )
        .map((item) => item.quest_id),
    ),
)

const displayedQuests = computed(() => {
  const assignedDailyIds = new Set(
    myQuests.value
      .filter((item) => item.quest.is_daily && isToday(item.daily_date))
      .map((item) => item.quest_id),
  )

  return quests.value.filter((quest) => !quest.is_daily || assignedDailyIds.has(quest.id))
})

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

async function acceptQuest(questId: number) {
  processingId.value = questId
  error.value = ''

  try {
    const userQuest = await questService.acceptQuest(questId)
    myQuests.value.unshift(userQuest)
  } catch {
    error.value = 'Не вдалося прийняти квест.'
  } finally {
    processingId.value = null
  }
}

async function completeQuest(userQuestId: number) {
  processingId.value = userQuestId
  error.value = ''

  try {
    const updatedQuest = await questService.completeQuest(userQuestId)

    const index = myQuests.value.findIndex((item) => item.id === updatedQuest.id)

    if (index !== -1) {
      myQuests.value[index] = updatedQuest
    }

    void authStore.fetchUser().catch(() => {
      error.value = 'Квест виконано, але не вдалося оновити дані персонажа.'
    })
  } catch (err) {
    console.error('Помилка завершення квесту:', err)
    error.value = 'Не вдалося завершити квест.'
  } finally {
    processingId.value = null
  }
}

function getActiveUserQuest(questId: number) {
  const quest = quests.value.find((item) => item.id === questId)

  return myQuests.value.find(
    (item) =>
      item.quest_id === questId &&
      item.status === 'active' &&
      (!quest?.is_daily || isToday(item.daily_date)),
  )
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
      <div>
        <p class="eyebrow">ТВІЙ ШЛЯХ ДО ВЕРШИНИ</p>
        <h1>Квести</h1>
        <p class="subtitle">Виконуй завдання, отримуй досвід та розвивай свого персонажа.</p>
      </div>

      <RouterLink to="/dashboard" class="back-link"> ← До персонажа </RouterLink>
    </header>

    <p v-if="error" class="error-message">{{ error }}</p>

    <div v-if="isLoading" class="message">Завантаження квестів...</div>

    <div v-else-if="displayedQuests.length === 0" class="message">
      Поки що доступних квестів немає.
    </div>

    <section v-else class="quests-grid">
      <article v-for="quest in displayedQuests" :key="quest.id" class="quest-card">
        <div class="quest-top">
          <div class="quest-labels">
            <span v-if="quest.is_daily" class="daily-badge">DAILY</span>
            <span class="category">{{ quest.category }}</span>
          </div>
          <span class="difficulty" :class="quest.difficulty">
            {{ difficultyLabels[quest.difficulty] }}
          </span>
        </div>

        <h2>{{ quest.title }}</h2>
        <p class="description">{{ quest.description }}</p>

        <div class="rewards">
          <h3>Нагороди</h3>

          <div class="reward-row">
            <span>Досвід</span>
            <strong>+{{ quest.experience_reward }} XP</strong>
          </div>
          <div class="reward-row">
            <span>Сила</span>
            <strong>+{{ quest.strength_reward }}</strong>
          </div>
          <div class="reward-row">
            <span>Інтелект</span>
            <strong>+{{ quest.intelligence_reward }}</strong>
          </div>
          <div class="reward-row">
            <span>Дисципліна</span>
            <strong>+{{ quest.discipline_reward }}</strong>
          </div>
        </div>

        <button v-if="activeQuestIds.has(quest.id)" class="accept-button active-button" disabled>
          Квест активний
        </button>

        <button
          v-else-if="completedQuestIds.has(quest.id)"
          class="accept-button completed-button"
          disabled
        >
          Квест виконано
        </button>

        <button
          v-else
          class="accept-button"
          :disabled="processingId === quest.id"
          @click="acceptQuest(quest.id)"
        >
          {{ processingId === quest.id ? 'Обробка...' : 'Прийняти квест' }}
        </button>

        <button
          v-if="getActiveUserQuest(quest.id)"
          class="complete-button"
          :disabled="processingId === getActiveUserQuest(quest.id)?.id"
          @click="completeQuest(getActiveUserQuest(quest.id)!.id)"
        >
          {{ processingId === getActiveUserQuest(quest.id)?.id ? 'Обробка...' : 'Завершити квест' }}
        </button>
      </article>
    </section>
  </main>
</template>

<style scoped>
.quests-page {
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

.subtitle {
  color: #aaa3bd;
}

.back-link {
  color: #c4b5fd;
  text-decoration: none;
}

.quests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 22px;
}

.quest-card {
  display: flex;
  flex-direction: column;
  padding: 24px;
  background: #1b172b;
  border: 1px solid #342b4c;
  border-radius: 16px;
}

.quest-top {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 20px;
}

.category,
.difficulty {
  font-size: 12px;
  padding: 6px 10px;
  border-radius: 20px;
  background: #302743;
  color: #c4b5fd;
}

.difficulty.easy {
  color: #86efac;
}
.difficulty.medium {
  color: #fcd34d;
}
.difficulty.hard {
  color: #fca5a5;
}
.difficulty.epic {
  color: #d8b4fe;
}

.quest-card h2 {
  font-size: 21px;
  margin: 0 0 12px;
}

.description {
  color: #aaa3bd;
  line-height: 1.5;
  min-height: 48px;
}

.rewards {
  margin: 22px 0;
  padding-top: 16px;
  border-top: 1px solid #342b4c;
}

.rewards h3 {
  font-size: 14px;
  margin-bottom: 12px;
}

.reward-row {
  display: flex;
  justify-content: space-between;
  margin: 9px 0;
  color: #aaa3bd;
  font-size: 14px;
}

.reward-row strong {
  color: #c4b5fd;
}

.accept-button,
.complete-button {
  margin-top: 10px;
  padding: 12px;
  border: none;
  border-radius: 9px;
  background: #8b5cf6;
  color: white;
  font-weight: bold;
  cursor: pointer;
}

.accept-button:hover:not(:disabled),
.complete-button:hover:not(:disabled) {
  background: #7c3aed;
}

.accept-button:disabled,
.complete-button:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.active-button {
  background: #403653;
}

.completed-button {
  background: #22543d;
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
  .quests-page {
    padding: 24px 16px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
  }
}

.quest-labels {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 7px;
}

.daily-badge {
  padding: 6px 10px;
  border: 1px solid #6845a8;
  border-radius: 20px;
  background: #302047;
  color: #c4a8ff;
  font-size: 11px;
  font-weight: bold;
  letter-spacing: 1px;
}
</style>
