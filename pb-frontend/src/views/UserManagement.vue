<script setup>
import { ref, onMounted } from 'vue'
import Sidebar from '@/components/Admin/Sidebar.vue'
import AddUserForm from '@/components/UserManagement/AddUserForm.vue'
import userService from '@/services/userService'
import editIcon from '@/assets/edit.png'
import deleteIcon from '@/assets/delete.png'

const users = ref([])
const isLoading = ref(false)
const isUserModalOpen = ref(false)
const selectedUser = ref(null)
const showDeleteModal = ref(false)
const userToDelete = ref(null)

const fetchUsers = async () => {
  isLoading.value = true
  try {
    users.value = await userService.getAll()
  } catch (error) {
    console.error('Error fetching users:', error)
  } finally {
    isLoading.value = false
  }
}

const openAddUserModal = () => {
  selectedUser.value = null
  isUserModalOpen.value = true
}

const openEditUserModal = (user) => {
  selectedUser.value = user
  isUserModalOpen.value = true
}

const closeUserModal = () => {
  isUserModalOpen.value = false
  selectedUser.value = null
}

const onUserSaved = () => {
  fetchUsers()
}

const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
  userToDelete.value = null
}

const deleteUser = async () => {
  if (!userToDelete.value) return
  
  try {
    await userService.delete(userToDelete.value.id)
    users.value = users.value.filter(u => u.id !== userToDelete.value.id)
    showDeleteModal.value = false
    userToDelete.value = null
  } catch (error) {
    console.error('Error deleting user:', error)
  }
}

onMounted(() => {
  fetchUsers()
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
          <h1 class="text-3xl font-semibold">User Management</h1>
          <button 
            @click="openAddUserModal"
            class="hover:cursor-pointer text-white px-6 py-2 rounded-full hover:opacity-90 transition" 
            style="background-color: #65558F;"
          >
            Add User
          </button>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <!-- Table Header -->
          <div class="bg-pbpurple text-white font-bold grid grid-cols-4 gap-4 px-6 py-4">
            <p class="text-center">Full Name</p>
            <p class="text-center">Email</p>
            <p class="text-center">Status</p>
            <p class="text-center">Action</p>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="text-center py-8">
            <p class="text-gray-500">Loading users...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="users.length === 0" class="text-center py-8">
            <p class="text-gray-500">No users found. Add your first user!</p>
          </div>

          <!-- Table Rows -->
          <template v-else>
            <div 
              v-for="user in users" 
              :key="user.id"
              class="text-black grid grid-cols-4 gap-4 px-6 py-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition"
            >
              <p class="text-center">{{ user.name }}</p>
              <p class="text-center">{{ user.email }}</p>
              <div class="flex justify-center">
                <span 
                  :class="user.status === 'active' 
                    ? 'bg-green-500 text-white' 
                    : 'bg-red-500 text-white'"
                  class="px-3 py-1 rounded-full text-sm font-medium capitalize"
                >
                  {{ user.status }}
                </span>
              </div>
              <div class="flex items-center justify-center gap-3">
                <img :src="editIcon" alt="Edit" class="w-5 h-5 cursor-pointer hover:opacity-80 transition" @click="openEditUserModal(user)" />
                <img :src="deleteIcon" alt="Delete" class="w-5 h-5 cursor-pointer hover:opacity-80 transition" @click="confirmDelete(user)" />
              </div>
            </div>
          </template>
        </div>
      </div>
    </main>
  </div>

  <!-- User Modal (Add/Edit) -->
  <AddUserForm :isOpen="isUserModalOpen" :user="selectedUser" @close="closeUserModal" @user-saved="onUserSaved" />

  <!-- Delete Confirmation Modal -->
  <Transition name="modal">
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="cancelDelete"></div>
      <div class="relative bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Delete User</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="cancelDelete"
            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="deleteUser"
            class="px-4 py-2 text-white bg-red-500 rounded-lg hover:bg-red-600 transition cursor-pointer"
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
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>