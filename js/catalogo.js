function cargarCatalogo() {
    const busquedaInput = document.getElementById('busqueda');
    const generoSelect = document.getElementById('genero');
    const ordenSelect = document.getElementById('orden');
    const resultadosDiv = document.getElementById('resultados');
    let timeoutId;

    function buscarLibros() {
        const busqueda = busquedaInput.value;
        const genero = generoSelect.value;
        const orden = ordenSelect.value;

        resultadosDiv.innerHTML = `
            <div class="flex justify-center items-center py-12">
                <i class="fas fa-spinner fa-spin text-purple-600 text-3xl"></i>
            </div>
        `;

        fetch(`/biblioteca/ajax/buscar_catalogo.php?busqueda=${encodeURIComponent(busqueda)}&genero=${genero}&orden=${orden}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la red');
                }
                return response.json();
            })
            .then(libros => {
                if (!Array.isArray(libros)) {
                    throw new Error('Formato de respuesta inválido');
                }

                if (libros.length === 0) {
                    resultadosDiv.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-book text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-600 text-lg">No se encontraron libros que coincidan con tu búsqueda.</p>
                        </div>
                    `;
                    return;
                }

                const html = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        ${libros.map(libro => `
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="relative h-96">
                                    ${libro.imagen_portada ? 
                                        `<img src="/biblioteca/${libro.imagen_portada}" alt="${libro.titulo}" class="w-full h-full object-cover">` :
                                        `<div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-book text-gray-400 text-4xl"></i>
                                         </div>`
                                    }
                                </div>
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">${libro.titulo}</h3>
                                    <p class="text-gray-600 mb-4">${libro.autor_nombre} ${libro.autor_apellido}</p>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="px-2 py-1 text-sm rounded-full ${
                                            libro.estado === 'Disponible' ? 
                                            'bg-green-100 text-green-800' : 
                                            'bg-red-100 text-red-800'
                                        }">${libro.estado}</span>
                                        <span class="text-sm text-gray-500">${libro.cantidad_disponible} disponibles</span>
                                    </div>
                                    <a href="/biblioteca/vistas/libro/detalle.php?id=${libro.id_libro}" 
                                       class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                resultadosDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                resultadosDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        Error al cargar los resultados. Por favor, intenta de nuevo.
                    </div>
                `;
            });
    }

    busquedaInput.addEventListener('input', () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(buscarLibros, 300);
    });

    generoSelect.addEventListener('change', buscarLibros);
    ordenSelect.addEventListener('change', buscarLibros);

    buscarLibros();
}

document.addEventListener('DOMContentLoaded', cargarCatalogo);