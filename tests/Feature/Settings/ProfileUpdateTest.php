<?php

use App\Models\User;

test('SIAKAD account settings routes are disabled', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get('/settings')->assertNotFound();
    $this->get('/settings/profile')->assertNotFound();
    $this->get('/alumni/pengaturan')->assertNotFound();
});
