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
        Schema::create('news_categories', function (Blueprint $table) {
            $table->foreignId('news_id')->constrained(
                table: 'news', column: 'id'
            )
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->foreignId('cat_id')->constrained(
                table: 'categories', column: 'id'
            )
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->primary(['news_id', 'cat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_categories');
    }
};
