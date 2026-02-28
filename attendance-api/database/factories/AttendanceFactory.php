<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'session_id' => \App\Models\AttendanceSession::factory(),
            'type' => fake()->randomElement(['checkin', 'checkout']),
            'status' => fake()->randomElement(['present', 'late', 'absent']),
            'lat' => fake()->latitude(-6.3, -6.1), // roughly Jakarta lat
            'lng' => fake()->longitude(106.7, 106.9), // roughly Jakarta lng
            'face_confidence' => fake()->randomFloat(4, 0.8, 1.0),
            'liveness_score' => fake()->randomFloat(4, 0.8, 1.0),
            'checked_at' => fake()->dateTimeThisMonth(),
        ];
    }
}
