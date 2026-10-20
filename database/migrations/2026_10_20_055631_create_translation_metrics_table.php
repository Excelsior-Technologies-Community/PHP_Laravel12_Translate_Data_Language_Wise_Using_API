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
        Schema::create('translation_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('google');
            $table->string('source_locale', 10)->default('en');
            $table->string('target_locale', 10);
            $table->unsignedInteger('characters_count')->default(0);
            $table->unsignedInteger('words_count')->default(0);
            $table->boolean('from_cache')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_metrics');
    }
};
