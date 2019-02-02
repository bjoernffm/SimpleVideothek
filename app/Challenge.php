<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    public function bear()
    {
        $bearing = new ChallengeBearing();
        $bearing->amount = 0;
        $bearing->total = 0;
        $bearing->challenge_group = $this->group;
    }
}
