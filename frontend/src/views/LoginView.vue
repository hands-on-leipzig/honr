<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
    <!-- HONR Logo at Top -->
    <div class="mb-8">
      <img 
        src="@/assets/logos/honr-logo.png" 
        alt="HONR" 
        class="h-12 object-contain"
      />
    </div>

    <div class="w-full max-w-sm">
      <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Login Form -->
        <template v-if="mode === 'login'">
          <h1 class="text-2xl font-bold text-center mb-6">Anmelden</h1>
          
          <form @submit.prevent="handleLogin">
            <div class="mb-4">
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
              <input
                id="email"
                v-model="email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div class="mb-4">
              <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Passwort</label>
              <input
                id="password"
                v-model="password"
                type="password"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div class="mb-4">
              <label class="flex items-center">
                <input
                  v-model="remember"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-600">Angemeldet bleiben</span>
              </label>
            </div>
            
            <div v-if="ssoError" class="mb-4 p-3 bg-amber-50 text-amber-800 rounded-md text-sm">
              {{ ssoError }}
            </div>
            <div v-if="error" class="mb-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">
              {{ error }}
            </div>
            
            <button
              type="submit"
              :disabled="loading"
              class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
            >
              {{ loading ? 'Wird angemeldet...' : 'Anmelden' }}
            </button>

            <a
              :href="ssoRedirectUrl"
              class="mt-3 w-full inline-block py-2 px-4 text-center border border-gray-300 bg-white text-gray-700 font-medium rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              HANDS on SSO
            </a>
          </form>
          
          <div class="mt-4 text-center space-y-2">
            <button @click="mode = 'reset'" class="text-sm text-blue-600 hover:underline">
              Passwort vergessen?
            </button>
            <div class="text-sm text-gray-600">
              Noch kein Konto? 
              <button @click="mode = 'register'" class="text-blue-600 hover:underline">Registrieren</button>
            </div>
          </div>
        </template>

        <!-- Register Form -->
        <template v-else-if="mode === 'register'">
          <h1 class="text-2xl font-bold text-center mb-6">Registrieren</h1>
          
          <form @submit.prevent="handleRegister">
            <div class="mb-4">
              <label for="reg-email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
              <input
                id="reg-email"
                v-model="regEmail"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div class="mb-4">
              <label for="reg-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input
                id="reg-name"
                v-model="regName"
                type="text"
                required
                @blur="checkNicknameAvailability"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p v-if="nicknameError" class="text-xs text-red-600 mt-1">{{ nicknameError }}</p>
              <p v-else class="text-xs text-gray-500 mt-1">Dein Name muss einzigartig sein.</p>
            </div>
            
            <div class="mb-4">
              <label for="reg-password" class="block text-sm font-medium text-gray-700 mb-1">Passwort</label>
              <input
                id="reg-password"
                v-model="regPassword"
                type="password"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <ul class="mt-1 text-xs text-gray-500 space-y-0.5">
                <li :class="regPassword.length >= 8 ? 'text-green-600' : ''">• Mindestens 8 Zeichen</li>
                <li :class="/[A-Z]/.test(regPassword) ? 'text-green-600' : ''">• Mindestens 1 Großbuchstabe</li>
                <li :class="/[a-z]/.test(regPassword) ? 'text-green-600' : ''">• Mindestens 1 Kleinbuchstabe</li>
                <li :class="/[0-9]/.test(regPassword) ? 'text-green-600' : ''">• Mindestens 1 Zahl</li>
              </ul>
            </div>
            
            <div class="mb-4">
              <label for="reg-password-confirm" class="block text-sm font-medium text-gray-700 mb-1">Passwort bestätigen</label>
              <input
                id="reg-password-confirm"
                v-model="regPasswordConfirm"
                type="password"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p v-if="regPassword && regPasswordConfirm && regPassword !== regPasswordConfirm" class="text-xs text-red-600 mt-1">
                Passwörter stimmen nicht überein.
              </p>
            </div>
            
            <div v-if="error" class="mb-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">
              {{ error }}
            </div>
            
            <div v-if="success" class="mb-4 p-3 bg-green-50 text-green-700 rounded-md text-sm">
              {{ success }}
            </div>
            
            <button
              type="submit"
              :disabled="loading || (regPassword !== regPasswordConfirm)"
              class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
            >
              {{ loading ? 'Wird registriert...' : 'Registrieren' }}
            </button>
          </form>
          
          <div class="mt-4 text-center">
            <button @click="mode = 'login'; error = ''; success = ''" class="text-sm text-blue-600 hover:underline">
              Zurück zur Anmeldung
            </button>
          </div>
        </template>

        <!-- Reset Password Form -->
        <template v-else-if="mode === 'reset'">
          <h1 class="text-2xl font-bold text-center mb-6">Passwort zurücksetzen</h1>
          
          <form @submit.prevent="handleResetPassword">
            <div class="mb-4">
              <label for="reset-email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
              <input
                id="reset-email"
                v-model="resetEmail"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div v-if="error" class="mb-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">
              {{ error }}
            </div>
            
            <div v-if="success" class="mb-4 p-3 bg-green-50 text-green-700 rounded-md text-sm">
              {{ success }}
            </div>
            
            <button
              type="submit"
              :disabled="loading"
              class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
            >
              {{ loading ? 'Wird gesendet...' : 'Link senden' }}
            </button>
          </form>
          
          <div class="mt-4 text-center">
            <button @click="mode = 'login'; error = ''; success = ''" class="text-sm text-blue-600 hover:underline">
              Zurück zur Anmeldung
            </button>
          </div>
        </template>
      </div>
    </div>

    <!-- Hands On Technology Logo at Bottom -->
    <div class="mt-8">
      <a 
        href="https://www.hands-on-technology.org/de/engagement" 
        target="_blank" 
        rel="noopener noreferrer"
        class="inline-block hover:opacity-80 transition-opacity"
      >
        <img 
          src="@/assets/logos/hands-on-logo.png" 
          alt="Hands On Technology" 
          class="h-10 object-contain"
        />
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/client'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const apiBaseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const ssoRedirectUrl = computed(() => `${apiBaseUrl}/auth/sso/redirect`)

const mode = ref<'login' | 'register' | 'reset'>('login')
const ssoError = ref('')

// Login
const email = ref('')
const password = ref('')
const remember = ref(false)

// Register
const regEmail = ref('')
const regName = ref('')
const regPassword = ref('')
const regPasswordConfirm = ref('')
const nicknameError = ref('')

// Reset
const resetEmail = ref('')

// Shared
const loading = ref(false)
const error = ref('')
const success = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  
  try {
    const user = await authStore.login(email.value, password.value)
    // Fetch user to check wizard status
    const { useUserStore } = await import('@/stores/user')
    const userStore = useUserStore()
    await userStore.fetchUser()
    
    // Redirect to wizard if not completed, otherwise to awards
    if (userStore.user && !userStore.user.wizard_completed) {
      router.push('/wizard')
    } else {
      router.push('/awards')
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Anmeldung fehlgeschlagen.'
  } finally {
    loading.value = false
  }
}

async function finishSsoLogin(token: string) {
  authStore.setToken(token)
  const { useUserStore } = await import('@/stores/user')
  const userStore = useUserStore()
  await userStore.fetchUser()
  if (userStore.user && !userStore.user.wizard_completed) {
    router.replace('/wizard')
  } else {
    router.replace('/awards')
  }
}

onMounted(() => {
  const token = route.query.sso_token
  const err = route.query.sso_error
  if (typeof err === 'string' && err) {
    ssoError.value = decodeURIComponent(err)
    router.replace({ path: '/login', query: {} })
  }
  if (typeof token === 'string' && token) {
    router.replace({ path: '/login', query: {} })
    finishSsoLogin(token)
  }
})

async function checkNicknameAvailability() {
  if (!regName.value) {
    nicknameError.value = ''
    return
  }
  try {
    const response = await api.post('/auth/check-nickname', { nickname: regName.value })
    nicknameError.value = response.data.available ? '' : 'Dieser Name ist schon vergeben.'
  } catch {
    nicknameError.value = ''
  }
}

async function handleRegister() {
  if (regPassword.value !== regPasswordConfirm.value) {
    error.value = 'Passwörter stimmen nicht überein.'
    return
  }
  
  loading.value = true
  error.value = ''
  success.value = ''
  
  try {
    await api.post('/auth/register', {
      email: regEmail.value,
      nickname: regName.value,
      password: regPassword.value,
      password_confirmation: regPasswordConfirm.value
    })
    success.value = 'Registrierung erfolgreich! Bitte überprüfe deine E-Mails, um dein Konto zu bestätigen.'
    regEmail.value = ''
    regName.value = ''
    regPassword.value = ''
    regPasswordConfirm.value = ''
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Registrierung fehlgeschlagen.'
  } finally {
    loading.value = false
  }
}

async function handleResetPassword() {
  loading.value = true
  error.value = ''
  success.value = ''
  
  try {
    await api.post('/auth/forgot-password', {
      email: resetEmail.value
    })
    success.value = 'Falls ein Konto mit dieser E-Mail existiert, wurde ein Link zum Zurücksetzen gesendet.'
  } catch (err: any) {
    // Always show success to prevent email enumeration
    success.value = 'Falls ein Konto mit dieser E-Mail existiert, wurde ein Link zum Zurücksetzen gesendet.'
  } finally {
    loading.value = false
  }
}
</script>


