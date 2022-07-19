<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MailCarrier\MailCarrier\Models\Log;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignIdFor(Log::class)
                ->index()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('strategy');
            $table->string('name');
            $table->integer('size');
            $table->string('path')->nullable();
            $table->string('disk')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
