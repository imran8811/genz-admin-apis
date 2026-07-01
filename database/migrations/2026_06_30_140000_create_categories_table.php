<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu categories — the canonical catalog owned by genz-admin.
 * `type` = single (one price) | sized (per-size prices keyed by `sizes`).
 * Deal groups are categories whose slug ends in "deals" (feed convention).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->enum('type', ['single', 'sized'])->default('single');
            $table->json('sizes')->nullable();
            $table->boolean('is_coming_soon')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('image_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
