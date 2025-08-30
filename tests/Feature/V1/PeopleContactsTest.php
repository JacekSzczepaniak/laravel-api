<?php


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

it('creates, lists, updates and deletes a contact for a person', function () {
    $personId = makePersonId($this);

    // CREATE contact (email)
    $create = $this->postJson("/api/v1/people/{$personId}/contacts", [
        'type' => 'email',
        'value' => 'john.doe@example.com',
        'is_primary' => true,
    ])->assertCreated()
        ->assertJsonStructure(['id', 'type', 'value', 'is_primary']);

    $contactId = $create->json('id');
    expect($contactId)->toBeInt();

    // LIST contacts
    $this->getJson("/api/v1/people/{$personId}/contacts")
        ->assertOk()
        ->assertSee('john.doe@example.com');

    // UPDATE contact
    $this->patchJson("/api/v1/people/{$personId}/contacts/{$contactId}", [
        'value' => 'john+2@example.com',
        'is_primary' => false,
    ])->assertOk()
        ->assertJsonPath('value', 'john+2@example.com')
        ->assertJsonPath('is_primary', false);

    // DELETE contact (soft delete)
    $this->deleteJson("/api/v1/people/{$personId}/contacts/{$contactId}")
        ->assertNoContent();

    $this->assertSoftDeleted('person_contacts', ['id' => $contactId]);
});

it('rejects duplicate contact (same person, type and value)', function () {
    $personId = makePersonId($this);

    $payload = ['type' => 'email', 'value' => 'dup@example.com', 'is_primary' => false];

    $this->postJson("/api/v1/people/{$personId}/contacts", $payload)->assertCreated();
    $this->postJson("/api/v1/people/{$personId}/contacts", $payload)->assertStatus(422);
});
