<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function a_user_can_list_stores()
    {
        Store::factory()->count(3)->create(['user_id' => $this->user->id]);
        Store::factory()->count(2)->create(); // Other users' stores

        $response = $this->actingAs($this->user)->getJson('/api/stores');

        $response->assertOk()
                 ->assertJsonCount(3, 'data')
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'user_id',
                             'name',
                             'platform',
                             'store_url',
                             'api_key',
                             'api_secret',
                             'access_token',
                             'created_at',
                             'updated_at',
                             'deleted_at',
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function a_user_can_create_a_store()
    {
        $storeData = [
            'name' => 'My New Store',
            'platform' => 'shopify',
            'store_url' => 'https://mynewstore.myshopify.com',
            'access_token' => 'shpat_newtoken',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/stores', $storeData);

        $response->assertCreated()
                 ->assertJsonFragment([
                     'name' => 'My New Store',
                     'platform' => 'shopify',
                     'store_url' => 'https://mynewstore.myshopify.com',
                 ]);

        $this->assertDatabaseHas('stores', [
            'user_id' => $this->user->id,
            'name' => 'My New Store',
            'platform' => 'shopify',
            'store_url' => 'https://mynewstore.myshopify.com',
            'access_token' => 'shpat_newtoken',
        ]);
    }

    /** @test */
    public function a_user_can_view_their_store()
    {
        $store = Store::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/stores/' . $store->id);

        $response->assertOk()
                 ->assertJsonFragment([
                     'id' => $store->id,
                     'name' => $store->name,
                 ]);
    }

    /** @test */
    public function a_user_cannot_view_another_users_store()
    {
        $otherUserStore = Store::factory()->create(); // Belongs to another user

        $response = $this->actingAs($this->user)->getJson('/api/stores/' . $otherUserStore->id);

        $response->assertForbidden();
    }

    /** @test */
    public function a_user_can_update_their_store()
    {
        $store = Store::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'name' => 'Updated Store Name',
            'platform' => 'woocommerce',
            'api_key' => 'ck_updated',
            'api_secret' => 'cs_updated',
        ];

        $response = $this->actingAs($this->user)->putJson('/api/stores/' . $store->id, $updatedData);

        $response->assertOk()
                 ->assertJsonFragment([
                     'id' => $store->id,
                     'name' => 'Updated Store Name',
                     'platform' => 'woocommerce',
                 ]);

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'Updated Store Name',
            'platform' => 'woocommerce',
            'api_key' => 'ck_updated',
            'api_secret' => 'cs_updated',
        ]);
    }

    /** @test */
    public function a_user_cannot_update_another_users_store()
    {
        $otherUserStore = Store::factory()->create(); // Belongs to another user

        $updatedData = [
            'name' => 'Attempted Update',
        ];

        $response = $this->actingAs($this->user)->putJson('/api/stores/' . $otherUserStore->id, $updatedData);

        $response->assertForbidden();
    }

    /** @test */
    public function a_user_can_delete_their_store()
    {
        $store = Store::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson('/api/stores/' . $store->id);

        $response->assertNoContent();

        $this->assertSoftDeleted('stores', ['id' => $store->id]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_store()
    {
        $otherUserStore = Store::factory()->create(); // Belongs to another user

        $response = $this->actingAs($this->user)->deleteJson('/api/stores/' . $otherUserStore->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('stores', ['id' => $otherUserStore->id]); // Ensure it's not deleted
    }
}
