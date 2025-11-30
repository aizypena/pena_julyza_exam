<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import pbImage from '@/assets/purplebug.png'
import adminAvatar from '@/assets/admin-avatar.png'
import logoutIcon from '@/assets/logout-icon.png'
import { authService } from '@/services/authService'

const router = useRouter()
const route = useRoute()
const adminName = ref('Admin')

const currentPath = computed(() => route.path)

const navigateTo = (path) => {
  router.push(path)
}

const handleLogout = async () => {
  try {
    await authService.logout()
  } catch (error) {
    console.error('Logout error:', error)
    // Clear local storage anyway
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
  }
  // Redirect to login page
  window.location.href = '/login'
}

onMounted(() => {
  const user = authService.getCurrentUser()
  if (user) {
    adminName.value = user.name
  }
})
</script>

<template>
  <aside class="w-64 min-h-screen flex flex-col bg-gray-100 border-r border-gray-200">
    <!-- Top Section -->
    <div class="flex-1 flex flex-col p-6">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img :src="pbImage" alt="PurpleBug Logo" class="h-10" />
      </div>

      <!-- Horizontal Line -->
      <hr class="border-gray-300 mb-6 mx-4" />

      <!-- Navigation Menu -->
      <nav class="space-y-4">
        <div 
          @click="navigateTo('/admin/products')"
          :class="currentPath === '/admin/products' ? 'text-white bg-pbpurple' : 'text-gray-700 hover:bg-gray-200'"
          class="font-semibold px-4 py-3 rounded-lg cursor-pointer transition"
        >
          Product Management
        </div>
        <div 
          @click="navigateTo('/admin/orders')"
          :class="currentPath === '/admin/orders' ? 'text-white bg-pbpurple' : 'text-gray-700 hover:bg-gray-200'"
          class="font-semibold px-4 py-3 rounded-lg cursor-pointer transition"
        >
          Orders
        </div>
        <div 
          @click="navigateTo('/admin/users')"
          :class="currentPath === '/admin/users' ? 'text-white bg-pbpurple' : 'text-gray-700 hover:bg-gray-200'"
          class="font-semibold px-4 py-3 rounded-lg cursor-pointer transition"
        >
          User Management
        </div>
      </nav>
    </div>

    <!-- Bottom Section -->
    <div class="p-6">
      <!-- Horizontal Line -->
      <hr class="border-gray-300 mb-6 mx-4" />

      <!-- Admin Details -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img :src="adminAvatar" alt="Admin Avatar" class="h-10 w-10 rounded-full" />
          <div class="text-gray-800">
            <p class="text-sm font-medium">Hi,</p>
            <p class="font-semibold">{{ adminName }}</p>
          </div>
        </div>
        <button @click="handleLogout" class="text-gray-600 hover:cursor-pointer">
          <img :src="logoutIcon" alt="Logout" class="h-6 w-6" />
        </button>
      </div>
    </div>
  </aside>
</template>