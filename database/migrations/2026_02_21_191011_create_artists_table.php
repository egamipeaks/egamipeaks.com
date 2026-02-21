<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('bio')->nullable();
            $table->json('links')->nullable();
            $table->foreignId('hero_image_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->timestamps();
        });
    }
};
