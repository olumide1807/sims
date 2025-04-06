document.addEventListener('DOMContentLoaded', function() {
    // Get references to filter elements
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const filterButton = document.querySelector('.filter-bar button');
    
    // Function to apply filters using AJAX
    function applyFilters() {
        // Show loading indicator
        document.querySelector('.inventory-table').classList.add('loading');
        
        // Get filter values
        const searchTerm = searchInput.value.trim();
        const category = categoryFilter.value;
        const status = statusFilter.value;
        
        // Create FormData object
        const formData = new FormData();
        formData.append('action', 'filter_inventory');
        formData.append('search', searchTerm);
        formData.append('category', category);
        formData.append('status', status);
        
        // Send AJAX request
        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Update table content
            updateTableContent(data.items);
            
            // Update pagination
            updatePagination(data.pagination);
            
            // Remove loading indicator
            document.querySelector('.inventory-table').classList.remove('loading');
            
            // Update URL for bookmarking without page reload
            updateURL(searchTerm, category, status, data.pagination.current_page);
        })
        .catch(error => {
            console.error('Error applying filters:', error);
            document.querySelector('.inventory-table').classList.remove('loading');
            alert('An error occurred while filtering inventory. Please try again.');
        });
    }
    
    // Function to update table content
function updateTableContent(items) {
    const tableBody = document.getElementById('inventoryTableBody');
    tableBody.innerHTML = '';
    
    if (items.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = '<td colspan="9" class="text-center py-4">No products found matching your filters.</td>';
        tableBody.appendChild(emptyRow);
        return;
    }
    
    items.forEach(item => {
        // Create main product row
        const row = document.createElement('tr');
        
        // Determine expand button for variants
        let expandButton = '';
        if (item.variants && item.variants.length > 0) {
            expandButton = `<span class="expand-btn" onclick="toggleVariants(${item.product_id})" id="expand-${item.product_id}">
                <i class="fas fa-plus"></i>
            </span>`;
        }
        
        // Determine status badge class
        let statusBadge = '';
        if (item.product_stock <= item.low_stock_alert && item.product_stock > 0) {
            statusBadge = '<span class="status-badge status-low-stock">Low Stock</span>';
        } else if (item.product_stock == 0) {
            statusBadge = '<span class="status-badge status-out-of-stock">Out of Stock</span>';
        } else {
            statusBadge = '<span class="status-badge status-in-stock">In Stock</span>';
        }
        
        row.innerHTML = `
            <td>${expandButton}</td>
            <td>${item.product_name}</td>
            <td>${item.category}</td>
            <td>${item.product_stock}</td>
            <td>${item.price_per_sachet}</td>
            <td>${item.price_per_packet}</td>
            <td>${statusBadge}</td>
            <td>${item.created_at}</td>
            <td>
                <button class="action-btn" onclick="editProduct(${item.product_id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn" onclick="deleteProduct(${item.product_id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
        
        // Add variant rows if any
        if (item.variants && item.variants.length > 0) {
            const variantRow = document.createElement('tr');
            variantRow.id = `variants-${item.product_id}`;
            variantRow.className = 'variant-row';
            variantRow.style.display = 'none';
            
            let variantContent = `<td colspan="9">
                <table class="variant-table">
                    <thead>
                        <tr>
                            <th>Variant</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
                    
            item.variants.forEach(variant => {
                variantContent += `
                    <tr>
                        <td>${variant.variant_name}</td>
                        <td>${variant.variant_stock}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                onclick="openUpdateVariantModal(${item.product_id}, '${variant.variant_name}', ${variant.variant_stock}, '${item.product_name}')">
                                <i class="fas fa-edit me-1"></i>Update
                            </button>
                        </td>
                    </tr>`;
            });
            
            variantContent += `</tbody></table></td>`;
            variantRow.innerHTML = variantContent;
            tableBody.appendChild(variantRow);
        }
    });
}
    // Function to update pagination
    function updatePagination(paginationData) {
        const paginationContainer = document.querySelector('.pagination');
        if (!paginationContainer) return;
        
        let paginationHTML = '';
        const currentPage = paginationData.current_page;
        const totalPages = paginationData.total_pages;
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<a href="javascript:void(0)" data-page="${currentPage - 1}">&laquo; Prev</a>`;
        }
        
        // Calculate range for pagination
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        // First page and ellipsis
        if (startPage > 1) {
            paginationHTML += `<a href="javascript:void(0)" data-page="1">1</a>`;
            if (startPage > 2) {
                paginationHTML += `<span style="background-color: transparent; border: none; color: #495057;">...</span>`;
            }
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            if (i == currentPage) {
                paginationHTML += `<span>${i}</span>`;
            } else {
                paginationHTML += `<a href="javascript:void(0)" data-page="${i}">${i}</a>`;
            }
        }
        
        // Last page and ellipsis
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<span style="background-color: transparent; border: none; color: #495057;">...</span>`;
            }
            paginationHTML += `<a href="javascript:void(0)" data-page="${totalPages}">${totalPages}</a>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<a href="javascript:void(0)" data-page="${currentPage + 1}">Next &raquo;</a>`;
        }
        
        paginationContainer.innerHTML = paginationHTML;
        
        // Add event listeners to new pagination links
        paginationContainer.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', function() {
                const page = this.getAttribute('data-page');
                loadPage(page);
            });
        });
    }
    
    // Function to load a specific page
    function loadPage(page) {
        // Get current filter values
        const searchTerm = searchInput.value.trim();
        const category = categoryFilter.value;
        const status = statusFilter.value;
        
        // Create FormData object
        const formData = new FormData();
        formData.append('action', 'filter_inventory');
        formData.append('search', searchTerm);
        formData.append('category', category);
        formData.append('status', status);
        formData.append('page', page);
        
        // Show loading indicator
        document.querySelector('.inventory-table').classList.add('loading');
        
        // Send AJAX request
        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Update table content
            updateTableContent(data.items);
            
            // Update pagination
            updatePagination(data.pagination);
            
            // Remove loading indicator
            document.querySelector('.inventory-table').classList.remove('loading');
            
            // Update URL for bookmarking without page reload
            updateURL(searchTerm, category, status, page);
        })
        .catch(error => {
            console.error('Error loading page:', error);
            document.querySelector('.inventory-table').classList.remove('loading');
        });
    }
    
    // Function to update URL without reloading the page
    function updateURL(search, category, status, page) {
        const url = new URL(window.location.href);
        
        // Clear existing parameters
        url.searchParams.delete('search');
        url.searchParams.delete('category');
        url.searchParams.delete('status');
        url.searchParams.delete('page');
        
        // Add new parameters
        if (search) url.searchParams.set('search', search);
        if (category) url.searchParams.set('category', category);
        if (status) url.searchParams.set('status', status);
        if (page && page > 1) url.searchParams.set('page', page);
        
        // Update browser history without reloading
        window.history.pushState({}, '', url.toString());
    }
    
    // Add event listener to filter button
    if (filterButton) {
        filterButton.addEventListener('click', applyFilters);
    }
    
    // Add event listener for Enter key in search input
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    }
    
    // Initialize with URL parameters (if any)
    const urlParams = new URLSearchParams(window.location.search);
    if (searchInput && urlParams.has('search')) {
        searchInput.value = urlParams.get('search');
    }
    if (categoryFilter && urlParams.has('category')) {
        categoryFilter.value = urlParams.get('category');
    }
    if (statusFilter && urlParams.has('status')) {
        statusFilter.value = urlParams.get('status');
    }
    
    // Add event listeners to pagination links on initial load
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.getAttribute('href').split('page=')[1].split('&')[0];
            loadPage(page);
        });
    });
    
    // Add loading indicator styles
    const style = document.createElement('style');
    style.textContent = `
        .inventory-table.loading {
            position: relative;
            min-height: 200px;
        }
        .inventory-table.loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 10;
        }
        .inventory-table.loading::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 11;
        }
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    applyFilters();
});