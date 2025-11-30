import { createRouter, createWebHistory } from 'vue-router'
import ProductsLandingPage from '@/views/ProductsLandingPage.vue'
import LoginPage from '@/views/LoginPage.vue'
import RegistrationPage from '@/views/RegistrationPage.vue'
import VerifyEmailPage from '@/views/VerifyEmailPage.vue'
import ProductsManagement from '@/views/ProductsManagement.vue'
import AdminOrders from '@/views/AdminOrders.vue'
import UserManagement from '@/views/UserManagement.vue'
import { authService } from '@/services/authService'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: ProductsLandingPage
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage
    },
    {
      path: '/register',
      name: 'register',
      component: RegistrationPage
    },
    {
      path: '/verify-email',
      name: 'verify-email',
      component: VerifyEmailPage
    },
    {
      path: '/admin/products',
      name: 'products-management',
      component: ProductsManagement,
      meta: { requiresAdmin: true }
    },
    {
      path: '/admin/orders',
      name: 'admin-orders',
      component: AdminOrders,
      meta: { requiresAdmin: true }
    },
    {
      path: '/admin/users',
      name: 'user-management',
      component: UserManagement,
      meta: { requiresAdmin: true }
    }
  ]
})

// Navigation guard to protect admin routes
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAdmin) {
    if (!authService.isAuthenticated()) {
      // Not logged in, redirect to login
      next('/login')
    } else if (!authService.isAdmin()) {
      // Logged in but not admin, redirect to home
      next('/')
    } else {
      // Admin user, allow access
      next()
    }
  } else {
    // Route doesn't require admin, allow access
    next()
  }
})

export default router
