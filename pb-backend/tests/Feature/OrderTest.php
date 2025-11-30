<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected string $userToken;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'guest']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->userToken = $this->user->createToken('auth_token')->plainTextToken;
        $this->adminToken = $this->admin->createToken('auth_token')->plainTextToken;
    }

    /**
     * Test user can place an order.
     */
    public function test_user_can_place_order(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 50.00]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->postJson('/api/orders', [
                'items' => [
                    ['id' => $product->id, 'quantity' => 2, 'name' => $product->name, 'price' => $product->price]
                ],
                'total' => 100.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order' => ['id', 'user_id', 'items', 'total', 'status']
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        // Check stock was decremented
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);
    }

    /**
     * Test order fails with insufficient stock.
     */
    public function test_order_fails_with_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 50.00]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->postJson('/api/orders', [
                'items' => [
                    ['id' => $product->id, 'quantity' => 10, 'name' => $product->name, 'price' => $product->price]
                ],
                'total' => 500.00,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test user can view their orders.
     */
    public function test_user_can_view_their_orders(): void
    {
        Order::factory()->count(2)->create(['user_id' => $this->user->id]);
        Order::factory()->create(['user_id' => $this->admin->id]); // Other user's order

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/orders/my-orders');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    /**
     * Test admin can view all orders.
     */
    public function test_admin_can_view_all_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test admin can update order status.
     */
    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/orders/{$order->id}", [
                'status' => 'delivered',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'delivered',
        ]);
    }

    /**
     * Test admin can delete order and stock is restored.
     */
    public function test_admin_can_delete_order_and_stock_is_restored(): void
    {
        $product = Product::factory()->create(['stock' => 8]);
        $order = Order::factory()->create([
            'status' => 'pending',
            'items' => [['id' => $product->id, 'quantity' => 2]],
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        
        // Stock should be restored
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }

    /**
     * Test unauthenticated user cannot place order.
     */
    public function test_unauthenticated_user_cannot_place_order(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => [],
            'total' => 0,
        ]);

        $response->assertStatus(401);
    }
}
