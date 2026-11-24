<?php

use App\Models\AlumniPeserta;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('active users authenticate regardless of their Spatie role', function (?string $role): void {
    $user = User::factory()->create();

    if ($role !== null) {
        $roleId = DB::table('roles')->insertGetId([
            'name' => $role,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user, 'web');
})->with([
    'without a role' => null,
    'with the Admin role' => 'Admin',
    'with another role' => 'KIP',
]);

test('an active user without alumni permissions can access every internal alumni route', function (): void {
    $user = User::factory()->create(['email_verified_at' => null]);
    $peserta = AlumniPeserta::factory()->create();

    $this->actingAs($user, 'web');

    $this->get(route('dashboard'))->assertRedirect(route('alumni.dashboard'));
    $this->get(route('alumni.dashboard'))->assertOk();
    $this->get(route('alumni.participants'))->assertOk();
    $this->get(route('alumni.participants.show', $peserta))->assertOk();
    $this->get(route('alumni.curation'))->assertOk();
    $this->get(route('alumni.publications'))->assertOk();
    $this->get(route('alumni.scoring'))->assertOk();
    $this->get(route('alumni.settings'))->assertOk();
    $this->get(route('alumni.export.participants'))->assertOk();
});

test('inactive SIAKAD accounts are rejected at login and on protected routes', function (): void {
    $user = User::factory()->inactive()->create();

    $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('username');

    $this->assertGuest('web');

    $this->actingAs($user, 'web')
        ->get(route('alumni.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest('web');
});

test('guests are redirected from internal alumni routes while the public registration remains available', function (): void {
    $this->get(route('alumni.dashboard'))->assertRedirect(route('login'));
    $this->get(route('alumni.register'))->assertOk();
});

test('a login session persists for the next request and logout invalidates it', function (): void {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->get(route('alumni.dashboard'))->assertOk();

    $this->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest('web');
    $this->get(route('alumni.dashboard'))->assertRedirect(route('login'));
});
