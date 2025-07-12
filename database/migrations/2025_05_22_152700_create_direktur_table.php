<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('direktur', function (Blueprint $table) {
            $table->id('id_direktur');
            $table->string('nama');
            $table->string('nip')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('direktur');
    }
}; 