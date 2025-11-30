<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['update:currentPage']);

const visiblePages = computed(() => {
  return [1, 2, 3, '...', 67, 68];
});

const goToPage = (page) => {
  if (typeof page === 'number' && page >= 1 && page <= props.totalPages) {
    emit('update:currentPage', page);
  }
};

const prevPage = () => {
  if (props.currentPage > 1) {
    emit('update:currentPage', props.currentPage - 1);
  }
};

const nextPage = () => {
  if (props.currentPage < props.totalPages) {
    emit('update:currentPage', props.currentPage + 1);
  }
};
</script>

<template>
  <div v-if="totalPages >= 1" class="flex justify-center items-center gap-2 mt-8">
    <button
      @click="prevPage"
      :disabled="currentPage === 1"
      class="px-3 py-2 transition hover:cursor-pointer flex items-center gap-1 hover:border rounded-lg"
      :class="currentPage === 1 ? 'cursor-not-allowed' : 'bg-white hover:bg-gray-50'"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Previous
    </button>

    <template v-for="(page, index) in visiblePages" :key="index">
      <span v-if="page === '...'" class="px-2 text-gray-500">...</span>
      <button
        v-else
        @click="goToPage(page)"
        class="px-4 py-2 rounded-lg border transition hover:cursor-pointer"
        :class="currentPage === page ? 'bg-black text-white' : 'bg-white hover:bg-gray-50'"
      >
        {{ page }}
      </button>
    </template>

    <button
      @click="nextPage"
      :disabled="currentPage === totalPages"
      class="px-3 py-2 transition hover:border rounded-lg hover:cursor-pointer flex items-center gap-1"
      :class="currentPage === totalPages ? 'cursor-not-allowed' : 'bg-white hover:bg-gray-50'"
    >
      Next
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>
  </div>
</template>
