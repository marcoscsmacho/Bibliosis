// js/search.js

function initializeSearch(genreEndpoint) {
    const searchInput = document.getElementById('busqueda_libros');
    const orderSelect = document.getElementById('orden_libros');
    const resultsContainer = document.getElementById('results-container');
    let searchTimeout;

    function updateResults() {
        const searchTerm = searchInput.value;
        const orderBy = orderSelect.value;
        
        resultsContainer.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-purple-600 text-3xl"></i></div>';

        fetch(`${genreEndpoint}&search=${encodeURIComponent(searchTerm)}&order=${orderBy}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-book text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-600 text-lg">No se encontraron libros que coincidan con tu búsqueda.</p>
                        </div>
                    `;
                    return;
                }

                resultsContainer.innerHTML = data.map(libro => `
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="relative h-96">
                            ${libro.imagen_portada ? 
                                `<img src="../${libro.imagen_portada}" alt="${libro.titulo}" class="w-full h-full object-cover">` :
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
                            <a href="../vistas/libro/detalle.php?id=${libro.id_libro}" 
                               class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                Ver detalles
                            </a>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => {
                console.error('Error:', error);
                resultsContainer.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        Error al cargar los resultados. Por favor, intenta de nuevo.
                    </div>
                `;
            });
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateResults, 300);
    });

    orderSelect.addEventListener('change', updateResults);
}