<script setup>
import { ref, onMounted } from 'vue'
import Sidebar from '@/components/Admin/Sidebar.vue'
import AddProductForm from '@/components/ProductsManagement/AddProductForm.vue'
import productService from '@/services/productService'
import editIcon from '@/assets/edit.png'
import deleteIcon from '@/assets/delete.png'

const isProductModalOpen = ref(false)
const selectedProduct = ref(null)
const products = ref([])
const isLoading = ref(false)
const showDeleteModal = ref(false)
const productToDelete = ref(null)

const fetchProducts = async () => {
  isLoading.value = true
  try {
    products.value = await productService.getAll()
  } catch (error) {
    console.error('Error fetching products:', error)
  } finally {
    isLoading.value = false
  }
}

const openAddProductModal = () => {
  selectedProduct.value = null
  isProductModalOpen.value = true
}

const openEditProductModal = (product) => {
  selectedProduct.value = product
  isProductModalOpen.value = true
}

const closeProductModal = () => {
  isProductModalOpen.value = false
  selectedProduct.value = null
}

const onProductSaved = () => {
  fetchProducts()
}

const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
  productToDelete.value = null
}

const deleteProduct = async () => {
  if (!productToDelete.value) return
  
  try {
    await productService.delete(productToDelete.value.id)
    products.value = products.value.filter(p => p.id !== productToDelete.value.id)
    showDeleteModal.value = false
    productToDelete.value = null
  } catch (error) {
    console.error('Error deleting product:', error)
  }
}

const formatPrice = (price) => {
  return `₱ ${parseFloat(price).toFixed(2)}`
}

onMounted(() => {
  fetchProducts()
})
</script>

<template>
  <div class="flex min-h-screen">
    <Sidebar />
    
    <!-- Main Content Area -->
    <main class="flex-1 p-8" style="background-color: #8B3F9324;">
      <div class="flex flex-col gap-8">
        <!-- Top Section -->
        <div class="top-container bg-white px-4 py-8 flex justify-between items-center rounded-lg shadow-md">
          <h1 class="text-3xl font-semibold">Products Management</h1>
          <button 
            @click="openAddProductModal"
            class="hover:cursor-pointer text-white px-6 py-2 rounded-full hover:opacity-90 transition" 
            style="background-color: #65558F;"
          >
            Add Product
          </button>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <!-- Table Header -->
          <div class="bg-pbpurple text-white font-bold grid grid-cols-4 gap-4 px-6 py-4">
            <p class="text-center">Product Name</p>
            <p class="text-center">Price</p>
            <p class="text-center">Number of Stocks</p>
            <p class="text-center">Action</p>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="text-center py-8">
            <p class="text-gray-500">Loading products...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="products.length === 0" class="text-center py-8">
            <p class="text-gray-500">No products found. Add your first product!</p>
          </div>

          <!-- Table Rows -->
          <template v-else>
            <div 
              v-for="product in products" 
              :key="product.id"
              class="text-black grid grid-cols-4 gap-4 px-6 py-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition"
            >
              <p class="text-center">{{ product.name }}</p>
              <p class="text-center">{{ formatPrice(product.price) }}</p>
              <p class="text-center">{{ product.stock }}</p>
              <div class="flex items-center justify-center gap-3">
                <img :src="editIcon" alt="Edit" class="w-5 h-5 cursor-pointer hover:opacity-80 transition" @click="openEditProductModal(product)" />
                <img :src="deleteIcon" alt="Delete" class="w-5 h-5 cursor-pointer hover:opacity-80 transition" @click="confirmDelete(product)" />
              </div>
            </div>
          </template>
        </div>
      </div>
    </main>
  </div>

  <!-- Product Modal (Add/Edit) -->
  <AddProductForm :isOpen="isProductModalOpen" :product="selectedProduct" @close="closeProductModal" @product-saved="onProductSaved" />

  <!-- Delete Confirmation Modal -->
  <Transition name="modal">
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="cancelDelete"></div>
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Delete Product</h2>
        <p class="text-gray-600 mb-6">Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.</p>
        <div class="flex justify-end gap-3">
          <button 
            @click="cancelDelete"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition hover:cursor-pointer"
          >
            Cancel
          </button>
          <button 
            @click="deleteProduct"
            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition hover:cursor-pointer"
          >
            Delete
          </button>
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
</style>