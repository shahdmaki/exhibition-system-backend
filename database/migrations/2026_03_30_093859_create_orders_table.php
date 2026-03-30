<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        
        // معلومات المشتري (ممكن يكون زائر مو مسجل)
        $table->string('customer_name');
        $table->string('customer_phone');
        $table->text('customer_address')->nullable();
        
        // السعر الإجمالي لكل الطلبية
        $table->decimal('total_price', 10, 2)->default(0);
        
        // حالة الطلب (جديد، تم الشحن، ملغى)
        $table->string('status')->default('pending'); 

        $table->timestamps();
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
