<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Thiagoprz\CompositeKey\HasCompositeKey;

class SavedPost extends Model
{
    use HasFactory, HasCompositeKey;
    protected $primaryKey = ['idPost', 'idUser'];
    public $incrementing = false;
    protected $hidden = ['idUser'];

    public function user()
    {
        return $this->belongsTo(User::class, "idUser", "idUser");
    }

}
