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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('address')->nullable(); // JSON для мультиязычности
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->string('currency', 3)->default('USD');
            $table->json('languages')->default('["en"]'); // Поддерживаемые языки
            $table->string('default_language', 5)->default('en');
            $table->json('business_hours')->nullable(); // Часы работы
            $table->json('delivery_zones')->nullable(); // Зоны доставки
            $table->json('settings')->nullable(); // Дополнительные настройки
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Индексы для производительности
            $table->index(['status', 'verified_at']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
