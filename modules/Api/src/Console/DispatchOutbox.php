<?php

namespace Modules\Api\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Api\Mail\WelcomePersonMail;
use Modules\Api\Models\OutboxMessage;

class DispatchOutbox extends Command
{
    protected $signature = 'outbox:dispatch {--limit=50}';
    protected $description = 'Send pending outbox messages';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $ids = OutboxMessage::query()
            ->where('status','pending')
            ->where(fn($q) => $q->whereNull('available_at')->orWhere('available_at','<=',now()))
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id');

        if ($ids->isEmpty()) return self::SUCCESS;

        OutboxMessage::whereIn('id', $ids)->update(['status' => 'processing']);

        OutboxMessage::whereIn('id', $ids)->get()->each(function(OutboxMessage $msg) {
            try {
                match ($msg->type) {
                    'welcome_email' => $this->sendWelcome($msg->payload),
                    default => throw new \RuntimeException("Unknown type {$msg->type}"),
                };
                $msg->update(['status' => 'sent', 'last_error' => null]);
            } catch (\Throwable $e) {
                $msg->update([
                    'status'    => 'failed',
                    'attempts'  => DB::raw('attempts + 1'),
                    'last_error'=> substr($e->getMessage(), 0, 2000),
                    'available_at' => now()->addMinutes(5),
                ]);
            }
        });

        return self::SUCCESS;
    }

    private function sendWelcome(array $payload): void
    {
        Mail::to($payload['email'])->send(new WelcomePersonMail($payload['name']));
    }
}
