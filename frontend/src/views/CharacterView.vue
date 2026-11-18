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
  box-sizing: border-box;
  padding: 32px;
  background: #090d18;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.page-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 26px;
}

.eyebrow {
  color: #a78bfa;
  font-size: 10px;
  font-weight: bold;
  letter-spacing: 1.5px;
}

.page-heading h1 {
  margin: 9px 0;
  font-size: 26px;
  font-weight: 600;
}

.page-heading p {
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
  font-size: 11px;
  text-decoration: none;
  transition: 0.2s ease;
}

.back-link:hover {
  background: #211b36;
  border-color: #6541a5;
}

.back-link svg {
  flex-shrink: 0;
}

.hero-card {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 230px;
  box-sizing: border-box;
  overflow: hidden;
  padding: 28px;
  border: 1px solid #392b60;
  border-radius: 14px;
  background: radial-gradient(circle at 80% 50%, #302052 0%, #17152a 42%, #101625 100%);
}

.hero-content {
  position: relative;
  z-index: 1;
  width: 65%;
  min-width: 0;
}

.role-label {
  display: inline-block;
  padding: 6px 10px;
  border: 1px solid #493276;
  border-radius: 6px;
  background: #211b36;
  color: #c4a8ff;
  font-size: 9px;
  font-weight: bold;
  letter-spacing: 0.8px;
}

.hero-content h2 {
  margin: 14px 0 5px;
  color: #f5f2ff;
  font-size: 27px;
  font-weight: 600;
  overflow-wrap: anywhere;
}

.hero-subtitle {
  margin: 0;
  color: #929bb2;
  font-size: 12px;
}

.xp-section {
  max-width: 480px;
  margin-top: 25px;
}

.xp-label {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 9px;
  color: #929bb2;
  font-size: 10px;
}

.xp-label strong {
  color: #e5ddff;
}

.progress-track {
  height: 8px;
  overflow: hidden;
  border-radius: 10px;
  background: #252d41;
}

.progress-fill {
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #8b5cf6, #c4a8ff);
  transition: width 0.3s ease;
}

.xp-section small {
  display: block;
  margin-top: 9px;
  color: #929bb2;
  font-size: 10px;
}

.hero-avatar {
  position: absolute;
  top: 50%;
  right: 15%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 145px;
  height: 145px;
  overflow: hidden;
  transform: translateY(-50%);
  border: 3px solid #8055bc;
  border-radius: 50%;
  background: linear-gradient(145deg, #b38be6, #492b76);
  box-shadow: 0 0 40px #8b5cf655;
  color: white;
  font-size: 60px;
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
  right: 22px;
  bottom: 18px;
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
  color: #f5f2ff;
  font-size: 20px;
}

.stats-section {
  margin-top: 30px;
}

.section-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 15px;
  margin-bottom: 16px;
}

.section-heading h2 {
  margin: 7px 0 0;
  color: #f5f2ff;
  font-size: 17px;
  font-weight: 600;
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
  padding: 18px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
  transition:
    border-color 0.2s ease,
    transform 0.2s ease;
}

.stat-card:hover {
  border-color: #49376c;
  transform: translateY(-2px);
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
  width: 42px;
  height: 42px;
  border-radius: 11px;
  background: #211b36;
  color: #b18bff;
}

.stat-icon svg {
  flex-shrink: 0;
  stroke-width: 1.8;
}

.stat-label {
  color: #737d96;
  font-size: 8px;
  letter-spacing: 1px;
}

.stat-card h3 {
  margin: 18px 0 7px;
  color: #aab2c8;
  font-size: 13px;
  font-weight: 500;
}

.stat-value {
  color: #f5f2ff;
  font-size: 30px;
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
  gap: 14px;
  margin-top: 20px;
  padding: 18px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
}

.info-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #c4a8ff;
}

.info-icon svg {
  stroke-width: 1.8;
}

.info-panel h3 {
  margin: 2px 0 7px;
  color: #f5f2ff;
  font-size: 13px;
}

.info-panel p {
  margin: 0;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.6;
}

.empty-state {
  padding: 45px 20px;
  border: 1px solid #252d41;
  border-radius: 13px;
  background: #101625;
  text-align: center;
}

.empty-state h2 {
  color: #f5f2ff;
  font-size: 17px;
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

.empty-state a:hover {
  color: #c4a8ff;
}

@media (max-width: 900px) {
  .character-page {
    padding: 24px;
  }

  .hero-avatar {
    right: 12%;
    width: 125px;
    height: 125px;
  }

  .stats-grid {
    gap: 12px;
  }

  .stat-card {
    padding: 15px;
  }
}

@media (max-width: 700px) {
  .page-heading {
    align-items: flex-start;
    flex-direction: column;
    gap: 14px;
  }

  .hero-card {
    min-height: 250px;
    padding: 20px;
  }

  .hero-content {
    width: 85%;
  }

  .hero-content h2 {
    font-size: 23px;
  }

  .hero-avatar {
    right: -30px;
    width: 125px;
    height: 125px;
    opacity: 0.3;
  }

  .level-badge {
    right: 16px;
    bottom: 14px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .character-page {
    padding: 20px 14px 30px;
  }

  .page-heading h1 {
    font-size: 22px;
  }

  .page-heading p {
    font-size: 11px;
  }

  .hero-card {
    min-height: 245px;
    padding: 17px;
  }

  .hero-content {
    width: 100%;
  }

  .hero-content h2 {
    font-size: 21px;
  }

  .xp-section {
    max-width: 100%;
    margin-top: 22px;
  }

  .xp-label {
    font-size: 9px;
  }

  .section-heading h2 {
    font-size: 15px;
  }

  .stat-card {
    padding: 16px;
  }

  .info-panel {
    padding: 15px;
  }
}

@media (max-width: 360px) {
  .character-page {
    padding: 16px 10px 25px;
  }

  .hero-card {
    padding: 14px;
  }

  .hero-content h2 {
    font-size: 19px;
  }

  .section-heading {
    align-items: flex-start;
    flex-direction: column;
    gap: 7px;
  }
}
</style>
