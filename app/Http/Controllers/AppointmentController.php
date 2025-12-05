<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\JsonAPIResponse;
use App\Repositories\AppointmentRepository;
use Illuminate\Http\Request;

class AppointmentController extends Controller {
    use JsonAPIResponse;

    public function __construct(private AppointmentRepository $appointmentrepository){}

    public function store(Request $request) {

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor'     => 'required|string',
            'date_time'  => 'required|date'
        ]);

        // validar solapamiento
        if ($this->appointmentrepository->existsOverlap(
            $validated['doctor'],
            $validated['date_time']
        )) {
            return $this->errorResponse(
                'El horario ya está ocupado para ese doctor', null, 400
            );
        }

        $appointment = $this->appointmentrepository->create($validated);

        return $this->successResponse(
            'Cita creada exitosamente', $appointment
        );
    }

    public function index() {
        return $this->successResponse(
            'Listado de citas',
            $this->appointmentrepository->makeModel()->with('patient')->where('status','scheduled')->get()
        );
    }

    public function cancel(int $id) { 
        if(!$this->appointmentrepository->cancel($id)){
            return $this->errorResponse('Cita no encontrada', null,404);
        }
        return $this->successResponse('Cita cancelada');
    }
}
