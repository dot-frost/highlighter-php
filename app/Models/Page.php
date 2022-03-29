<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'highlights'
    ];

    protected $casts = [
        'highlights' => 'json',
    ];
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($page) {
            Storage::disk('public')->delete($page->imagePath);
        });
    }

    protected function imageName(): Attribute
    {
        return Attribute::get(fn() => $this->number);
    }

    protected function imagePath(): Attribute
    {
        $page = $this;
        return Attribute::get(function ()use($page) {
            foreach (['jpg', 'jpeg', 'png', 'gif'] as $extension) {
                $path = "{$this->book->path}/pages/{$this->imageName}.{$extension}";
                if (Storage::disk('public')->exists($path)) {
                    return $path;
                }
            }
            return null;
        });
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn() => \Storage::url($this->imagePath));
    }

    public function storeImage(UploadedFile $image): void
    {
        $image->storeAs($this->book->path . '/pages', "{$this->imageName}.{$image->extension()}", ['disk' => 'public']);
    }

    public function book(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function next(): Attribute
    {
        return Attribute::get(fn() => $this->book->pages()->where('number', '>', $this->number)->orderBy('number')->first());
    }
    public function previous(): Attribute
    {
        return Attribute::get(fn() => $this->book->pages()->where('number', '<', $this->number)->orderByDesc('number')->first());
    }

    public function phrases():Attribute
    {
        $page = $this;
        return Attribute::get(function ()use($page){
            $phrases = [];
            foreach($page->highlights ?: [] as $highlight){
                if (isset($highlight['data']) && isset($highlight['data']['phrase_id'])){
                    $phrases[] = $highlight['data']['phrase_id'];
                }
            }
            return Phrase::whereIn('id',$phrases)->get();
        });
    }
}

