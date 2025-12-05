<?php

namespace App\Repositories;

use App\Models\Appointment;

class AppointmentRepository extends BaseRepository {

    public function model(): string {
        return Appointment::class;
    }

    /** Valida si existe cita en el mismo horario para el mismo doctor */
    public function existsOverlap(string $doctor, string $dateTime): bool {
        return $this->model
            ->where('doctor', $doctor)
            ->where('date_time', $dateTime)
            ->where('status', 'scheduled')
            ->exists();
    }

    public function cancel(int $id): bool {
        $appointment = $this->find($id);
        if(!$appointment) return false;
        return $appointment->update(['status' => 'canceled']);
    }
}
