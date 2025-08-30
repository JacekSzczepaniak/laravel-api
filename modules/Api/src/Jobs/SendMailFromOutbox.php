<?php


namespace Modules\Api\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\Api\Mail\VerifyContactMail;
use Modules\Api\Mail\WelcomePersonMail;
use Modules\Api\Models\OutboxMessage;

class SendMailFromOutbox implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $outboxId)
    {
    }

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        $msg = OutboxMessage::find($this->outboxId);
        if (!$msg || $msg->status !== 'processing') {
            return;
        }

        try {
            $payload = $msg->payload;

            switch ($msg->type) {
                case 'mail.welcome':
                    foreach ($payload['to'] as $email) {
                        Mail::to($email)->send(new WelcomePersonMail($payload['full_name']));
                    }
                    break;

                case 'mail.verify_contact':
                    Mail::to($payload['to'])->send(new VerifyContactMail(
                        $payload['full_name'],
                        $payload['code']
                    ));
                    break;
            }

            $msg->status = 'sent';
            $msg->save();
        } catch (\Throwable $e) {
            $msg->attempts++;
            $msg->status = 'pending';
            $msg->available_at = now()->addMinutes(5);
            $msg->last_error = $e->getMessage();
            $msg->save();

            throw $e;
        }
    }
}
