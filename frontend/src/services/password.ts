import api from './api'

export interface ChangePasswordData {
  current_password: string
  password: string
  password_confirmation: string
}

interface ChangePasswordResponse {
  success: boolean
  message: string
}

export const passwordService = {
  async changePassword(data: ChangePasswordData): Promise<string> {
    const response = await api.put<ChangePasswordResponse>('/api/change-password', data)

    return response.data.message
  },
}
