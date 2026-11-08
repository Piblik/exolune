<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationsStore } from '../stores/notifications'
import { Bell } from 'lucide-vue-next'

const authStore = useAuthStore()
const notificationsStore = useNotificationsStore()
const route = useRoute()

const isNotificationsOpen = ref(false)

const headerContent = computed(() => {
  const pages: Record<string, { title: string; subtitle: string }> = {
    '/character': {
      title: 'Мій персонаж',
      subtitle: 'Твій розвиток, характеристики та нагороди.',
    },
    '/quests': {
      title: 'Квести',
      subtitle: 'Обирай випробування та здобувай досвід.',
    },
    '/achievements': {
      title: 'Досягнення',
      subtitle: 'Колекція твоїх перемог і нових цілей.',
    },
    '/statistics': {
      title: 'Статистика',
      subtitle: 'Аналізуй прогрес і знаходь свій ритм.',
    },
    '/history': {
      title: 'Історія активності',
      subtitle: 'Усі кроки твоєї пригоди в одному місці.',
    },
    '/settings': {
      title: 'Налаштування',
      subtitle: 'Керуй профілем, сповіщеннями та приватністю.',
    },
  }

  if (route.path === '/dashboard') {
    return {
      title: `Вітаємо, ${authStore.user?.name || 'Гравцю'}!`,
      subtitle: 'Продовжуй рух до кращої версії себе.',
    }
  }

  return (
    pages[route.path] ?? {
      title: `Вітаємо, ${authStore.user?.name || 'Гравцю'}!`,
      subtitle: 'Продовжуй рух до кращої версії себе.',
    }
  )
})

onMounted(() => {
  notificationsStore.fetchNotifications()
})

function toggleNotifications() {
  isNotificationsOpen.value = !isNotificationsOpen.value

  if (isNotificationsOpen.value) {
    notificationsStore.fetchNotifications()
  }
}

async function markAsRead(id: string, readAt: string | null) {
  if (!readAt) {
    await notificationsStore.markAsRead(id)
  }
}

async function markAllAsRead() {
  await notificationsStore.markAllAsRead()
}

async function deleteNotification(id: string) {
  await notificationsStore.deleteNotification(id)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleString('uk-UA', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function notificationIcon(type: string): string {
  const icons: Record<string, string> = {
    quest_completed: '⚔',
    achievement_unlocked: '🏆',
    experience_gained: '✨',
    level_up: '⬆',
  }

  return icons[type] || '🔔'
}
</script>

<template>
  <header class="app-header">
    <div class="greeting">
      <h2>{{ headerContent.title }}</h2>
      <p>{{ headerContent.subtitle }}</p>
    </div>

    <div class="header-actions">
      <div class="notification-wrapper">
        <button
          class="header-icon notification"
          :class="{ active: isNotificationsOpen }"
          title="Сповіщення"
          @click="toggleNotifications"
        >
          <Bell :size="17" :stroke-width="1.8" />

          <span v-if="notificationsStore.unreadCount > 0" class="notification-badge">
            {{ notificationsStore.unreadCount > 99 ? '99+' : notificationsStore.unreadCount }}
          </span>
        </button>

        <div v-if="isNotificationsOpen" class="notifications-dropdown">
          <div class="notifications-header">
            <div>
              <strong>Сповіщення</strong>
              <small> {{ notificationsStore.unreadCount }} непрочитаних </small>
            </div>

            <button class="close-button" @click="isNotificationsOpen = false">×</button>
          </div>

          <div v-if="notificationsStore.unreadCount > 0" class="notifications-actions">
            <button @click="markAllAsRead">Позначити всі як прочитані</button>
          </div>

          <div v-if="notificationsStore.isLoading" class="empty-state">Завантаження...</div>

          <div v-else-if="notificationsStore.error" class="empty-state error">
            {{ notificationsStore.error }}
          </div>

          <div v-else-if="notificationsStore.notifications.length === 0" class="empty-state">
            <span>🔔</span>
            <p>Поки що немає сповіщень</p>
            <small>Тут з'являтимуться твої ігрові події.</small>
          </div>

          <div v-else class="notifications-list">
            <div
              v-for="notification in notificationsStore.notifications"
              :key="notification.id"
              class="notification-item"
              :class="{ unread: !notification.read_at }"
              @click="markAsRead(notification.id, notification.read_at)"
            >
              <div class="notification-icon">
                {{ notificationIcon(notification.data.type) }}
              </div>

              <div class="notification-content">
                <strong>{{ notification.data.title }}</strong>
                <p>{{ notification.data.message }}</p>

                <small>
                  {{ formatDate(notification.created_at) }}
                </small>
              </div>

              <button
                class="delete-button"
                title="Видалити"
                @click.stop="deleteNotification(notification.id)"
              >
                ×
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 66px;
  padding: 10px 24px;
  background: #090d18;
  border-bottom: 1px solid #202638;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.greeting {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
}

.greeting h2 {
  margin: 0;
  font-size: 17px;
  font-weight: 600;
  line-height: 1.3;
  letter-spacing: 0.1px;
}

.greeting p {
  margin: 0;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.4;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 9px;
}

.header-icon {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 29px;
  height: 29px;
  border: 1px solid #293147;
  border-radius: 8px;
  background: transparent;
  color: #aab2c8;
  font-size: 19px;
}

.notification {
  cursor: pointer;
  font-size: 15px;
  transition: 0.2s;
}

.notification:hover,
.notification.active {
  background: #1d1a35;
  border-color: #6541a5;
  color: #c4b5fd;
}

.notification-badge {
  position: absolute;
  top: -7px;
  right: -7px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  border: 2px solid #090d18;
  border-radius: 20px;
  background: #8b5cf6;
  color: white;
  font-size: 9px;
  font-weight: bold;
}

.notification-wrapper {
  position: relative;
}

.notifications-dropdown {
  position: absolute;
  top: 42px;
  right: 0;
  z-index: 200;
  width: 350px;
  max-height: 460px;
  overflow: hidden;
  background: #111625;
  border: 1px solid #30374d;
  border-radius: 12px;
  box-shadow: 0 12px 35px #0008;
}

.notifications-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px;
  border-bottom: 1px solid #293147;
}

.notifications-header strong {
  display: block;
  font-size: 13px;
}

.notifications-header small {
  display: block;
  margin-top: 5px;
  color: #929bb2;
  font-size: 10px;
}

.close-button,
.delete-button {
  border: none;
  background: transparent;
  color: #929bb2;
  cursor: pointer;
}

.close-button {
  font-size: 22px;
}

.notifications-actions {
  padding: 9px 15px;
  border-bottom: 1px solid #293147;
  text-align: right;
}

.notifications-actions button {
  border: none;
  background: transparent;
  color: #a78bfa;
  font-size: 10px;
  cursor: pointer;
}

.notifications-list {
  max-height: 330px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 13px;
  border-bottom: 1px solid #252c3e;
  cursor: pointer;
  transition: 0.2s;
}

.notification-item:hover {
  background: #1a2032;
}

.notification-item.unread {
  background: #1a1930;
}

.notification-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: #292044;
  font-size: 16px;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-content strong {
  display: block;
  margin-bottom: 5px;
  color: #eee9ff;
  font-size: 11px;
}

.notification-content p {
  margin: 0 0 6px;
  color: #aab2c8;
  font-size: 10px;
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.notification-content small {
  color: #737e98;
  font-size: 9px;
}

.delete-button {
  font-size: 18px;
}

.delete-button:hover,
.close-button:hover {
  color: #f87171;
}

.empty-state {
  padding: 30px 15px;
  color: #929bb2;
  font-size: 11px;
  text-align: center;
}

.empty-state > span {
  font-size: 25px;
}

.empty-state p {
  margin: 10px 0 5px;
  color: #e8e4f5;
}

.empty-state small {
  font-size: 10px;
}

.error {
  color: #f87171;
}

@media (max-width: 500px) {
  .app-header {
    padding: 10px 12px;
  }

  .notifications-dropdown {
    position: fixed;
    top: 75px;
    right: 10px;
    width: min(350px, calc(100vw - 20px));
  }

  .greeting h2 {
    font-size: 14px;
  }

  .greeting p {
    font-size: 9px;
  }
}
</style>
