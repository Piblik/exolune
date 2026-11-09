import api from './api'

export interface Quest {
  id: number
  title: string
  description: string
  category: string
  difficulty: 'easy' | 'medium' | 'hard' | 'epic'
  experience_reward: number
  strength_reward: number
  intelligence_reward: number
  discipline_reward: number
  is_active: boolean
  is_daily: boolean
}

export interface UserQuest {
  id: number
  user_id: number
  quest_id: number
  daily_date: string | null
  status: 'active' | 'completed' | 'rejected'
  started_at: string
  completed_at: string | null
  quest: Quest
}

interface QuestsResponse {
  success: boolean
  quests: Quest[]
}

interface UserQuestsResponse {
  success: boolean
  data: UserQuest[]
}

export const questService = {
  async getQuests(): Promise<Quest[]> {
    const response = await api.get<QuestsResponse>('/api/quests')
    return response.data.quests
  },

  async getMyQuests(): Promise<UserQuest[]> {
    const response = await api.get<UserQuestsResponse>('/api/my-quests')
    return response.data.data
  },

  async acceptQuest(questId: number): Promise<UserQuest> {
    const response = await api.post<{ data: UserQuest }>('/api/quests/accept', {
      quest_id: questId,
    })

    return response.data.data
  },

  async completeQuest(userQuestId: number): Promise<UserQuest> {
    const response = await api.post<{ data: UserQuest }>(`/api/quests/${userQuestId}/complete`)

    return response.data.data
  },
}
