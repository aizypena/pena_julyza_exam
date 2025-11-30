<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authService } from '@/services/authService'
import pbImage from "@/assets/purplebug.png"
import SuccessToast from '@/components/NotificationsBadge/SuccessToast.vue'
import ErrorToast from '@/components/NotificationsBadge/ErrorToast.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const success = ref('')
const error = ref('')

onMounted(async () => {
  const token = route.query.token

  if (!token) {
    error.value = 'Invalid verification link.'
    loading.value = false
    return
  }

  try {
    await authService.verifyEmail(token)
    success.value = 'Email verified successfully! Redirecting to login...'
    setTimeout(() => {
      router.push('/login')
    }, 3000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to verify email. The link may have expired.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <main class="flex flex-col items-center justify-center min-h-screen p-6">
    <div class="mb-12">
      <router-link to="/">
        <img :src="pbImage" alt="PurpleBug Logo" class="h-12 cursor-pointer hover:opacity-80 transition" />
      </router-link>
    </div>

    <div class="w-full max-w-md text-center">
      <SuccessToast v-if="success" :message="success" />
      <ErrorToast v-if="error" :message="error" />

      <div v-if="loading" class="flex flex-col items-center gap-4">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-pbpurple"></div>
        <p class="text-gray-600">Verifying your email...</p>
      </div>

      <div v-else-if="error" class="mt-8">
        <router-link 
          to="/login" 
          class="text-pbpurple hover:underline"
        >
          Go to Login
        </router-link>
      </div>
    </div>
  </main>
</template>
