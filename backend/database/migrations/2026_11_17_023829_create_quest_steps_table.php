<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quest_id')
                ->constrained('quests')
                ->cascadeOnDelete();

            $table->string('title', 150);
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['quest_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_steps');
    }
};
