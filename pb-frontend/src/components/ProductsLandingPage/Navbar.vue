<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import pbImage from '@/assets/purplebug.png'
import userAvatar from '@/assets/Avatar.png'
import lockIcon from '@/assets/Lock.svg'
import penIcon from '@/assets/Pen tool.svg'
import CartModal from './CartModal.vue'
import { authService } from '@/services/authService'
import cartService from '@/services/cartService'

const router = useRouter()
const isCartModalOpen = ref(false)
const currentUser = ref(null)
const cartCount = ref(0)

const isLoggedIn = computed(() => authService.isAuthenticated())

const updateCartCount = () => {
  cartCount.value = cartService.getCartCount()
}

onMounted(() => {
  currentUser.value = authService.getCurrentUser()
  updateCartCount()
  window.addEventListener('cart-updated', updateCartCount)
})

onUnmounted(() => {
  window.removeEventListener('cart-updated', updateCartCount)
})

const goToLogin = () => {
  router.push('/login')
}

const goToSignUp = () => {
  router.push('/register')
}

const openCartModal = () => {
  isCartModalOpen.value = true
}

const closeCartModal = () => {
  isCartModalOpen.value = false
}

const handleLogout = async () => {
  try {
    await authService.logout()
    currentUser.value = null
    window.location.href = '/'
  } catch (error) {
    console.error('Logout error:', error)
    // Clear local storage anyway
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    currentUser.value = null
    window.location.href = '/'
  }
}
</script>

<template>
  <header>
    <div class="flex items-center justify-between text-center py-4 px-4">
      <div class="left-nav">
        <a href="/">
        <img :src="pbImage" alt="PurpleBug Logo" class="h-8 mb-2"/>
      </a>
      </div>
      <div class="right-nav flex items-center gap-6">
        <div class="profile-container flex items-center gap-3 text-start">
          <div class="avatar-container">
            <img :src="userAvatar" alt="User Avatar" class="h-8 w-8 rounded-full"/>
          </div>
          <div>
            <p class="font-semibold">Hi, {{ isLoggedIn && currentUser ? currentUser.name : 'Guest' }}!</p>
            <p class="text-gray-400 text-sm">{{ isLoggedIn && currentUser ? currentUser.email : 'Welcome to PurpleBug' }}</p>
          </div>
        </div>
        <div @click="openCartModal" class="relative cursor-pointer">
          <img src="/Shopping cart.svg" alt="Shopping Cart" class="w-6 h-6 hover:opacity-80 transition"/>
          <span class="absolute -top-2 -right-2 bg-pbpurple text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ cartCount }}</span>
        </div>
        <!-- Show Login/Signup when not logged in -->
        <template v-if="!isLoggedIn">
          <div>
            <button @click="goToLogin" class="px-4 py-2 bg-pbpurple text-white rounded-lg hover:opacity-90 transition font-medium flex items-center gap-2 hover:cursor-pointer">
              <img :src="lockIcon" alt="Lock" class="w-4 h-4"/>
              LOGIN
            </button>
          </div>
          <div>
            <button @click="goToSignUp" class="px-4 py-2 bg-pbpurple text-white rounded-lg hover:opacity-90 transition font-medium flex items-center gap-2 hover:cursor-pointer">
              <img :src="penIcon" alt="Pen" class="w-4 h-4"/>
              SIGN UP
            </button>
          </div>
        </template>
        <!-- Show Logout when logged in -->
        <template v-else>
          <div>
            <button @click="handleLogout" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium flex items-center gap-2 hover:cursor-pointer">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              LOGOUT
            </button>
          </div>
        </template>
      </div>
    </div>
    <hr class="border-t border-gray-700 my-6 mx-4"/>
    
    <!-- Cart Modal -->
    <CartModal :isOpen="isCartModalOpen" @close="closeCartModal" />
  </header>
</template>