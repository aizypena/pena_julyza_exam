
<script setup>
import { ref, watch, computed, defineProps, defineEmits } from 'vue'
import productService from '@/services/productService'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  product: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'product-saved'])

const isEditMode = computed(() => !!props.product)

const formData = ref({
  name: '',
  price: '',
  stock: ''
})

const selectedImage = ref(null)
const imagePreview = ref(null)
const isLoading = ref(false)
const errorMessage = ref('')

// Watch for product changes to populate form in edit mode
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    formData.value = {
      name: newProduct.name,
      price: newProduct.price,
      stock: newProduct.stock
    }
    if (newProduct.image) {
      imagePreview.value = `${import.meta.env.VITE_API_URL?.replace('/api', '')}/storage/${newProduct.image}`
    } else {
      imagePreview.value = null
    }
    selectedImage.value = null
  }
}, { immediate: true })

// Reset form when modal opens for adding
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && !props.product) {
    formData.value = { name: '', price: '', stock: '' }
    selectedImage.value = null
    imagePreview.value = null
    errorMessage.value = ''
  }
})

const closeModal = () => {
  formData.value = { name: '', price: '', stock: '' }
  selectedImage.value = null
  imagePreview.value = null
  errorMessage.value = ''
  emit('close')
}

const handleImageSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedImage.value = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

const preventInvalidChars = (event) => {
  // Prevent e, E, +, - characters in number inputs
  if (['e', 'E', '+', '-'].includes(event.key)) {
    event.preventDefault()
  }
}

const handleSubmit = async () => {
  // Validation
  if (formData.value.price < 0 || formData.value.stock < 0) {
    errorMessage.value = 'Price and stock cannot be negative!'
    return
  }

  if (!formData.value.name || !formData.value.price || formData.value.stock === '') {
    errorMessage.value = 'Please fill in all required fields!'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    const productData = {
      name: formData.value.name,
      price: formData.value.price,
      stock: formData.value.stock
    }

    if (selectedImage.value) {
      productData.image = selectedImage.value
    }

    if (isEditMode.value) {
      await productService.update(props.product.id, productData)
    } else {
      await productService.create(productData)
    }

    emit('product-saved')
    closeModal()
  } catch (error) {
    console.error('Error saving product:', error)
    errorMessage.value = error.response?.data?.message || `Failed to ${isEditMode.value ? 'update' : 'create'} product. Please try again.`
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <Transition name="modal">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-transparent backdrop-blur-sm" @click="closeModal"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 bg-pbpurple rounded-t-lg">
          <h2 class="text-2xl font-bold text-white">{{ isEditMode ? 'Edit Product' : 'Add Product' }}</h2>
          <div class="flex gap-3">
            <button
              @click="handleSubmit"
              :disabled="isLoading"
              class="px-6 py-2 bg-white text-pbpurple rounded-lg hover:opacity-90 transition font-medium hover:cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isLoading ? 'Saving...' : 'Save' }}
            </button>
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-2 bg-white text-pbpurple rounded-lg hover:opacity-90 transition font-medium hover:cursor-pointer"
            >
              Cancel
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="p-6">
          <!-- Error Message -->
          <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ errorMessage }}
          </div>

          <div class="flex gap-6">
            <!-- Left Side - Image Upload -->
            <div class="shrink-0">
              <label class="block mb-2 font-medium text-gray-700">Product Image</label>
              <div 
                class="w-48 h-48 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-pbpurple transition overflow-hidden"
                @click="$refs.imageInput.click()"
              >
                <img v-if="imagePreview" :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <input 
                ref="imageInput"
                type="file" 
                accept="image/*" 
                class="hidden" 
                @change="handleImageSelect"
              />
            </div>

            <!-- Right Side - Form Fields -->
            <div class="flex-1 space-y-4">
              <!-- Price -->
              <div class="flex flex-col">
                <label for="price" class="mb-2 font-medium text-gray-700">Price</label>
                <input
                  v-model="formData.price"
                  type="number"
                  step="0.01"
                  id="price"
                  required
                  min="0"
                  @keydown="preventInvalidChars"
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple"
                  placeholder="Enter price"
                />
              </div>

              <!-- Product Name -->
              <div class="flex flex-col">
                <label for="product-name" class="mb-2 font-medium text-gray-700">Product Name</label>
                <input
                  v-model="formData.name"
                  type="text"
                  id="product-name"
                  required
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple"
                  placeholder="Enter product name"
                />
              </div>

              <!-- Number of Stocks -->
              <div class="flex flex-col">
                <label for="stock" class="mb-2 font-medium text-gray-700">Number of Stocks</label>
                <input
                  v-model="formData.stock"
                  type="number"
                  id="stock"
                  required
                  min="0"
                  @keydown="preventInvalidChars"
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple"
                  placeholder="Enter stock quantity"
                />
              </div>
            </div>
          </div>
        </form>
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
