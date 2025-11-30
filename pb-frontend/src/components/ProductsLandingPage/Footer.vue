<script setup>
import { ref } from 'vue'
import pbImage from '@/assets/purplebug.png'

</script>

<template>
  <footer class="">
    <hr class="border-t border-gray-700 my-6 mx-4"/>
    <div class="flex flex-col items-center justify-center text-center text-gray-500 py-16">
      <a href="/">
        <img :src="pbImage" alt="PurpleBug Logo" class="h-8 mb-2"/>
      </a>
      <p>Copyright 2025 PurpleBug Inc.</p>
    </div>
  </footer>
</template>