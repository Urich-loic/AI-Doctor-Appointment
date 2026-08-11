<?php

namespace App\Ai\Tools;

use App\Models\Doctor;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListDoctors implements Tool
{
    public function name(){
        return "List_doctor";
    }
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List of doctors optionally filtered by specialty.'
        .'use this to find a doctor and their numeric id before checking'
        .'availability of booking';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $doctors = Doctor::query()
        ->when($request['specialty'] ?? null, fn($query, $specialty)=>$query
        ->where('specialty','like',"%{$specialty}"))
        ->orderBy('name')
        ->get(['id','name','specialty','bio']);

        if($doctors->empty()){
            return "No doctor match that search";
        }

        return $doctors->map(fn(Doctor $d)=>"#{$d->id}: {$d->name} - {$d->specialty}. {$d->bio}")->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'specialty' => $schema->string()->description('Optional specialty filter e.g : "Cardiologist".'),
        ];
    }
}
