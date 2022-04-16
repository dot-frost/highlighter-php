<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('status')->default('INITIAL');
        });

        \App\Models\Page::all()->each(function ($page) {
            $page->status = \App\Models\Page::STATUS_INITIAL;
            if ($page->phrases->count() > 0) {
                $page->status = \App\Models\Page::STATUS_PENDING;
            }
            $page->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
