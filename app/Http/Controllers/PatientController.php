<?php

namespace App\Http\Controllers;

use App\Repositories\PatientRepository;
use Illuminate\Http\Request;

class PatientController extends Controller {

    public function __construct(private PatientRepository $patientRepository) {

    }

    public function store(Request $request){
        $validated = $request->validate([
            'name'  => 'required',
            'email' => 'required|email|unique:patients,email',
            'phone' => 'required|min:7'
        ]);

        $patient = $this->patientRepository->create($validated);

        return $this->successResponse('Patient registered successfully', $patient);
    }
}
