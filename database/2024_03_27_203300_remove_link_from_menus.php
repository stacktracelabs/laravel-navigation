<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \StackTrace\Navigation\Menu::query()->eachById(function (\StackTrace\Navigation\Menu $menu) {
            if ($menu->link_id) {
                $link = \StackTrace\Navigation\Link::query()->firstWhere('id', $menu->link_id);

                if ($link instanceof \StackTrace\Navigation\Link) {
                    $link->linkable()->associate($menu);
                    $link->save();
                }
            }
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('link_id');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('link_id')->nullable()->constrained('links')->nullOnDelete();
        });
    }
};
