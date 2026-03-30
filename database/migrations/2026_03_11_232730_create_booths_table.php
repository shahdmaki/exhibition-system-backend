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
    Schema::create('booths', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
        
        // أضفنا رقم الجناح
        $table->string('booth_number'); 
        
        $table->decimal('price', 10, 2);
        
        // غيرنا area إلى size لتطابق الكود، أو يمكنك إبقاؤها area وتعديل الكنترولر
        $table->string('size'); 
        
        $table->json('coordinates')->nullable();
        
        // عدلنا الحالات لتطابق ما كتبناه في الـ Validation بالكنترولر
        $table->enum('status', ['available', 'booked', 'under_maintenance'])->default('available');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booths');
    }
};
