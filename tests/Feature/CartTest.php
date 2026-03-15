<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\CartItem;
use App\Models\Event;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Event $event;

    protected Batch $batch;

    protected TicketType $ticketType;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'user']);
        $this->user = User::factory()->create();

        $categoryId = DB::table('event_categories')->insertGetId(['name' => 'Test Category']);
        $localId = DB::table('locals')->insertGetId([
            'name' => 'Test Local',
            'street' => 'Street',
            'number_street' => '123',
            'neighborhood' => 'Center',
            'max_people' => 100,
        ]);
        $this->event = Event::create([
            'id_category' => $categoryId,
            'id_local' => $localId,
            'name' => 'Test Event',
            'description' => 'Description',
            'date' => now()->addDays(7),
            'time' => '20:00',
            'max_tickets_per_cpf' => 4,
        ]);
        $this->batch = Batch::create([
            'price' => 50.00,
            'initial_date' => now(),
            'end_date' => now()->addDays(5),
            'quantity' => 100,
        ]);
        $this->ticketType = TicketType::create(['name' => 'Standard']);
    }

    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }

    public function test_cart_index_returns_empty_items(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson(['items' => []]);
    }

    public function test_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/cart', [
            'id_event' => $this->event->id,
            'id_batch' => $this->batch->id,
            'id_ticket_type' => $this->ticketType->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => __('Item added to cart.'),
                'item' => [
                    'quantity' => 2,
                    'event' => ['name' => 'Test Event'],
                    'batch' => ['price' => 50.0],
                    'ticket_type' => ['name' => 'Standard'],
                    'subtotal' => 100.0,
                ],
            ]);
        $this->assertDatabaseHas('cart_items', [
            'id_user' => $this->user->id,
            'id_event' => $this->event->id,
            'quantity' => 2,
        ]);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $item = CartItem::create([
            'id_user' => $this->user->id,
            'id_event' => $this->event->id,
            'id_batch' => $this->batch->id,
            'id_ticket_type' => $this->ticketType->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/cart/{$item->id}", ['quantity' => 3]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => __('Cart updated successfully.'),
                'item' => ['quantity' => 3],
            ]);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 3]);
    }

    public function test_cannot_update_another_users_cart_item(): void
    {
        $otherUser = User::factory()->create();
        $item = CartItem::create([
            'id_user' => $otherUser->id,
            'id_event' => $this->event->id,
            'id_batch' => $this->batch->id,
            'id_ticket_type' => $this->ticketType->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/cart/{$item->id}", ['quantity' => 5]);

        $response->assertStatus(404);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $item = CartItem::create([
            'id_user' => $this->user->id,
            'id_event' => $this->event->id,
            'id_batch' => $this->batch->id,
            'id_ticket_type' => $this->ticketType->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/cart/{$item->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => __('Item removed from cart.')]);
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_add_to_cart_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/cart', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_event', 'id_batch', 'id_ticket_type', 'quantity']);
    }
}
