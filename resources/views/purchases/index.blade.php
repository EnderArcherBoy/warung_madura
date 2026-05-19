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
                <h6 class="font-weight-bolder mb-0">{{ $title }}</h6>
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
                    <li class="nav-item px-3 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body p-0">
                            <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown pe-2 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell cursor-pointer"></i>
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4"
                            aria-labelledby="dropdownMenuButton">
                            <li class="mb-2">
                                <a class="dropdown-item border-radius-md" href="javascript:;">
                                    <div class="d-flex py-1">
                                        <div class="my-auto">
                                            <img src="{{ asset('layout/assets/img/team-2.jpg') }}"
                                                class="avatar avatar-sm  me-3 ">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                <span class="font-weight-bold">New message</span> from Laur
                                            </h6>
                                            <p class="text-xs text-secondary mb-0 ">
                                                <i class="fa fa-clock me-1"></i>
                                                13 minutes ago
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6>Purchase Orders</h6>
                <a href="{{ route('purchases.create') }}" class="btn bg-gradient-primary btn-sm mb-0">Create Purchase Order</a>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                @if(session('success'))
                    <div class="alert alert-success mt-3 mx-4 text-white" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger mt-3 mx-4 text-white" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Note Number</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Distributor</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Items</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Price</th>
                                <th class="text-secondary opacity-7"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchases as $purchase)
                            <tr>
                                <td>
                                    <div class="d-flex px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $purchase->note_number }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $purchase->purchase_date->format('d M Y') }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $purchase->distributor->name ?? 'Unknown' }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-gradient-info">{{ $purchase->purchaseDetails->count() }} line items</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</span>
                                </td>
                                <td class="align-middle" style="width: 300px;">
                                    <div class="d-flex justify-content-end px-3">
                                        <a href="{{ route('purchases.show', $purchase->note_number) }}" class="btn btn-sm btn-outline-info me-2 mb-0" data-toggle="tooltip" data-original-title="View Details">
                                            View
                                        </a>
                                        @if(in_array(auth()->user()->role, ['owner', 'admin']))
                                        <button type="button"
                                            class="btn btn-sm btn-outline-warning me-2 mb-0 btn-edit"
                                            data-note="{{ $purchase->note_number }}"
                                            data-edit-url="{{ route('purchases.edit', $purchase->note_number) }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('purchases.destroy', $purchase->note_number) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger mb-0 btn-delete" data-note="{{ $purchase->note_number }}">
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-secondary text-sm">No purchase orders found. <a href="{{ route('purchases.create') }}">Create one now</a></p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const VERIFY_URL = "{{ route('purchases.verify-password') }}";
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /**
     * Show password confirmation modal.
     * Returns a promise that resolves to true if password is correct, false otherwise.
     */
    function askPassword(actionLabel) {
        return Swal.fire({
            title: 'Password Required',
            html: `
                <p class="text-sm text-muted mb-3">Enter your account password to confirm <strong>${actionLabel}</strong>.</p>
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

    // ========== EDIT BUTTON FLOW ==========
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const noteNumber = this.dataset.note;
            const editUrl = this.dataset.editUrl;

            // Step 1: Password confirmation first
            askPassword('editing purchase #' + noteNumber).then(verified => {
                if (verified) {
                    // Step 2: Confirm action
                    Swal.fire({
                        title: 'Edit Purchase?',
                        html: `<p>Do you want to edit purchase <strong>#${noteNumber}</strong>?</p>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-edit me-1"></i> Yes, Edit',
                        cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
                        confirmButtonColor: '#fbcf33',
                        cancelButtonColor: '#8392ab',
                        customClass: {
                            popup: 'shadow-lg border-radius-xl',
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            // Redirect to edit page
                            window.location.href = editUrl;
                        }
                    });
                }
            });
        });
    });

    // ========== DELETE BUTTON FLOW ==========
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const noteNumber = this.dataset.note;
            const form = this.closest('form');

            // Step 1: Password confirmation first
            askPassword('deleting purchase #' + noteNumber).then(verified => {
                if (verified) {
                    // Step 2: Confirm action
                    Swal.fire({
                        title: 'Delete Purchase?',
                        html: `<p>Do you want to delete purchase <strong>#${noteNumber}</strong>?<br><small class="text-danger">This action cannot be undone. Stock will be restored.</small></p>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-trash me-1"></i> Yes, Delete',
                        cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
                        confirmButtonColor: '#ea0606',
                        cancelButtonColor: '#8392ab',
                        customClass: {
                            popup: 'shadow-lg border-radius-xl',
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            // Step 3: Show success and submit
                            Swal.fire({
                                title: 'Purchase Deleted!',
                                html: `<p>Purchase <strong>#${noteNumber}</strong> has been deleted successfully.</p>`,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#82d616',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'shadow-lg border-radius-xl',
                                }
                            }).then(() => {
                                form.submit();
                            });
                        }
                    });
                }
            });
        });
    });
});
</script>
@endpush
@endsection
