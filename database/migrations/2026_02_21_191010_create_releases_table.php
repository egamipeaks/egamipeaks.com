<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->string('slug')->unique();
            $table->date('release_date')->nullable();
            $table->longText('description')->nullable();
            $table->text('credits')->nullable();
            $table->string('visibility', 20)->default('draft');
            $table->foreignId('cover_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->uuid('share_token')->unique();
            $table->timestamps();

            $table->index(['visibility', 'release_date']);
            $table->index('artist_id');
        });
    }
};
