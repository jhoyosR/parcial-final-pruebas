<?php

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository extends BaseRepository {
    
    public function model(): string {
        return Patient::class;
    }
}
