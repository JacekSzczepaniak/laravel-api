<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

beforeEach(function () {
    // Wymuś konfigurację bezstanową dla testów
    config()->set('cache.default', 'array');     // cache in-memory
    config()->set('session.driver', 'array');    // sesja in-memory (bez plików/cookies)

    // Wyłącz cache Sanctum (gdyby config/sanctum.php nie został jeszcze w pełni załadowany)
    config()->set('sanctum.cache.store', null);
    config()->set('sanctum.cache.ttl', 0);
    config()->set('sanctum.stateful', []);       // pewność, że brak stateful для testów

    // Wyczyść konfigurację i cache aplikacji, aby wyżej ustawione wartości obowiązywały
    try {
        Artisan::call('config:clear');
    } catch (\Throwable $e) {
    }
    try {
        Artisan::call('cache:clear');
    } catch (\Throwable $e) {
    }

    cache()->flush();
});


function makePersonId(TestCase $t): int
{
    return $t->postJson('/api/v1/people', [
        'first_name' => 'John',
        'last_name'  => 'Doe',
    ])->json('id');
}
