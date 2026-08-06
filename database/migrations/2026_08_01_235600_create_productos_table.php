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
        Schema::create('productos', function (Blueprint $table) {

            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->foreignId('categoria_id')
                ->constrained('categorias')
                ->cascadeOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->cascadeOnDelete();

            $table->string('codigo');

            $table->string('nombre');

            $table->text('descripcion')
                ->nullable();

            $table->decimal('precio_compra', 12, 2);

            $table->decimal('precio_venta', 12, 2);

            $table->integer('stock')
                ->default(0);

            $table->integer('stock_minimo')
                ->default(5);

            $table->timestamps();

            $table->unique(['negocio_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
