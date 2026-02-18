import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/client'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('auth_token'))

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string) {
    const response = await apiClient.post('/auth/login', { email, password })
    token.value = response.data.token
    localStorage.setItem('auth_token', response.data.token)
    return response.data.user
  }

  function setToken(newToken: string) {
    token.value = newToken
    localStorage.setItem('auth_token', newToken)
  }

  async function logout() {
    try {
      await apiClient.post('/auth/logout')
    } catch {
      // Ignore errors on logout
    }
    token.value = null
    localStorage.removeItem('auth_token')
  }

  return { token, isAuthenticated, login, logout, setToken }
})


