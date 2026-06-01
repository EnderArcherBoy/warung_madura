@extends('layout.master')
@section('menu')
    @include('layout.menu')
@endsection
@section('content')
 <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
        navbar-scroll="true">
        <div class="container-fluid py-1 px-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">{{ $title }}</li>
                </ol>
                <h6 class="font-weight-bolder mb-0">Edit Purchase: {{ $purchase->note_number }}</h6>
            </nav>
            <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                    <div class="input-group">
                        <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                        <input type="text" class="form-control" placeholder="Type here...">
                    </div>
                </div>
                <ul class="navbar-nav  justify-content-end">
                    <li class="nav-item d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body font-weight-bold px-0">
                            <i class="fa fa-user me-sm-1"></i>
                            <span class="d-sm-inline d-none">Sign In</span>
                        </a>
                    </li>
                    <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                            <div class="sidenav-toggler-inner">
                                <i class="sidenav-toggler-line"></i>
                                <i class="sidenav-toggler-line"></i>
                                <i class="sidenav-toggler-line"></i>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <div class="card mb-4">
            <div class="card-header pb-0 border-bottom">
                <div class="d-flex align-items-center">
                    <a href="{{ route('purchases.show', $purchase->note_number) }}" class="btn btn-sm btn-outline-secondary mb-0 me-3">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <h6 class="mb-0">Edit Purchase Order</h6>
                </div>
            </div>

            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger text-white">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger text-white">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('purchases.update', $purchase->note_number) }}" method="POST" id="purchase-form">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Left Side: Purchase Meta -->
                        <div class="col-md-4 border-end">
                            <h6 class="text-sm">Purchase Details</h6>

                            <div class="form-group">
                                <label for="note_number">Note Number</label>
                                <input type="text" name="note_number" id="note_number" class="form-control mb-3" value="{{ $purchase->note_number }}" readonly>
                                <small class="text-muted">Auto-generated. Read-only.</small>
                            </div>

                            <div class="form-group">
                                <label for="purchase_date">Purchase Date</label>
                                <input type="date" name="purchase_date" id="purchase_date" class="form-control mb-3" required value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}">
                            </div>

                            <div class="form-group">
                                <label for="distributor_id">Distributor</label>
                                <select name="distributor_id" id="distributor_id" class="form-control mb-3" required>
                                    <option value="">-- Select Distributor --</option>
                                    @foreach($distributors as $distributor)
                                        <option value="{{ $distributor->id }}" {{ old('distributor_id', $purchase->distributor_id) == $distributor->id ? 'selected' : '' }}>
                                            {{ $distributor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="bg-light p-3 border-radius-lg mt-4 text-center">
                                <p class="text-sm mb-0"><b>Total Amount</b></p>
                                <h3 id="grand-total-display" class="text-primary mb-0">Rp 0</h3>
                                <small class="text-muted text-xxs">(Calculated dynamically)</small>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <a href="{{ route('purchases.show', $purchase->note_number) }}" class="btn btn-outline-secondary w-100 mb-0">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn bg-gradient-warning w-100 mb-0" id="btn-submit" disabled>
                                    <i class="fas fa-save me-1"></i> Update Purchase
                                </button>
                            </div>
                        </div>

                        <!-- Right Side: Dynamic Product Cart -->
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <b>Purchase Items</b>
                                <button type="button" class="btn btn-sm btn-outline-primary mb-0" id="btn-add-row">
                                    + Add Item
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-items-center mb-0" id="cart-table">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 px-2">Product</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 px-2" style="width: 100px;">Purchase Price</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 px-2" style="width: 80px;">Margin</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 px-2" style="width: 80px;">Qty</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 px-2" style="width: 120px;">Subtotal</th>
                                            <th class="text-secondary opacity-7 px-2" style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-body">
                                        <!-- Rows dynamically added via Javascript -->
                                    </tbody>
                                </table>
                            </div>
                            <p id="empty-cart-msg" class="text-center text-sm text-muted mt-3">Your cart is empty. Click "+ Add Item" to begin.</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     PURCHASE FORM DYNAMIC ITEM HANDLER
     ========================================== -->
<script>
    // Load all products from server into a JSON array for dynamic pricing lookups
    const productsList = {!! json_encode($productsList) !!};

    // Pre-load existing purchase details
    const existingDetails = {!! json_encode($purchase->purchaseDetails->map(function($detail) {
        return [
            'serial' => $detail->serial_number_product,
            'purchase_price' => $detail->purchase_price,
            'selling_margin' => $detail->selling_margin,
            'quantity' => $detail->purchase_amount,
            'subtotal' => $detail->subtotal,
        ];
    })->toArray()) !!};

    document.addEventListener('DOMContentLoaded', function() {
        const cartBody = document.getElementById('cart-body');
        const emptyMsg = document.getElementById('empty-cart-msg');
        const btnAddRow = document.getElementById('btn-add-row');
        const btnSubmit = document.getElementById('btn-submit');
        const grandTotalDisplay = document.getElementById('grand-total-display');

        // Function to create a new product row
        function createProductRow(rowIndex = null, existingData = null) {
            const index = rowIndex !== null ? rowIndex : cartBody.children.length;
            const row = document.createElement('tr');
            row.className = 'product-row';
            row.dataset.index = index;

            // Use existing data if provided (for pre-loading), otherwise empty
            const serial = existingData ? existingData.serial : '';
            const purchasePrice = existingData ? existingData.purchase_price : '';
            const sellingMargin = existingData ? existingData.selling_margin : '';
            const quantity = existingData ? existingData.quantity : '1';
            const subtotal = existingData ? existingData.subtotal : 0;

            row.innerHTML = `
                <td class="px-2">
                    <select name="product_serial[]" class="form-select form-select-sm product-select" required>
                        <option value="">-- Select Product --</option>
                        ${productsList.map(p => `<option value="${p.serial}" ${p.serial === serial ? 'selected' : ''}>${p.name} (Stock: ${p.stock})</option>`).join('')}
                    </select>
                </td>
                <td class="text-center px-2">
                    <input type="number" name="purchase_price[]" class="form-control form-control-sm purchase-price text-center" min="1" required placeholder="0" value="${purchasePrice}">
                </td>
                <td class="text-center px-2">
                    <input type="number" name="selling_margin[]" class="form-control form-control-sm selling-margin text-center" min="0" value="${sellingMargin}" required>
                </td>
                <td class="text-center px-2">
                    <input type="number" name="purchase_amount[]" class="form-control form-control-sm quantity text-center" min="1" value="${quantity}" required>
                </td>
                <td class="text-center px-2">
                    <input type="text" class="form-control form-control-sm subtotal text-center" disabled value="Rp ${subtotal > 0 ? subtotal.toLocaleString('id-ID', {minimumFractionDigits: 0}) : '0'}" readonly>
                </td>
                <td class="text-center px-2">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row mb-0">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            // Attach event listeners to the row
            const priceInput = row.querySelector('.purchase-price');
            const marginInput = row.querySelector('.selling-margin');
            const quantityInput = row.querySelector('.quantity');
            const subtotalDisplay = row.querySelector('.subtotal');
            const removeBtn = row.querySelector('.btn-remove-row');

            // Update subtotal when any field changes
            const updateSubtotal = () => {
                const price = parseFloat(priceInput.value) || 0;
                const quantity = parseFloat(quantityInput.value) || 0;
                const subtotal = price * quantity;
                subtotalDisplay.value = 'Rp ' + subtotal.toLocaleString('id-ID', {minimumFractionDigits: 0});
                updateGrandTotal();
            };

            priceInput.addEventListener('input', updateSubtotal);
            quantityInput.addEventListener('input', updateSubtotal);
            marginInput.addEventListener('input', updateGrandTotal);
            removeBtn.addEventListener('click', () => {
                row.remove();
                updateUI();
            });

            return row;
        }

        // Update grand total from all rows
        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.product-row').forEach(row => {
                const price = parseFloat(row.querySelector('.purchase-price').value) || 0;
                const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
                grandTotal += price * quantity;
            });

            grandTotalDisplay.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID', {minimumFractionDigits: 0});
        }

        // Update UI visibility
        function updateUI() {
            const hasItems = cartBody.children.length > 0;
            emptyMsg.style.display = hasItems ? 'none' : 'block';
            btnSubmit.disabled = !hasItems;
            updateGrandTotal();
        }

        // Add row button
        btnAddRow.addEventListener('click', () => {
            const newRow = createProductRow();
            cartBody.appendChild(newRow);
            updateUI();
        });

        // Pre-load existing purchase details
        if (existingDetails.length > 0) {
            existingDetails.forEach((detail, idx) => {
                const newRow = createProductRow(idx, detail);
                cartBody.appendChild(newRow);
            });
        } else {
            // Initialize with one empty row if no existing details
            const newRow = createProductRow();
            cartBody.appendChild(newRow);
        }
        updateUI();
    });
</script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const purchaseForm = document.getElementById('purchase-form');
    const VERIFY_URL = "{{ route('purchases.verify-password') }}";
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const NOTE_NUMBER = "{{ $purchase->note_number }}";

    /**
     * Show password confirmation modal.
     * Returns a promise that resolves to true if password is correct, false otherwise.
     */
    function askPassword(actionLabel) {
        return Swal.fire({
            title: 'Password Required',
            html: `
                <p class="text-sm text-muted mb-3">Enter your BOSS password to confirm <strong>${actionLabel}</strong>.</p>
                <input type="password" id="swal-password" class="swal2-input" placeholder="Your password" autocomplete="current-password">
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check me-1"></i> Confirm',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
            confirmButtonColor: '#82d616',
            cancelButtonColor: '#8392ab',
            focusConfirm: false,
            allowOutsideClick: false,
            customClass: {
                popup: 'shadow-lg border-radius-xl',
            },
            preConfirm: () => {
                const password = document.getElementById('swal-password').value;
                if (!password) {
                    Swal.showValidationMessage('Please enter your password');
                    return false;
                }
                return fetch(VERIFY_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: password })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.verified) {
                        Swal.showValidationMessage('Incorrect password. Please try again.');
                        return false;
                    }
                    return true;
                })
                .catch(error => {
                    Swal.showValidationMessage('Error verifying password. Please try again.');
                    return false;
                });
            }
        }).then(result => {
            return result.isConfirmed && result.value === true;
        });
    }

    // Intercept form submission
    let bypassConfirm = false;
    purchaseForm.addEventListener('submit', function(e) {
        if (bypassConfirm) return; // Let it through on second pass
        e.preventDefault();

        // Step 1: Password confirmation first
        askPassword('updating purchase #' + NOTE_NUMBER).then(verified => {
            if (verified) {
                // Step 2: Confirm action
                Swal.fire({
                    title: 'Update Purchase?',
                    html: `<p>Do you want to update purchase <strong>#${NOTE_NUMBER}</strong>?</p>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-save me-1"></i> Yes, Update',
                    cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
                    confirmButtonColor: '#fbcf33',
                    cancelButtonColor: '#8392ab',
                    customClass: {
                        popup: 'shadow-lg border-radius-xl',
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        // Step 3: Show success then submit
                        Swal.fire({
                            title: 'Purchase Updated!',
                            html: `<p>Purchase <strong>#${NOTE_NUMBER}</strong> has been updated successfully.</p>`,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#82d616',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'shadow-lg border-radius-xl',
                            }
                        }).then(() => {
                            bypassConfirm = true;
                            purchaseForm.submit();
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection
