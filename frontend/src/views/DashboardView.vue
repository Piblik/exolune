<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { questService, type UserQuest } from '../services/quests'
import { achievementService, type UserAchievement } from '../services/achievements'
import api from '../services/api'
import {
  LogOut,
  ChevronRight,
  Swords,
  Brain,
  Flame,
  Trophy,
  Check,
  Circle,
  Target,
  Sparkles,
  Award,
  Activity,
} from 'lucide-vue-next'

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
  data: {
    data: ExperienceLog[]
  }
}

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const character = computed(() => user.value?.character)

const avatarUrl = computed(() => {
  const avatar = character.value?.avatar

  if (!avatar) return null

  return `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/storage/${avatar}`
})

const myQuests = ref<UserQuest[]>([])
const achievements = ref<UserAchievement[]>([])
const logs = ref<ExperienceLog[]>([])

const isLoading = ref(true)
const error = ref('')

const requiredExperience = computed(() => {
  if (!character.value) return 0

  return Math.ceil(100 * Math.pow(character.value.level, 1.5))
})

const experienceProgress = computed(() => {
  if (!character.value || !requiredExperience.value) return 0

  return Math.min(100, (character.value.experience / requiredExperience.value) * 100)
})

const completedQuests = computed(() => myQuests.value.filter((item) => item.status === 'completed'))

const activeQuests = computed(() => myQuests.value.filter((item) => item.status === 'active'))

const recentAchievements = computed(() => [...achievements.value].slice(0, 3))

const recentLogs = computed(() => logs.value.slice(0, 4))

const questProgress = computed(() => {
  if (!myQuests.value.length) return 0

  return Math.round((completedQuests.value.length / myQuests.value.length) * 100)
})

async function loadDashboard() {
  isLoading.value = true
  error.value = ''

  try {
    const [quests, unlocked, history] = await Promise.all([
      questService.getMyQuests(),
      achievementService.getMyAchievements(),
      api.get<HistoryResponse>('/api/experience-logs'),
    ])

    myQuests.value = quests
    achievements.value = unlocked
    logs.value = history.data.data.data
  } catch {
    error.value = 'Не вдалося завантажити дані Dashboard.'
  } finally {
    isLoading.value = false
  }
}

function formatDate(date: string) {
  return new Date(date).toLocaleString('uk-UA', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getSourceName(source: string) {
  const sources: Record<string, string> = {
    quest: 'Квест',
    achievement: 'Досягнення',
  }

  return sources[source] || source
}

async function handleLogout() {
  try {
    await authStore.logout()
    await router.push({ name: 'login' })
  } catch {
    alert('Не вдалося вийти з акаунта. Спробуй ще раз.')
  }
}

onMounted(loadDashboard)
</script>

<template>
  <main class="dashboard">
    <div class="dashboard-top">
      <button class="logout-button" @click="handleLogout">
        <LogOut :size="14" />
        Вийти з акаунта
      </button>
    </div>

    <div v-if="isLoading" class="loading">Завантаження Dashboard...</div>

    <div v-else-if="error" class="error-message">
      {{ error }}
      <button @click="loadDashboard">Спробувати ще раз</button>
    </div>

    <template v-else-if="character">
      <section class="top-grid">
        <article class="character-card">
          <div class="character-details">
            <span class="role-label">МАНДРІВНИК</span>

            <h1>{{ character.name }}</h1>
            <p>Стратег · Рівень {{ character.level }}</p>

            <div class="experience">
              <div class="experience-label">
                <span>Прогрес рівня</span>
                <strong> {{ character.experience }} / {{ requiredExperience }} XP </strong>
              </div>

              <div class="progress-track">
                <div class="progress-fill" :style="{ width: `${experienceProgress}%` }"></div>
              </div>

              <small>
                Ще {{ Math.max(0, requiredExperience - character.experience) }}
                XP до наступного рівня
              </small>
            </div>
          </div>

          <div class="character-portrait">
            <div class="portrait-circle">
              <img v-if="avatarUrl" :src="avatarUrl" :alt="character.name" />

              <span v-else>
                {{ character.name.charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>

          <RouterLink to="/character" class="profile-link">
            Профіль
            <ChevronRight :size="15" />
          </RouterLink>
        </article>

        <article class="activity-card">
          <div class="streak-icon">
            <Trophy :size="21" />
          </div>
          <span class="card-eyebrow">ТВОЯ ПРИГОДА</span>
          <h2>{{ completedQuests.length }}</h2>
          <p>Виконаних квестів</p>

          <div class="mini-progress">
            <div class="mini-progress-fill" :style="{ width: `${questProgress}%` }"></div>
          </div>

          <small>{{ questProgress }}% виконано від прийнятих квестів</small>
        </article>
      </section>

      <section class="stats-grid">
        <article class="stat-card strength">
          <div class="stat-icon">
            <Swords :size="21" />
          </div>
          <div>
            <span>Сила</span>
            <strong>{{ character.strength }}</strong>
          </div>
        </article>

        <article class="stat-card intelligence">
          <div class="stat-icon">
            <Brain :size="21" />
          </div>
          <div>
            <span>Інтелект</span>
            <strong>{{ character.intelligence }}</strong>
          </div>
        </article>

        <article class="stat-card discipline">
          <div class="stat-icon">
            <Flame :size="21" />
          </div>
          <div>
            <span>Дисципліна</span>
            <strong>{{ character.discipline }}</strong>
          </div>
        </article>
      </section>

      <section class="content-grid">
        <article class="panel quests-panel">
          <div class="panel-header">
            <div>
              <h2>Твої квести</h2>
              <p>{{ completedQuests.length }} з {{ myQuests.length }} виконано</p>
            </div>

            <RouterLink to="/quests" class="panel-link">
              Усі квести
              <ChevronRight :size="14" />
            </RouterLink>
          </div>

          <div class="quest-progress">
            <div class="quest-progress-fill" :style="{ width: `${questProgress}%` }"></div>
          </div>

          <div v-if="myQuests.length" class="quest-list">
            <div v-for="item in myQuests.slice(0, 4)" :key="item.id" class="quest-item">
              <div class="quest-status" :class="{ completed: item.status === 'completed' }">
                <Check v-if="item.status === 'completed'" :size="12" />
                <Circle v-else :size="12" />
              </div>

              <div class="quest-info">
                <strong>{{ item.quest.title }}</strong>
                <small>{{ item.quest.description }}</small>
              </div>

              <span class="quest-reward" :class="{ done: item.status === 'completed' }">
                {{
                  item.status === 'completed' ? 'Виконано' : `+${item.quest.experience_reward} XP`
                }}
              </span>
            </div>
          </div>

          <div v-else class="empty-state">
            Ти ще не прийняв жодного квесту.
            <RouterLink to="/quests" class="panel-link">
              Усі квести
              <ChevronRight :size="14" />
            </RouterLink>
          </div>

          <RouterLink to="/quests" class="add-quest"> + Переглянути доступні квести </RouterLink>
        </article>

        <article class="panel challenge-panel">
          <div class="panel-header">
            <div>
              <h2>Активні квести</h2>
              <p>Твої поточні завдання</p>
            </div>
            <span class="challenge-icon">
              <Target :size="23" />
            </span>
          </div>

          <div class="challenge-number">
            {{ activeQuests.length }}
          </div>

          <p class="challenge-description">
            {{
              activeQuests.length
                ? 'У тебе є завдання, які чекають на виконання.'
                : 'Зараз немає активних квестів. Час знайти новий виклик!'
            }}
          </p>

          <div class="active-quest-list">
            <div v-for="item in activeQuests.slice(0, 3)" :key="item.id" class="active-quest">
              <Sparkles :size="14" />
              {{ item.quest.title }}
            </div>
          </div>

          <RouterLink to="/quests" class="challenge-link"> Перейти до квестів → </RouterLink>
        </article>

        <article class="panel achievements-panel">
          <div class="panel-header">
            <div>
              <h2>Останні досягнення</h2>
              <p>{{ achievements.length }} відкрито</p>
            </div>

            <RouterLink to="/achievements" class="panel-link">
              Переглянути всі
              <ChevronRight :size="14" />
            </RouterLink>
          </div>

          <div v-if="recentAchievements.length" class="achievement-list">
            <div v-for="item in recentAchievements" :key="item.id" class="achievement-item">
              <div class="achievement-icon">
                <Award :size="20" />
              </div>

              <div class="achievement-info">
                <strong>{{ item.achievement.title }}</strong>
                <small>{{ item.achievement.description }}</small>
              </div>

              <span class="unlocked-label">Відкрито</span>
            </div>
          </div>

          <div v-else class="empty-state">Поки немає відкритих досягнень.</div>
        </article>

        <article class="panel activity-panel">
          <div class="panel-header">
            <div>
              <h2>Остання активність</h2>
              <p>Твій шлях розвитку</p>
            </div>

            <RouterLink to="/history" class="panel-link">
              Уся історія
              <ChevronRight :size="14" />
            </RouterLink>
          </div>

          <div v-if="recentLogs.length" class="log-list">
            <div v-for="log in recentLogs" :key="log.id" class="log-item">
              <div class="log-icon">
                <Activity :size="18" />
              </div>

              <div class="log-info">
                <strong>{{ log.description }}</strong>
                <small> {{ getSourceName(log.source) }} · {{ formatDate(log.created_at) }} </small>
              </div>

              <strong class="log-xp" :class="{ negative: log.experience < 0 }">
                {{ log.experience > 0 ? '+' : '' }}{{ log.experience }} XP
              </strong>
            </div>
          </div>

          <div v-else class="empty-state">
            Тут з'являтимуться твої виконані завдання та нагороди.
          </div>
        </article>
      </section>
    </template>

    <div v-else class="loading">Персонажа не знайдено.</div>
  </main>
</template>

<style scoped>
.dashboard {
  min-height: calc(100vh - 66px);
  padding: 20px clamp(16px, 3vw, 36px) 40px;
  background: #090d18;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.dashboard-top {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 14px;
}

.logout-button {
  padding: 7px 13px;
  border: 1px solid #293147;
  border-radius: 8px;
  background: #111727;
  color: #aaa3bd;
  font-size: 11px;
  cursor: pointer;
}

.logout-button:hover {
  border-color: #8b5cf6;
  color: #fff;
}

.top-grid {
  display: grid;
  grid-template-columns: minmax(0, 2.5fr) minmax(210px, 0.85fr);
  gap: 12px;
}

.character-card {
  position: relative;
  display: flex;
  align-items: center;
  min-height: 185px;
  overflow: hidden;
  padding: 25px;
  border: 1px solid #392b60;
  border-radius: 15px;
  background: radial-gradient(circle at 80% 50%, #302052 0%, #17152a 40%, #111525 100%);
}

.character-details {
  position: relative;
  z-index: 2;
  width: 65%;
}

.role-label {
  display: inline-block;
  padding: 5px 8px;
  border: 1px solid #493276;
  border-radius: 5px;
  background: #251b3e;
  color: #c4a8ff;
  font-size: 9px;
  font-weight: bold;
}

.character-details h1 {
  margin: 13px 0 5px;
  font-size: 23px;
}

.character-details > p {
  margin: 0;
  color: #929bb2;
  font-size: 11px;
}

.experience {
  margin-top: 24px;
}

.experience-label {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 8px;
  font-size: 9px;
  color: #a7a9bc;
}

.experience-label strong {
  color: #e5ddff;
}

.progress-track,
.quest-progress,
.mini-progress {
  height: 6px;
  overflow: hidden;
  border-radius: 10px;
  background: #292d43;
}

.progress-fill,
.quest-progress-fill,
.mini-progress-fill {
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #7545d9, #b17aff);
  transition: width 0.3s ease;
}

.experience small {
  display: block;
  margin-top: 9px;
  color: #9b8cbd;
  font-size: 9px;
}

.character-portrait {
  position: absolute;
  top: 50%;
  right: 8%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 145px;
  height: 145px;
  transform: translateY(-50%);
  border: 1px solid #4d347a;
  border-radius: 50%;
  background: radial-gradient(circle, #3e2466, #211632 65%, transparent 66%);
}

.portrait-circle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 92px;
  height: 92px;
  border: 3px solid #8055bc;
  border-radius: 50%;
  background: linear-gradient(145deg, #b38be6, #492b76);
  box-shadow: 0 0 35px #8b5cf666;
  color: white;
  font-size: 38px;
  font-weight: bold;
}

.portrait-circle img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.profile-link {
  position: absolute;
  right: 18px;
  bottom: 15px;
  padding: 7px 14px;
  border: 1px solid #39334f;
  border-radius: 7px;
  color: #d6c9f5;
  font-size: 10px;
  text-decoration: none;
}

.activity-card {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 20px;
  border: 1px solid #343044;
  border-radius: 15px;
  background: radial-gradient(circle at 80% 0%, #29231f, #111522 55%);
}

.streak-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 39px;
  height: 39px;
  margin-bottom: 15px;
  border-radius: 50%;
  background: #29241f;
  color: #fbbf24;
  font-size: 22px;
}

.card-eyebrow {
  color: #929bb2;
  font-size: 9px;
  letter-spacing: 1px;
}

.activity-card h2 {
  margin: 9px 0 3px;
  font-size: 30px;
}

.activity-card p,
.activity-card small {
  margin: 0 0 12px;
  color: #929bb2;
  font-size: 10px;
}

.mini-progress {
  margin-bottom: 8px;
}

.mini-progress-fill {
  background: #e6a52e;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin: 12px 0;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  border: 1px solid #252d41;
  border-radius: 12px;
  background: #101625;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 39px;
  height: 39px;
  flex-shrink: 0;
  border-radius: 10px;
  background: #211b36;
  color: #b18bff;
  font-size: 20px;
}

.stat-card span {
  display: block;
  margin-bottom: 4px;
  color: #969db3;
  font-size: 10px;
}

.stat-card strong {
  font-size: 19px;
}

.intelligence .stat-icon {
  background: #112b30;
  color: #2dd4bf;
}

.discipline .stat-icon {
  background: #12302c;
  color: #34d399;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 2.5fr) minmax(210px, 0.85fr);
  gap: 12px;
}

.panel {
  min-width: 0;
  padding: 18px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 17px;
}

.panel-header h2 {
  margin: 0 0 5px;
  font-size: 14px;
}

.panel-header p {
  margin: 0;
  color: #929bb2;
  font-size: 10px;
}

.panel-link {
  flex-shrink: 0;
  color: #a78bfa;
  font-size: 10px;
  text-decoration: none;
}

.quest-progress {
  height: 4px;
  margin-bottom: 10px;
}

.quest-progress-fill {
  background: #25c69a;
}

.quest-list {
  display: grid;
}

.quest-item,
.achievement-item,
.log-item {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 12px 0;
  border-bottom: 1px solid #252b3b;
}

.quest-status {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  border: 1px solid #555b72;
  border-radius: 50%;
  color: #9ca3b8;
  font-size: 10px;
}

.quest-status.completed {
  border-color: #28c99c;
  background: #28c99c;
  color: #091b18;
}

.quest-info,
.achievement-info,
.log-info {
  display: grid;
  flex: 1;
  min-width: 0;
  gap: 5px;
}

.quest-info strong,
.achievement-info strong,
.log-info strong {
  overflow: hidden;
  font-size: 11px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.quest-info small,
.achievement-info small,
.log-info small {
  overflow: hidden;
  color: #929bb2;
  font-size: 9px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.quest-reward {
  flex-shrink: 0;
  padding: 5px 8px;
  border: 1px solid #514079;
  border-radius: 6px;
  color: #c4a8ff;
  font-size: 9px;
}

.quest-reward.done {
  border-color: #176b55;
  background: #12372f;
  color: #48d6aa;
}

.add-quest {
  display: block;
  margin-top: 12px;
  padding: 10px;
  border: 1px solid #2d354a;
  border-radius: 8px;
  color: #c2c9db;
  font-size: 10px;
  text-align: center;
  text-decoration: none;
}

.challenge-panel {
  display: flex;
  flex-direction: column;
}

.challenge-icon {
  color: #c4a8ff;
  font-size: 24px;
}

.challenge-number {
  margin: 10px 0;
  color: #c4a8ff;
  font-size: 38px;
  font-weight: bold;
}

.challenge-description {
  color: #a1a8bd;
  font-size: 11px;
  line-height: 1.6;
}

.active-quest-list {
  display: grid;
  gap: 12px;
  margin-top: 15px;
}

.active-quest {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #d9d3ea;
  font-size: 10px;
}

.active-quest span {
  color: #a78bfa;
}

.challenge-link {
  margin-top: auto;
  padding-top: 22px;
  color: #a78bfa;
  font-size: 10px;
  text-decoration: none;
}

.achievement-icon,
.log-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  border-radius: 9px;
  background: #29213d;
  color: #c4a8ff;
  font-size: 18px;
}

.unlocked-label {
  flex-shrink: 0;
  color: #65d6b0;
  font-size: 9px;
}

.log-xp {
  flex-shrink: 0;
  color: #a78bfa;
  font-size: 10px;
}

.log-xp.negative {
  color: #fca5a5;
}

.empty-state {
  padding: 20px 8px;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.6;
}

.empty-state a {
  display: block;
  margin-top: 8px;
  color: #a78bfa;
  text-decoration: none;
}

.loading,
.error-message {
  padding: 60px 20px;
  color: #b9a5e8;
  text-align: center;
}

.error-message button {
  display: block;
  margin: 15px auto;
  padding: 9px 16px;
  border: 1px solid #8b5cf6;
  border-radius: 8px;
  background: transparent;
  color: white;
  cursor: pointer;
}

@media (max-width: 900px) {
  .top-grid,
  .content-grid {
    grid-template-columns: 1fr;
  }

  .character-portrait {
    right: 5%;
  }
}

@media (max-width: 600px) {
  .dashboard {
    padding: 14px 10px 25px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .character-card {
    min-height: 210px;
    padding: 17px;
  }

  .character-details {
    width: 75%;
  }

  .character-details h1 {
    font-size: 19px;
  }

  .character-portrait {
    right: -25px;
    opacity: 0.35;
  }

  .profile-link {
    right: 12px;
    bottom: 10px;
  }

  .panel {
    padding: 14px;
  }
}

.logout-button,
.profile-link,
.panel-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
}

.stat-icon svg,
.streak-icon svg,
.challenge-icon svg,
.achievement-icon svg,
.log-icon svg {
  stroke-width: 1.8;
  flex-shrink: 0;
}

.logout-button svg,
.profile-link svg,
.panel-link svg {
  flex-shrink: 0;
}

.strength .stat-icon {
  background: #35202d;
  color: #fb7185;
}

.intelligence .stat-icon {
  background: #112b30;
  color: #2dd4bf;
}

.discipline .stat-icon {
  background: #12302c;
  color: #34d399;
}

.streak-icon svg {
  stroke-width: 2;
}

.challenge-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.active-quest svg {
  flex-shrink: 0;
  color: #a78bfa;
}

.achievement-icon svg,
.log-icon svg {
  color: #c4a8ff;
}

.quest-status {
  display: flex;
  align-items: center;
  justify-content: center;
}

.quest-status svg {
  stroke-width: 2;
}
</style>
