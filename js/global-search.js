document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    let searchTimeout;
    
    // Obtener la ruta base desde el atributo data del body
    const basePath = document.querySelector('body').getAttribute('data-base-path') || '/biblioteca/';

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
            return;
        }

        // Esperar 300ms antes de hacer la búsqueda
        searchTimeout = setTimeout(() => {
            // Usar una ruta absoluta para el archivo AJAX
            fetch(basePath + 'ajax/search_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'query=' + encodeURIComponent(query)
            })
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                
                if (data.error) {
                    searchResults.innerHTML = `<div class="p-2 text-gray-500">Error: ${data.error}</div>`;
                    searchResults.style.display = 'block';
                    return;
                }
                
                if (data.length > 0) {
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                        div.innerHTML = `
                            <div class="flex items-center space-x-3">
                                ${item.imagen_portada ? 
                                    `<img src="${item.imagen_portada}" class="w-10 h-14 object-cover">` :
                                    `<div class="w-10 h-14 bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-book text-gray-400"></i>
                                    </div>`
                                }
                                <div>
                                    <div class="font-medium">${item.titulo}</div>
                                    <div class="text-sm text-gray-600">${item.autor_nombre} ${item.autor_apellido}</div>
                                </div>
                            </div>
                        `;
                        
                        div.addEventListener('click', () => {
                            window.location.href = basePath + `vistas/libro/detalle.php?id=${item.id_libro}`;
                        });
                        
                        searchResults.appendChild(div);
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = '<div class="p-2 text-gray-500">No se encontraron resultados</div>';
                    searchResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                searchResults.innerHTML = '<div class="p-2 text-gray-500">Error al buscar</div>';
                searchResults.style.display = 'block';
            });
        }, 300);
    });

    // Ocultar los resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Manejar la navegación con teclas en los resultados
    searchInput.addEventListener('keydown', function(e) {
        if (searchResults.style.display === 'block') {
            const items = searchResults.querySelectorAll('div.p-2');
            if (items.length === 0) return;
            
            const current = searchResults.querySelector('.bg-gray-100');
            let index = -1;
            
            if (current) {
                index = Array.from(items).indexOf(current);
                current.classList.remove('bg-gray-100');
            }
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                index = (index + 1) % items.length;
                items[index].classList.add('bg-gray-100');
                items[index].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                index = (index - 1 + items.length) % items.length;
                items[index].classList.add('bg-gray-100');
                items[index].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (index >= 0) {
                    items[index].click();
                }
            }
        }
    });
});