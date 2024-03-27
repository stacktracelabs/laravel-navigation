<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \StackTrace\Navigation\Menu::query()->whereNull('handle')->eachById(function (\StackTrace\Navigation\Menu $menu) {
            $menu->handle = 'me_'.\Illuminate\Support\Str::random(28);
            $menu->save();
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->string('handle')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('handle')->nullable(true)->change();
        });
    }
};
