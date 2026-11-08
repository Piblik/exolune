import api from './api'

export interface Achievement {
  id: number
  title: string
  description: string
  icon:string | null
  condition_type: string
  condition_value: number
  experience_reward: number
  is_active: boolean
}

export interface UserAchievement {
  id: number
  user_id: number
  achievement_id: number
  unlocked_at: string
  achievement: Achievement
}

interface AchievementsResponse {
  success: boolean
  achievements: Achievement[]
}

interface UserAchievementsResponse {
  success: boolean
  achievements: UserAchievement[]
}

export const achievementService = {
  async getAchievements(): Promise<Achievement[]> {
    const response = await api.get<AchievementsResponse>('/api/achievements')
    return response.data.achievements
  },

  async getMyAchievements(): Promise<UserAchievement[]> {
    const response = await api.get<UserAchievementsResponse>(
      '/api/my-achievements',
    )
    return response.data.achievements
  },
}
