import api from './api'

export const orderService = {
  // Place a new order
  async createOrder(orderData) {
    const response = await api.post('/orders', orderData)
    return response.data
  },

  // Get all orders (admin)
  async getOrders() {
    const response = await api.get('/orders')
    return response.data
  },

  // Get single order
  async getOrder(id) {
    const response = await api.get(`/orders/${id}`)
    return response.data
  },

  // Update order status
  async updateOrderStatus(id, status) {
    const response = await api.put(`/orders/${id}`, { status })
    return response.data
  },

  // Delete order
  async deleteOrder(id) {
    const response = await api.delete(`/orders/${id}`)
    return response.data
  },

  // Get current user's orders
  async getMyOrders() {
    const response = await api.get('/orders/my-orders')
    return response.data
  }
}
