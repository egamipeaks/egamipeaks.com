<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('disk');
            $table->string('path');
            $table->string('mime');
            $table->unsignedBigInteger('bytes');
            $table->string('sha256', 64)->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['disk', 'path']);
        });
    }
};
