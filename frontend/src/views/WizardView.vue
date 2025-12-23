<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <!-- Progress Bar -->
      <div class="mb-6">
        <div class="flex justify-between mb-2">
          <span class="text-sm text-gray-600">Schritt {{ currentStep }} von {{ totalSteps }}</span>
          <span class="text-sm text-gray-600">{{ Math.round((currentStep / totalSteps) * 100) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
          ></div>
        </div>
      </div>

      <!-- Step 1: Welcome -->
      <div v-if="currentStep === 1" class="space-y-4">
        <h2 class="text-2xl font-bold mb-4">Willkommen zu HONR</h2>
        <p class="text-gray-700">
          Wie bei Hands on Technology freuen wir uns über dein Engagement und darüber, dass du es hier mit anderen teilst.
        </p>
        <p class="text-gray-700">
          Mit den nächsten Schritten erstellst du dein User-Profile. Du entscheidest selbst, wie viel Du über dich verrätst.
        </p>
        <button
          @click="nextStep"
          class="w-full mt-6 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Weiter
        </button>
      </div>

      <!-- Step 2: Name -->
      <div v-if="currentStep === 2" class="space-y-4">
        <h2 class="text-2xl font-bold mb-4">Name</h2>
        <p class="text-sm text-gray-600 mb-4">Der Name muss eindeutig sein.</p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input
            v-model="wizardData.nickname"
            type="text"
            required
            @blur="checkNicknameAvailability"
            class="w-full px-3 py-2 border border-gray-300 rounded-md"
            placeholder="Dein Name"
          />
          <p v-if="nicknameError" class="mt-1 text-sm text-red-600">{{ nicknameError }}</p>
          <p v-else-if="wizardData.nickname && !nicknameChecking" class="mt-1 text-sm text-gray-500">
            Dein Name muss einzigartig sein.
          </p>
        </div>
        <button
          @click="nextStep"
          :disabled="!wizardData.nickname || nicknameError || nicknameChecking"
          class="w-full mt-6 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Weiter
        </button>
      </div>

      <!-- Step 3: Bio -->
      <div v-if="currentStep === 3" class="space-y-4">
        <h2 class="text-2xl font-bold mb-4">Über dich</h2>
        <p class="text-sm text-gray-600 mb-4">Dieser Text erscheint unter deinem Namen</p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Über mich</label>
          <textarea
            v-model="wizardData.short_bio"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md"
            placeholder="Optional: Erzähle etwas über dich..."
          ></textarea>
        </div>
        <div class="flex gap-2">
          <button
            @click="skipStep"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Überspringen
          </button>
          <button
            @click="nextStep"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Weiter
          </button>
        </div>
      </div>

      <!-- Step 4: Contact Link -->
      <div v-if="currentStep === 4" class="space-y-4">
        <h2 class="text-2xl font-bold mb-4">Kontakt-Link</h2>
        <p class="text-sm text-gray-600 mb-4">Dieser Link erscheint neben deinem Namen</p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Typ</label>
          <div class="flex gap-4 mb-4">
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
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ contactLinkType === 'email' ? 'E-Mail-Adresse' : 'Link' }}
          </label>
          <input
            v-model="wizardData.contact_link"
            :type="contactLinkType === 'email' ? 'email' : 'text'"
            class="w-full px-3 py-2 border border-gray-300 rounded-md"
            :placeholder="contactLinkType === 'email' ? 'email@example.com' : 'z.B. https://linkedin.com/in/...'"
          />
          <p class="mt-1 text-xs text-gray-500">
            {{ contactLinkType === 'email' ? 'E-Mail-Adresse' : 'Social media Link oder Website' }}
          </p>
        </div>
        <div class="flex gap-2">
          <button
            @click="skipStep"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Überspringen
          </button>
          <button
            @click="nextStep"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Weiter
          </button>
        </div>
      </div>

      <!-- Step 5: Email Settings -->
      <div v-if="currentStep === 5" class="space-y-4">
        <h2 class="text-2xl font-bold mb-4">eMail-Einstellungen</h2>
        <div class="space-y-4">
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="wizardData.email_notify_proposals"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-3 text-sm text-gray-700">Benachrichtung zu angenommenen Vorschlägen</span>
          </label>
          
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="wizardData.email_tool_info"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-3 text-sm text-gray-700">Informationen zum Tool</span>
          </label>
          
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="wizardData.email_volunteer_newsletter"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-3 text-sm text-gray-700">Volunteer-Newsletter von Hands On Technology</span>
          </label>
        </div>
        <button
          @click="nextStep"
          class="w-full mt-6 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Weiter
        </button>
      </div>

      <!-- Step 6: Finish -->
      <div v-if="currentStep === 6" class="space-y-4">
        <h2 class="text-2xl font-bold mb-4">Fertig</h2>
        <p class="text-gray-700">
          Du kannst deine Eingaben jederzeit in den Einstellungen ändern.
        </p>
        <p class="text-gray-700">
          Weiter geht's damit, dass du eingibst, was du als Volunteer schon alles gemacht hast.
        </p>
        <button
          @click="completeWizard"
          :disabled="completing"
          class="w-full mt-6 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ completing ? 'Speichern...' : 'Weiter' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import apiClient from '@/api/client'

const router = useRouter()
const userStore = useUserStore()

const totalSteps = 6
const currentStep = ref(1)
const completing = ref(false)
const nicknameChecking = ref(false)
const nicknameError = ref('')
const contactLinkType = ref<'email' | 'link'>('email')

const wizardData = reactive({
  nickname: '',
  short_bio: '',
  contact_link: '',
  email_notify_proposals: false,
  email_tool_info: false,
  email_volunteer_newsletter: false,
})

// Initialize from existing user data if available
onMounted(async () => {
  await userStore.fetchUser()
  if (userStore.user) {
    wizardData.nickname = userStore.user.nickname || ''
    wizardData.short_bio = userStore.user.short_bio || ''
    
    // Determine contact link type
    if (userStore.user.contact_link) {
      const link = userStore.user.contact_link
      if (link.startsWith('mailto:')) {
        contactLinkType.value = 'email'
        wizardData.contact_link = link.replace('mailto:', '')
      } else {
        contactLinkType.value = 'link'
        wizardData.contact_link = link
      }
    }
    
    wizardData.email_notify_proposals = userStore.user.email_notify_proposals || false
    wizardData.email_tool_info = userStore.user.email_tool_info || false
    wizardData.email_volunteer_newsletter = userStore.user.email_volunteer_newsletter || false
  }
})

async function checkNicknameAvailability() {
  if (!wizardData.nickname) {
    nicknameError.value = ''
    return
  }
  
  nicknameChecking.value = true
  nicknameError.value = ''
  
  try {
    const response = await apiClient.post('/auth/check-nickname', { nickname: wizardData.nickname })
    if (!response.data.available) {
      nicknameError.value = 'Dieser Name ist schon vergeben.'
    }
  } catch (err) {
    nicknameError.value = ''
  } finally {
    nicknameChecking.value = false
  }
}

function nextStep() {
  if (currentStep.value < totalSteps) {
    currentStep.value++
  }
}

function skipStep() {
  nextStep()
}

async function completeWizard() {
  completing.value = true
  
  try {
    // Normalize contact link (add mailto: for emails)
    let contactLink = wizardData.contact_link
    if (contactLinkType.value === 'email' && contactLink && !contactLink.startsWith('mailto:')) {
      contactLink = `mailto:${contactLink}`
    }
    
    // Save all wizard data
    await apiClient.put('/user', {
      nickname: wizardData.nickname,
      short_bio: wizardData.short_bio || null,
      contact_link: contactLink || null,
      email_notify_proposals: wizardData.email_notify_proposals,
      email_tool_info: wizardData.email_tool_info,
      email_volunteer_newsletter: wizardData.email_volunteer_newsletter,
      wizard_completed: true,
    })
    
    // Refresh user data
    await userStore.fetchUser()
    
    // Navigate to awards with engagements tab
    router.push({ name: 'awards', query: { tab: 'engagements' } })
  } catch (err: any) {
    console.error('Error completing wizard:', err)
    alert('Fehler beim Speichern. Bitte versuche es erneut.')
  } finally {
    completing.value = false
  }
}
</script>

