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
        Schema::create('glossary_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term'); // e.g. "Laravel", "Apple", "React"
            $table->string('replacement')->nullable(); // Optional fixed translation
            $table->string('target_locale')->default('all'); // 'all' or specific like 'hi', 'gu'
            $table->boolean('do_not_translate')->default(true); // Lock as original term
            $table->string('description')->nullable(); // e.g. "Brand Name", "Technical Jargon"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glossary_terms');
    }
};
