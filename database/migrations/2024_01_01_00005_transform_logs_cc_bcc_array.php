<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MailCarrier\Models\Log;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('migrations')->where('migration', '6_transform_logs_cc_bcc_array')->exists()) {
            return;
        }

        $tableName = (new Log)->getTable();

        DB::table($tableName)
            ->whereNotNull('cc')
            ->orWhereNotNull('bcc')
            ->orderBy('id')
            ->each(function (object $log) use ($tableName) {
                $changes = [];

                if (!is_null($log->cc)) {
                    $changes['cc'] = [json_decode($log->cc, true)];
                }

                if (!is_null($log->bcc)) {
                    $changes['bcc'] = [json_decode($log->bcc, true)];
                }

                if (!empty($changes)) {
                    DB::table($tableName)
                        ->where('id', $log->id)
                        ->update($changes);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
