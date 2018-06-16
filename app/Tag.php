<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public function media()
    {
        return $this->belongsToMany('App\Media')->withTimestamps();
    }
}
