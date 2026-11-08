<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
  Award,
  Trophy,
  Target,
  Lock,
  Check,
  Gift,
  ChevronLeft,
  Dumbbell,
  Brain,
  Flame,
} from 'lucide-vue-next'
import {
  achievementService,
  type Achievement,
  type UserAchievement,
} from '../services/achievements'

const achievements = ref<Achievement[]>([])
const myAchievements = ref<UserAchievement[]>([])
const isLoading = ref(true)
const error = ref('')

const unlockedIds = computed(() => new Set(myAchievements.value.map((item) => item.achievement_id)))

const unlockedCount = computed(() => myAchievements.value.length)

const progress = computed(() => {
  if (!achievements.value.length) return 0

  return Math.round((unlockedCount.value / achievements.value.length) * 100)
})

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('uk-UA', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

function getConditionText(achievement: Achievement) {
  const conditions: Record<string, string> = {
    quests_completed: `Виконати ${achievement.condition_value} квестів`,
    level_reached: `Досягти ${achievement.condition_value} рівня`,
    strength_reached: `Досягти ${achievement.condition_value} сили`,
    intelligence_reached: `Досягти ${achievement.condition_value} інтелекту`,
    discipline_reached: `Досягти ${achievement.condition_value} дисципліни`,
  }

  return conditions[achievement.condition_type] ?? 'Особлива умова'
}

function getAchievementIcon(achievement: Achievement) {
  const icons: Record<string, any> = {
    quests_completed: Target,
    level_reached: Trophy,
    strength_reached: Dumbbell,
    intelligence_reached: Brain,
    discipline_reached: Flame,
  }

  return icons[achievement.condition_type] ?? Award
}

function getUnlockedDate(achievementId: number) {
  const item = myAchievements.value.find((item) => item.achievement_id === achievementId)

  return item ? formatDate(item.unlocked_at) : ''
}

async function loadAchievements() {
  isLoading.value = true
  error.value = ''

  try {
    const [all, unlocked] = await Promise.all([
      achievementService.getAchievements(),
      achievementService.getMyAchievements(),
    ])

    achievements.value = all
    myAchievements.value = unlocked
  } catch {
    error.value = 'Не вдалося завантажити досягнення.'
  } finally {
    isLoading.value = false
  }
}

onMounted(loadAchievements)
</script>

<template>
  <main class="achievements-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">ТВОЇ НАГОРОДИ</p>
        <h1>Досягнення</h1>
        <p class="subtitle">Кожен виконаний квест наближає тебе до нових звершень.</p>
      </div>

      <RouterLink to="/dashboard" class="back-link">
        <ChevronLeft :size="16" />
        До персонажа
      </RouterLink>
    </header>

    <section class="progress-card">
      <div class="progress-info">
        <div>
          <h2>Твій прогрес</h2>
          <p>Відкриті досягнення</p>
        </div>

        <strong>{{ unlockedCount }} / {{ achievements.length }}</strong>
      </div>

      <div class="progress-track">
        <div class="progress-fill" :style="{ width: `${progress}%` }"></div>
      </div>

      <p class="progress-percent">{{ progress }}% завершено</p>
    </section>

    <div v-if="isLoading" class="message">Завантаження досягнень...</div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <section v-else class="achievements-grid">
      <article
        v-for="achievement in achievements"
        :key="achievement.id"
        class="achievement-card"
        :class="{
          unlocked: unlockedIds.has(achievement.id),
          locked: !unlockedIds.has(achievement.id),
        }"
      >
        <div class="achievement-icon">
          <component :is="getAchievementIcon(achievement)" :size="30" />
        </div>

        <div class="achievement-content">
          <span
            class="achievement-status"
            :class="{
              'status-unlocked': unlockedIds.has(achievement.id),
              'status-locked': !unlockedIds.has(achievement.id),
            }"
          >
            <template v-if="unlockedIds.has(achievement.id)">
              <Check :size="13" />
              Відкрито
            </template>

            <template v-else>
              <Lock :size="12" />
              Заблоковано
            </template>
          </span>

          <h2>{{ achievement.title }}</h2>
          <p>{{ achievement.description }}</p>

          <div class="achievement-condition">
            {{ getConditionText(achievement) }}
          </div>

          <div class="achievement-footer">
            <span class="xp-reward">
              <Gift :size="14" />
              +{{ achievement.experience_reward }} XP
            </span>

            <span v-if="unlockedIds.has(achievement.id)">
              {{ getUnlockedDate(achievement.id) }}
            </span>
          </div>
        </div>
      </article>
    </section>
  </main>
</template>

<style scoped>
.achievements-page {
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

.progress-card {
  max-width: 1000px;
  padding: 24px;
  margin: 0 auto 32px;
  background: #1b172b;
  border: 1px solid #342b4c;
  border-radius: 16px;
  width: 100%;
  box-sizing: border-box;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.progress-info h2 {
  margin: 0 0 6px;
}

.progress-info p,
.progress-percent {
  color: #aaa3bd;
  font-size: 14px;
}

.progress-info strong {
  color: #c4b5fd;
  font-size: 24px;
}

.progress-track {
  height: 10px;
  margin-top: 20px;
  overflow: hidden;
  background: #302944;
  border-radius: 10px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #8b5cf6, #c084fc);
  border-radius: 10px;
  transition: width 0.3s ease;
}

.progress-percent {
  margin-bottom: 0;
  text-align: right;
}

.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
  gap: 20px;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.achievement-card {
  display: flex;
  gap: 18px;
  padding: 24px;
  background: #1b172b;
  border: 1px solid #342b4c;
  border-radius: 16px;
}

.achievement-card.unlocked {
  border-color: #8b5cf6;
}

.achievement-card.locked {
  opacity: 0.65;
}

.achievement-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  background: #302944;
  border-radius: 16px;
  font-size: 32px;
}

.achievement-content {
  flex: 1;
  min-width: 0;
}

.achievement-status {
  font-size: 11px;
  text-transform: uppercase;
  font-weight: bold;
}

.status-unlocked {
  color: #86efac;
}

.status-locked {
  color: #aaa3bd;
}

.achievement-card h2 {
  margin: 8px 0;
  font-size: 19px;
}

.achievement-card p {
  color: #aaa3bd;
  font-size: 14px;
  line-height: 1.5;
}

.achievement-condition {
  margin-top: 14px;
  color: #c4b5fd;
  font-size: 13px;
}

.achievement-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 10px;
  margin-top: 18px;
  padding-top: 14px;
  border-top: 1px solid #342b4c;
  color: #c4b5fd;
  font-size: 12px;
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
  .achievements-page {
    padding: 24px 16px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color 0.2s ease;
}

.back-link:hover {
  color: #a78bfa;
}

.achievement-icon svg {
  stroke-width: 1.8;
  color: #c4b5fd;
}

.achievement-card.unlocked .achievement-icon {
  background: #30234b;
  box-shadow: 0 0 20px #8b5cf622;
}

.achievement-card.unlocked .achievement-icon svg {
  color: #c4a8ff;
}

.achievement-card.locked .achievement-icon svg {
  color: #858096;
}

.achievement-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.achievement-status svg {
  flex-shrink: 0;
}

.xp-reward {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.xp-reward svg {
  color: #c4a8ff;
  stroke-width: 1.8;
}
</style>
