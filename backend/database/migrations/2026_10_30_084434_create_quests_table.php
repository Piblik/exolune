<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quests', function (Blueprint $table) {
            $table->id();

            $table->string('title', 100);
            $table->text('description');

            $table->string('category', 50);
            $table->enum('difficulty', [
                'easy',
                'medium',
                'hard',
                'epic',
            ])->default('easy');

            $table->unsignedInteger('experience_reward')->default(0);

            $table->unsignedInteger('strength_reward')->default(0);
            $table->unsignedInteger('intelligence_reward')->default(0);
            $table->unsignedInteger('discipline_reward')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quests');
    }
};
