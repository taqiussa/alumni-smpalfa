<?php

use App\Models\User;

test('SIAKAD password settings route is disabled', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/settings/security')
        ->assertNotFound();
});
