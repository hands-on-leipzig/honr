<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Einstellungen</h1>

    <!-- Administration (Admin Only) -->
    <div v-if="userStore.user?.is_admin" class="bg-white rounded-lg shadow mb-4">
      <RouterLink
        to="/admin/tables"
        class="block w-full px-4 py-3 text-left hover:bg-gray-50"
      >
        <span class="font-semibold text-blue-600">Administration</span>
      </RouterLink>
    </div>

    <!-- Account Settings -->
    <div class="bg-white rounded-lg shadow mb-4">
      <div class="p-4 border-b border-gray-200">
        <h2 class="font-semibold">Benutzer-Einstellungen</h2>
      </div>
      <div class="divide-y divide-gray-200">
        <!-- E-Mail ändern -->
        <button @click="showEmailModal = true" class="w-full px-4 py-3 text-left hover:bg-gray-50">
          E-Mail ändern
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
        
        <!-- Kontakt-Link ändern -->
        <button @click="showContactLinkModal = true" class="w-full px-4 py-3 text-left hover:bg-gray-50">
          {{ userStore.user?.contact_link ? 'Kontakt-Link ändern' : 'Kontakt-Link hinzufügen' }}
        </button>
        
        <!-- eMail-Einstellungen -->
        <button @click="showEmailSettingsModal = true" class="w-full px-4 py-3 text-left hover:bg-gray-50">
          eMail-Einstellungen
        </button>
        
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
    <Modal :show="showNameModal" @close="showNameModal = false" title="Name ändern">
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
    </Modal>

    <!-- Bio Modal -->
    <Modal :show="showBioModal" @close="showBioModal = false" title="Über mich ändern">
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
    </Modal>

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

    <!-- Email Modal -->
    <div v-if="showEmailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-sm p-6">
        <h3 class="text-lg font-semibold mb-4">E-Mail ändern</h3>
        <form @submit.prevent="requestEmailChange">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Aktuelle E-Mail</label>
            <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">
              {{ userStore.user?.email }}
            </div>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Neue E-Mail</label>
            <input v-model="emailForm.new_email" type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Passwort zur Bestätigung</label>
            <input v-model="emailForm.password" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div v-if="emailError" class="mb-4 text-red-600 text-sm">{{ emailError }}</div>
          <div v-if="emailSuccess" class="mb-4 text-green-600 text-sm">{{ emailSuccess }}</div>
          <div class="flex gap-2">
            <button type="button" @click="closeEmailModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button type="submit" :disabled="emailLoading" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ emailLoading ? 'Senden...' : 'Link senden' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Email Settings Modal -->
    <Modal :show="showEmailSettingsModal" @close="closeEmailSettingsModal" title="eMail-Einstellungen">
      <form @submit.prevent="updateEmailPreferences">
        <div class="space-y-4 mb-4">
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="emailPreferences.email_notify_proposals"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-3 text-sm text-gray-700">Benachrichtung zu angenommenen Vorschlägen</span>
          </label>
          
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="emailPreferences.email_tool_info"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-3 text-sm text-gray-700">Informationen zum Tool</span>
          </label>
          
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="emailPreferences.email_volunteer_newsletter"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-3 text-sm text-gray-700">Volunteer-Newsletter von Hands On Technology</span>
          </label>
        </div>
        <div v-if="emailSettingsError" class="mb-4 text-red-600 text-sm">{{ emailSettingsError }}</div>
        <div class="flex gap-2">
          <button type="button" @click="closeEmailSettingsModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
          <button type="submit" :disabled="emailSettingsLoading" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ emailSettingsLoading ? 'Speichern...' : 'Speichern' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Contact Link Modal -->
    <Modal :show="showContactLinkModal" @close="closeContactLinkModal" title="Kontakt-Link ändern">
      <form @submit.prevent="updateContactLink">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Typ</label>
          <div class="flex gap-4">
            <label class="flex items-center cursor-pointer">
              <input
                type="radio"
                v-model="contactLinkType"
                value="email"
                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">E-Mail</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input
                type="radio"
                v-model="contactLinkType"
                value="link"
                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">Link</span>
            </label>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ contactLinkType === 'email' ? 'E-Mail-Adresse' : 'Link' }}
          </label>
          <input 
            v-model="contactLinkForm.contact_link" 
            :type="contactLinkType === 'email' ? 'email' : 'text'"
            class="w-full px-3 py-2 border border-gray-300 rounded-md" 
            :placeholder="contactLinkType === 'email' ? 'email@example.com' : 'z.B. https://linkedin.com/in/...'"
          />
          <p class="mt-1 text-xs text-gray-500">
            {{ contactLinkType === 'email' ? 'E-Mail-Adresse' : 'Social media Link oder Website' }}
          </p>
        </div>
        <div v-if="contactLinkError" class="mb-4 text-red-600 text-sm">{{ contactLinkError }}</div>
        <div class="flex gap-2">
          <button type="button" @click="closeContactLinkModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
          <button type="submit" :disabled="contactLinkLoading" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ contactLinkLoading ? 'Speichern...' : 'Speichern' }}
          </button>
        </div>
      </form>
    </Modal>

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
import Modal from '@/components/common/Modal.vue'

const router = useRouter()
const userStore = useUserStore()
const authStore = useAuthStore()

// Modal visibility
const showEmailModal = ref(false)
const showPasswordModal = ref(false)
const showNameModal = ref(false)
const showBioModal = ref(false)
const showContactLinkModal = ref(false)
const showEmailSettingsModal = ref(false)
const showRegionalPartnerModal = ref(false)
const showDeleteModal = ref(false)

// Email preferences
const emailPreferences = reactive({
  email_notify_proposals: false,
  email_tool_info: false,
  email_volunteer_newsletter: false,
})

// Forms
const emailForm = reactive({ new_email: '', password: '' })
const passwordForm = reactive({ current: '', new: '', confirm: '' })
const nameForm = reactive({ nickname: '' })
const bioForm = reactive({ short_bio: '' })
const contactLinkForm = reactive({ contact_link: '' })
const contactLinkType = ref<'email' | 'link'>('link')
const regionalPartnerForm = reactive({ name: '' })
const deleteForm = reactive({ password: '' })

// Loading states
const emailLoading = ref(false)
const passwordLoading = ref(false)
const nameLoading = ref(false)
const bioLoading = ref(false)
const contactLinkLoading = ref(false)
const emailSettingsLoading = ref(false)
const regionalPartnerLoading = ref(false)
const deleteLoading = ref(false)

// Error states
const emailError = ref('')
const emailSuccess = ref('')
const passwordError = ref('')
const nameError = ref('')
const bioError = ref('')
const contactLinkError = ref('')
const emailSettingsError = ref('')
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

function closeEmailModal() {
  showEmailModal.value = false
  emailForm.new_email = ''
  emailForm.password = ''
  emailError.value = ''
  emailSuccess.value = ''
}

function closePasswordModal() {
  showPasswordModal.value = false
  passwordForm.current = ''
  passwordForm.new = ''
  passwordForm.confirm = ''
  passwordError.value = ''
}

function closeEmailSettingsModal() {
  showEmailSettingsModal.value = false
  emailSettingsError.value = ''
  // Reset to current user values
  if (userStore.user) {
    emailPreferences.email_notify_proposals = userStore.user.email_notify_proposals || false
    emailPreferences.email_tool_info = userStore.user.email_tool_info || false
    emailPreferences.email_volunteer_newsletter = userStore.user.email_volunteer_newsletter || false
  }
}

onMounted(async () => {
  await userStore.fetchUser()
  if (userStore.user) {
    nameForm.nickname = userStore.user.nickname || ''
    bioForm.short_bio = userStore.user.short_bio || ''
    // Strip mailto: prefix when loading for display
    const contactLink = userStore.user.contact_link || ''
    if (contactLink.startsWith('mailto:')) {
      contactLinkForm.contact_link = contactLink.replace(/^mailto:/i, '')
      contactLinkType.value = 'email'
    } else {
      contactLinkForm.contact_link = contactLink
      contactLinkType.value = 'link'
    }
    regionalPartnerForm.name = userStore.user.regional_partner_name || ''
    emailPreferences.email_notify_proposals = userStore.user.email_notify_proposals || false
    emailPreferences.email_tool_info = userStore.user.email_tool_info || false
    emailPreferences.email_volunteer_newsletter = userStore.user.email_volunteer_newsletter || false
  }
})

async function requestEmailChange() {
  emailError.value = ''
  emailSuccess.value = ''
  emailLoading.value = true
  try {
    await apiClient.post('/user/email-change', {
      new_email: emailForm.new_email,
      password: emailForm.password,
    })
    emailSuccess.value = 'Ein Bestätigungslink wurde an die neue E-Mail-Adresse gesendet.'
    emailForm.password = ''
  } catch (err: any) {
    emailError.value = err.response?.data?.message || 'Fehler beim Anfordern der E-Mail-Änderung.'
  } finally {
    emailLoading.value = false
  }
}

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
    await apiClient.put('/user', { nickname: nameForm.nickname })
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
    await apiClient.put('/user', { short_bio: bioForm.short_bio })
    await userStore.fetchUser()
    showBioModal.value = false
  } catch (err: any) {
    bioError.value = err.response?.data?.message || 'Fehler beim Ändern der Biografie.'
  } finally {
    bioLoading.value = false
  }
}

async function updateContactLink() {
  contactLinkError.value = ''
  contactLinkLoading.value = true
  try {
    let contactLink = contactLinkForm.contact_link.trim()
    
    // If email type, add mailto: prefix before saving
    if (contactLinkType.value === 'email' && contactLink) {
      // Remove existing mailto: if present (shouldn't be, but just in case)
      contactLink = contactLink.replace(/^mailto:/i, '')
      contactLink = 'mailto:' + contactLink
    }
    
    await apiClient.put('/user', { contact_link: contactLink || null })
    await userStore.fetchUser()
    showContactLinkModal.value = false
  } catch (err: any) {
    contactLinkError.value = err.response?.data?.message || 'Fehler beim Ändern des Kontakt-Links.'
  } finally {
    contactLinkLoading.value = false
  }
}

function closeContactLinkModal() {
  showContactLinkModal.value = false
  contactLinkError.value = ''
  // Reset form to current user values
  if (userStore.user) {
    const contactLink = userStore.user.contact_link || ''
    if (contactLink.startsWith('mailto:')) {
      contactLinkForm.contact_link = contactLink.replace(/^mailto:/i, '')
      contactLinkType.value = 'email'
    } else {
      contactLinkForm.contact_link = contactLink
      contactLinkType.value = 'link'
    }
  } else {
    contactLinkForm.contact_link = ''
    contactLinkType.value = 'link'
  }
}

async function updateEmailPreferences() {
  emailSettingsError.value = ''
  emailSettingsLoading.value = true
  try {
    await apiClient.put('/user', {
      email_notify_proposals: emailPreferences.email_notify_proposals,
      email_tool_info: emailPreferences.email_tool_info,
      email_volunteer_newsletter: emailPreferences.email_volunteer_newsletter,
    })
    await userStore.fetchUser()
    showEmailSettingsModal.value = false
  } catch (err: any) {
    emailSettingsError.value = err.response?.data?.message || 'Fehler beim Speichern der E-Mail-Einstellungen.'
  } finally {
    emailSettingsLoading.value = false
  }
}

async function updateRegionalPartner() {
  regionalPartnerError.value = ''
  regionalPartnerLoading.value = true
  try {
    await apiClient.put('/user', { regional_partner_name: regionalPartnerForm.name })
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
