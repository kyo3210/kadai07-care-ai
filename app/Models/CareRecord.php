<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareRecord extends Model
{
    use HasFactory;

    // 保存を許可するカラム（列）のリスト
    protected $fillable = [
        'client_id',
        'content',
        'body_temp',
        'blood_pressure_high',
        'blood_pressure_low',
        'water_intake',
        'recorded_by',
        'recorded_at', // データベースにある名前に合わせる
    ];

    /**
     * この記録に紐づく利用者を取得
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}