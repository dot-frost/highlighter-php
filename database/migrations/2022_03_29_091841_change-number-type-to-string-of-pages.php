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
            $table->string('number')->change();
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
            \App\Models\Page::with('book')->get()->each(function (\App\Models\Page $page) {
                $bookPath = $page->book->path;
                $imagePath = $page->imagePath;

                $page->number = (int) $page->number;
                $page->save();

                $imageName = Str::afterLast($imagePath, '/');
                $imageExtension = Str::afterLast($imageName, '.');
                $imageName = Str::beforeLast($imageName, '.');

                $oldImagePath = "{$bookPath}/pages/{$imageName}.{$imageExtension}";
                $newImagePath = "{$bookPath}/pages/{$page->number}.{$imageExtension}";
                Storage::disk('public')->move($oldImagePath, $newImagePath);
            });

            $table->unsignedInteger('number')->change();
        });
    }
};
