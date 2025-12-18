import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface User {
  id: number
  email: string
  nickname: string | null
  status: string
  home_location: string | null
  short_bio: string | null
  regional_partner_name: string | null
  consent_to_newsletter: boolean
  is_admin: boolean
}

export const useUserStore = defineStore('user', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)

  async function fetchUser() {
    loading.value = true
    try {
      const response = await apiClient.get('/user')
      user.value = response.data
    } catch (error) {
      user.value = null
    } finally {
      loading.value = false
    }
  }

  return { user, loading, fetchUser }
})

