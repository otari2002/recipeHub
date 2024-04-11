<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasApiTokens, Prunable;

    protected $primaryKey = 'idUser';

    protected $fillable = ['fullName', 'username', 'uuid', 'email', 'password', 'provider', 'email_verified'];

    protected $hidden = [
        'pivot',
        'password',
        "fcm_device_token"
    ];

    public function prunable(): Builder
    {
        return static::where('request_delete_at', '<=', now()->subDays(2));
    }

    public function savedRecipes()
    {
        return $this->hasMany(SavedRecipe::class, 'idUser', 'idUser');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'idUser', 'idUser');
    }
}
