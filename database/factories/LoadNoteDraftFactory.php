<?php

namespace Database\Factories;

use App\Models\LoadNoteDraft;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LoadNoteDraft> */
class LoadNoteDraftFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['request_id' => fake()->uuid(), 'payload_hash' => hash('sha256', fake()->uuid()),
            'ticket_number' => fake()->unique()->bothify('SJB-########'), 'mine_name' => fake()->company(),
            'loaded_on' => today()->toDateString(), 'vehicle_number' => '169', 'gross_kg' => 45540,
            'tare_kg' => 15320, 'net_kg' => 30220, 'photo_path' => 'mobile/load-notes/example.jpg',
            'photo_mime' => 'image/jpeg', 'status' => 'draft', 'created_by' => User::factory()];
    }

    public function forVehicle(Vehicle $vehicle, User $creator): static
    {
        return $this->state(fn (): array => [
            'vendor_id' => $vehicle->vendor_id,
            'vehicle_id' => $vehicle->id,
            'vehicle_number' => $vehicle->nomor_lambung,
            'created_by' => $creator->id,
        ]);
    }
}
