<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 50);
            $table->string('avatar')->nullable();

            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('experience')->default(0);

            $table->unsignedInteger('strength')->default(1);
            $table->unsignedInteger('intelligence')->default(1);
            $table->unsignedInteger('discipline')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
