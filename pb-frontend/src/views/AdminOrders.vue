<script setup>
import { ref, onMounted } from 'vue'
import Sidebar from '@/components/Admin/Sidebar.vue'
import ViewOrders from '@/components/Orders/ViewOrders.vue'
import { orderService } from '@/services/orderService'

const orders = ref([])
const isLoading = ref(false)
const selectedOrder = ref(null)
const showViewModal = ref(false)
const showDeleteModal = ref(false)
const orderToDelete = ref(null)

const statusColors = {
  'pending': 'bg-orange-100 text-orange-800',
  'for delivery': 'bg-yellow-100 text-yellow-800',
  'delivered': 'bg-green-100 text-green-800',
  'canceled': 'bg-red-100 text-red-800'
}

const fetchOrders = async () => {
  isLoading.value = true
  try {
    const response = await orderService.getOrders()
    orders.value = response.data || response
  } catch (error) {
    console.error('Failed to fetch orders:', error)
  } finally {
    isLoading.value = false
  }
}

const getProductNames = (items) => {
  if (!items || items.length === 0) return 'No items'
  if (items.length === 1) return items[0].name
  return `${items[0].name} +${items.length - 1} more`
}

const viewOrder = (order) => {
  selectedOrder.value = order
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedOrder.value = null
}

const confirmDelete = (order) => {
  orderToDelete.value = order
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
  orderToDelete.value = null
}

const deleteOrder = async () => {
  if (!orderToDelete.value) return
  
  try {
    await orderService.deleteOrder(orderToDelete.value.id)
    orders.value = orders.value.filter(o => o.id !== orderToDelete.value.id)
    showDeleteModal.value = false
    orderToDelete.value = null
    
    // Dispatch event to refresh products (stock restored)
    window.dispatchEvent(new CustomEvent('products-updated'))
  } catch (error) {
    console.error('Failed to delete order:', error)
  }
}

const updateStatus = async (orderId, newStatus) => {
  try {
    await orderService.updateOrderStatus(orderId, newStatus)
    const order = orders.value.find(o => o.id === orderId)
    if (order) {
      order.status = newStatus
    }
    if (selectedOrder.value && selectedOrder.value.id === orderId) {
      selectedOrder.value.status = newStatus
    }
  } catch (error) {
    console.error('Failed to update status:', error)
  }
}

onMounted(() => {
  fetchOrders()
})
</script>

<template>
  <div class="flex min-h-screen">
    <Sidebar />
    
    <!-- Main Content Area -->
    <main class="flex-1 p-8" style="background-color: #8B3F9324;">
      <div class="flex flex-col gap-8">
        <!-- Top Section -->
        <div class="bg-white px-4 py-8 flex justify-between items-center rounded-lg shadow-md">
          <h1 class="text-3xl font-semibold">Orders</h1>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <!-- Table Header -->
          <div class="bg-pbpurple text-white font-bold grid grid-cols-4 gap-4 px-6 py-4">
            <p class="text-center">Full Name</p>
            <p class="text-center">Product</p>
            <p class="text-center">Status</p>
            <p class="text-center">Action</p>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="text-center py-8">
            <p class="text-gray-500">Loading orders...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="orders.length === 0" class="text-center py-8">
            <p class="text-gray-500">No orders found.</p>
          </div>

          <!-- Table Rows -->
          <template v-else>
            <div 
              v-for="order in orders" 
              :key="order.id"
              class="text-black grid grid-cols-4 gap-4 px-6 py-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition items-center"
            >
              <p class="text-center">{{ order.user?.name || 'Guest' }}</p>
              <p class="text-center">{{ getProductNames(order.items) }}</p>
              <div class="flex justify-center">
                <span 
                  :class="statusColors[order.status]"
                  class="px-3 py-1 rounded-full text-sm font-medium capitalize"
                >
                  {{ order.status }}
                </span>
              </div>
              <div class="flex items-center justify-center gap-3">
                <!-- View Icon -->
                <button @click="viewOrder(order)" class="p-2 hover:cursor-pointer hover:bg-gray-100 rounded-full transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <!-- Delete Icon -->
                <button @click="confirmDelete(order)" class="p-2 hover:cursor-pointer hover:bg-red-50 rounded-full transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </main>

    <!-- View Order Modal -->
    <ViewOrders 
      :isOpen="showViewModal" 
      :order="selectedOrder" 
      @close="closeViewModal" 
      @update-status="updateStatus" 
    />

    <!-- Delete Confirmation Modal -->
    <Transition name="modal">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="cancelDelete"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Delete Order</h2>
          <p class="text-gray-600 mb-6">Are you sure you want to delete this order? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button 
              @click="cancelDelete"
              class="px-4 py-2 border border-gray-300 hover:cursor-pointer rounded-lg hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button 
              @click="deleteOrder"
              class="px-4 py-2 bg-red-500 text-white hover:cursor-pointer rounded-lg hover:bg-red-600 transition"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
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
</style>