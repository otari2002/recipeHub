<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $primaryKey = 'idNotification';
    protected $hidden = ['data'];
    use HasFactory;
}
