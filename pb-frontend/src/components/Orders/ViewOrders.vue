<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  order: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'update-status'])

const closeModal = () => {
  emit('close')
}

const handleStatusChange = (newStatus) => {
  emit('update-status', props.order.id, newStatus)
}
</script>

<template>
  <Transition name="modal">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-transparent backdrop-blur-sm" @click="closeModal"></div>
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 bg-pbpurple rounded-t-lg">
          <h2 class="text-2xl font-bold text-white">{{ order?.user?.name || 'Guest' }}</h2>
          <div class="flex gap-3">
            <button
              @click="closeModal"
              class="px-6 py-2 bg-white text-pbpurple rounded-lg hover:opacity-90 transition font-medium hover:cursor-pointer"
            >
              Save
            </button>
            <button
              @click="closeModal"
              class="px-6 py-2 bg-white text-pbpurple rounded-lg hover:opacity-90 transition font-medium hover:cursor-pointer"
            >
              Close
            </button>
          </div>
        </div>

        <!-- Modal Content -->
        <div v-if="order" class="p-6 overflow-y-auto max-h-[60vh]">
          <div class="space-y-4">
            <!-- Row 1: Full Name | Product Ordered -->
            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-2 font-medium text-gray-700">Full Name</label>
                <input
                  type="text"
                  :value="order.user?.name || 'Guest'"
                  disabled
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full bg-gray-100 text-gray-600"
                />
              </div>

              <div class="flex flex-col">
                <label class="mb-2 font-medium text-gray-700">Product Ordered</label>
                <input
                  type="text"
                  :value="order.items?.map(item => item.name).join(', ')"
                  disabled
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full bg-gray-100 text-gray-600"
                />
              </div>
            </div>

            <!-- Row 2: Status | Quantity -->
            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-2 font-medium text-gray-700">Status</label>
                <select 
                  :value="order.status"
                  @change="handleStatusChange($event.target.value)"
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pbpurple bg-white"
                >
                  <option value="pending">Pending</option>
                  <option value="for delivery">For Delivery</option>
                  <option value="delivered">Delivered</option>
                  <option value="canceled">Canceled</option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="mb-2 font-medium text-gray-700">Quantity</label>
                <input
                  type="text"
                  :value="order.items?.reduce((sum, item) => sum + item.quantity, 0)"
                  disabled
                  class="border border-gray-300 rounded-lg px-4 py-2 w-full bg-gray-100 text-gray-600"
                />
              </div>
            </div>
          </div>
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
