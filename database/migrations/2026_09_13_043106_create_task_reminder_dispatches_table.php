<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_reminder_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('reminder_type', 32);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['task_id', 'reminder_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_reminder_dispatches');
    }
};
