<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use App\Http\ResponseMessages;

class
User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'mobile_number',
        'is_admin',
        'is_active',
        'role_id',
        'two_factor_code',
        'two_factor_expires_at'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function generateTwoFactorCode() {
        $this->timestamps = false;
        $this->two_factor_code = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(10);

        $this->save();
    }

    public function resetTwoFactorCode() {
        $this->timestamps = false;
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    public function sendPasswordResetNotification($token)
    {
        $url = 'http://localhost:8000/api/reset-password/'.$token;
//      $url = 'http://localhost:8000/api/reset-password?token='.$token;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function isAdministrator() {
        return $this->is_admin;
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function sendSmsNotification($verificationCode)
    {
        $basic  = new \Nexmo\Client\Credentials\Basic('38abdc95', 'g9NpuespT3ztnYZv');
        $client = new \Nexmo\Client($basic);

        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS('966566787868', 'Fancy cards', 'Verification code: '. $verificationCode)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {
            return response()->json(['message' => ResponseMessages::SUCCESSFULLY_SENT], 200);
        } else {
            return response()->json(['message' => ResponseMessages::SENT_FAILED, $message->getStatus()]);
        }
    }

}
