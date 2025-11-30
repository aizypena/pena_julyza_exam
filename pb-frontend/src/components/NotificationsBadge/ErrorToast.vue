<script setup>
import { defineProps } from 'vue'
import xCircle from '@/assets/x-circle.png'

defineProps({
  message: {
    type: String,
    required: true
  }
})
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg animate-slide-in" style="background-color: #C73B3B;">
    <img :src="xCircle" alt="Error" class="h-6 w-6" />
    <p class="text-white font-semibold">{{ message }}</p>
  </div>
</template>

<style scoped>
@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}
</style>