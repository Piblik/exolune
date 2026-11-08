import api from './api'

export interface NotificationData {
  type: string
  title: string
  message: string
  icon: string
  data: Record<string, unknown>
}

export interface GameNotification {
  id: string
  data: NotificationData
  read_at: string | null
  created_at: string
}

interface NotificationsResponse {
  success: boolean
  notifications: GameNotification[]
  unread_count: number
}

interface ActionResponse {
  success: boolean
  message: string
}

export const notificationService = {
  async getAll(): Promise<NotificationsResponse> {
    const response = await api.get<NotificationsResponse>('/api/notifications')

    return response.data
  },

  async markAsRead(id: string): Promise<void> {
    await api.patch<ActionResponse>(`/api/notifications/${id}/read`)
  },

  async markAllAsRead(): Promise<void> {
    await api.patch<ActionResponse>('/api/notifications/read-all')
  },

  async delete(id: string): Promise<void> {
    await api.delete<ActionResponse>(`/api/notifications/${id}`)
  },
}
