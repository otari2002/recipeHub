<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;

class LoginOtp extends Model
{
    use HasFactory, Prunable;
    public $timestamps = false;
    protected $fillable = ['email','otp', 'expiration_date'];
    protected $hidden = ['otp'];

    public function prunable(): Builder
    {
        return static::where('expiration_date', '<=', now()->subDay());
    }
    public function usersWithEmailOtp()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
