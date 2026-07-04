<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['todo_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('completions');
    }
};
