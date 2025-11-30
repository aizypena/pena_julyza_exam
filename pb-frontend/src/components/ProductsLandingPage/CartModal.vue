<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import cartService from '@/services/cartService'
import { orderService } from '@/services/orderService'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close', 'order-placed'])

const closeModal = () => {
  emit('close')
}

const cartItems = ref([])
const isPlacingOrder = ref(false)
const orderSuccess = ref(false)
const orderError = ref('')

const loadCart = () => {
  cartItems.value = cartService.getCart()
}

const updateQuantity = (productId, newQuantity) => {
  cartService.updateQuantity(productId, newQuantity)
  loadCart()
}

const removeItem = (productId) => {
  cartService.removeFromCart(productId)
  loadCart()
}

const getTotalPrice = () => {
  return cartService.getCartTotal().toFixed(2)
}

const getImageUrl = (image) => {
  if (image) {
    return `${import.meta.env.VITE_API_URL?.replace('/api', '')}/storage/${image}`
  }
  return null
}

const placeOrder = async () => {
  if (cartItems.value.length === 0) return
  
  isPlacingOrder.value = true
  orderError.value = ''
  orderSuccess.value = false
  
  try {
    const orderData = {
      items: cartItems.value.map(item => ({
        id: item.id,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        image: item.image
      })),
      total: cartService.getCartTotal()
    }
    
    await orderService.createOrder(orderData)
    
    // Clear cart after successful order
    cartService.clearCart()
    loadCart()
    
    orderSuccess.value = true
    emit('order-placed')
    
    // Dispatch event to refresh products (stock updated)
    window.dispatchEvent(new CustomEvent('products-updated'))
    
    // Auto close after 2 seconds
    setTimeout(() => {
      orderSuccess.value = false
      closeModal()
    }, 2000)
  } catch (error) {
    orderError.value = error.response?.data?.message || 'Failed to place order. Please try again.'
  } finally {
    isPlacingOrder.value = false
  }
}

// Load cart when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    loadCart()
    orderSuccess.value = false
    orderError.value = ''
  }
})

onMounted(() => {
  loadCart()
  window.addEventListener('cart-updated', loadCart)
})

onUnmounted(() => {
  window.removeEventListener('cart-updated', loadCart)
})
</script>

<template>
  <Transition name="modal">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-transparent backdrop-blur-sm" @click="closeModal"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b">
          <div class="flex items-center gap-3">
            <img src="/Shopping cart.svg" alt="Cart" class="w-6 h-6" />
            <h2 class="text-2xl font-bold text-gray-800">Cart</h2>
          </div>
          <button 
            @click="placeOrder"
            :disabled="cartItems.length === 0 || isPlacingOrder"
            class="bg-pbpurple text-white hover:cursor-pointer px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isPlacingOrder ? 'PLACING ORDER...' : 'PLACE ORDER' }}
          </button>
        </div>

        <!-- Success Message -->
        <div v-if="orderSuccess" class="mx-6 mt-4 p-4 bg-green-100 text-green-700 rounded-lg flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Order placed successfully!
        </div>

        <!-- Error Message -->
        <div v-if="orderError" class="mx-6 mt-4 p-4 bg-red-100 text-red-700 rounded-lg flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          {{ orderError }}
        </div>

        <!-- Cart Items -->
        <div class="p-6 overflow-y-auto max-h-[60vh]">
          <div v-if="cartItems.length === 0" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="text-gray-500 text-lg">Your cart is empty</p>
            <p class="text-gray-400 text-sm mt-2">Add some products to get started!</p>
          </div>

          <!-- Cart items will be rendered here -->
          <div v-else class="space-y-4">
            <div v-for="item in cartItems" :key="item.id" class="flex gap-4 p-4 border rounded-lg">
              <!-- Product Image -->
              <div class="shrink-0 h-32 w-32 bg-gray-100 rounded flex items-center justify-center">
                <img 
                  v-if="item.image" 
                  :src="getImageUrl(item.image)" 
                  :alt="item.name" 
                  class="w-full h-full object-cover rounded" 
                />
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-12 w-12 text-gray-300"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
              
              <!-- Product Details -->
              <div class="flex-1 flex flex-col justify-between">
                <div class="space-y-2">
                  <h3 class="font-semibold text-gray-800 text-lg">{{ item.name }}</h3>
                  <p class="text-gray-600"><span class="font-medium">Price:</span> ₱{{ parseFloat(item.price).toFixed(2) }}</p>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-600">Quantity:</span>
                    <button 
                      @click="updateQuantity(item.id, item.quantity - 1)"
                      :disabled="item.quantity <= 1"
                      class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition hover:cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-gray-200"
                    >
                      -
                    </button>
                    <span class="w-8 text-center">{{ item.quantity }}</span>
                    <button 
                      @click="updateQuantity(item.id, item.quantity + 1)"
                      :disabled="!item.stock || item.quantity >= item.stock"
                      class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition hover:cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-gray-200"
                    >
                      +
                    </button>
                  </div>
                  <p v-if="item.stock" class="text-gray-500 text-sm">Stock available: {{ item.stock }}</p>
                  <p v-if="item.quantity >= item.stock" class="text-red-500 text-sm">Maximum quantity reached</p>
                  <p class="text-gray-800 font-semibold"><span class="font-medium">Value:</span> ₱{{ (parseFloat(item.price) * item.quantity).toFixed(2) }}</p>
                </div>
              </div>

              <!-- Remove Button -->
              <button 
                @click="removeItem(item.id)"
                class="self-start p-2 text-red-500 hover:bg-red-50 rounded-full transition"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-6 flex items-center gap-4" style="background-color: #8B3F93;">
          <span class="text-lg font-semibold text-white">Total:</span>
          <span class="text-2xl font-bold text-white">₱{{ getTotalPrice() }}</span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.9);
}
</style>