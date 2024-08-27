<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MailCarrier\Models\Template;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('migrations')->where('migration', '4_create_logs_table')->exists()) {
            return;
        }

        Schema::create('logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignIdFor(Template::class)
                ->nullable()
                ->index()
                ->constrained()
                ->nullOnDelete();

            $table->string('status')->index();
            $table->string('trigger')->nullable()->index();
            $table->string('subject');
            $table->string('recipient');
            $table->text('error')->nullable();
            $table->json('sender');
            $table->json('template_frozen')->nullable();
            $table->json('variables')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->timestamps();

            $table->index(['subject', 'recipient']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
