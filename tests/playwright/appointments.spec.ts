import { test, expect } from '@playwright/test';

// Variables para datos de prueba
const PATIENT_DATA = {
    name: 'Juan Test Perez',
    email: 'juan.perez.test@example.com',
    phone: '3001234567',
};

const DOCTOR_NAME = 'Dr. Automatizado';

// Genera una marca de tiempo futura para la cita
function getFutureDateTime(minutesOffset = 60) {
    const date = new Date();
    // Añade el offset en minutos (y un margen de 5 minutos extra)
    date.setMinutes(date.getMinutes() + minutesOffset + 5); 
    // Redondea a la hora o media hora más cercana para consistencia
    date.setMinutes(Math.ceil(date.getMinutes() / 30) * 30);
    date.setSeconds(0);
    date.setMilliseconds(0);

    // Formato requerido por <input type="datetime-local">: YYYY-MM-DDTHH:MM
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

test.describe('Flujo completo de Pacientes y Citas', () => {

    let appointmentTime: any;
    let appointmentTimeOverlap: any;
    
    // Configuración inicial 
    test.beforeAll(async () => {
        // Generamos dos horas de cita, una solapada y otra posterior
        appointmentTime = getFutureDateTime(60); 
        appointmentTimeOverlap = appointmentTime; // Mismo horario para la prueba de solapamiento
    });
    
    // Registro de paciente con validación de error
    test('1. Validar errores de registro de paciente (email y campos requeridos)', async ({ page }) => {
        
        await page.goto('/patients'); 
        
        // Llenar campos con datos incorrectos
        await page.locator('#patientName').fill('Prueba Error');
        await page.locator('#patientEmail').fill('no-es-un-email'); 
        await page.locator('#patientPhone').fill('123'); 
        
        // Forzar el envío de la petición a la API
        await page.getByRole('button', { name: 'Registrar Paciente' }).click();
        
        const errorLocator = page.locator('#patientMessage.alert-danger');
        
        // Espera a que el elemento aparezca y no esté vacío.
        await expect(errorLocator).toBeVisible(); 
        await expect(errorLocator).not.toBeEmpty();
        
        const errorMessage = await errorLocator.textContent();
        
        // Aseguramos que contiene los mensajes de las validaciones:
        await expect(errorMessage).toContain('The email field must be a valid email address.'); 
        await expect(errorMessage).toContain('The phone field must be at least 7 characters.'); 

        // Limpiar el formulario para el siguiente paso
        await page.reload();
    });

    // Flujo completo exitoso
    test('2. Flujo completo: Registro de paciente y agendamiento de cita', async ({ page }) => {
        
        // Registrar Paciente
        await page.goto('/patients');

        await page.locator('#patientName').fill(PATIENT_DATA.name);
        await page.locator('#patientEmail').fill(PATIENT_DATA.email);
        await page.locator('#patientPhone').fill(PATIENT_DATA.phone);

        await page.getByRole('button', { name: 'Registrar Paciente' }).click();
        
        // Esperar mensaje de éxito
        await page.waitForSelector('#patientMessage.alert-success:not(.d-none)');
        await expect(page.locator('#patientMessage')).toContainText('Paciente registrado exitosamente');
        
        // Agendar Cita
        await page.goto('/appointments');
        
        // Esperar a que los pacientes carguen en el selector
        await page.waitForFunction(() => document.getElementById('patientId').options.length > 1);

        // Seleccionar el paciente recién creado por su nombre
        await page.locator('#patientId').selectOption({ label: PATIENT_DATA.name });

        await page.locator('#doctor').fill(DOCTOR_NAME);
        await page.locator('#dateTime').fill(appointmentTime);

        await page.getByRole('button', { name: 'Agendar Cita' }).click();
        
        // Esperar mensaje de éxito
        await page.waitForSelector('#appointmentMessage.alert-success:not(.d-none)');
        await expect(page.locator('#appointmentMessage')).toContainText('Cita creada exitosamente');
        
        // Verificar que la cita aparece en la tabla
        await page.waitForTimeout(500); 
        
        const appointmentRow = page.locator('#appointmentsTable tr', { hasText: PATIENT_DATA.name });
        await expect(appointmentRow).toBeVisible();
        await expect(appointmentRow).toContainText(DOCTOR_NAME);
    });
    
    // Validar solapamiento
    test('3. No permitir agendar cita en horario ya ocupado (solapamiento)', async ({ page }) => {
        
        await page.goto('/appointments');

        // Esperar a que los pacientes carguen
        await page.waitForFunction(() => document.getElementById('patientId').options.length > 1);

        // Intentar agendar con el mismo doctor y el mismo horario (solapamiento)
        await page.locator('#patientId').selectOption({ label: PATIENT_DATA.name });
        await page.locator('#doctor').fill(DOCTOR_NAME); 
        await page.locator('#dateTime').fill(appointmentTimeOverlap); 

        await page.getByRole('button', { name: 'Agendar Cita' }).click();

        // Esperar mensaje de error de la API
        await page.waitForSelector('#appointmentMessage.alert-danger:not(.d-none)');
        
        const errorMessage = await page.locator('#appointmentMessage').textContent();
        await expect(errorMessage).toContain('El horario ya está ocupado para ese doctor');
    });
    
    // Cancelar cita
    test('4. Cancelar la cita creada', async ({ page }) => {
        
        await page.goto('/appointments');
        
        // Localizar la fila de la cita a cancelar
        const appointmentRow = page.locator('#appointmentsTable tr', { hasText: PATIENT_DATA.name });
        await expect(appointmentRow).toBeVisible();

        // Captura el alert de confirmación y lo acepta
        page.once('dialog', dialog => {
            expect(dialog.message()).toContain('¿Cancelar cita?');
            dialog.accept();
        });

        // Clic en el botón Cancelar dentro de la fila
        await appointmentRow.getByRole('button', { name: 'Cancelar' }).click();
        
        // La tabla se recarga y la fila debería desaparecer
        await page.waitForTimeout(1000); 

        // Verificar que la cita ya no aparece como agendada
        await expect(
            page.locator('#appointmentsTable tr', { hasText: PATIENT_DATA.name })
        ).toHaveCount(0);
    });
});