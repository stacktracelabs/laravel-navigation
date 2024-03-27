<?php

use StackTrace\Navigation\Menu;
use Fureev\Trees\Migrate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->nullable()->index();
            $table->json('title')->nullable();
            $table->foreignId('link_id')->nullable()->constrained('links')->nullOnDelete();
            $table->json('meta')->nullable();
            Migrate::columns($table, Menu::make()->getTreeConfig());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
