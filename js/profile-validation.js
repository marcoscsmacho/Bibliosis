document.addEventListener('DOMContentLoaded', function() {
    // Elementos del formulario
    const form = document.getElementById('profileForm');
    const currentPasswordInput = document.getElementById('password_actual');
    const newPasswordInput = document.getElementById('password_nuevo');
    const submitButton = document.getElementById('submitButton');
    
    // Elementos para mostrar mensajes de error/éxito
    const currentPasswordFeedback = document.getElementById('current-password-feedback');
    const newPasswordFeedback = document.getElementById('new-password-feedback');
    
    // Variable para trackear validación de contraseña actual
    let isCurrentPasswordValid = true;
    // Variable para trackear si el campo de contraseña actual está vacío
    let isCurrentPasswordEmpty = true;
    
    // Validación de la contraseña actual (verificación asíncrona)
    currentPasswordInput.addEventListener('input', function() {
        const currentPassword = this.value.trim();
        
        // Si está vacío, no realizar validación
        if (currentPassword === '') {
            currentPasswordFeedback.textContent = '';
            currentPasswordFeedback.className = '';
            isCurrentPasswordValid = true;
            isCurrentPasswordEmpty = true;
            updateSubmitButton();
            return;
        }
        
        isCurrentPasswordEmpty = false;
        
        // Solo verificar si está escribiendo una contraseña (tiene al menos 3 caracteres)
        if (currentPassword.length >= 3) {
            // Indica que estamos verificando
            currentPasswordFeedback.textContent = 'Verificando...';
            currentPasswordFeedback.className = 'text-blue-500 text-sm mt-1';
            
            // Crear un objeto FormData para enviar los datos
            const formData = new FormData();
            formData.append('action', 'verify_password');
            formData.append('current_password', currentPassword);
            
            // Realizar petición AJAX al servidor
            fetch('ajax/verify_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    currentPasswordFeedback.textContent = 'Contraseña correcta';
                    currentPasswordFeedback.className = 'text-green-500 text-sm mt-1';
                    isCurrentPasswordValid = true;
                } else {
                    currentPasswordFeedback.textContent = 'La contraseña actual no es correcta';
                    currentPasswordFeedback.className = 'text-red-500 text-sm mt-1';
                    isCurrentPasswordValid = false;
                }
                updateSubmitButton();
            })
            .catch(error => {
                console.error('Error:', error);
                currentPasswordFeedback.textContent = 'Error al verificar la contraseña';
                currentPasswordFeedback.className = 'text-red-500 text-sm mt-1';
                isCurrentPasswordValid = false;
                updateSubmitButton();
            });
        }
    });
    
    // Validación de la nueva contraseña (sin AJAX, solo verificación de longitud)
    newPasswordInput.addEventListener('input', function() {
        const newPassword = this.value.trim();
        
        // Si está vacío, no realizar validación
        if (newPassword === '') {
            newPasswordFeedback.textContent = '';
            newPasswordFeedback.className = '';
            updateSubmitButton();
            return;
        }
        
        // Validar longitud mínima
        if (newPassword.length < 8) {
            newPasswordFeedback.textContent = 'La contraseña debe tener al menos 8 caracteres';
            newPasswordFeedback.className = 'text-red-500 text-sm mt-1';
            newPasswordInput.classList.add('border-red-500');
            newPasswordInput.classList.remove('border-green-500');
        } else {
            newPasswordFeedback.textContent = 'Contraseña válida';
            newPasswordFeedback.className = 'text-green-500 text-sm mt-1';
            newPasswordInput.classList.remove('border-red-500');
            newPasswordInput.classList.add('border-green-500');
        }
        
        updateSubmitButton();
    });
    
    // Función para habilitar/deshabilitar el botón de envío
    function updateSubmitButton() {
        const newPassword = newPasswordInput.value.trim();
        
        // Si hay contraseña actual y no es válida, deshabilitar
        if (!isCurrentPasswordEmpty && !isCurrentPasswordValid) {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        // Si hay nueva contraseña y es menor a 8 caracteres, deshabilitar
        if (newPassword !== '' && newPassword.length < 8) {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        // En otro caso, habilitar el botón
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    // Validar el formulario antes de enviar
    form.addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value.trim();
        const currentPassword = currentPasswordInput.value.trim();
        
        // Si la contraseña actual no es válida, prevenir envío
        if (currentPassword !== '' && !isCurrentPasswordValid) {
            e.preventDefault();
            return;
        }
        
        // Si hay nueva contraseña y es menor a 8 caracteres, prevenir envío
        if (newPassword !== '' && newPassword.length < 8) {
            e.preventDefault();
            return;
        }
    });
});