<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->token = $this->admin->createToken('auth_token')->plainTextToken;
    }

    /**
     * Test anyone can view products list.
     */
    public function test_anyone_can_view_products_list(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test anyone can view a single product.
     */
    public function test_anyone_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
            ]);
    }

    /**
     * Test admin can create a product.
     */
    public function test_admin_can_create_product(): void
    {
        Storage::fake('public');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'price' => 99.99,
                'stock' => 50,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'product' => ['id', 'name', 'price', 'stock']
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 50,
        ]);
    }

    /**
     * Test admin can create product with image.
     */
    public function test_admin_can_create_product_with_image(): void
    {
        Storage::fake('public');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Product With Image',
                'price' => 49.99,
                'stock' => 25,
                'image' => UploadedFile::fake()->image('product.jpg'),
            ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('products', [
            'name' => 'Product With Image',
        ]);
    }

    /**
     * Test product creation fails with invalid data.
     */
    public function test_product_creation_fails_with_invalid_data(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => '',
                'price' => -10,
                'stock' => -5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock']);
    }

    /**
     * Test admin can update a product.
     */
    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Updated Product',
                'price' => 149.99,
                'stock' => 100,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
            'stock' => 100,
        ]);
    }

    /**
     * Test admin can delete a product.
     */
    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test unauthenticated user cannot create product.
     */
    public function test_unauthenticated_user_cannot_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 50,
        ]);

        $response->assertStatus(401);
    }
}
