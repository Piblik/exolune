<script setup lang="ts">
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const links = [
  { title: 'Головна', path: '/dashboard' },
  { title: 'Квести', path: '/quests' },
  { title: 'Досягнення', path: '/achievements' },
  { title: 'Історія', path: '/history' },
]
</script>

<template>
  <nav class="navbar">
    <RouterLink to="/dashboard" class="logo"> exolune <span>RPG</span> </RouterLink>

    <div class="nav-links">
      <RouterLink
        v-for="link in links"
        :key="link.path"
        :to="link.path"
        class="nav-link"
        active-class="active"
      >
        {{ link.title }}
      </RouterLink>
    </div>

    <div class="nav-user">
      <div class="user-avatar">
        {{ authStore.user?.name?.charAt(0).toUpperCase() || '?' }}
      </div>

      <span class="user-name">
        {{ authStore.user?.name || 'Гравець' }}
      </span>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 30px;
  padding: 18px 48px;
  background: #171326;
  border-bottom: 1px solid #342b4c;
  color: #f5f2ff;
  font-family: Arial, sans-serif;
}

.logo {
  color: #f5f2ff;
  font-size: 23px;
  font-weight: bold;
  text-decoration: none;
  white-space: nowrap;
}

.logo span {
  color: #a78bfa;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 28px;
}

.nav-link {
  color: #aaa3bd;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.2s;
}

.nav-link:hover,
.nav-link.active {
  color: #c4b5fd;
}

.nav-link.active {
  font-weight: bold;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #8b5cf6;
  color: white;
  font-weight: bold;
}

.user-name {
  font-size: 14px;
  color: #e9e3f5;
}

@media (max-width: 850px) {
  .navbar {
    flex-wrap: wrap;
    padding: 16px 20px;
  }

  .nav-links {
    order: 3;
    width: 100%;
    overflow-x: auto;
    gap: 22px;
    padding: 8px 0;
  }

  .user-name {
    display: none;
  }
}
</style>
