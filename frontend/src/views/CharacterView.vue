<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { ChevronLeft, Swords, Brain, Flame, Sparkles } from 'lucide-vue-next'

const authStore = useAuthStore()

const user = computed(() => authStore.user)
const character = computed(() => user.value?.character)

const avatarUrl = computed(() => {
  const avatar = character.value?.avatar

  if (!avatar) return null

  return `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/storage/${avatar}`
})

const requiredExperience = computed(() => {
  if (!character.value) return 0

  return Math.ceil(100 * Math.pow(character.value.level, 1.5))
})

const experienceProgress = computed(() => {
  if (!character.value || !requiredExperience.value) return 0

  return Math.min(100, (character.value.experience / requiredExperience.value) * 100)
})

const stats = computed(() => {
  if (!character.value) return []

  return [
    {
      name: 'Сила',
      value: character.value.strength,
      icon: Swords,
      description: 'Фізична підготовка та витривалість',
      color: 'strength',
    },
    {
      name: 'Інтелект',
      value: character.value.intelligence,
      icon: Brain,
      description: 'Знання, навчання та розвиток',
      color: 'intelligence',
    },
    {
      name: 'Дисципліна',
      value: character.value.discipline,
      icon: Flame,
      description: 'Самоконтроль та регулярність',
      color: 'discipline',
    },
  ]
})
</script>

<template>
  <main class="character-page">
    <div class="page-heading">
      <div>
        <span class="eyebrow">ТВІЙ ІГРОВИЙ ПРОФІЛЬ</span>
        <h1>Мій персонаж</h1>
        <p>Переглядай свій прогрес та характеристики героя.</p>
      </div>

      <RouterLink to="/dashboard" class="back-link">
        <ChevronLeft :size="15" />
        До Dashboard
      </RouterLink>
    </div>

    <template v-if="character">
      <section class="hero-card">
        <div class="hero-content">
          <span class="role-label">МАНДРІВНИК</span>

          <h2>{{ character.name }}</h2>
          <p class="hero-subtitle">Стратег · Рівень {{ character.level }}</p>

          <div class="xp-section">
            <div class="xp-label">
              <span>Досвід персонажа</span>
              <strong> {{ character.experience }} / {{ requiredExperience }} XP </strong>
            </div>

            <div class="progress-track">
              <div class="progress-fill" :style="{ width: `${experienceProgress}%` }"></div>
            </div>

            <small>
              Залишилося
              {{ Math.max(0, requiredExperience - character.experience) }}
              XP до наступного рівня
            </small>
          </div>
        </div>

        <div class="hero-avatar">
          <img v-if="avatarUrl" :src="avatarUrl" :alt="character.name" />

          <span v-else>
            {{ character.name.charAt(0).toUpperCase() }}
          </span>
        </div>

        <div class="level-badge">
          <span>LEVEL</span>
          <strong>{{ character.level }}</strong>
        </div>
      </section>

      <section class="stats-section">
        <div class="section-heading">
          <div>
            <span class="eyebrow">ХАРАКТЕРИСТИКИ</span>
            <h2>Параметри персонажа</h2>
          </div>
          <span class="stats-count">3 характеристики</span>
        </div>

        <div class="stats-grid">
          <article v-for="stat in stats" :key="stat.name" class="stat-card" :class="stat.color">
            <div class="stat-top">
              <div class="stat-icon">
                <component :is="stat.icon" :size="22" />
              </div>
              <span class="stat-label">ХАРАКТЕРИСТИКА</span>
            </div>

            <h3>{{ stat.name }}</h3>
            <strong class="stat-value">{{ stat.value }}</strong>
            <p>{{ stat.description }}</p>
          </article>
        </div>
      </section>

      <section class="info-panel">
        <div class="info-icon">
          <Sparkles :size="24" />
        </div>
        <div>
          <h3>Твій шлях тільки починається</h3>
          <p>
            Виконуй квести, отримуй досвід та розвивай характеристики. Кожна дія наближає тебе до
            наступного рівня.
          </p>
        </div>
      </section>
    </template>

    <div v-else class="empty-state">
      <h2>Персонажа не знайдено</h2>
      <p>Спробуй повернутися на Dashboard.</p>
      <RouterLink to="/dashboard">Повернутися</RouterLink>
    </div>
  </main>
</template>

<style scoped>
.character-page {
  min-height: calc(100vh - 66px);
  padding: 28px clamp(16px, 3vw, 36px) 45px;
  background: #090d18;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.page-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 25px;
}

.eyebrow {
  color: #a78bfa;
  font-size: 10px;
  font-weight: bold;
  letter-spacing: 1.5px;
}

.page-heading h1 {
  margin: 9px 0;
  font-size: 27px;
}

.page-heading p {
  margin: 0;
  color: #929bb2;
  font-size: 12px;
}

.back-link {
  padding: 10px 14px;
  border: 1px solid #343044;
  border-radius: 8px;
  color: #c4a8ff;
  font-size: 11px;
  text-decoration: none;
}

.back-link:hover {
  border-color: #8b5cf6;
}

.hero-card {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 245px;
  overflow: hidden;
  padding: 30px;
  border: 1px solid #392b60;
  border-radius: 16px;
  background: radial-gradient(circle at 80% 50%, #302052 0%, #17152a 42%, #111525 100%);
}

.hero-content {
  z-index: 1;
  width: 65%;
}

.role-label {
  display: inline-block;
  padding: 6px 10px;
  border: 1px solid #493276;
  border-radius: 5px;
  background: #251b3e;
  color: #c4a8ff;
  font-size: 9px;
  font-weight: bold;
}

.hero-content h2 {
  margin: 15px 0 5px;
  font-size: 30px;
}

.hero-subtitle {
  margin: 0;
  color: #929bb2;
  font-size: 12px;
}

.xp-section {
  max-width: 480px;
  margin-top: 28px;
}

.xp-label {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 9px;
  color: #a7a9bc;
  font-size: 10px;
}

.xp-label strong {
  color: #e5ddff;
}

.progress-track {
  height: 8px;
  overflow: hidden;
  border-radius: 10px;
  background: #292d43;
}

.progress-fill {
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #7545d9, #b17aff);
  transition: width 0.3s ease;
}

.xp-section small {
  display: block;
  margin-top: 9px;
  color: #9b8cbd;
  font-size: 10px;
}

.hero-avatar {
  position: absolute;
  top: 50%;
  right: 15%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 155px;
  height: 155px;
  transform: translateY(-50%);
  border: 3px solid #8055bc;
  border-radius: 50%;
  background: linear-gradient(145deg, #b38be6, #492b76);
  box-shadow: 0 0 45px #8b5cf666;
  color: white;
  font-size: 65px;
  font-weight: bold;
}

.hero-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.level-badge {
  position: absolute;
  right: 25px;
  bottom: 20px;
  display: grid;
  gap: 3px;
  text-align: center;
}

.level-badge span {
  color: #a78bfa;
  font-size: 9px;
  letter-spacing: 1px;
}

.level-badge strong {
  font-size: 20px;
}

.stats-section {
  margin-top: 32px;
}

.section-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 15px;
  margin-bottom: 17px;
}

.section-heading h2 {
  margin: 7px 0 0;
  font-size: 18px;
}

.stats-count {
  color: #929bb2;
  font-size: 10px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 15px;
}

.stat-card {
  min-width: 0;
  padding: 20px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
}

.stat-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 43px;
  height: 43px;
  border-radius: 11px;
  background: #211b36;
  color: #b18bff;
  font-size: 22px;
}

.stat-label {
  color: #737d96;
  font-size: 8px;
  letter-spacing: 1px;
}

.stat-card h3 {
  margin: 20px 0 7px;
  color: #aab1c5;
  font-size: 13px;
}

.stat-value {
  font-size: 32px;
}

.stat-card p {
  margin: 9px 0 0;
  color: #929bb2;
  font-size: 10px;
  line-height: 1.5;
}

.intelligence .stat-icon {
  background: #112b30;
  color: #2dd4bf;
}

.discipline .stat-icon {
  background: #12302c;
  color: #34d399;
}

.info-panel {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-top: 20px;
  padding: 20px;
  border: 1px solid #343044;
  border-radius: 13px;
  background: #101625;
}

.info-icon {
  color: #c4a8ff;
  font-size: 25px;
}

.info-panel h3 {
  margin: 2px 0 8px;
  font-size: 13px;
}

.info-panel p {
  margin: 0;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.6;
}

.empty-state {
  padding: 60px 20px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
  text-align: center;
}

.empty-state p {
  color: #929bb2;
  font-size: 12px;
}

.empty-state a {
  display: inline-block;
  margin-top: 10px;
  color: #a78bfa;
  text-decoration: none;
}

@media (max-width: 700px) {
  .page-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .hero-card {
    min-height: 260px;
    padding: 20px;
  }

  .hero-content {
    width: 80%;
  }

  .hero-content h2 {
    font-size: 23px;
  }

  .hero-avatar {
    right: -35px;
    width: 125px;
    height: 125px;
    opacity: 0.35;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .level-badge {
    right: 18px;
    bottom: 15px;
  }
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.back-link svg {
  flex-shrink: 0;
}

.stat-icon svg {
  stroke-width: 1.8;
  flex-shrink: 0;
}

.info-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.info-icon svg {
  stroke-width: 1.8;
}
</style>
