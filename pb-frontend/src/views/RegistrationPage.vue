<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '@/services/authService'
import pbImage from "@/assets/purplebug.png";
import emailIcon from "@/assets/email-icon.png";
import lockIcon from "@/assets/lock-icon.png";
import accountIcon from "@/assets/account-icon.png";
import SuccessToast from '@/components/NotificationsBadge/SuccessToast.vue'
import ErrorToast from '@/components/NotificationsBadge/ErrorToast.vue'

const router = useRouter()

const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const error = ref('')
const success = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  if (formData.value.password !== formData.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  loading.value = true

  try {
    await authService.register({
      name: formData.value.name,
      email: formData.value.email,
      password: formData.value.password,
      password_confirmation: formData.value.password_confirmation
    })
    
    success.value = 'Registration successful! Please check your email to verify your account.'
    setTimeout(() => {
      router.push('/login')
    }, 3000)
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      if (errors.email) {
        error.value = 'This email is already registered. Please use a different email or login.'
      } else {
        error.value = Object.values(errors).flat().join(' ')
      }
    } else {
      error.value = err.response?.data?.message || 'Registration failed. Please try again.'
    }
    console.error('Registration error:', err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="flex flex-col items-center justify-center min-h-screen p-6">
    <div class="mb-12">
      <router-link to="/">
        <img :src="pbImage" alt="PurpleBug Logo" class="h-12 cursor-pointer hover:opacity-80 transition" />
      </router-link>
    </div>
    <div class="w-full max-w-2xl">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <SuccessToast v-if="success" :message="success" />
        
        <ErrorToast v-if="error" :message="error" />

        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col">
            <label for="fullname" class="mb-2 font-medium">Full Name</label>
            <div class="relative">
              <img
                :src="accountIcon"
                alt="Account"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5"
              />
              <input
                v-model="formData.name"
                type="text"
                placeholder="Name:"
                id="fullname"
                name="fullname"
                required
                class="border border-gray-300 rounded-lg pl-12 pr-4 py-2 w-full"
              />
            </div>
          </div>
          <div class="flex flex-col">
            <label for="email" class="mb-2 font-medium">Email</label>
            <div class="relative">
              <img
                :src="emailIcon"
                alt="Email"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5"
              />
              <input
                v-model="formData.email"
                type="email"
                placeholder="Email:"
                id="email"
                name="email"
                required
                class="border border-gray-300 rounded-lg pl-12 pr-4 py-2 w-full"
              />
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col">
            <label for="password" class="mb-2 font-medium">Password</label>
            <div class="relative">
              <img
                :src="lockIcon"
                alt="Lock"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5"
              />
              <input
                v-model="formData.password"
                type="password"
                placeholder="Password:"
                id="password"
                name="password"
                required
                minlength="8"
                class="border border-gray-300 rounded-lg pl-12 pr-4 py-2 w-full"
              />
            </div>
          </div>
          <div class="flex flex-col">
            <label for="confirm-password" class="mb-2 font-medium">Confirm Password</label>
            <div class="relative">
              <img
                :src="lockIcon"
                alt="Lock"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5"
              />
              <input
                v-model="formData.password_confirmation"
                type="password"
                placeholder="Confirm Password:"
                id="confirm-password"
                name="confirm-password"
                required
                minlength="8"
                class="border border-gray-300 rounded-lg pl-12 pr-4 py-2 w-full"
              />
            </div>
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="bg-pbpurple hover:cursor-pointer text-white py-2 px-4 rounded-lg hover:opacity-90 transition font-medium mt-2 w-36 self-center disabled:opacity-50"
        >
          {{ loading ? 'LOADING...' : 'REGISTER' }}
        </button>
      </form>
    </div>
  </main>
</template>