import api from './api'

const productService = {
  // Get all products
  async getAll() {
    const response = await api.get('/products')
    return response.data
  },

  // Get single product
  async getById(id) {
    const response = await api.get(`/products/${id}`)
    return response.data
  },

  // Create new product
  async create(productData) {
    const formData = new FormData()
    formData.append('name', productData.name)
    formData.append('price', productData.price)
    formData.append('stock', productData.stock)
    
    if (productData.image) {
      formData.append('image', productData.image)
    }

    const response = await api.post('/products', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  // Update product
  async update(id, productData) {
    const formData = new FormData()
    formData.append('_method', 'PUT') // Laravel needs this for PUT with FormData
    
    if (productData.name) formData.append('name', productData.name)
    if (productData.price) formData.append('price', productData.price)
    if (productData.stock !== undefined) formData.append('stock', productData.stock)
    
    if (productData.image) {
      formData.append('image', productData.image)
    }

    const response = await api.post(`/products/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  // Delete product
  async delete(id) {
    const response = await api.delete(`/products/${id}`)
    return response.data
  }
}

export default productService
