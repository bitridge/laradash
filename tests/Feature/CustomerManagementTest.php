<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_customers_list()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('customers.index'));

        $response->assertStatus(200)
            ->assertViewIs('customers.index')
            ->assertSee($customer->name);
    }

    public function test_user_can_create_customer()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('customers.store'), [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'company_name' => 'Test Company',
                'address' => '123 Test St',
                'notes' => 'Test notes',
            ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_customer()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->put(route('customers.update', $customer), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '0987654321',
                'company_name' => 'Updated Company',
                'address' => 'Updated Address',
                'notes' => 'Updated notes',
            ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_delete_customer()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted($customer);
    }

    public function test_user_cannot_access_other_users_customers()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->get(route('customers.show', $customer));

        $response->assertStatus(403);
    }

    public function test_validation_fails_with_invalid_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('customers.store'), [
                'name' => '',
                'email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }
} 