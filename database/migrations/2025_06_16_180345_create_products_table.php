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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->json('name'); // Мультиязычное название
            $table->string('slug');
            $table->json('description')->nullable(); // Мультиязычное описание
            $table->string('sku')->nullable(); // Артикул
            $table->enum('type', ['simple', 'combo', 'modifier'])->default('simple');
            $table->json('images')->nullable(); // Массив URL изображений
            $table->json('prices'); // Цены по каналам: {"web": 10.99, "mobile": 9.99, "delivery": 12.99}
            $table->decimal('cost_price', 10, 2)->nullable(); // Себестоимость
            $table->integer('calories')->nullable();
            $table->json('allergens')->nullable(); // Аллергены
            $table->json('ingredients')->nullable(); // Ингредиенты
            $table->json('nutritional_info')->nullable(); // Пищевая ценность
            $table->json('modifiers')->nullable(); // ID модификаторов
            $table->json('combo_items')->nullable(); // Для комбо - ID продуктов
            $table->json('channels')->default('["web", "mobile"]'); // Каналы продаж
            $table->json('availability_hours')->nullable(); // Время доступности
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('stock_quantity')->nullable(); // Остатки
            $table->boolean('track_stock')->default(false);
            $table->json('settings')->nullable(); // Дополнительные настройки
            $table->timestamps();
            
            // Индексы
            $table->index(['restaurant_id', 'category_id', 'is_active']);
            $table->index(['restaurant_id', 'type', 'is_active']);
            $table->index(['restaurant_id', 'is_featured']);
            $table->unique(['restaurant_id', 'slug']);
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
