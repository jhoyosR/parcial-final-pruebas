@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Registro de Pacientes</h2>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Nuevo Paciente</h5>
    </div>
    <div class="card-body">
        <form id="patientForm">
            <div class="mb-3">
                <label for="patientName" class="form-label">Nombre</label>
                <input type="text" id="patientName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="patientEmail" class="form-label">Email</label>
                <input type="email" id="patientEmail" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="patientPhone" class="form-label">Teléfono</label>
                <input type="tel" id="patientPhone" class="form-control" required minlength="7">
            </div>
            <button type="submit" class="btn btn-success">Registrar Paciente</button>
        </form>
        <div id="patientMessage" class="mt-3 alert d-none" role="alert"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    
    document.getElementById('patientForm').addEventListener('submit', function(e) {
        e.preventDefault();
        savePatient();
    });

    /**
     * Registra un nuevo paciente llamando a la ruta POST /patients
     */
    function savePatient() {
        const name = document.getElementById('patientName').value;
        const email = document.getElementById('patientEmail').value;
        const phone = document.getElementById('patientPhone').value;
        const messageDiv = document.getElementById('patientMessage');

        messageDiv.classList.add('d-none'); 

        fetch(`${API_URL}/patients`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ name, email, phone })
        })
        .then(async res => {
            const data = await res.json();
            messageDiv.textContent = data.message || (res.ok ? 'Paciente registrado exitosamente.' : 'Error al registrar.');
            
            if (res.ok) {
                messageDiv.classList.remove('alert-danger');
                messageDiv.classList.add('alert-success');
                document.getElementById('patientForm').reset(); 
            } else {
                // Manejo de errores de validación
                const errors = data.errors ? Object.values(data.errors).flat().join(', ') : '';
                messageDiv.textContent = data.message + (errors ? ': ' + errors : '');
                messageDiv.classList.remove('alert-success');
                messageDiv.classList.add('alert-danger');
            }
            messageDiv.classList.remove('d-none');
        })
        .catch(error => {
            messageDiv.textContent = 'Error de conexión: ' + error.message;
            messageDiv.classList.remove('alert-success');
            messageDiv.classList.add('alert-danger', 'd-block');
        });
    }
</script>
@endpush