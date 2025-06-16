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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->json('name'); // Мультиязычное название
            $table->string('slug');
            $table->json('description')->nullable(); // Мультиязычное описание
            $table->enum('type', ['main', 'breakfast', 'lunch', 'dinner', 'drinks', 'special'])->default('main');
            $table->json('channels')->default('["web", "mobile"]'); // Каналы продаж
            $table->json('availability_hours')->nullable(); // Время доступности
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Настройки меню
            $table->timestamps();
            
            // Индексы
            $table->index(['restaurant_id', 'is_active', 'type']);
            $table->unique(['restaurant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
