<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('estimated_price', 12, 2);
            $table->decimal('actual_price', 12, 2)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('category', ['produce', 'dairy', 'meat', 'pantry', 'cleaning', 'other']);
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_items');
    }
};
