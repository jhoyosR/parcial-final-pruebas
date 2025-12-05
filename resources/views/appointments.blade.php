@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Citas Médicas</h2>
</div>

<div class="card shadow-sm mb-5">
    <div class="card-header">
        <h5>Agendar Nueva Cita</h5>
    </div>
    <div class="card-body">
        <form id="appointmentForm">
            <div class="mb-3">
                <label for="patientId" class="form-label">Paciente</label>
                <select id="patientId" class="form-select" required>
                    <option value="">Cargando pacientes...</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="doctor" class="form-label">Doctor</label>
                <input type="text" id="doctor" class="form-control" required placeholder="Ej: Dr. García">
            </div>

            <div class="mb-3">
                <label for="dateTime" class="form-label">Fecha y Hora</label>
                <input type="datetime-local" id="dateTime" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Agendar Cita</button>
        </form>
        <div id="appointmentMessage" class="mt-3 alert d-none" role="alert"></div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Citas Agendadas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID Cita</th>
                        <th>Paciente</th>
                        <th>Doctor</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="appointmentsTable"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Función de inicio 
    function init() {
        loadPatients();      
        loadAppointments();  
    }

    // Cargar selector de pacientes
    function loadPatients() {
        fetch(`${API_URL}/patients`)
            .then(res => res.json())
            .then(response => {
                const patients = response.data || response; 
                const select = document.getElementById('patientId');
                
                select.innerHTML = '<option value="">Seleccione un paciente...</option>';
                
                patients.forEach(p => {
                    select.innerHTML += `<option value="${p.id}">${p.name}</option>`;
                });
            })
            .catch(err => {
                console.error(err);
                document.getElementById('patientId').innerHTML = '<option value="">Error al cargar pacientes</option>';
            });
    }

    // Guardar cita
    document.getElementById('appointmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveAppointment();
    });

    function saveAppointment() {
        const patient_id = document.getElementById('patientId').value;
        const doctor = document.getElementById('doctor').value;
        const date_time = document.getElementById('dateTime').value;
        const messageDiv = document.getElementById('appointmentMessage');

        if(!patient_id) {
            alert("Por favor seleccione un paciente");
            return;
        }

        messageDiv.classList.add('d-none');

        fetch(`${API_URL}/appointments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ patient_id, doctor, date_time })
        })
        .then(async res => {
            const data = await res.json();
            
            if (res.ok) {
                messageDiv.textContent = 'Cita creada exitosamente.';
                messageDiv.className = 'mt-3 alert alert-success';
                document.getElementById('appointmentForm').reset();
                document.getElementById('patientId').value = ""; 
                loadAppointments(); 
            } else {
                messageDiv.textContent = data.message || 'Error al agendar.';
                messageDiv.className = 'mt-3 alert alert-danger';
            }
            messageDiv.classList.remove('d-none');
        });
    }

    // Cargar tabla de citas
    function loadAppointments() {
        fetch(`${API_URL}/appointments`)
            .then(res => res.json())
            .then(response => {
                const data = response.data || response;
                let html = '';
                
                if (data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center">No hay citas.</td></tr>';
                } else {
                    data.forEach(app => {
                        const date = new Date(app.date_time).toLocaleString();

                        // Verificamos si existe el paciente
                        const patientName = app.patient ? app.patient.name : 'Desconocido';
                        const patientPhone = app.patient ? app.patient.phone : '';

                        html += `
                            <tr>
                                <td>${app.id}</td>
                                <td>${patientName}</td>
                                <td>${app.doctor}</td>
                                <td>${date}</td>
                                <td><span class="badge bg-primary">${app.status}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="cancelAppointment(${app.id})">Cancelar</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                document.getElementById('appointmentsTable').innerHTML = html;
            });
    }

    // Cancelar cita
    function cancelAppointment(id) {
        if(!confirm('¿Cancelar cita?')) return;
        fetch(`${API_URL}/appointments/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' }
        }).then(res => {
            if(res.ok) loadAppointments();
            else alert('No se pudo cancelar la cita');
        });
    }

    init();
</script>
@endpush