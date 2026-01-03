<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// ↓ 追加が必要なインポート
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'office_id', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * パスワードリセット通知の送信
     * クラスの波括弧 { } の内側に入れる必要があります
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new class($token) extends ResetPassword {
            public function toMail($notifiable)
            {
                $url = url(route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false));

                return (new MailMessage)
                    ->subject('【大切なお知らせ】パスワード再設定のご案内')
                    ->greeting('お疲れ様です、' . $notifiable->name . 'さん。')
                    ->line('パスワードをお忘れとのことで承りました。以下のボタンから新しいパスワードを設定いただけます。')
                    ->action('新しいパスワードを登録する', $url)
                    ->line('※このURLの有効期限は60分間です。')
                    ->line('もし心当たりがない場合は、このメールを破棄してください。そのままのパスワードでご利用いただけます。')
                    ->salutation('よろしくお願いいたします。');
            }
        });
    }
} 

