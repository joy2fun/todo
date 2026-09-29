<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('method')->default('GET');
            $table->string('path');
            $table->smallInteger('status_code')->default(200);
            $table->string('content_type')->default('application/json');
            $table->text('body')->nullable();
            $table->json('headers')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['method', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('endpoints');
    }
};
