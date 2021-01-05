<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, SoftDeletes;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getUserName($id)
    {
        $user = User::where('id', $id)->get();

        return $user->name;
    }
}
