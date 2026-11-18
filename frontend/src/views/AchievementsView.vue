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
  box-sizing: border-box;
  padding: 32px;
  background: #090d18;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  margin-bottom: 28px;
}

.eyebrow {
  margin: 0 0 9px;
  color: #a78bfa;
  font-size: 10px;
  letter-spacing: 1.8px;
  font-weight: bold;
}

h1 {
  margin: 0 0 8px;
  color: #f5f2ff;
  font-size: 26px;
  font-weight: 600;
}

.subtitle {
  margin: 0;
  color: #929bb2;
  font-size: 12px;
  line-height: 1.5;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
  padding: 9px 12px;
  border: 1px solid #252d41;
  border-radius: 8px;
  background: #101625;
  color: #c4a8ff;
  text-decoration: none;
  font-size: 11px;
  transition: 0.2s ease;
}

.back-link:hover {
  background: #211b36;
  border-color: #6541a5;
  color: #e0d4ff;
}

.progress-card {
  width: 100%;
  max-width: 1000px;
  box-sizing: border-box;
  padding: 20px;
  margin: 0 auto 24px;
  background: #101625;
  border: 1px solid #252d41;
  border-radius: 13px;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.progress-info h2 {
  margin: 0 0 5px;
  color: #f5f2ff;
  font-size: 15px;
  font-weight: 600;
}

.progress-info p {
  margin: 0;
  color: #929bb2;
  font-size: 11px;
}

.progress-info strong {
  color: #c4a8ff;
  font-size: 20px;
  white-space: nowrap;
}

.progress-track {
  height: 8px;
  margin-top: 17px;
  overflow: hidden;
  background: #252d41;
  border-radius: 10px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #8b5cf6, #c4a8ff);
  border-radius: 10px;
  transition: width 0.3s ease;
}

.progress-percent {
  margin: 9px 0 0;
  color: #929bb2;
  font-size: 10px;
  text-align: right;
}

.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr));
  gap: 16px;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.achievement-card {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  min-width: 0;
  padding: 18px;
  box-sizing: border-box;
  background: #101625;
  border: 1px solid #252d41;
  border-radius: 13px;
  transition:
    border-color 0.2s ease,
    background 0.2s ease;
}

.achievement-card:hover {
  background: #131b2d;
  border-color: #3b3658;
}

.achievement-card.unlocked {
  border-color: #6541a5;
}

.achievement-card.locked {
  opacity: 0.65;
}

.achievement-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: #211b36;
}

.achievement-icon svg {
  width: 25px;
  height: 25px;
  color: #858096;
  stroke-width: 1.8;
}

.achievement-card.unlocked .achievement-icon {
  background: #29213d;
  box-shadow: 0 0 18px #8b5cf622;
}

.achievement-card.unlocked .achievement-icon svg {
  color: #c4a8ff;
}

.achievement-card.locked .achievement-icon svg {
  color: #858096;
}

.achievement-content {
  flex: 1;
  min-width: 0;
}

.achievement-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 9px;
  font-weight: bold;
  text-transform: uppercase;
}

.achievement-status svg {
  flex-shrink: 0;
}

.status-unlocked {
  color: #25c69a;
}

.status-locked {
  color: #929bb2;
}

.achievement-card h2 {
  margin: 8px 0 6px;
  color: #f5f2ff;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.4;
}

.achievement-card p {
  margin: 0;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.achievement-condition {
  margin-top: 12px;
  color: #c4a8ff;
  font-size: 10px;
  line-height: 1.5;
}

.achievement-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 8px;
  margin-top: 15px;
  padding-top: 12px;
  border-top: 1px solid #252d41;
  color: #929bb2;
  font-size: 10px;
}

.xp-reward {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #c4a8ff;
  font-weight: 600;
}

.xp-reward svg {
  color: #c4a8ff;
  stroke-width: 1.8;
}

.message,
.error-message {
  max-width: 1000px;
  box-sizing: border-box;
  padding: 20px;
  margin: 0 auto;
  border: 1px solid #252d41;
  border-radius: 12px;
  background: #101625;
  color: #c4a8ff;
  font-size: 12px;
}

.error-message {
  color: #fb7185;
}

@media (max-width: 900px) {
  .achievements-page {
    padding: 24px;
  }

  .achievements-grid {
    grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr));
  }
}

@media (max-width: 600px) {
  .achievements-page {
    padding: 20px 14px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 20px;
  }

  h1 {
    font-size: 22px;
  }

  .subtitle {
    font-size: 11px;
  }

  .progress-card {
    padding: 16px;
    margin-bottom: 18px;
  }

  .progress-info strong {
    font-size: 17px;
  }

  .achievements-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .achievement-card {
    gap: 12px;
    padding: 15px;
  }

  .achievement-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
  }

  .achievement-icon svg {
    width: 22px;
    height: 22px;
  }
}

@media (max-width: 360px) {
  .achievements-page {
    padding: 16px 10px;
  }

  .achievement-card {
    gap: 10px;
    padding: 12px;
  }

  .achievement-footer {
    flex-direction: column;
  }
}
</style>
