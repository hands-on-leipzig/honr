<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Einstellungen</h1>

    <!-- Account Settings -->
    <div class="bg-white rounded-lg shadow mb-4">
      <div class="p-4 border-b border-gray-200">
        <h2 class="font-semibold">Benutzer-Einstellungen</h2>
      </div>
      <div class="divide-y divide-gray-200">
        <!-- E-Mail ändern - disabled, requires mail server -->
        <button class="w-full px-4 py-3 text-left text-gray-400 cursor-not-allowed" disabled>
          E-Mail ändern (demnächst)
        </button>
        
        <!-- Passwort ändern -->
        <button @click="showPasswordModal = true" class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Passwort ändern
        </button>
        
        <!-- Name ändern -->
        <button @click="showNameModal = true" class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Name ändern
        </button>
        
        <!-- Über mich ändern -->
        <button @click="showBioModal = true" class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Über mich ändern
        </button>
        
        <!-- Newsletter-Einwilligung -->
        <div class="w-full px-4 py-3 flex items-center justify-between">
          <span>Newsletter-Einwilligung</span>
          <label class="relative inline-flex items-center cursor-pointer">
            <input 
              type="checkbox" 
              v-model="newsletterConsent" 
              @change="updateNewsletter"
              class="sr-only peer"
            />
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
          </label>
        </div>
        
        <!-- Regionalpartner Name ändern - only show if user is regional partner -->
        <button 
          v-if="userStore.user?.is_admin" 
          @click="showRegionalPartnerModal = true" 
          class="w-full px-4 py-3 text-left hover:bg-gray-50"
        >
          Regionalpartner Name ändern
        </button>
        
        <!-- Abmelden -->
        <button @click="handleLogout" class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Abmelden
        </button>
        
        <!-- Benutzer löschen -->
        <button @click="showDeleteModal = true" class="w-full px-4 py-3 text-left text-red-600 hover:bg-red-50">
          Benutzer löschen
        </button>
      </div>
    </div>

    <!-- Support -->
    <div class="bg-white rounded-lg shadow mb-4">
      <div class="p-4 border-b border-gray-200">
        <h2 class="font-semibold">Support</h2>
      </div>
      <div class="px-4 py-3">
        <p class="text-sm text-gray-600 mb-3">
          Hast du Ideen, Feedback oder Vorschläge zur Verbesserung? Wir freuen uns über deine Nachricht!
        </p>
        <a
          href="mailto:honr@hands-on-technology.org?subject=HONR%20-%20Feedback"
          class="inline-flex items-center text-blue-600 hover:underline"
        >
          honr@hands-on-technology.org
        </a>
      </div>
    </div>

    <!-- Administration (Admin Only) -->
    <div v-if="userStore.user?.is_admin" class="bg-white rounded-lg shadow mb-4">
      <RouterLink
        to="/admin/tables"
        class="block w-full px-4 py-3 text-left hover:bg-gray-50"
      >
        <span class="font-semibold text-blue-600">Administration</span>
      </RouterLink>
    </div>

    <!-- Password Modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Passwort ändern</h3>
        <form @submit.prevent="updatePassword">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Aktuelles Passwort</label>
            <input v-model="passwordForm.current" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Neues Passwort</label>
            <input v-model="passwordForm.new" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            <ul class="mt-1 text-xs text-gray-500 space-y-0.5">
              <li :class="passwordForm.new.length >= 8 ? 'text-green-600' : ''">• Mindestens 8 Zeichen</li>
              <li :class="/[A-Z]/.test(passwordForm.new) ? 'text-green-600' : ''">• Mindestens 1 Großbuchstabe</li>
              <li :class="/[a-z]/.test(passwordForm.new) ? 'text-green-600' : ''">• Mindestens 1 Kleinbuchstabe</li>
              <li :class="/[0-9]/.test(passwordForm.new) ? 'text-green-600' : ''">• Mindestens 1 Zahl</li>
            </ul>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Neues Passwort bestätigen</label>
            <input v-model="passwordForm.confirm" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            <p v-if="passwordForm.confirm && passwordForm.new !== passwordForm.confirm" class="mt-1 text-sm text-red-600">
              Passwörter stimmen nicht überein
            </p>
          </div>
          <div v-if="passwordError" class="mb-4 text-red-600 text-sm">{{ passwordError }}</div>
          <div class="flex gap-2">
            <button type="button" @click="closePasswordModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button type="submit" :disabled="passwordLoading || !isPasswordValid" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ passwordLoading ? 'Speichern...' : 'Speichern' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Name Modal -->
    <div v-if="showNameModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Name ändern</h3>
        <form @submit.prevent="updateName">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="nameForm.nickname" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div v-if="nameError" class="mb-4 text-red-600 text-sm">{{ nameError }}</div>
          <div class="flex gap-2">
            <button type="button" @click="showNameModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button type="submit" :disabled="nameLoading" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ nameLoading ? 'Speichern...' : 'Speichern' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bio Modal -->
    <div v-if="showBioModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Über mich ändern</h3>
        <form @submit.prevent="updateBio">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Über mich</label>
            <textarea v-model="bioForm.short_bio" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
          </div>
          <div v-if="bioError" class="mb-4 text-red-600 text-sm">{{ bioError }}</div>
          <div class="flex gap-2">
            <button type="button" @click="showBioModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button type="submit" :disabled="bioLoading" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ bioLoading ? 'Speichern...' : 'Speichern' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Regional Partner Modal -->
    <div v-if="showRegionalPartnerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Regionalpartner Name ändern</h3>
        <form @submit.prevent="updateRegionalPartner">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Regionalpartner Name</label>
            <input v-model="regionalPartnerForm.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div v-if="regionalPartnerError" class="mb-4 text-red-600 text-sm">{{ regionalPartnerError }}</div>
          <div class="flex gap-2">
            <button type="button" @click="showRegionalPartnerModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button type="submit" :disabled="regionalPartnerLoading" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ regionalPartnerLoading ? 'Speichern...' : 'Speichern' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-sm p-6">
        <h3 class="text-lg font-semibold mb-4 text-red-600">Benutzer löschen</h3>
        <p class="mb-4 text-gray-600">Bist du sicher, dass du deinen Account löschen möchtest? Diese Aktion kann nicht rückgängig gemacht werden.</p>
        <form @submit.prevent="deleteAccount">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Passwort zur Bestätigung</label>
            <input v-model="deleteForm.password" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div v-if="deleteError" class="mb-4 text-red-600 text-sm">{{ deleteError }}</div>
          <div class="flex gap-2">
            <button type="button" @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button type="submit" :disabled="deleteLoading" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50">
              {{ deleteLoading ? 'Löschen...' : 'Löschen' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/api/client'

const router = useRouter()
const userStore = useUserStore()
const authStore = useAuthStore()

// Modal visibility
const showPasswordModal = ref(false)
const showNameModal = ref(false)
const showBioModal = ref(false)
const showRegionalPartnerModal = ref(false)
const showDeleteModal = ref(false)

// Newsletter
const newsletterConsent = ref(false)

// Forms
const passwordForm = reactive({ current: '', new: '', confirm: '' })
const nameForm = reactive({ nickname: '' })
const bioForm = reactive({ short_bio: '' })
const regionalPartnerForm = reactive({ name: '' })
const deleteForm = reactive({ password: '' })

// Loading states
const passwordLoading = ref(false)
const nameLoading = ref(false)
const bioLoading = ref(false)
const regionalPartnerLoading = ref(false)
const deleteLoading = ref(false)

// Error states
const passwordError = ref('')
const nameError = ref('')
const bioError = ref('')
const regionalPartnerError = ref('')
const deleteError = ref('')

// Password validation
const isPasswordValid = computed(() => {
  const pw = passwordForm.new
  return (
    pw.length >= 8 &&
    /[A-Z]/.test(pw) &&
    /[a-z]/.test(pw) &&
    /[0-9]/.test(pw) &&
    pw === passwordForm.confirm
  )
})

function closePasswordModal() {
  showPasswordModal.value = false
  passwordForm.current = ''
  passwordForm.new = ''
  passwordForm.confirm = ''
  passwordError.value = ''
}

onMounted(async () => {
  await userStore.fetchUser()
  if (userStore.user) {
    newsletterConsent.value = userStore.user.consent_to_newsletter
    nameForm.nickname = userStore.user.nickname || ''
    bioForm.short_bio = userStore.user.short_bio || ''
    regionalPartnerForm.name = userStore.user.regional_partner_name || ''
  }
})

async function updatePassword() {
  passwordError.value = ''
  if (passwordForm.new !== passwordForm.confirm) {
    passwordError.value = 'Passwörter stimmen nicht überein.'
    return
  }
  passwordLoading.value = true
  try {
    await apiClient.put('/user/password', {
      current_password: passwordForm.current,
      password: passwordForm.new,
      password_confirmation: passwordForm.confirm,
    })
    showPasswordModal.value = false
    passwordForm.current = ''
    passwordForm.new = ''
    passwordForm.confirm = ''
  } catch (err: any) {
    passwordError.value = err.response?.data?.message || 'Fehler beim Ändern des Passworts.'
  } finally {
    passwordLoading.value = false
  }
}

async function updateName() {
  nameError.value = ''
  nameLoading.value = true
  try {
    await apiClient.put('/user/profile', { nickname: nameForm.nickname })
    await userStore.fetchUser()
    showNameModal.value = false
  } catch (err: any) {
    nameError.value = err.response?.data?.message || 'Fehler beim Ändern des Namens.'
  } finally {
    nameLoading.value = false
  }
}

async function updateBio() {
  bioError.value = ''
  bioLoading.value = true
  try {
    await apiClient.put('/user/profile', { short_bio: bioForm.short_bio })
    await userStore.fetchUser()
    showBioModal.value = false
  } catch (err: any) {
    bioError.value = err.response?.data?.message || 'Fehler beim Ändern der Biografie.'
  } finally {
    bioLoading.value = false
  }
}

async function updateNewsletter() {
  try {
    await apiClient.put('/user/profile', { consent_to_newsletter: newsletterConsent.value })
    await userStore.fetchUser()
  } catch (err: any) {
    // Revert on error
    newsletterConsent.value = !newsletterConsent.value
  }
}

async function updateRegionalPartner() {
  regionalPartnerError.value = ''
  regionalPartnerLoading.value = true
  try {
    await apiClient.put('/user/profile', { regional_partner_name: regionalPartnerForm.name })
    await userStore.fetchUser()
    showRegionalPartnerModal.value = false
  } catch (err: any) {
    regionalPartnerError.value = err.response?.data?.message || 'Fehler beim Ändern des Regionalpartner-Namens.'
  } finally {
    regionalPartnerLoading.value = false
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

async function deleteAccount() {
  deleteError.value = ''
  deleteLoading.value = true
  try {
    await apiClient.delete('/user', { data: { password: deleteForm.password } })
    await authStore.logout()
    router.push('/login')
  } catch (err: any) {
    deleteError.value = err.response?.data?.message || 'Fehler beim Löschen des Accounts.'
  } finally {
    deleteLoading.value = false
  }
}
</script>
