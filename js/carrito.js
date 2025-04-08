/**
 * Funciones JavaScript para la gestión del carrito de préstamos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Agregar evento a todos los botones de "Agregar al carrito" con la clase js-agregar-carrito
    const botonesAgregar = document.querySelectorAll('.js-agregar-carrito');
    
    botonesAgregar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const idLibro = this.dataset.idLibro;
            const formData = new FormData();
            formData.append('id_libro', idLibro);
            
            // Mostrar indicador de carga
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agregando...';
            this.disabled = true;
            
            fetch('/biblioteca/agregar_al_carrito.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Restaurar el botón
                this.innerHTML = '<i class="fas fa-cart-plus mr-2"></i> Agregar al carrito';
                this.disabled = false;
                
                // Mostrar notificación
                if (data.success) {
                    // Actualizar el contador del carrito en el header
                    const carritoCounter = document.querySelector('.js-carrito-counter');
                    if (carritoCounter) {
                        carritoCounter.textContent = data.cantidad_carrito;
                        carritoCounter.classList.remove('hidden');
                    }
                    
                    // Mostrar mensaje de éxito
                    mostrarNotificacion('success', data.message);
                } else {
                    // Mostrar mensaje de error
                    mostrarNotificacion('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = '<i class="fas fa-cart-plus mr-2"></i> Agregar al carrito';
                this.disabled = false;
                mostrarNotificacion('error', 'Error al procesar la solicitud');
            });
        });
    });
    
    // Función para mostrar notificaciones
    function mostrarNotificacion(tipo, mensaje) {
        // Crear elemento de notificación
        const notificacion = document.createElement('div');
        notificacion.className = `fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${
            tipo === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        
        notificacion.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${mensaje}</span>
            </div>
        `;
        
        // Agregar al DOM
        document.body.appendChild(notificacion);
        
        // Eliminar después de 3 segundos
        setTimeout(() => {
            notificacion.style.opacity = '0';
            notificacion.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                document.body.removeChild(notificacion);
            }, 500);
        }, 3000);
    }
    
    // Función para eliminar items del carrito directamente
    const botonesEliminar = document.querySelectorAll('.js-eliminar-carrito');
    
    botonesEliminar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!confirm('¿Estás seguro de eliminar este libro del carrito?')) {
                return;
            }
            
            const idCarrito = this.dataset.idCarrito;
            const formData = new FormData();
            formData.append('id_carrito', idCarrito);
            
            fetch('/biblioteca/eliminar_del_carrito.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Eliminar el elemento del DOM
                    const itemCarrito = this.closest('.item-carrito');
                    if (itemCarrito) {
                        itemCarrito.remove();
                    }
                    
                    // Actualizar el contador
                    const carritoCounter = document.querySelector('.js-carrito-counter');
                    if (carritoCounter) {
                        if (data.cantidad_carrito > 0) {
                            carritoCounter.textContent = data.cantidad_carrito;
                        } else {
                            carritoCounter.classList.add('hidden');
                            // Si no hay más items, mostrar mensaje de carrito vacío
                            const contenedorCarrito = document.querySelector('#contenedor-carrito');
                            if (contenedorCarrito) {
                                contenedorCarrito.innerHTML = `
                                    <div class="text-center py-10">
                                        <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500">Tu carrito está vacío</p>
                                        <a href="/biblioteca/vistas/catalogo/index.php" class="mt-4 inline-block text-purple-600 hover:text-purple-800">
                                            Explorar catálogo
                                        </a>
                                    </div>
                                `;
                            }
                        }
                    }
                    
                    mostrarNotificacion('success', data.message);
                } else {
                    mostrarNotificacion('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('error', 'Error al procesar la solicitud');
            });
        });
    });
});