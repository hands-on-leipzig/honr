import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface User {
  id: number
  email: string
  nickname: string | null
  status: string
  short_bio: string | null
  contact_link: string | null
  email_notify_proposals: boolean
  email_tool_info: boolean
  email_volunteer_newsletter: boolean
  is_admin: boolean
  wizard_completed: boolean
}

export const useUserStore = defineStore('user', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)

  async function fetchUser() {
    loading.value = true
    try {
      const response = await apiClient.get('/user')
      const data = response.data
      // Only accept a valid user object (avoid treating HTML/error payload as user)
      if (data && typeof data === 'object' && typeof (data as User).wizard_completed === 'boolean') {
        user.value = data as User
      } else {
        user.value = null
      }
    } catch (error) {
      user.value = null
    } finally {
      loading.value = false
    }
  }

  return { user, loading, fetchUser }
})

