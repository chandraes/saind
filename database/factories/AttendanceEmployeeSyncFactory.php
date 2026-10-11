<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceEmployeeSyncFactory extends Factory
{
    public function definition(): array
    {
        $sourceId = (string) fake()->unique()->numberBetween(1, 1000000);
        $payload = ['source_employee_id' => $sourceId, 'employee_number' => 'STAFF-'.$sourceId, 'name' => fake()->name(), 'position' => null, 'status' => 'active', 'employment_started_at' => null];

        return ['source_employee_id' => $sourceId, 'payload' => $payload, 'payload_hash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)), 'source_version' => 1];
    }
}
