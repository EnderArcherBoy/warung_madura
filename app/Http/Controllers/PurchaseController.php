<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * Display a paginated list of all purchases.
     */
    public function index()
    {
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }
        // Eager load purchaseDetails and distributor to prevent N+1 queries
        $purchases = Purchase::with(['purchaseDetails', 'distributor'])
                             ->latest('purchase_date')
                             ->paginate(10);

        return view('purchases.index', [
            'title'     => 'Purchases',
            'purchases' => $purchases,
        ]);
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }
        // Get all distributors and products for dropdowns
        $distributors = Distributor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $productsList = $products->map(function($p) {
            return [
                'serial' => $p->serial_number,
                'name'   => $p->name,
                'price'  => $p->price,
                'stock'  => $p->stock
            ];
        })->toArray();

        return view('purchases.create', [
            'title'        => 'Purchases',
            'distributors' => $distributors,
            'productsList' => $productsList,
        ]);
    }

    /**
     * Store a newly created purchase in the database.
     * Handles multi-item purchase with stock increment.
     */
    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }
        // validasi data
        $request->validate([
            'purchase_date'       => 'required|date',
            'distributor_id'      => 'required|integer|exists:distributors,id',
            'product_serial'      => 'required|array|min:1',
            'product_serial.*'    => 'required|string|exists:products,serial_number',
            'purchase_price'      => 'required|array|min:1',
            'purchase_price.*'    => 'required|integer|min:1',
            'selling_margin'      => 'required|array|min:1',
            'selling_margin.*'    => 'required|integer|min:0',
            'purchase_amount'     => 'required|array|min:1',
            'purchase_amount.*'   => 'required|integer|min:1',
        ], [
            'product_serial.required' => 'You must add at least one product to the purchase.',
            'product_serial.*.exists' => 'Selected product is invalid.',
        ]);

        DB::beginTransaction();

        try {
            // Generate unique note_number for this purchase
            $noteNumber = $this->generateNoteNumber();

            // Create the Purchase Header with temporary total
            $purchase = Purchase::create([
                'note_number'   => $noteNumber,
                'purchase_date' => $request->purchase_date,
                'distributor_id' => $request->distributor_id,
                'total_price'   => 0,
            ]);

            $grandTotal = 0;

            // Loop through each submitted product row
            foreach ($request->product_serial as $index => $serial) {
                $purchasePrice = $request->purchase_price[$index];
                $sellingMargin = $request->selling_margin[$index];
                $quantity = $request->purchase_amount[$index];

                // Fetch the product for validation and stock update
                $product = Product::where('serial_number', $serial)->firstOrFail();

                // Calculate selling price from purchase price + margin
                $sellingPrice = $purchasePrice + $sellingMargin;
                $subtotal = $purchasePrice * $quantity;
                $grandTotal += $subtotal;

                // Create the Purchase Detail line item
                PurchaseDetail::create([
                    'note_number_purchase'   => $noteNumber,
                    'serial_number_product'  => $product->serial_number,
                    'purchase_price'         => $purchasePrice,
                    'selling_margin'         => $sellingMargin,
                    'purchase_amount'        => $quantity,
                    'subtotal'               => $subtotal,
                ]);

                // Increment the product's stock (receiving goods)
                $product->increment('stock', $quantity);

                // Update product price with selling price
                $product->update(['price' => $sellingPrice]);
            }

            // Update the Purchase Header with the final calculated total
            $purchase->update(['total_price' => $grandTotal]);

            DB::commit();

            return redirect()->route('purchases.show', ['purchase' => $purchase->note_number])
                           ->with('success', 'Purchase order #' . $noteNumber . ' recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to record purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific purchase's line items (detailed receipt view).
     */
    public function show(string $id)
    {
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }
        // Find by note_number instead of id
        $purchase = Purchase::where('note_number', $id)
                           ->with(['purchaseDetails.product', 'distributor'])
                           ->firstOrFail();

        return view('purchases.show', [
            'title'    => 'Purchases',
            'purchase' => $purchase,
        ]);
    }

    /**
     * Show the form for editing an existing purchase order.
     */
    public function edit(string $id)
    {
        // Only owner and admin can edit purchases
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Find by note_number instead of id
        $purchase = Purchase::where('note_number', $id)
                           ->with(['purchaseDetails.product', 'distributor'])
                           ->firstOrFail();

        // Get all distributors and products for dropdowns
        $distributors = Distributor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $productsList = $products->map(function($p) {
            return [
                'serial' => $p->serial_number,
                'name'   => $p->name,
                'price'  => $p->price,
                'stock'  => $p->stock
            ];
        })->toArray();

        return view('purchases.edit', [
            'title'        => 'Purchases',
            'purchase'     => $purchase,
            'distributors' => $distributors,
            'productsList' => $productsList,
        ]);
    }

    /**
     * Update an existing purchase order and recalculate stock.
     * Reverts old stock and applies new stock based on updated quantities.
     */
    public function update(Request $request, string $id)
    {
        // Only owner and admin can update purchases
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Find by note_number instead of id
        $purchase = Purchase::where('note_number', $id)
                           ->with('purchaseDetails')
                           ->firstOrFail();

        // Validate request data
        $request->validate([
            'purchase_date'       => 'required|date',
            'distributor_id'      => 'required|integer|exists:distributors,id',
            'product_serial'      => 'required|array|min:1',
            'product_serial.*'    => 'required|string|exists:products,serial_number',
            'purchase_price'      => 'required|array|min:1',
            'purchase_price.*'    => 'required|integer|min:1',
            'selling_margin'      => 'required|array|min:1',
            'selling_margin.*'    => 'required|integer|min:0',
            'purchase_amount'     => 'required|array|min:1',
            'purchase_amount.*'   => 'required|integer|min:1',
        ], [
            'product_serial.required' => 'You must add at least one product to the purchase.',
            'product_serial.*.exists' => 'Selected product is invalid.',
        ]);

        DB::beginTransaction();

        try {
            // Step 1: Revert old stock - subtract all old purchase quantities from products
            foreach ($purchase->purchaseDetails as $oldDetail) {
                if ($oldDetail->product) {
                    $oldDetail->product->decrement('stock', $oldDetail->purchase_amount);
                }
            }

            // Step 2: Delete old purchase details
            $purchase->purchaseDetails()->delete();

            // Step 3: Update purchase header
            $purchase->update([
                'purchase_date' => $request->purchase_date,
                'distributor_id' => $request->distributor_id,
                'total_price' => 0, // Will be recalculated
            ]);

            $grandTotal = 0;

            // Step 4: Create new purchase details and add new stock
            foreach ($request->product_serial as $index => $serial) {
                $purchasePrice = $request->purchase_price[$index];
                $sellingMargin = $request->selling_margin[$index];
                $quantity = $request->purchase_amount[$index];

                // Fetch the product
                $product = Product::where('serial_number', $serial)->firstOrFail();

                // Calculate selling price from purchase price + margin
                $sellingPrice = $purchasePrice + $sellingMargin;
                $subtotal = $purchasePrice * $quantity;
                $grandTotal += $subtotal;

                // Create new Purchase Detail line item
                PurchaseDetail::create([
                    'note_number_purchase'   => $purchase->note_number,
                    'serial_number_product'  => $product->serial_number,
                    'purchase_price'         => $purchasePrice,
                    'selling_margin'         => $sellingMargin,
                    'purchase_amount'        => $quantity,
                    'subtotal'               => $subtotal,
                ]);

                // Increment the product's stock (adding new quantities)
                $product->increment('stock', $quantity);

                // Update product price with selling price
                $product->update(['price' => $sellingPrice]);
            }

            // Step 5: Update purchase total
            $purchase->update(['total_price' => $grandTotal]);

            DB::commit();

            return redirect()->route('purchases.show', ['purchase' => $purchase->note_number])
                           ->with('success', 'Purchase order #' . $purchase->note_number . ' updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update purchase: ' . $e->getMessage());
        }
    }

    /**
     * Remove a purchase order and restore stock to products.
     */
    public function destroy(string $id)
    {
        // Only owner and admin can delete purchases
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $purchase = Purchase::where('note_number', $id)->firstOrFail();

        DB::beginTransaction();

        try {
            // Restore stock for all purchased items
            foreach ($purchase->purchaseDetails as $detail) {
                if ($detail->product) {
                    $detail->product->decrement('stock', $detail->purchase_amount);
                }
            }

            // Delete details first (in case there's no CASCADE DELETE)
            $purchase->purchaseDetails()->delete();
            $purchase->delete();

            DB::commit();
            return redirect()->route('purchases.index')
                           ->with('success', 'Purchase deleted and stock restored successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique note_number for purchases.
     * Format: PUR-YYYYMMDD-XXXX (e.g., PUR-20260401-0001)
     */
    private function generateNoteNumber()
    {
        $date = date('Ymd');
        $prefix = 'PUR-' . $date . '-';

        // Count existing purchase notes for today
        $count = Purchase::where('note_number', 'like', $prefix . '%')
                        ->count();

        return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Verify the Owner's password via AJAX.
     * Used by SweetAlert2 modals for password confirmation before edit/delete.
     * Checks against the first active Owner account, regardless of who is logged in.
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Get the first active owner
        $owner = User::where('role', 'owner')->where('is_active', true)->first();

        if (!$owner) {
            return response()->json([
                'verified' => false,
                'message' => 'No active owner account found.'
            ]);
        }

        // Check password against owner's password
        $verified = Hash::check($request->password, $owner->password);

        return response()->json(['verified' => $verified]);
    }
}
