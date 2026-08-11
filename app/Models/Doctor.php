<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


#[Fillable(['name','speciality','bio'])]
class Doctor extends Model
{
    public function appointements(){
        return $this->hasMany(Appointment::class);
    }

    public function availabilities(){
        return $this->hasMany(DoctorAvailability::class);
    }
    /** 
    *
    *@return list<string>
    */

    public function slotFor(Carbon $date){
        $availability = $this->availabilities()->firstWhere('day_of_week',$date->dayOfWeek);

        if(!$availability){
            return[];
        }

        $slot = [];
        $start = Carbon::parse($availability->start_time);
        $end = Carbon::parse($availability->end_time);

        for($time = $start->copy();$time < $end; $time->addHour()){
            $slot[] = $time->format('H:i');
        }

        return $slot;
    }
}
