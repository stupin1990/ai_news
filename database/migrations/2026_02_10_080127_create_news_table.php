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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_id')->constrained(
                table: 'categories', column: 'id'
            );
            $table->string('title', 1000);
            $table->string('slug', 1000);
            $table->string('image', 1000)->nullable();
            $table->string('source_url', 1000);
            $table->string('external_id')->index();
            $table->text('content')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestampTz('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
