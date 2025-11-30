import api from './api'

export const authService = {
  // Register new user (does NOT auto-login)
  async register(userData) {
    const response = await api.post('/register', userData)
    // Don't save token - user should verify email first
    return response.data
  },

  // Login user
  async login(credentials) {
    const response = await api.post('/login', credentials)
    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    return response.data
  },

  // Logout user
  async logout() {
    await api.post('/logout')
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
  },

  // Verify email
  async verifyEmail(token) {
    const response = await api.post('/verify-email', { token })
    return response.data
  },

  // Resend verification email
  async resendVerification(email) {
    const response = await api.post('/resend-verification', { email })
    return response.data
  },

  // Get current user
  getCurrentUser() {
    const userStr = localStorage.getItem('user')
    return userStr ? JSON.parse(userStr) : null
  },

  // Check if user is authenticated
  isAuthenticated() {
    return !!localStorage.getItem('auth_token')
  },

  // Check if user is admin
  isAdmin() {
    const user = this.getCurrentUser()
    return user && user.role === 'admin'
  }
}
