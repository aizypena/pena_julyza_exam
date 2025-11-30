<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import Navbar from '@/components/ProductsLandingPage/Navbar.vue';
import Footer from '@/components/ProductsLandingPage/Footer.vue';
import Pagination from '@/components/ProductsLandingPage/Pagination.vue';
import productService from '@/services/productService';
import { authService } from '@/services/authService';
import cartService from '@/services/cartService';

const router = useRouter();

const products = ref([]);
const isLoading = ref(false);
const searchQuery = ref('');
const sortOrder = ref('asc');
const currentPage = ref(1);
const itemsPerPage = 6;
const addedToCart = ref(null);

const fetchProducts = async () => {
  isLoading.value = true;
  try {
    products.value = await productService.getAll();
  } catch (error) {
    console.error('Error fetching products:', error);
  } finally {
    isLoading.value = false;
  }
};

const filteredProducts = computed(() => {
  let result = [...products.value];

  if (searchQuery.value) {
    result = result.filter((product) =>
      product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  result.sort((a, b) => {
    if (sortOrder.value === 'asc') {
      return parseFloat(a.price) - parseFloat(b.price);
    } else {
      return parseFloat(b.price) - parseFloat(a.price);
    }
  });

  return result;
});

const totalPages = computed(() => {
  return Math.ceil(filteredProducts.value.length / itemsPerPage);
});

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredProducts.value.slice(start, end);
});

// Reset to page 1 when search query changes
watch(searchQuery, () => {
  currentPage.value = 1;
});

const formatPrice = (price) => {
  return `₱ ${parseFloat(price).toFixed(2)}`;
};

const getImageUrl = (image) => {
  if (image) {
    return `${import.meta.env.VITE_API_URL?.replace('/api', '')}/storage/${image}`;
  }
  return null;
};

const getProductCardClass = (index) => {
  const total = paginatedProducts.value.length;
  const remainder = total % 4;
  
  // Check if this item is in the last row
  const lastRowStart = total - remainder;
  
  if (remainder === 2 && index >= lastRowStart) {
    // 2 items in last row - each takes 2 columns
    return 'lg:col-span-2';
  }
  
  return '';
};

const addToCart = (product) => {
  if (!authService.isAuthenticated()) {
    router.push('/login');
    return;
  }
  cartService.addToCart(product);
  addedToCart.value = product.id;
  
  // Reset the "Added" state after 2 seconds
  setTimeout(() => {
    addedToCart.value = null;
  }, 2000);
};

onMounted(() => {
  fetchProducts();
  window.addEventListener('products-updated', fetchProducts);
});

onUnmounted(() => {
  window.removeEventListener('products-updated', fetchProducts);
});
</script>

<template>
  <Navbar />
  <div class="functions-container flex justify-center items-center">
    <div class="flex justify-between container py-8">
      <div class="search-container relative">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search" 
          class="search-input border rounded-full pl-4 pr-10 py-2 w-80" 
        />
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="w-5 h-5 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
          />
        </svg>
      </div>
      <div class="buttons-container flex gap-2">
        <button 
          @click="sortOrder = 'asc'"
          :class="sortOrder === 'asc' ? 'bg-pbpurple text-white' : 'bg-gray-100'"
          class="btn rounded-xl px-2 hover:cursor-pointer transition"
        >
          Price ascending
        </button>
        <button 
          @click="sortOrder = 'desc'"
          :class="sortOrder === 'desc' ? 'bg-pbpurple text-white' : 'bg-gray-100'"
          class="btn rounded-xl px-2 hover:cursor-pointer transition"
        >
          Price descending
        </button>
      </div>
    </div>
  </div>
  <div class="flex justify-center items-center products-container py-8">
    <div class="container">
      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-16">
        <p class="text-gray-500">Loading products...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredProducts.length === 0" class="text-center py-16">
        <p class="text-gray-500">No products found.</p>
      </div>

      <!-- Products Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
          v-for="(product, index) in paginatedProducts"
          :key="product.id"
          :class="getProductCardClass(index)"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition"
        >
          <!-- Product Image -->
          <div class="h-48 bg-gray-100 flex items-center justify-center">
            <img
              v-if="product.image"
              :src="getImageUrl(product.image)"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              class="h-16 w-16 text-gray-300"
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

          <!-- Product Info -->
          <div class="p-4">
            <h3 class="font-semibold text-gray-800 mb-2">{{ product.name }}</h3>
            <p class="text-pbpurple font-bold text-lg">{{ formatPrice(product.price) }}</p>
            <p class="text-gray-500 text-sm mt-1">Stock: {{ product.stock }}</p>
            <button 
              @click="addToCart(product)"
              :disabled="product.stock <= 0"
              class="mt-3 w-full py-2 rounded-lg transition"
              :class="product.stock <= 0 
                ? 'bg-gray-400 text-white cursor-not-allowed' 
                : addedToCart === product.id 
                  ? 'bg-green-500 text-white hover:cursor-pointer' 
                  : 'bg-pbpurple text-white hover:opacity-90 hover:cursor-pointer'"
            >
              {{ product.stock <= 0 ? 'Out of Stock' : addedToCart === product.id ? '✓ Added to Cart' : 'Add to Cart' }}
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="py-8">
    <!-- Pagination -->
      <Pagination 
        v-model:currentPage="currentPage" 
        :totalPages="totalPages" 
      />
  </div>
  <Footer />
</template>