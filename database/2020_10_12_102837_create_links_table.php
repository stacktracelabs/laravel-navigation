<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->json('label')->nullable();
            $table->json('href')->nullable();
            $table->boolean('is_localized')->default(false);
            $table->boolean('is_external')->default(false);
            $table->nullableMorphs('resource');
            $table->string('route_type')->nullable();
            $table->string('route_name')->nullable();
            $table->json('route_params')->nullable();
            $table->json('query_params')->nullable();
            $table->nullableMorphs('linkable');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
