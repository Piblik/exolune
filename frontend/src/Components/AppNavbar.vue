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
  background: #090d18;
  border-right: 1px solid #252d41;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
  transition: width 0.2s ease;
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
  white-space: nowrap;
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
  flex-shrink: 0;
  border-radius: 9px;
  background: linear-gradient(135deg, #8b5cf6, #6541a5);
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
  min-height: 36px;
  padding: 8px 10px;
  border: 1px solid transparent;
  border-radius: 9px;
  color: #929bb2;
  text-decoration: none;
  font-size: 12px;
  transition: 0.2s ease;
}

.nav-link:hover {
  color: #f5f2ff;
  background: #151b2b;
  border-color: #252d41;
}

.nav-link.active {
  color: #c4a8ff;
  background: #211b36;
  border-color: #382c62;
}

.nav-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  flex-shrink: 0;
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
  border-top: 1px solid #252d41;
}

.avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  overflow: hidden;
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
  white-space: nowrap;
}

.profile-info small {
  color: #929bb2;
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

  .logo > span:last-child {
    display: none;
  }

  .nav-link {
    justify-content: center;
    padding: 10px 5px;
    font-size: 0;
  }

  .nav-icon {
    font-size: 17px;
  }

  .profile {
    justify-content: center;
  }

  .profile-info {
    display: none;
  }
}

@media (max-width: 360px) {
  .sidebar {
    width: 54px;
    padding: 10px 5px;
  }

  .nav-link {
    padding: 9px 3px;
  }
}
</style>
