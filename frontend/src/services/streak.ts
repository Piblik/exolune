import api from './api'

export interface Streak {
  current_streak: number
  longest_streak: number
  last_activity_date: string | null
}

interface StreakResponse {
  data: Streak
}

export const streakService = {
  async get(): Promise<Streak> {
    const response = await api.get<StreakResponse>('/api/streak')
    return response.data.data
  },
}
