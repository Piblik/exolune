import { defineStore } from 'pinia';
import { ref } from 'vue';
import {
  authService,
  type User,
  type LoginData,
  type RegisterData,
} from '../services/auth';

interface ApiError {
  response?: {
    data?: {
      message?: string;
    };
  };
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User |null>(null);
  const isAuthenticated = ref(false);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  async function register(data: RegisterData) {
    isLoading.value = true;
    error.value = null;

    try {
      user.value = await authService.register(data);
      isAuthenticated.value = true;
    } catch (err: unknown) {
      const apiError = err as ApiError;
      error.value =
        error.value = apiError.response?.data?.message ?? 'Помилка реєстрації';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function login(data: LoginData) {
    isAuthenticated.value = true;
    error.value = null;

    try {
      user.value = await authService.login(data);
      isAuthenticated.value = true;
    } catch (err: unknown) {
      const apiError = err as ApiError;
      error.value = error.value = apiError.response?.data?.message ?? 'Помилка аторизації'
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchUser() {
    isLoading.value = true;

    try {
      user.value = await authService.getUser();
      isAuthenticated.value = true;
    } catch {
      user.value = null;
      isAuthenticated.value = false;
    } finally {
      isLoading.value = false;
    }
  }

  async function logout() {
    isLoading.value = true;

    try {
      await authService.logout();
    } finally {
      user.value = null;
      isAuthenticated.value = false;
      isLoading.value = false;
    }
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    register,
    login,
    fetchUser,
    logout,
  };
});
