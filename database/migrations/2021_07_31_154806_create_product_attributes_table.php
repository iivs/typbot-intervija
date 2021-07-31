<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');

            // In order to have a product attribute, it must at least have a key. Value can be optional.
            $table->string('key');
            $table->string('value')->nullable();

            // Custom timestamps. Also make "created_at" nullable to be consistent with other tables.
            $table->timestamp('created_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Create reference.
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_attributes');
    }
}
