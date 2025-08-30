<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Api\Enums\ContactType;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('people', function (Blueprint $t) {
            $t->id();
            $t->string('first_name', 100);
            $t->string('last_name', 100);

            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('person_contacts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('person_id')->constrained('people')->cascadeOnDelete();

            $t->enum('type', ContactType::values());
            $t->string('value', 320);
            $t->boolean('is_primary')->default(false);

            $t->timestamp('verified_at')->nullable();
            $t->string('verification_code', 64)->nullable();
            $t->timestamp('verification_expires_at')->nullable();

            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $t->json('meta')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->unique(['person_id', 'type', 'value']);
            $t->index(['type', 'value']);
        });

        Schema::create('outbox_messages', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('channel', 32)->default('mail');
            $t->string('event', 64);
            $t->json('payload');
            $t->string('status', 16)->default('pending');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->timestamp('available_at')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->text('last_error')->nullable();
            $t->string('correlation_id', 100)->nullable();

            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamps();
            $t->index(['status', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');
        Schema::dropIfExists('person_contacts');
        Schema::dropIfExists('people');
    }
};
