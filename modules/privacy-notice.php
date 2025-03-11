<!-- Banner de aviso de privacidad -->
<div id="privacy-banner" class="fixed bottom-0 inset-x-0 pb-2 sm:pb-5 z-50 hidden">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="p-2 rounded-lg bg-purple-600 shadow-lg sm:p-3">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-purple-800">
                        <i class="fas fa-shield-alt text-white"></i>
                    </span>
                    <p class="ml-3 font-medium text-white truncate">
                        <span class="md:inline">
                            Utilizamos cookies para mejorar tu experiencia. Al continuar navegando, aceptas nuestra política de privacidad.
                        </span>
                    </p>
                </div>
                <div class="order-3 mt-2 flex-shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto">
                    <button id="view-privacy-policy" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-purple-600 bg-white hover:bg-purple-50">
                        Ver política
                    </button>
                </div>
                <div class="order-2 flex-shrink-0 sm:order-3 sm:ml-2">
                    <button id="accept-privacy" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-800 hover:bg-purple-900">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal con la política de privacidad completa -->
<div id="privacy-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl mx-4 sm:mx-auto max-h-screen overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Política de Privacidad - BiblioSis
            </h3>
            <button id="close-privacy-modal" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-4 py-5 sm:p-6 overflow-y-auto" style="max-height: 60vh;">
            <h4 class="text-lg font-semibold mb-4">1. Introducción</h4>
            <p class="mb-4">
                Bienvenido a BiblioSis. Esta Política de Privacidad describe cómo recopilamos, utilizamos y compartimos tu información personal cuando utilizas nuestro sistema de biblioteca. Por favor, léela detenidamente para entender nuestras prácticas respecto a tus datos personales.
            </p>
            
            <h4 class="text-lg font-semibold mb-4">2. Información que recopilamos</h4>
            <p class="mb-4">
                Recopilamos la siguiente información personal:
            </p>
            <ul class="list-disc pl-5 mb-4">
                <li>Información de identificación personal (nombre, apellidos, correo electrónico)</li>
                <li>Información de contacto (teléfono, dirección)</li>
                <li>Historial de préstamos y devoluciones</li>
                <li>Información sobre tus interacciones con nuestro sitio web</li>
                <li>Cookies y datos de uso</li>
            </ul>
            
            <h4 class="text-lg font-semibold mb-4">3. Cómo utilizamos tu información</h4>
            <p class="mb-4">
                Utilizamos la información que recopilamos para:
            </p>
            <ul class="list-disc pl-5 mb-4">
                <li>Gestionar tu cuenta y los préstamos de libros</li>
                <li>Comunicarnos contigo sobre el estado de tus préstamos</li>
                <li>Mejorar nuestros servicios y experiencia de usuario</li>
                <li>Enviar notificaciones relacionadas con el servicio</li>
                <li>Cumplir con obligaciones legales</li>
            </ul>
            
            <h4 class="text-lg font-semibold mb-4">4. Compartición de datos</h4>
            <p class="mb-4">
                No compartimos tu información personal con terceros excepto en las siguientes circunstancias:
            </p>
            <ul class="list-disc pl-5 mb-4">
                <li>Con tu consentimiento explícito</li>
                <li>Cuando sea requerido por la ley</li>
                <li>Con proveedores de servicios que nos ayudan a operar el sistema</li>
            </ul>
            
            <h4 class="text-lg font-semibold mb-4">5. Seguridad de datos</h4>
            <p class="mb-4">
                Implementamos medidas de seguridad técnicas y organizativas para proteger tus datos personales contra acceso no autorizado, alteración, divulgación o destrucción.
            </p>
            
            <h4 class="text-lg font-semibold mb-4">6. Tus derechos</h4>
            <p class="mb-4">
                Tienes derecho a:
            </p>
            <ul class="list-disc pl-5 mb-4">
                <li>Acceder a tus datos personales</li>
                <li>Rectificar datos incorrectos</li>
                <li>Solicitar la eliminación de tus datos</li>
                <li>Oponerte al procesamiento de tus datos</li>
                <li>Retirar tu consentimiento en cualquier momento</li>
            </ul>
            
            <h4 class="text-lg font-semibold mb-4">7. Cookies</h4>
            <p class="mb-4">
                Utilizamos cookies para mejorar tu experiencia, recordar tus preferencias y entender cómo interactúas con nuestro sitio. Puedes configurar tu navegador para rechazar todas las cookies o para que te avise cuando se envíe una cookie.
            </p>
            
            <h4 class="text-lg font-semibold mb-4">8. Cambios a esta política</h4>
            <p class="mb-4">
                Podemos actualizar nuestra Política de Privacidad ocasionalmente. Te notificaremos cualquier cambio publicando la nueva Política de Privacidad en esta página y actualizando la fecha de "última actualización".
            </p>
            
            <h4 class="text-lg font-semibold mb-4">9. Contacto</h4>
            <p class="mb-4">
                Si tienes preguntas sobre esta Política de Privacidad, por favor contáctanos en: 
                <a href="mailto:bibliosissoporte@gmail.com" class="text-purple-600 hover:text-purple-800">bibliosissoporte@gmail.com</a>
            </p>
            
            <p class="text-sm text-gray-500 mt-8">
                Última actualización: 10 de marzo de 2025
            </p>
        </div>
        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
            <button type="button" id="accept-and-close" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                Aceptar y cerrar
            </button>
        </div>
    </div>
</div>

<!-- Asegúrate de incluir el script JS -->
<script src="js/privacy-notice.js"></script>