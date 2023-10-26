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
        $tableName = (new Log())->getTable();

        DB::table($tableName)->each(function (object $log) use ($tableName) {
            $changes = [];

            if (!is_null($log->cc)) {
                $changes['cc'] = [$log->cc];
            }

            if (!is_null($log->bcc)) {
                $changes['bcc'] = [$log->bcc];
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
