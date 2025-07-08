<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'api_token' => 'test_token_123',
        ]);
        
        $this->token = $this->user->api_token;
    }

    public function test_unauthorized_request_is_rejected(): void
    {
        $response = $this->getJson('/api/bookings');

        $response->assertStatus(401)
                ->assertJson(['error' => 'API token is required']);
    }

    public function test_can_create_booking_with_multiple_slots(): void
    {
        $slots = [
            [
                'start_time' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
                'end_time' => now()->addDay()->setTime(13, 0)->toDateTimeString(),
            ],
            [
                'start_time' => now()->addDay()->setTime(13, 30)->toDateTimeString(),
                'end_time' => now()->addDay()->setTime(14, 30)->toDateTimeString(),
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('/api/bookings', [
            'slots' => $slots,
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'user_id',
                        'slots' => [
                            '*' => [
                                'id',
                                'start_time',
                                'end_time',
                            ],
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseCount('booking_slots', 2);
    }

    public function test_cannot_create_booking_with_conflicting_slots(): void
    {
        // Создаем первое бронирование через API
        $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('/api/bookings', [
            'slots' => [
                [
                    'start_time' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
                    'end_time' => now()->addDay()->setTime(13, 0)->toDateTimeString(),
                ],
            ],
        ]);

        // Пытаемся создать конфликтующее бронирование
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('/api/bookings', [
            'slots' => [
                [
                    'start_time' => now()->addDay()->setTime(12, 30)->toDateTimeString(),
                    'end_time' => now()->addDay()->setTime(13, 30)->toDateTimeString(),
                ],
            ],
        ]);

        $response->assertStatus(422)
                ->assertJson(['error' => 'Time slot conflicts with existing booking']);
    }

    public function test_can_update_slot_successfully(): void
    {
        // Создаем бронирование через API
        $bookingResponse = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('/api/bookings', [
            'slots' => [
                [
                    'start_time' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
                    'end_time' => now()->addDay()->setTime(13, 0)->toDateTimeString(),
                ],
            ],
        ]);

        $booking = $bookingResponse->json('data');
        $slot = $booking['slots'][0];

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->patchJson("/api/bookings/{$booking['id']}/slots/{$slot['id']}", [
            'start_time' => now()->addDay()->setTime(14, 0)->toDateTimeString(),
            'end_time' => now()->addDay()->setTime(15, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'start_time',
                        'end_time',
                    ],
                ]);
    }

    public function test_cannot_update_slot_with_conflict(): void
    {
        // Создаем первое бронирование через API
        $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('/api/bookings', [
            'slots' => [
                [
                    'start_time' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
                    'end_time' => now()->addDay()->setTime(13, 0)->toDateTimeString(),
                ],
            ],
        ]);

        // Создаем второе бронирование для обновления
        $bookingResponse = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('/api/bookings', [
            'slots' => [
                [
                    'start_time' => now()->addDay()->setTime(14, 0)->toDateTimeString(),
                    'end_time' => now()->addDay()->setTime(15, 0)->toDateTimeString(),
                ],
            ],
        ]);

        $booking = $bookingResponse->json('data');
        $slot = $booking['slots'][0];

        // Пытаемся обновить слот с конфликтом
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->patchJson("/api/bookings/{$booking['id']}/slots/{$slot['id']}", [
            'start_time' => now()->addDay()->setTime(12, 30)->toDateTimeString(),
            'end_time' => now()->addDay()->setTime(13, 30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
                ->assertJson(['error' => 'Time slot conflicts with existing booking']);
    }

    public function test_can_add_slot_to_existing_booking(): void
    {
        // Создаем бронирование через API
        $bookingResponse = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('/api/bookings', [
            'slots' => [
                [
                    'start_time' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
                    'end_time' => now()->addDay()->setTime(13, 0)->toDateTimeString(),
                ],
            ],
        ]);

        $booking = $bookingResponse->json('data');

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson("/api/bookings/{$booking['id']}/slots", [
            'start_time' => now()->addDay()->setTime(14, 0)->toDateTimeString(),
            'end_time' => now()->addDay()->setTime(15, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'start_time',
                        'end_time',
                    ],
                ]);

        $this->assertDatabaseCount('booking_slots', 2);
    }

    public function test_cannot_access_other_users_booking(): void
    {
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'api_token' => 'other_token_456',
        ]);

        // Создаем бронирование для другого пользователя
        $booking = Booking::create(['user_id' => $otherUser->id]);
        BookingSlot::create([
            'booking_id' => $booking->id,
            'start_time' => now()->addDay()->setTime(12, 0),
            'end_time' => now()->addDay()->setTime(13, 0),
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(403)
                ->assertJson(['error' => 'Unauthorized']);
    }
}
