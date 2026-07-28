<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'nomor_penerima',
        'isi_pesan',
        'tipe_pengiriman',
        'status_pengiriman',
        'response_fonnte',
        'dikirim_oleh',
    ];

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }
}
