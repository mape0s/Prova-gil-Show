<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas', function (Blueprint $table) {
            $table->string('numero', 20)->nullable()->unique()->after('id');
        });

        foreach (\App\Models\Conta::query()->orderBy('id')->get() as $conta) {
            $conta->updateQuietly(['numero' => str_pad((string) $conta->id, 8, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        Schema::table('contas', function (Blueprint $table) {
            $table->dropUnique(['numero']);
            $table->dropColumn('numero');
        });
    }
};
