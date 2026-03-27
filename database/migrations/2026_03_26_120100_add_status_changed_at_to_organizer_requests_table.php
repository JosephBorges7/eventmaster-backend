<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizer_requests', function (Blueprint $table) {
            $table->timestamp('status_changed_at')->nullable()->after('status')->index();
        });

        DB::table('organizer_requests')
            ->whereNull('status_changed_at')
            ->update(['status_changed_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizer_requests', function (Blueprint $table) {
            $table->dropColumn('status_changed_at');
        });
    }
};
