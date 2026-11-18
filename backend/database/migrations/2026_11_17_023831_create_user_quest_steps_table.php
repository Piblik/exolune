<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_quest_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_quest_id')
                ->constrained('user_quests')
                ->cascadeOnDelete();

            $table->foreignId('quest_step_id')
                ->constrained('quest_steps')
                ->cascadeOnDelete();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'user_quest_id',
                'quest_step_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_quest_steps');
    }
};
