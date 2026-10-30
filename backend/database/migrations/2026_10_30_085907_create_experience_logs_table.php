<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experience_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('character_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('source', 50);
            $table->string('description', 255);

            $table->integer('experience');

            $table->integer('strength_change')->default(0);
            $table->integer('intelligence_change')->default(0);
            $table->integer('discipline_change')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_logs');
    }
};
