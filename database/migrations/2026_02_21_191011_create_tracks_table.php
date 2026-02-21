<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug', 255);
            $table->smallInteger('position')->default(1);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->longText('lyrics')->nullable();
            $table->text('credits')->nullable();
            $table->foreignId('audio_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->timestamps();

            $table->unique(['release_id', 'slug']);
            $table->index(['release_id', 'position']);
        });
    }
};
