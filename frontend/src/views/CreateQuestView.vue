<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { questService, type CreateQuestStep, type CreateQuestData } from '../services/quests'

const router = useRouter()

const title = ref('')
const description = ref('')
const category = ref('Навчання')
const difficulty = ref<CreateQuestData['difficulty']>('medium')

const steps = ref<CreateQuestStep[]>([
  { title: '', description: '' },
  { title: '', description: '' },
])

const isSubmitting = ref(false)
const error = ref('')

const difficultyLabels = {
  easy: 'Легкий',
  medium: 'Середній',
  hard: 'Складний',
  epic: 'Епічний',
}

const baseExperience = {
  easy: 30,
  medium: 50,
  hard: 80,
  epic: 120,
}

const baseStatReward = {
  easy: 2,
  medium: 4,
  hard: 7,
  epic: 10,
}

const rewards = computed(() => {
  const count = steps.value.length

  const experience = Math.min(200, baseExperience[difficulty.value] + (count - 2) * 10)

  const statReward = Math.min(15, baseStatReward[difficulty.value] + Math.floor((count - 2) / 2))

  const normalizedCategory = category.value.toLowerCase()

  let strength = 0
  let intelligence = 0
  let discipline = 0

  if (
    normalizedCategory.includes('спорт') ||
    normalizedCategory.includes('трен') ||
    normalizedCategory.includes('фітнес') ||
    normalizedCategory.includes('sport') ||
    normalizedCategory.includes('fitness')
  ) {
    strength = statReward
  } else if (
    normalizedCategory.includes('навч') ||
    normalizedCategory.includes('освіт') ||
    normalizedCategory.includes('читан') ||
    normalizedCategory.includes('study') ||
    normalizedCategory.includes('education')
  ) {
    intelligence = statReward
  } else {
    discipline = statReward
  }

  return {
    experience,
    strength,
    intelligence,
    discipline,
  }
})

function addStep() {
  if (steps.value.length >= 8) return

  steps.value.push({
    title: '',
    description: '',
  })
}

function removeStep(index: number) {
  if (steps.value.length <= 2) return

  steps.value.splice(index, 1)
}

async function createQuest() {
  error.value = ''

  if (steps.value.length < 2 || steps.value.length > 8) {
    error.value = 'Квест повинен містити від 2 до 8 етапів.'
    return
  }

  if (steps.value.some((step) => !step.title.trim())) {
    error.value = 'Заповни назви всіх етапів.'
    return
  }

  isSubmitting.value = true

  try {
    const questData: CreateQuestData = {
      title: title.value.trim(),
      description: description.value.trim(),
      category: category.value,
      difficulty: difficulty.value,
      steps: steps.value.map((step) => ({
        title: step.title.trim(),
        description: step.description?.trim() || undefined,
      })),
    }

    await questService.createQuest(questData)

    await router.push({ name: 'quests' })
  } catch (err) {
    console.error('Помилка створення квесту:', err)
    error.value = 'Не вдалося створити квест. Перевір дані та спробуй ще раз.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="create-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">СТВОРИ ВЛАСНЕ ВИПРОБУВАННЯ</p>
        <h1>Новий квест</h1>
        <p class="subtitle">Визнач свою мету, розбий її на етапи та отримай нагороду.</p>
      </div>

      <RouterLink to="/quests" class="back-link"> ← До квестів </RouterLink>
    </header>

    <form class="create-layout" @submit.prevent="createQuest">
      <section class="form-section">
        <div class="section-heading">
          <span class="section-number">01</span>
          <div>
            <h2>Основна інформація</h2>
            <p>Опиши завдання, яке хочеш виконати.</p>
          </div>
        </div>

        <div class="field">
          <label for="quest-title">Назва квесту</label>
          <input
            id="quest-title"
            v-model="title"
            type="text"
            maxlength="100"
            placeholder="Наприклад, Вивчити TypeScript"
            required
          />
        </div>

        <div class="field">
          <label for="quest-description">Опис</label>
          <textarea
            id="quest-description"
            v-model="description"
            maxlength="1000"
            rows="4"
            placeholder="Опиши, чого хочеш досягти..."
            required
          ></textarea>
        </div>

        <div class="fields-row">
          <div class="field">
            <label for="quest-category">Категорія</label>
            <select id="quest-category" v-model="category">
              <option value="Навчання">Навчання</option>
              <option value="Спорт">Спорт</option>
              <option value="Саморозвиток">Саморозвиток</option>
              <option value="Здоров'я">Здоров'я</option>
              <option value="Інше">Інше</option>
            </select>
          </div>

          <div class="field">
            <label for="quest-difficulty">Складність</label>
            <select id="quest-difficulty" v-model="difficulty">
              <option value="easy">Легка</option>
              <option value="medium">Середня</option>
              <option value="hard">Складна</option>
              <option value="epic">Епічна</option>
            </select>
          </div>
        </div>
      </section>

      <section class="form-section">
        <div class="section-heading">
          <span class="section-number">02</span>
          <div>
            <h2>Етапи квесту</h2>
            <p>Розділи велику мету на невеликі завдання.</p>
          </div>
        </div>

        <div class="steps-list">
          <article v-for="(step, index) in steps" :key="index" class="step-card">
            <div class="step-heading">
              <span class="step-number">ЕТАП {{ index + 1 }}</span>

              <button
                v-if="steps.length > 2"
                type="button"
                class="remove-step"
                @click="removeStep(index)"
              >
                Видалити
              </button>
            </div>

            <div class="field">
              <label :for="`step-title-${index}`">Назва етапу</label>
              <input
                :id="`step-title-${index}`"
                v-model="step.title"
                type="text"
                maxlength="100"
                placeholder="Наприклад, Пройти теорію"
                required
              />
            </div>

            <div class="field">
              <label :for="`step-description-${index}`"> Опис етапу </label>
              <textarea
                :id="`step-description-${index}`"
                v-model="step.description"
                maxlength="500"
                rows="2"
                placeholder="Додаткові деталі (необов'язково)"
              ></textarea>
            </div>
          </article>
        </div>

        <button v-if="steps.length < 8" type="button" class="add-step-button" @click="addStep">
          + Додати етап
        </button>

        <p class="steps-counter">Додано етапів: {{ steps.length }} / 8</p>
      </section>

      <aside class="rewards-section">
        <p class="eyebrow">НАГОРОДА ЗА КВЕСТ</p>
        <h2>Твоя винагорода</h2>
        <p class="reward-description">
          Нагорода розраховується автоматично залежно від складності та кількості етапів.
        </p>

        <div class="xp-reward">
          <span>Досвід</span>
          <strong>+{{ rewards.experience }} XP</strong>
        </div>

        <div class="reward-divider"></div>

        <div class="stat-reward">
          <span>Сила</span>
          <strong>+{{ rewards.strength }}</strong>
        </div>

        <div class="stat-reward">
          <span>Інтелект</span>
          <strong>+{{ rewards.intelligence }}</strong>
        </div>

        <div class="stat-reward">
          <span>Дисципліна</span>
          <strong>+{{ rewards.discipline }}</strong>
        </div>

        <div class="reward-divider"></div>

        <div class="reward-info">
          <span>Складність</span>
          <strong>{{ difficultyLabels[difficulty] }}</strong>
        </div>

        <div class="reward-info">
          <span>Кількість етапів</span>
          <strong>{{ steps.length }}</strong>
        </div>

        <p class="reward-note">Остаточні нагороди визначає сервер під час створення квесту.</p>

        <p v-if="error" class="error-message">
          {{ error }}
        </p>

        <button type="submit" class="create-button" :disabled="isSubmitting">
          {{ isSubmitting ? 'Створення...' : 'Створити квест' }}
        </button>

        <RouterLink to="/quests" class="cancel-link"> Скасувати </RouterLink>
      </aside>
    </form>
  </main>
</template>

<style scoped>
.create-page {
  min-height: calc(100vh - 66px);
  padding: 28px clamp(16px, 3vw, 36px) 45px;
  background: #090d18;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
  box-sizing: border-box;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 28px;
}

.eyebrow {
  margin: 0 0 9px;
  color: #a78bfa;
  font-size: 10px;
  font-weight: bold;
  letter-spacing: 1.5px;
}

.page-header h1 {
  margin: 0 0 9px;
  font-size: 27px;
}

.subtitle {
  margin: 0;
  color: #929bb2;
  font-size: 12px;
  line-height: 1.6;
}

.back-link,
.cancel-link {
  color: #c4a8ff;
  text-decoration: none;
  transition:
    color 0.2s,
    border-color 0.2s;
}

.back-link {
  display: inline-flex;
  align-items: center;
  padding: 10px 14px;
  border: 1px solid #343044;
  border-radius: 8px;
  font-size: 11px;
  white-space: nowrap;
}

.back-link:hover,
.cancel-link:hover {
  color: #a78bfa;
}

.back-link:hover {
  border-color: #8b5cf6;
}

.create-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  align-items: start;
  gap: 20px;
  max-width: 1450px;
  margin: 0 auto;
}

.form-section,
.rewards-section {
  padding: 24px;
  background: #101625;
  border: 1px solid #252d41;
  border-radius: 14px;
  box-sizing: border-box;
}

.form-section {
  margin-bottom: 18px;
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}

.section-number {
  display: grid;
  place-items: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 11px;
  background: #211b36;
  color: #c4a8ff;
  font-size: 13px;
  font-weight: bold;
}

.section-heading h2,
.rewards-section h2 {
  margin: 0 0 6px;
  color: #f5f2ff;
  font-size: 17px;
}

.section-heading p {
  margin: 0;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.5;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 19px;
}

.field:last-child {
  margin-bottom: 0;
}

.field label {
  color: #d8dced;
  font-size: 12px;
  font-weight: bold;
}

.field input,
.field textarea,
.field select {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 13px;
  border: 1px solid #252d41;
  border-radius: 8px;
  outline: none;
  background: #090d18;
  color: #f5f2ff;
  font-family: inherit;
  font-size: 12px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.field input:focus,
.field textarea:focus,
.field select:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 2px #8b5cf622;
}

.field textarea {
  resize: vertical;
  min-height: 75px;
}

.field input::placeholder,
.field textarea::placeholder {
  color: #737d96;
}

.fields-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.steps-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.step-card {
  padding: 18px;
  border: 1px solid #252d41;
  border-radius: 11px;
  background: #090d18;
}

.step-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 17px;
}

.step-number {
  color: #a78bfa;
  font-size: 10px;
  font-weight: bold;
  letter-spacing: 1px;
}

.remove-step {
  padding: 6px 10px;
  border: 1px solid #593343;
  border-radius: 7px;
  background: #281722;
  color: #fb7185;
  font-size: 10px;
  cursor: pointer;
  transition: 0.2s;
}

.remove-step:hover {
  border-color: #fb7185;
  background: #3b1d2a;
}

.add-step-button {
  width: 100%;
  margin-top: 15px;
  padding: 12px;
  border: 1px dashed #5b4688;
  border-radius: 9px;
  background: #211b36;
  color: #c4a8ff;
  font-size: 12px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
}

.add-step-button:hover {
  border-color: #a78bfa;
  background: #29213d;
}

.steps-counter {
  margin: 11px 0 0;
  color: #737d96;
  font-size: 10px;
  text-align: right;
}

.rewards-section {
  position: sticky;
  top: 24px;
}

.rewards-section h2 {
  margin-top: 10px;
  font-size: 19px;
}

.reward-description {
  margin: 0 0 22px;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.6;
}

.xp-reward,
.stat-reward,
.reward-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin: 14px 0;
  color: #929bb2;
  font-size: 11px;
}

.xp-reward strong {
  color: #c4a8ff;
  font-size: 17px;
}

.stat-reward strong,
.reward-info strong {
  color: #e5ddf8;
  font-size: 12px;
}

.reward-divider {
  height: 1px;
  margin: 18px 0;
  background: #252d41;
}

.reward-note {
  margin-top: 18px;
  color: #737d96;
  font-size: 10px;
  line-height: 1.6;
}

.create-button {
  width: 100%;
  margin-top: 16px;
  padding: 13px;
  border: 1px solid #9b79ed;
  border-radius: 8px;
  background: linear-gradient(135deg, #8b5cf6, #7044cb);
  color: white;
  font-size: 12px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
}

.create-button:hover:not(:disabled) {
  background: linear-gradient(135deg, #9b70ff, #8055df);
  box-shadow: 0 4px 16px #8b5cf633;
}

.create-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.cancel-link {
  display: block;
  margin-top: 14px;
  text-align: center;
  font-size: 11px;
}

.error-message {
  padding: 11px;
  border: 1px solid #653044;
  border-radius: 8px;
  background: #281722;
  color: #fb7185;
  font-size: 11px;
  line-height: 1.5;
}

@media (max-width: 1000px) {
  .create-layout {
    grid-template-columns: minmax(0, 1fr) 260px;
    gap: 14px;
  }

  .form-section,
  .rewards-section {
    padding: 19px;
  }
}

@media (max-width: 800px) {
  .create-layout {
    grid-template-columns: 1fr;
  }

  .rewards-section {
    position: static;
  }
}

@media (max-width: 600px) {
  .create-page {
    padding: 22px 14px 35px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 22px;
  }

  .page-header h1 {
    font-size: 24px;
  }

  .form-section,
  .rewards-section {
    padding: 17px;
    border-radius: 12px;
  }

  .section-heading {
    gap: 11px;
    margin-bottom: 20px;
  }

  .section-heading h2 {
    font-size: 15px;
  }

  .fields-row {
    grid-template-columns: 1fr;
    gap: 0;
  }

  .step-card {
    padding: 14px;
  }

  .field input,
  .field textarea,
  .field select {
    font-size: 16px;
  }
}
</style>
