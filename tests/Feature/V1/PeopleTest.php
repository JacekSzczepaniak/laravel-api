<?php


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

it('creates, shows, updates and deletes a person', function () {
    // CREATE
    $create = $this->postJson('/api/v1/people', [
        'first_name' => 'John',
        'last_name' => 'Doe',
    ])->assertCreated()
        ->assertJsonStructure(['id', 'first_name', 'last_name']);

    $id = $create->json('id');
    expect($id)->toBeInt();

    // SHOW
    $this->getJson("/api/v1/people/{$id}")
        ->assertOk()
        ->assertJsonPath('first_name', 'John')
        ->assertJsonPath('last_name', 'Doe');

    // UPDATE (PATCH)
    $this->patchJson("/api/v1/people/{$id}", [
        'first_name' => 'Johnny',
    ])->assertOk()
        ->assertJsonPath('first_name', 'Johnny');

    // DELETE (soft delete)
    $this->deleteJson("/api/v1/people/{$id}")
        ->assertNoContent();

    $this->assertSoftDeleted('people', ['id' => $id]);
});

it('lists people', function () {
    // tworzymy 2 rekordy przez API żeby nie polegać na fabrykach domenowych
    $this->postJson('/api/v1/people', ['first_name' => 'Jane', 'last_name' => 'Doe'])->assertCreated();
    $this->postJson('/api/v1/people', ['first_name' => 'Mike', 'last_name' => 'Smith'])->assertCreated();

    $res = $this->getJson('/api/v1/people')->assertOk();

    // Minimalna asercja niezależna od formatu paginacji
    $content = $res->getContent();
    $this->assertStringContainsString('Jane', $content);
    $this->assertStringContainsString('Mike', $content);
});
