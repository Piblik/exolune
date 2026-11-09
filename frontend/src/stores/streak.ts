import { defineStore } from 'pinia'
import { ref } from 'vue'
import { streakService, type Streak } from '../services/streak'

export const useStreakStore = defineStore('streak', () => {
  const streak = ref<Streak>({
    current_streak: 0,
    longest_streak: 0,
    last_activity_date: null,
  })

  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function fetchStreak() {
    isLoading.value = true
    error.value = null

    try {
      streak.value = await streakService.get()
    } catch (err) {
      console.error('Помилка завантаження серії:', err)
      error.value = 'Не вдалося завантажити серію активності.'
    } finally {
      isLoading.value = false
    }
  }

  function reset() {
    streak.value = {
      current_streak: 0,
      longest_streak: 0,
      last_activity_date: null,
    }
    error.value = null
  }

  return {
    streak,
    isLoading,
    error,
    fetchStreak,
    reset,
  }
})
