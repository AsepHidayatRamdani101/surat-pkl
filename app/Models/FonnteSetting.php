<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FonnteSetting extends Model
{
    use HasFactory;

    protected $table = 'fonnte_settings';

    protected $fillable = [
        'token',
        'updated_by',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public static function activeToken(): ?string
    {
        $token = static::query()->where('id', 1)->value('token');

        return $token ?: env('FONNTE_TOKEN');
    }
}
