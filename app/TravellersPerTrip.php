<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravellersPerTrip extends Model
{
        //https://blog.maqe.com/solved-eloquent-doesnt-support-composite-primary-keys-62b740120f
    protected $primaryKey = 'travellers_per_trip_id'; //word alleen door eloquent zelf gebruikt
    public function traveller()
    {
        return $this->belongsTo('App\Traveller', 'traveller_id', 'traveller_id');
    }
    public function trip()
    {
        return $this->belongsTo('App\Trip', 'trip_id', 'trip_id');
    }
}
