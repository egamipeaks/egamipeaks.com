<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type', 32)->index();
            $table->string('path', 2048)->nullable();
            $table->string('route_name')->nullable();
            $table->nullableMorphs('subject');
            $table->string('referer', 2048)->nullable();
            $table->string('visitor_hash', 64)->index();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['event_type', 'created_at']);
        });
    }
};
