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
  is_weekly: boolean
  created_by?: number | null
  steps?: QuestStep[]
}

export interface QuestStep {
  id: number
  title: string
  description: string | null
  sort_order: number
}

export interface UserQuestStep {
  id: number
  user_quest_id: number
  quest_step_id: number
  completed_at: string | null
  quest_step?: QuestStep
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
  steps?: UserQuestStep[]
}

export interface CreateQuestStep {
  title: string
  description?: string
}

export interface CreateQuestData {
  title: string
  description: string
  category: string
  difficulty: 'easy' | 'medium' | 'hard' | 'epic'
  steps: CreateQuestStep[]
}

interface QuestActionResponse {
  message: string
  user_quest: UserQuest
}

export const questService = {
  async getQuests(): Promise<Quest[]> {
    const response = await api.get<Quest[]>('/api/quests')
    return response.data
  },

  async getMyQuests(): Promise<UserQuest[]> {
    const response = await api.get<UserQuest[]>('/api/my-quests')
    return response.data
  },

  async acceptQuest(questId: number): Promise<UserQuest> {
    const response = await api.post<QuestActionResponse>('/api/quests/accept', {
      quest_id: questId,
    })

    return response.data.user_quest
  },

  async completeQuest(userQuestId: number): Promise<UserQuest> {
    const response = await api.post<QuestActionResponse>(`/api/quests/${userQuestId}/complete`)

    return response.data.user_quest
  },

  async createQuest(data: CreateQuestData): Promise<UserQuest> {
    const response = await api.post<QuestActionResponse>('/api/quests/custom', data)

    return response.data.user_quest
  },

  async completeStep(userQuestId: number, stepId: number): Promise<UserQuest> {
    const response = await api.post<{ message: string; user_quest: UserQuest }>(
      `/api/quests/${userQuestId}/steps/${stepId}/complete`,
    )

    return response.data.user_quest
  },
}
