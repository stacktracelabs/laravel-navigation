<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('is_localized');
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->boolean('is_localized')->default(false);
        });
    }
};
