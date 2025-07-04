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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique()->nullable();
            $table->tinyInteger('channel')->default(0)->comment('0=web, 1=mobile, 2=telegram, 3=whatsapp, 4=phone, 5=pos');
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=confirmed, 2=preparing, 3=ready, 4=out_for_delivery, 5=delivered, 6=cancelled');
            $table->tinyInteger('payment_status')->default(0)->comment('0=pending, 1=paid, 2=failed, 3=refunded');
            $table->string('payment_method')->nullable();
            $table->json('customer_info');
            $table->json('delivery_info');
            $table->decimal('items_total', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->text('special_instructions')->nullable();
            $table->integer('estimated_prep_time')->nullable();
            $table->integer('estimated_delivery_time')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Индексы для производительности
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'channel']);
            $table->index(['user_id', 'status']);
            $table->index('order_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index('channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
