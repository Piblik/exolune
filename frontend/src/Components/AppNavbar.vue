<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { computed } from 'vue'

const authStore = useAuthStore()

const avatarUrl = computed(() => {
  const avatar = authStore.user?.character?.avatar

  if (!avatar) return null

  return `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/storage/${avatar}`
})

const links = [
  { title: 'Головна', path: '/dashboard', icon: '⌂' },
  { title: 'Мій персонаж', path: '/character', icon: '◎' },
  { title: 'Квести', path: '/quests', icon: '⚔' },
  { title: 'Досягнення', path: '/achievements', icon: '♜' },
  { title: 'Статистика', path: '/statistics', icon: '▥' },
  { title: 'Історія активності', path: '/history', icon: '◷' },
  { title: 'Налаштування', path: '/settings', icon: '⚙' },
]
</script>

<template>
  <aside class="sidebar">
    <RouterLink to="/dashboard" class="logo">
      <span class="logo-icon">⚔</span>
      <span>exolune<small> RPG</small></span>
    </RouterLink>

    <nav class="navigation">
      <RouterLink
        v-for="link in links"
        :key="link.path"
        :to="link.path"
        class="nav-link"
        active-class="active"
      >
        <span class="nav-icon">{{ link.icon }}</span>
        {{ link.title }}
      </RouterLink>
    </nav>

    <div class="sidebar-bottom">

      <div class="profile">
        <div class="avatar">
          <img
            v-if="avatarUrl"
            :src="avatarUrl"
            :alt="authStore.user?.character?.name || 'Аватар'"
          />

          <span v-else>
            {{ authStore.user?.name?.charAt(0).toUpperCase() || '?' }}
          </span>
        </div>
        <div class="profile-info">
          <strong>{{ authStore.user?.name || 'Гравець' }}</strong>
          <small>Твій персонаж</small>
        </div>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 100;
  display: flex;
  flex-direction: column;
  width: 186px;
  padding: 16px 11px;
  background: #0d111e;
  border-right: 1px solid #252a3b;
  color: #e8e4f5;
  font-family: Arial, sans-serif;
}

.logo {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 3px 5px 28px;
  color: #fff;
  text-decoration: none;
  font-size: 17px;
  font-weight: bold;
}

.logo small {
  color: #a78bfa;
  font-size: 9px;
}

.logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 31px;
  height: 31px;
  border-radius: 9px;
  background: #8b5cf6;
  font-size: 17px;
}

.navigation {
  display: grid;
  gap: 7px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  min-height: 34px;
  padding: 8px 10px;
  border: 1px solid transparent;
  border-radius: 8px;
  color: #969bb0;
  text-decoration: none;
  font-size: 12px;
  transition: 0.2s;
}

.nav-link:hover {
  color: #fff;
  background: #171a2a;
}

.nav-link.active {
  color: #c4b5fd;
  background: #1d1a35;
  border-color: #382c62;
}

.nav-icon {
  width: 16px;
  text-align: center;
  font-size: 16px;
}

.sidebar-bottom {
  margin-top: auto;
}

.profile {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 12px 3px 3px;
  border-top: 1px solid #252a3b;
}

.avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  border-radius: 50%;
  background: #6541a5;
  color: white;
  font-size: 13px;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.profile-info {
  display: grid;
  gap: 4px;
  min-width: 0;
}

.profile-info strong {
  overflow: hidden;
  color: #e8e4f5;
  font-size: 10px;
  text-overflow: ellipsis;
}

.profile-info small {
  color: #9290aa;
  font-size: 9px;
}

@media (max-width: 650px) {
  .sidebar {
    width: 62px;
    padding: 12px 7px;
  }

  .logo {
    justify-content: center;
    padding: 3px 0 24px;
  }

  .logo > span:last-child,
  .nav-link:not(.active)::after,
  .nav-link {
    font-size: 0;
  }

  .nav-link {
    justify-content: center;
    padding: 10px 5px;
  }

  .nav-icon {
    font-size: 17px;
  }

  .profile {
    justify-content: center;
  }
}
</style>
