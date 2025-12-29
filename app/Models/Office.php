<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    // 一括保存を許可する項目を指定
    protected $fillable = [
        'name',
        'postcode',
        'address',
        'tel',
    ];

    /**
     * リレーション：この事業所に所属するユーザーたち
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * リレーション：この事業所が担当する利用者たち
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}