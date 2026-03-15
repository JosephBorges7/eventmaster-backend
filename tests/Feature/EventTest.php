<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_index_returns_paginated_list(): void
    {
        $categoryId = DB::table('event_categories')->insertGetId(['name' => 'Category']);
        $localId = DB::table('locals')->insertGetId([
            'name' => 'Venue',
            'street' => 'Main St',
            'number_street' => '1',
            'neighborhood' => 'Center',
            'max_people' => 50,
        ]);
        Event::create([
            'id_category' => $categoryId,
            'id_local' => $localId,
            'name' => 'First Event',
            'description' => 'Desc',
            'date' => now()->addDays(10),
            'time' => '19:00',
            'max_tickets_per_cpf' => 2,
        ]);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotNull($data);
        $this->assertCount(1, $data);
        $this->assertEquals('First Event', $data[0]['name']);
        $this->assertArrayHasKey('category', $data[0]);
        $this->assertArrayHasKey('local', $data[0]);
    }

    public function test_events_show_returns_single_event(): void
    {
        $categoryId = DB::table('event_categories')->insertGetId(['name' => 'Category']);
        $localId = DB::table('locals')->insertGetId([
            'name' => 'Venue',
            'street' => 'Main St',
            'number_street' => '1',
            'neighborhood' => 'Center',
            'max_people' => 50,
        ]);
        $event = Event::create([
            'id_category' => $categoryId,
            'id_local' => $localId,
            'name' => 'Single Event',
            'description' => 'Description',
            'date' => now()->addDays(5),
            'time' => '20:30',
            'max_tickets_per_cpf' => 4,
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $event->id,
                'name' => 'Single Event',
                'description' => 'Description',
                'time' => '20:30',
                'max_tickets_per_cpf' => 4,
                'category' => ['name' => 'Category'],
                'local' => ['name' => 'Venue'],
            ]);
    }

    public function test_events_show_returns_404_for_nonexistent_event(): void
    {
        $response = $this->getJson('/api/events/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => __('Record not found.')]);
    }
}
