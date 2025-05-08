<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Product Manager</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else

    @endif
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <div class="container mt-5">
        <h1 class="mb-4">Product Manager</h1>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Add New Product</h5>
            </div>
            <div class="card-body">
                <form id="productForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="product_name" required>
                        </div>
                        <div class="col-md-3">
                            <label for="quantity" class="form-label">Quantity in Stock</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required min="1">
                        </div>
                        <div class="col-md-3">
                            <label for="price" class="form-label">Price per Item ($)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required min="0.01">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Add Product</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Product List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Date Submitted</th>
                                <th>Total Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                <td id="grandTotal" class="fw-bold">$0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editProductName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="editProductName" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editQuantity" class="form-label">Quantity in Stock</label>
                            <input type="number" class="form-control" id="editQuantity" name="quantity" required min="1">
                        </div>
                        <div class="mb-3">
                            <label for="editPrice" class="form-label">Price per Item ($)</label>
                            <input type="number" step="0.01" class="form-control" id="editPrice" name="price" required min="0.01">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEdit">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        $(document).ready(function() {
            loadProducts();

           
            $('#productForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route("products.store") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#productForm')[0].reset();
                            loadProducts();
                        }
                    },
                    error: function(xhr) {
                        alert('Error saving product: ' + xhr.responseJSON.message);
                    }
                });
            });

            function loadProducts() {
                $.get('{{ route("products.index") }}', function(response) {
                    const products = response.products;
                    const tableBody = $('#productTableBody');
                    tableBody.empty();

                    products.forEach(product => {
                        const row = `
                    <tr data-id="${product.id}">
                        <td>${product.product_name}</td>
                        <td>${product.quantity}</td>
                        <td>$${product.price.toFixed(2)}</td>
                        <td>${new Date(product.created_at).toLocaleString()}</td>
                        <td>$${product.total_value.toFixed(2)}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-btn">Edit</button>
                        </td>
                    </tr>
                `;
                        tableBody.append(row);
                    });

                    $('#grandTotal').text('$${response.grand_total.toFixed(2)}');

                    $('.edit-btn').click(function() {
                        const row = $(this).closest('tr');
                        const id = row.data('id');
                        const product = products.find(p => p.id == id);

                        if (product) {
                            $('#editId').val(product.id);
                            $('#editProductName').val(product.product_name);
                            $('#editQuantity').val(product.quantity);
                            $('#editPrice').val(product.price);

                            const modal = new bootstrap.Modal(document.getElementById('editModal'));
                            modal.show();
                        }
                    });
                });
            }

            $('#saveEdit').click(function() {
                const formData = $('#editForm').serialize();

                $.ajax({
                    url: `/products/${$('#editId').val()}`,
                    type: 'POST',
                    data: formData + '&_method=PUT',
                    success: function(response) {
                        if (response.success) {
                            $('#editModal').modal('hide');
                            loadProducts();
                        }
                    },
                    error: function(xhr) {
                        alert('Error updating product: ' + xhr.responseJSON.message);
                    }
                });
            });
        });
    </script>
    @endpush
</body>

</html>