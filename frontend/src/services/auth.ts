import api from './api';

export interface Character {
  id: number;
  name: string;
  avatar: string | null;
  level: number;
  experience: number;
  strength: number;
  intelligence: number;
  discipline: number;
}

export interface User {
  id: number;
  name: string;
  email: string;
  character: Character;
}

interface AuthResponse {
  message?: string;
  user: User;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface LoginData {
  email: string;
  password: string;
}

export const authService = {
  async register(data: RegisterData): Promise<User> {
    await api.get('/sanctum/csrf-cookie');

    const response = await api.post<AuthResponse>(
      '/api/register',
      data
    );

    return response.data.user;
  },

  async login(data: LoginData): Promise<User> {
    await api.get('/sanctum/csrf-cookie');

    const response = await api.post<AuthResponse>(
      '/api/login',
      data
    );

    return response.data.user;
  },

  async getUser(): Promise<User> {
    const response = await api.get<User>('/api/user');

    return response.data;
  },

  async logout(): Promise<void> {
    await api.post('/api/logout');
  },
};
