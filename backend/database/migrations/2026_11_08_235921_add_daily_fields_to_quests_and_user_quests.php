<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->boolean('is_daily')
                ->default(false)
                ->after('is_active');
        });

        Schema::table('user_quests', function (Blueprint $table) {
            $table->date('daily_date')
                ->nullable()
                ->after('quest_id');

            $table->unique(
                ['user_id', 'quest_id', 'daily_date'],
                'user_daily_quest_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('user_quests', function (Blueprint $table) {
            $table->dropUnique('user_daily_quest_unique');
            $table->dropColumn('daily_date');
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn('is_daily');
        });
    }
};
