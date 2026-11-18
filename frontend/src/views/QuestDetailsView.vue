<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { questService, type Quest, type UserQuest } from '../services/quests'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const route = useRoute()
const router = useRouter()

const quest = ref<Quest | null>(null)
const userQuest = ref<UserQuest | null>(null)
const isLoading = ref(true)
const isProcessing = ref(false)
const error = ref('')

const difficultyLabels: Record<Quest['difficulty'], string> = {
  easy: 'Легкий',
  medium: 'Середній',
  hard: 'Складний',
  epic: 'Епічний',
}

const progress = computed(() => {
  if (!userQuest.value) return 0

  const total = quest.value?.steps?.length ?? 0
  if (total === 0) return 0

  const completed = userQuest.value.steps?.filter((step) => step.completed_at !== null).length ?? 0

  return Math.round((completed / total) * 100)
})

const totalSteps = computed(() => quest.value?.steps?.length ?? 0)

const completedSteps = computed(() => {
  if (!userQuest.value) return 0

  return userQuest.value.steps?.filter((step) => step.completed_at !== null).length ?? 0
})

const isCompleted = computed(() => userQuest.value?.status === 'completed')

const isAccepted = computed(() => userQuest.value?.status === 'active')

async function loadQuest() {
  isLoading.value = true
  error.value = ''

  try {
    const questId = Number(route.params.id)

    if (!Number.isInteger(questId) || questId <= 0) {
      throw new Error('Некоректний ID квесту')
    }

    const [allQuests, myQuests] = await Promise.all([
      questService.getQuests(),
      questService.getMyQuests(),
    ])

    console.log('MY QUESTS:', myQuests)
    console.log('TODAY:', getTodayDate())

    quest.value = allQuests.find((item) => item.id === questId) ?? null

    userQuest.value =
      myQuests.find((item) => {
        if (item.quest_id !== questId) return false
        if (!item.quest.is_daily) return true

        return item.daily_date ? formatLocalDate(item.daily_date) === getTodayDate() : false
      }) ?? null

    if (!quest.value) {
      error.value = 'Квест не знайдено.'
    }
  } catch (err) {
    console.error('Помилка завантаження квесту:', err)
    error.value = 'Не вдалося завантажити інформацію про квест.'
  } finally {
    isLoading.value = false
  }
}

async function acceptQuest() {
  if (!quest.value) return

  isProcessing.value = true
  error.value = ''

  try {
    userQuest.value = await questService.acceptQuest(quest.value.id)
  } catch (err) {
    console.error('Помилка прийняття квесту:', err)
    error.value = 'Не вдалося прийняти квест.'
  } finally {
    isProcessing.value = false
  }
}

async function completeStep(stepId: number) {
  if (!userQuest.value) return

  isProcessing.value = true
  error.value = ''

  try {
    userQuest.value = await questService.completeStep(userQuest.value.id, stepId)
  } catch (err) {
    console.error('Помилка виконання етапу:', err)
    error.value = 'Не вдалося виконати етап квесту.'
  } finally {
    isProcessing.value = false
  }
}

async function completeQuest() {
  if (!userQuest.value) return

  isProcessing.value = true
  error.value = ''

  try {
    userQuest.value = await questService.completeQuest(userQuest.value.id)

    await authStore.fetchUser()
  } catch (err) {
    console.error('Помилка завершення квесту:', err)
    error.value = 'Квест не вдалося завершити або оновити дані персонажа.'
  } finally {
    isProcessing.value = false
  }
}

function getTodayDate(): string {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

function formatLocalDate(dateString: string): string {
  const date = new Date(dateString)

  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

onMounted(loadQuest)
</script>

<template>
  <main class="details-page">
    <div class="page-container">
      <button class="back-button" @click="router.push('/quests')">← Назад до квестів</button>

      <div v-if="isLoading" class="message">Завантаження квесту...</div>

      <div v-else-if="error && !quest" class="message error">
        {{ error }}
      </div>

      <template v-else-if="quest">
        <section class="quest-header">
          <div class="quest-meta">
            <span class="category">{{ quest.category }}</span>

            <span class="difficulty" :class="quest.difficulty">
              {{ difficultyLabels[quest.difficulty] }}
            </span>

            <span v-if="quest.is_daily" class="daily-badge">DAILY</span>
            <span v-if="quest.is_weekly" class="weekly-badge">WEEKLY</span>
          </div>

          <div class="quest-status">
            <span v-if="isCompleted" class="status-badge completed"> ✓ Виконано </span>

            <span v-else-if="isAccepted" class="status-badge active"> ● Активний </span>

            <span v-else class="status-badge available"> ○ Доступний </span>
          </div>

          <h1>{{ quest.title }}</h1>
          <p class="description">{{ quest.description }}</p>
        </section>

        <p v-if="error" class="error-message">{{ error }}</p>

        <section class="content-grid">
          <div class="main-content">
            <div class="panel">
              <h2>Етапи квесту</h2>

              <template v-if="quest.steps?.length">
                <div v-if="isAccepted || isCompleted" class="progress-section">
                  <div class="progress-info">
                    <span>Прогрес виконання</span>

                    <strong>
                      {{ isCompleted ? totalSteps : completedSteps }}
                      з {{ totalSteps }} етапів · {{ isCompleted ? 100 : progress }}%
                    </strong>
                  </div>

                  <div class="progress-track">
                    <div
                      class="progress-fill"
                      :style="{ width: `${isCompleted ? 100 : progress}%` }"
                    ></div>
                  </div>
                </div>

                <div
                  v-for="(step, index) in quest.steps"
                  :key="step.id"
                  class="step-card"
                  :class="{
                    'step-completed':
                      isCompleted ||
                      userQuest?.steps?.some(
                        (item) => item.quest_step_id === step.id && item.completed_at,
                      ),
                  }"
                >
                  <div class="step-number">
                    {{ index + 1 }}
                  </div>

                  <div class="step-content">
                    <h3>{{ step.title }}</h3>
                    <p v-if="step.description">
                      {{ step.description }}
                    </p>
                  </div>

                  <div v-if="isAccepted || isCompleted" class="step-action">
                    <span
                      v-if="
                        isCompleted ||
                        userQuest?.steps?.some(
                          (item) => item.quest_step_id === step.id && item.completed_at,
                        )
                      "
                      class="completed-label"
                    >
                      ✓ Виконано
                    </span>

                    <button
                      v-else
                      class="step-button"
                      :disabled="isProcessing"
                      @click="completeStep(step.id)"
                    >
                      Виконати
                    </button>
                  </div>
                </div>
              </template>

              <p v-else class="empty-steps">Для цього квесту ще не додано етапів.</p>
            </div>
          </div>

          <aside class="sidebar">
            <div class="panel rewards-panel">
              <h2>Нагороди</h2>

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

              <div class="quest-action">
                <span v-if="isCompleted" class="completed-label"> ✓ Квест завершено </span>

                <button
                  v-else-if="isAccepted"
                  class="primary-button"
                  :disabled="isProcessing || (totalSteps > 0 && progress < 100)"
                  @click="completeQuest"
                >
                  {{ isProcessing ? 'Обробка...' : 'Завершити квест' }}
                </button>

                <button v-else class="primary-button" :disabled="isProcessing" @click="acceptQuest">
                  {{ isProcessing ? 'Обробка...' : 'Прийняти квест' }}
                </button>
              </div>
            </div>
          </aside>
        </section>
      </template>
    </div>
  </main>
</template>

<style scoped>
.details-page {
  min-height: 100vh;
  padding: 35px 20px 60px;
  box-sizing: border-box;
  background: #090d18;
  color: #f5f2ff;
}

.page-container {
  max-width: 1100px;
  margin: 0 auto;
}

.back-button {
  display: inline-flex;
  align-items: center;
  margin-bottom: 28px;
  padding: 10px 0;
  border: none;
  background: none;
  color: #a78bfa;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: color 0.2s;
}

.back-button:hover {
  color: #c4a8ff;
}

.quest-header {
  margin-bottom: 30px;
}

.quest-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 9px;
  margin-bottom: 16px;
}

.quest-meta span {
  padding: 6px 11px;
  border: 1px solid #303a52;
  border-radius: 7px;
  background: #151d30;
  color: #b8c1d8;
  font-size: 12px;
  font-weight: 600;
}

.quest-header h1 {
  margin: 0 0 12px;
  color: #f5f2ff;
  font-size: 32px;
  font-weight: 750;
  line-height: 1.25;
}

.description {
  max-width: 750px;
  margin: 0;
  color: #929bb2;
  font-size: 14px;
  line-height: 1.7;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 22px;
  align-items: start;
}

.panel {
  padding: 24px;
  border: 1px solid #252d41;
  border-radius: 14px;
  background: #101625;
}

.panel h2 {
  margin: 0 0 22px;
  color: #f5f2ff;
  font-size: 18px;
  font-weight: 700;
}

.step-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  margin-bottom: 12px;
  border: 1px solid #252d41;
  border-radius: 11px;
  background: #141c2d;
  transition:
    border-color 0.2s,
    background 0.2s;
}

.step-card:last-child {
  margin-bottom: 0;
}

.step-number {
  display: grid;
  place-items: center;
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  border: 1px solid #514078;
  border-radius: 10px;
  background: #211b36;
  color: #c4a8ff;
  font-size: 14px;
  font-weight: 700;
}

.step-content {
  flex: 1;
  min-width: 0;
}

.step-content h3 {
  margin: 0 0 5px;
  color: #f5f2ff;
  font-size: 14px;
  font-weight: 650;
}

.step-content p {
  margin: 0;
  color: #929bb2;
  font-size: 13px;
  line-height: 1.5;
}

.progress-section {
  margin-bottom: 22px;
  padding: 16px;
  border: 1px solid #252d41;
  border-radius: 10px;
  background: #141c2d;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 11px;
  color: #929bb2;
  font-size: 13px;
}

.progress-info strong {
  color: #c4a8ff;
  font-size: 13px;
  text-align: right;
}

.progress-track {
  height: 8px;
  overflow: hidden;
  border-radius: 10px;
  background: #252d41;
}

.progress-fill {
  height: 100%;
  border-radius: 10px;
  background: linear-gradient(90deg, #8b5cf6, #c4a8ff);
  transition: width 0.3s ease;
}

.step-button,
.primary-button {
  padding: 10px 14px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: #8b5cf6;
  color: #fff;
  font-size: 13px;
  font-weight: 650;
  cursor: pointer;
  transition:
    background 0.2s,
    transform 0.2s;
}

.step-button:hover:not(:disabled),
.primary-button:hover:not(:disabled) {
  background: #7c3aed;
  transform: translateY(-1px);
}

.step-button:disabled,
.primary-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  transform: none;
}

.completed-label {
  color: #25c69a;
  font-size: 13px;
  font-weight: 650;
}

.reward-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 0;
  border-bottom: 1px solid #252d41;
  color: #929bb2;
  font-size: 13px;
}

.reward-row strong {
  color: #c4a8ff;
  font-size: 14px;
  font-weight: 700;
}

.quest-action {
  margin-top: 22px;
}

.primary-button {
  width: 100%;
  padding: 13px;
  font-size: 14px;
}

.empty-steps,
.message {
  color: #929bb2;
  font-size: 14px;
  line-height: 1.6;
}

.message {
  padding: 40px 0;
  text-align: center;
}

.error,
.error-message {
  color: #fb7185;
}

.error-message {
  margin: 0 0 20px;
  padding: 11px 13px;
  border: 1px solid rgba(251, 113, 133, 0.25);
  border-radius: 8px;
  background: rgba(251, 113, 133, 0.08);
  font-size: 13px;
}

.step-card.step-completed {
  border-color: rgba(37, 198, 154, 0.3);
  background: rgba(37, 198, 154, 0.06);
}

.step-card.step-completed .step-number {
  border-color: rgba(37, 198, 154, 0.3);
  background: rgba(37, 198, 154, 0.12);
  color: #25c69a;
}

.step-card.step-completed .step-content h3 {
  color: #8ce4c8;
}

.quest-status {
  margin-top: 18px;
}

.status-badge {
  display: inline-block;
  padding: 7px 12px;
  border: 1px solid transparent;
  border-radius: 7px;
  font-size: 12px;
  font-weight: 650;
}

.status-badge.completed {
  border-color: rgba(37, 198, 154, 0.25);
  background: rgba(37, 198, 154, 0.1);
  color: #25c69a;
}

.status-badge.active {
  border-color: rgba(167, 139, 250, 0.25);
  background: #211b36;
  color: #c4a8ff;
}

.status-badge.available {
  border-color: #303a52;
  background: #151d30;
  color: #929bb2;
}

.quest-meta .daily-badge {
  border-color: #6845a8;
  background: #211b36;
  color: #c4a8ff;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.7px;
}

.quest-meta .weekly-badge {
  border-color: rgba(59, 130, 246, 0.4);
  background: rgba(59, 130, 246, 0.12);
  color: #93c5fd;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.7px;
}

@media (max-width: 800px) {
  .content-grid {
    grid-template-columns: 1fr;
  }

  .sidebar {
    width: 100%;
  }
}

@media (max-width: 550px) {
  .details-page {
    padding: 22px 14px 40px;
  }

  .quest-header h1 {
    font-size: 26px;
  }

  .panel {
    padding: 18px;
  }

  .step-card {
    flex-wrap: wrap;
  }

  .step-action {
    width: 100%;
    margin-left: 50px;
  }

  .progress-info {
    flex-direction: column;
    gap: 6px;
  }

  .progress-info strong {
    text-align: left;
  }
}

@media (max-width: 360px) {
  .step-action {
    margin-left: 0;
  }

  .quest-header h1 {
    font-size: 23px;
  }
}
</style>
