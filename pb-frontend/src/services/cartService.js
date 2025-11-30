// Cart service - stores cart in localStorage per user
const CART_KEY_PREFIX = 'shopping_cart_';

const getUserCartKey = () => {
  const user = localStorage.getItem('user');
  if (user) {
    const userData = JSON.parse(user);
    return `${CART_KEY_PREFIX}${userData.id}`;
  }
  return `${CART_KEY_PREFIX}guest`;
};

export const cartService = {
  // Get all cart items
  getCart() {
    const cartKey = getUserCartKey();
    const cart = localStorage.getItem(cartKey);
    return cart ? JSON.parse(cart) : [];
  },

  // Add item to cart
  addToCart(product, quantity = 1) {
    const cartKey = getUserCartKey();
    const cart = this.getCart();
    const existingItem = cart.find(item => item.id === product.id);
    const stock = product.stock || 999;

    if (existingItem) {
      // Don't exceed stock
      const newQuantity = existingItem.quantity + quantity;
      existingItem.quantity = Math.min(newQuantity, stock);
    } else {
      cart.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        stock: stock,
        quantity: Math.min(quantity, stock)
      });
    }

    localStorage.setItem(cartKey, JSON.stringify(cart));
    // Dispatch custom event for cart updates
    window.dispatchEvent(new CustomEvent('cart-updated', { detail: cart }));
    return cart;
  },

  // Update item quantity
  updateQuantity(productId, quantity, maxStock = null) {
    const cartKey = getUserCartKey();
    const cart = this.getCart();
    const item = cart.find(item => item.id === productId);

    if (item) {
      if (quantity <= 0) {
        return this.removeFromCart(productId);
      }
      // Use stored stock or provided maxStock
      const stock = maxStock || item.stock || 999;
      item.quantity = Math.min(quantity, stock);
      localStorage.setItem(cartKey, JSON.stringify(cart));
      window.dispatchEvent(new CustomEvent('cart-updated', { detail: cart }));
    }

    return cart;
  },

  // Remove item from cart
  removeFromCart(productId) {
    const cartKey = getUserCartKey();
    let cart = this.getCart();
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem(cartKey, JSON.stringify(cart));
    window.dispatchEvent(new CustomEvent('cart-updated', { detail: cart }));
    return cart;
  },

  // Clear entire cart
  clearCart() {
    const cartKey = getUserCartKey();
    localStorage.removeItem(cartKey);
    window.dispatchEvent(new CustomEvent('cart-updated', { detail: [] }));
    return [];
  },

  // Get cart count
  getCartCount() {
    const cart = this.getCart();
    return cart.reduce((total, item) => total + item.quantity, 0);
  },

  // Get cart total
  getCartTotal() {
    const cart = this.getCart();
    return cart.reduce((total, item) => total + (parseFloat(item.price) * item.quantity), 0);
  }
};

export default cartService;
