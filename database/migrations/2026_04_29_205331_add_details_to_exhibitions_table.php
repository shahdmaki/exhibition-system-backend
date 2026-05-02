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
        Schema::table('exhibitions', function (Blueprint $table) {
          $table->decimal('total_area', 10, 2)->nullable()->after('status'); 
        $table->integer('floors_count')->default(1)->after('total_area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
           $table->dropColumn(['total_area', 'floors_count']);
        });
    }
};
