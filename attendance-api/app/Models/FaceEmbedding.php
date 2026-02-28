<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    protected $fillable = [
        'user_id',
        'embedding_data',
        'version',
        'is_active',
        'registered_at',
    ];

    protected $hidden = [
        'embedding_data',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'registered_at' => 'datetime',
            'embedding_data' => 'encrypted:json', // Encrypt/Decrypt seamlessly
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
