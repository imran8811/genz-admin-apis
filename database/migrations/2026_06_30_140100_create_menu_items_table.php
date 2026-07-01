<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu items — the canonical catalog owned by genz-admin. `slug` is the stable,
 * immutable identity shared with every downstream consumer (web, app, web-apis
 * checkout re-pricing, RMS costing). `image_updated_at` is bumped on each image
 * upload and used as a cache-buster (?v=) in the public feed.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('price_type', ['single', 'sized'])->default('single');
            $table->unsignedInteger('price')->nullable();
            $table->json('prices')->nullable();
            $table->json('pizza_selection')->nullable();
            $table->json('deal_extras')->nullable();
            $table->string('default_size')->nullable();
            $table->string('tag')->nullable();
            $table->boolean('is_special')->default(false);
            $table->boolean('is_signature')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('image_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
