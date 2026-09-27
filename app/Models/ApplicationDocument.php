<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    public const TYPES = [
        'resume' => 'Résumé / CV',
        'transcript' => 'Transcript of Records',
        'license' => 'License / PRC ID',
        'certificate' => 'Certificate',
        'other' => 'Other',
    ];

    protected $fillable = ['application_id', 'type', 'original_name', 'path', 'size'];

    protected static function booted(): void
    {
        static::deleting(fn (self $doc) => Storage::disk('local')->delete($doc->path));
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
