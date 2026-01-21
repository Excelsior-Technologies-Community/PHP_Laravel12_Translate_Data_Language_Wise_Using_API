<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('locale', 10); // en, hi, gu
            $table->string('title');
            $table->text('content');
            $table->timestamps();
            
            $table->unique(['post_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
};