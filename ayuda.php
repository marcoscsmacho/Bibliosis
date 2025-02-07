<?php
// ayuda.php
session_start();
$pageTitle = 'Centro de Ayuda - BiblioSis';
require_once 'modules/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Centro de Ayuda</h1>

     

        <!-- Sección de FAQ -->
        <div class="space-y-6">
            <div class="border-b pb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Preguntas Frecuentes</h2>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="font-medium text-purple-600 mb-2">¿Cómo puedo prestar un libro?</h3>
                        <p class="text-gray-600">Para prestar un libro, primero debes iniciar sesión en tu cuenta. Luego, busca el libro que deseas en el catálogo y haz clic en "Prestar". El sistema te mostrará la disponibilidad y el período de préstamo.</p>
                    </div>

                    <div>
                        <h3 class="font-medium text-purple-600 mb-2">¿Cuántos libros puedo tener prestados a la vez?</h3>
                        <p class="text-gray-600">Puedes tener hasta 3 libros prestados simultáneamente.</p>
                    </div>

                    <div>
                        <h3 class="font-medium text-purple-600 mb-2">¿Cuál es el período de préstamo?</h3>
                        <p class="text-gray-600">El período estándar de préstamo es de 7 días. Puedes renovar el préstamo si no hay otros usuarios en lista de espera.</p>
                    </div>
                </div>
            </div>

            <!-- Sección de Contacto -->
            <div>
                <h2 class="text-xl font-semibold text-gray-700 mb-4">¿Necesitas más ayuda?</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                <form class="space-y-4" method="POST" action="procesar_ayuda.php">
                <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php 
                echo $_SESSION['mensaje'];
                unset($_SESSION['mensaje']); // Limpiar el mensaje después de mostrarlo
                ?>
            </div>
        <?php endif; ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Asunto
        </label>
        <input type="text" 
               name="asunto"
               required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Mensaje
        </label>
        <textarea name="mensaje" 
                  rows="4" 
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"></textarea>
    </div>

    <button type="submit" 
            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
        Enviar Mensaje
    </button>
</form>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg text-center">
                    <i class="fas fa-envelope text-purple-600 text-2xl mb-2"></i>
                    <h3 class="font-medium mb-1">Email</h3>
                    <p class="text-gray-600">ayuda@bibliosis.com</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg text-center">
                    <i class="fas fa-phone text-purple-600 text-2xl mb-2"></i>
                    <h3 class="font-medium mb-1">Teléfono</h3>
                    <p class="text-gray-600">+52 (123) 456-7890</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg text-center">
                    <i class="fas fa-clock text-purple-600 text-2xl mb-2"></i>
                    <h3 class="font-medium mb-1">Horario de Atención</h3>
                    <p class="text-gray-600">Lun - Vie: 9:00 - 18:00</p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once 'modules/footer.php'; ?>