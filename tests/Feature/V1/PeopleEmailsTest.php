<?php


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Modules\Api\Mail\WelcomePersonMail;


uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
    Mail::fake();
});

it('sends welcome email to all email contacts and ignores non-email types', function () {
    $personId = makePersonId($this, 'Jan', 'Kowalski');

    // dodajemy 2 e-maile
    $this->postJson("/api/v1/people/{$personId}/contacts", [
        'type' => 'email',
        'value' => 'a@example.com',
    ])->assertCreated();

    $this->postJson("/api/v1/people/{$personId}/contacts", [
        'type' => 'email',
        'value' => 'b@example.com',
    ])->assertCreated();

    // oraz kontakt nie-email (powinien być pominięty)
    $this->postJson("/api/v1/people/{$personId}/contacts", [
        'type' => 'phone',
        'value' => '+48123123123',
    ])->assertCreated();

    // wywołanie wysyłki
    $this->postJson("/api/v1/people/{$personId}/emails/send-welcome")
        ->assertStatus(202)
        ->assertJsonPath('sent', 2);

    // asercje Mail
    $this->assertCount(2, Mail::sent(WelcomePersonMail::class));

    $recipients = collect(Mail::sent(WelcomePersonMail::class))
        ->flatMap(fn ($m) => collect($m->to)->pluck('address'))
        ->all();

    $this->assertEqualsCanonicalizing(
        ['a@example.com', 'b@example.com'],
        $recipients
    );

    Mail::assertSent(WelcomePersonMail::class, function (WelcomePersonMail $mail) {
        return $mail->hasTo('a@example.com');
    });

    Mail::assertSent(WelcomePersonMail::class, function (WelcomePersonMail $mail) {
        return $mail->hasTo('b@example.com');
    });
});
