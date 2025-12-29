<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    // バイタル項目と詳細項目をすべて含めたリスト
    protected $fillable = [
        'id', 
        'office_id',
        'client_name', 
        'postcode',
        'address', 
        'contact_tel', 
        'insurace_number', // 被保険者番号
        'care_start_date', // 認定開始日
        'care_end_date',   // 認定終了日
        'care_manager',
        'care_manager_tel' // ケアマネ連絡先
    ];
}