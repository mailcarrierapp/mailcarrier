<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MailCarrier\Enums\LogStatus;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('tries')->default(0);
            $table->timestamp('last_try_at')->nullable();
        });

        DB::table('logs')->whereNot('status', LogStatus::Pending->value)->update([
            'last_try_at' => DB::raw('created_at'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('tries');
            $table->dropColumn('last_try_at');
        });
    }
};
