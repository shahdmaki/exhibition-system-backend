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
    Schema::create('exhibitions', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // عنوان المعرض
        $table->text('description')->nullable(); // وصف المعرض
        $table->date('start_date'); // تاريخ البدء
        $table->date('end_date'); // تاريخ الانتهاء
        $table->string('location'); // المكان الفعلي (المحافظة/القاعة)
        $table->enum('status', ['upcoming', 'active', 'ended'])->default('upcoming');
        $table->string('floor_plan_image')->nullable(); // صورة المخطط للخريطة التفاعلية
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibitions');
    }
};
