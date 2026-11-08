import { defineStore } from 'pinia'
import { ref } from 'vue'
import { notificationService, type GameNotification } from '../services/notifications'

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref<GameNotification[]>([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function fetchNotifications() {
    isLoading.value = true
    error.value = null

    try {
      const response = await notificationService.getAll()

      notifications.value = response.notifications
      unreadCount.value = response.unread_count
    } catch (err) {
      console.error('Notifications error:', err)
      error.value = 'Не вдалося завантажити сповіщення.'
    } finally {
      isLoading.value = false
    }
  }

  async function markAsRead(id: string) {
    try {
      await notificationService.markAsRead(id)

      const notification = notifications.value.find((item) => item.id === id)

      if (notification && !notification.read_at) {
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch (err) {
      console.error('Mark as read error:', err)
    }
  }

  async function markAllAsRead() {
    try {
      await notificationService.markAllAsRead()

      const now = new Date().toISOString()

      notifications.value.forEach((notification) => {
        notification.read_at ??= now
      })

      unreadCount.value = 0
    } catch (err) {
      console.error('Mark all as read error:', err)
    }
  }

  async function deleteNotification(id: string) {
    try {
      await notificationService.delete(id)

      const notification = notifications.value.find((item) => item.id === id)

      if (notification && !notification.read_at) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }

      notifications.value = notifications.value.filter((item) => item.id !== id)
    } catch (err) {
      console.error('Delete notification error:', err)
    }
  }

  function resetNotifications() {
    notifications.value = []
    unreadCount.value = 0
    error.value = null
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    error,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    resetNotifications,
  }
})
