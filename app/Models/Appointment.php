<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model {

    protected $fillable = ['patient_id','doctor','date_time','status'];

    protected $casts = [
        'date_time' => 'datetime'
    ];

    public function patient(): BelongsTo {
        return $this->belongsTo(Patient::class);
    }
}
