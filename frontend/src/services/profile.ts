import api from './api'
import type { User } from './auth'

export interface UpdateProfileData {
  name: string
  email: string
  character_name: string
  avatar?: File | null
}

interface ProfileResponse {
  success: boolean
  message: string
  user: User
}

export const profileService = {
  async updateProfile(data: UpdateProfileData): Promise<User> {
    const formData = new FormData()

    formData.append('name', data.name)
    formData.append('email', data.email)
    formData.append('character_name', data.character_name)

    if (data.avatar) {
      formData.append('avatar', data.avatar)
    }

    const response = await api.post<ProfileResponse>('/api/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    return response.data.user
  },

  async deleteAvatar(): Promise<User> {
    const response = await api.delete<ProfileResponse>('/api/profile/avatar')

    return response.data.user
  },
}
