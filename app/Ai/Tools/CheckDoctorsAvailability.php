<?php

namespace App\Ai\Tools;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Carbon\Constants\Format;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CheckDoctorsAvailability implements Tool
{
    public function name(){
        return "Check_doctor_availabilty";
    }
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Check which appointment time slots a doctor has free. Pass a single date to '
            . 'check that day, or pass days=7 (or more) to scan a whole week and offer the '
            . 'patient alternatives when their preferred day is full or off. Each doctor sets '
            . 'their own weekly schedule, so always check here before promising a time. Call '
            . 'list_doctors first to get the doctor id.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $doctor = Doctor::find($request['doctor_id']);

        if(!$doctor){
            return "There is no doctor with this id. use list_doctor to find a valide id";
        }

        $date = Carbon::parse($request['date'])->startOfDay();

        $slots = $doctor->slotsFor($date);

        if($slots->empty()){
            return "{$doctor->name} does not work on {$date->format('l,f,j')}. Try another day";
        }

        $booked = Appointment::where('doctor_id', $doctor)
        ->whereDate('scheduled_at',$date)
        ->get()->map(fn(Appointment $appointment)=>$appointment->scheduled_at->format('H:i'))->all();

        $free = array_values(array_diff($slots,$booked));

        if(empty($free)){
            return "{$doctor->name} is fully booked on {$date->toFormattedDateString()}.";
        }

        return "{$doctor->name} is available on {$date->toFormattedDateString()} at this times :".implode(',',$free).'.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'value' => $schema->string()->required(),
        ];
    }
}
