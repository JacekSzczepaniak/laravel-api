<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('Auth v1', function () {

    it('registers a user', function () {
        $payload = [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'Secret123!',
        ];

        $this->postJson('/api/v1/auth/register', $payload)
            ->assertCreated()
            ->assertJsonStructure(['id']);

        $this->assertDatabaseHas('users', ['email' => $payload['email']]);
    });

    it('logs in and returns a token', function () {
        $user = User::factory()->create([
            'email' => 'demo2@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'demo2@example.com',
            'password' => 'Secret123!',
        ])->assertOk()
            ->assertJsonStructure(['token']);
    });

    it('rejects invalid credentials', function () {
        $user = User::factory()->create([
            'email' => 'demo3@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'demo3@example.com',
            'password' => 'WrongPass!',
        ])->assertStatus(422);
    });

    it('returns current user on /me when authorized', function () {
        $user = User::factory()->create([
            'email' => 'me@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'me@example.com',
            'password' => 'Secret123!',
        ])->json('token');

        expect($token)->toBeString()->not->toBeEmpty();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonFragment(['email' => 'me@example.com']);
    });

    it('invalidates token on logout', function () {
        $user = User::factory()->create([
            'email' => 'logout@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'logout@example.com',
            'password' => 'Secret123!',
        ])->json('token');


        $this->withHeader('Authorization', 'Bearer '.$token)
                ->postJson('/api/v1/auth/logout')
                ->assertNoContent();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    });

});
