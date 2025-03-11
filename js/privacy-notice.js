// Función para gestionar el aviso de privacidad
document.addEventListener('DOMContentLoaded', function() {
    // Comprobar si el usuario ya ha aceptado la política de privacidad
    const privacyAccepted = localStorage.getItem('privacyAccepted');
    
    if (!privacyAccepted) {
        // Si no ha aceptado, mostrar el banner
        document.getElementById('privacy-banner').classList.remove('hidden');
    }
    
    // Manejar el botón de aceptar
    document.getElementById('accept-privacy').addEventListener('click', function() {
        // Guardar en localStorage que el usuario ha aceptado
        localStorage.setItem('privacyAccepted', 'true');
        // Ocultar el banner
        document.getElementById('privacy-banner').classList.add('hidden');
    });
    
    // Manejar el botón para abrir la política completa
    document.getElementById('view-privacy-policy').addEventListener('click', function() {
        document.getElementById('privacy-modal').classList.remove('hidden');
    });
    
    // Manejar el botón para cerrar la política completa
    document.getElementById('accept-and-close').addEventListener('click', function() {
        // Acepta la política de privacidad
        localStorage.setItem('privacyAccepted', 'true');
        // Oculta el banner
        document.getElementById('privacy-banner').classList.add('hidden');
        // Cierra el modal
        document.getElementById('privacy-modal').classList.add('hidden');
    });
});