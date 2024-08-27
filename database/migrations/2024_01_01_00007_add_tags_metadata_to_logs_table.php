<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('migrations')->where('migration', '8_add_tags_metadata_to_logs_table')->exists()) {
            return;
        }

        Schema::table('logs', function (Blueprint $table) {
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('tags');
            $table->dropColumn('metadata');
        });
    }
};
