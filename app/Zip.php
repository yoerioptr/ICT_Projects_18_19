<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zip extends Model
{
    protected $primaryKey = 'zip_id';

    public function traveller(){
        return $this->hasMany('App\Travellers', 'zip_id', 'zip_id');
    }

}
