<script setup>
import { ref, watch, computed, defineProps, defineEmits } from 'vue'
import userService from '@/services/userService'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'user-saved'])

const isEditMode = computed(() => !!props.user)

const formData = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  status: 'active'
})

const isLoading = ref(false)
const errorMessage = ref('')

// Watch for user changes to populate form in edit mode
watch(() => props.user, (newUser) => {
  if (newUser) {
    formData.value = {
      name: newUser.name,
      email: newUser.email,
      password: '',
      confirmPassword: '',
      status: newUser.status || 'active'
    }
  }
}, { immediate: true })

// Reset form when modal opens for adding
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && !props.user) {
    formData.value = { name: '', email: '', password: '', confirmPassword: '', status: 'active' }
    errorMessage.value = ''
  }
})

const closeModal = () => {
  formData.value = { name: '', email: '', password: '', confirmPassword: '', status: 'active' }
  errorMessage.value = ''
  emit('close')
}

const handleSubmit = async () => {
  // Validation
  if (!formData.value.name || !formData.value.email) {
    errorMessage.value = 'Please fill in all required fields!'
    return
  }

  if (!isEditMode.value && !formData.value.password) {
    errorMessage.value = 'Password is required!'
    return
  }

  if (formData.value.password && formData.value.password !== formData.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match!'
    return
  }

  if (formData.value.password && formData.value.password.length < 6) {
    errorMessage.value = 'Password must be at least 6 characters!'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    const userData = {
      name: formData.value.name,
      email: formData.value.email
    }

    if (formData.value.password) {
      userData.password = formData.value.password
    }

    if (isEditMode.value) {
      userData.status = formData.value.status
      await userService.update(props.user.id, userData)
    } else {
      await userService.create(userData)
    }

    emit('user-saved')
    closeModal()
  } catch (error) {
    console.error('Error saving user:', error)
    if (error.response?.data?.errors?.email) {
      errorMessage.value = 'This email is already registered!'
    } else {
      errorMessage.value = error.response?.data?.message || `Failed to ${isEditMode.value ? 'update' : 'create'} user. Please try again.`
    }
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
          <h2 class="text-2xl font-bold text-white">{{ isEditMode ? 'Edit User' : 'Add User' }}</h2>
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

          <div class="space-y-4">
            <!-- Row 1: Full Name | Email -->
            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label for="name" class="mb-2 font-medium text-gray-700">Full Name</label>
                <input
                  v-model="formData.name"
                  type="text"
                  id="name"
                  required
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple"
                  placeholder="Enter full name"
                />
              </div>

              <div class="flex flex-col">
                <label for="email" class="mb-2 font-medium text-gray-700">Email</label>
                <input
                  v-model="formData.email"
                  type="email"
                  id="email"
                  required
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple"
                  placeholder="Enter email"
                />
              </div>
            </div>

            <!-- Row 2: Password | Confirm Password -->
            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label for="password" class="mb-2 font-medium text-gray-700">
                  Password
                  <span v-if="isEditMode" class="text-gray-400 text-sm">(leave blank to keep current)</span>
                </label>
                <input
                  v-model="formData.password"
                  type="password"
                  id="password"
                  :required="!isEditMode"
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple"
                  placeholder="Enter password"
                />
              </div>

              <div class="flex flex-col">
                <label for="confirmPassword" class="mb-2 font-medium text-gray-700">Confirm Password</label>
                <input
                  v-model="formData.confirmPassword"
                  type="password"
                  id="confirmPassword"
                  :required="!isEditMode && formData.password"
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple"
                  placeholder="Confirm password"
                />
              </div>
            </div>

            <!-- Row 3: Status (Edit mode only) -->
            <div v-if="isEditMode" class="grid grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label for="status" class="mb-2 font-medium text-gray-700">Status</label>
                <select
                  v-model="formData.status"
                  id="status"
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple bg-white"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
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
