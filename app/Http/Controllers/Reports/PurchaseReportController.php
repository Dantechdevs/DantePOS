<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\SiteSetting;
use App\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReportController extends Controller
{
    /***************************************************************/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /***************************************************************/
    public function index()
    {
        $this->authenticateRole('reports');
        $suppliers = Supplier::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        return view('reports.purchase.index', compact('suppliers', 'users'));
    }
    /***************************************************************/
    public function purchaseReportList(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $time = date('H:i:s');

            $startDate = date('Y-m-d 00:00:00', strtotime($data['startDate']));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' ' . $time));
            $userId = $request->user_id;
            $supplierId = $request->supplier_id;
            $status = $request->input('status'); // Get the status filter

            $query = Purchase::with(['supplier:id,name', 'users:id,name'])
                ->select('id', 'date', 'purchase_no', 'supplier_id', 'grand_total', 'createdBy');

            // Apply date filter
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }

            // Apply user filter if provided
            if ($userId && $userId !== 'all') {
                $query->where('createdBy', $userId);
            }

            if ($supplierId && $supplierId !== 'all') {
                $query->where('supplier_id', $supplierId);
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status); // Apply the status filter
            }

            // Initialize response variables
            $purchases = [];
            $grandTotal = 0;

            // Process data in chunks
            $query->chunk(1000, function ($records) use (&$purchases, &$grandTotal) {
                foreach ($records as $record) {
                    $purchases[] = $record;
                    $grandTotal += $record->grand_total;
                }
            });
            $settings = SiteSetting::pluck('value', 'key');
            // Return the data as a JSON response
            return response()->json([
                'data' => $purchases,
                'grandTotal' => $grandTotal, // Calculate total on the backend
                'startDate' => date('d/m/Y', strtotime($startDate)),
                'endDate' => date('d/m/Y', strtotime($endDate)),
                'currency' => optional($settings)['currency']
            ]);
        }
    }
    /***************************************************************/
    public function supplierPayments()
    {
        $this->authenticateRole('reports');
        $suppliers = Supplier::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        return view('reports.purchase.supplier_payments', compact('suppliers', 'users'));
    }
    /***************************************************************/
    public function supplierPaymentsList(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $time = date('H:i:s');

            $startDate = date('Y-m-d 00:00:00', strtotime($data['startDate']));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' ' . $time));
            $userId = $request->user_id;
            $supplierId = $request->supplier_id;
            $status = $request->input('status'); // Get the status filter

            $query = SupplierPayment::with(['supplier:id,name', 'users:id,name'])
                ->select('id', 'date', 'purchase_no', 'supplier_id', 'amount', 'description', 'createdBy');

            // Apply date filter
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }

            // Apply user filter if provided
            if ($userId && $userId !== 'all') {
                $query->where('createdBy', $userId);
            }

            if ($supplierId && $supplierId !== 'all') {
                $query->where('supplier_id', $supplierId);
            }

            // Initialize response variables
            $purchases = [];
            $grandTotal = 0;

            // Process data in chunks
            $query->chunk(1000, function ($records) use (&$purchases, &$grandTotal) {
                foreach ($records as $record) {
                    $purchases[] = $record;
                    $grandTotal += $record->amount;
                }
            });
            usort($purchases, function ($a, $b) {
                return (new DateTime($b->date)) <=> (new DateTime($a->date));
            });
            // echo "<pre>"; print_r($purchases); exit;
            $settings = SiteSetting::pluck('value', 'key');
            // Return the data as a JSON response
            return response()->json([
                'data' => $purchases,
                'grandTotal' => $grandTotal, // Calculate total on the backend
                'startDate' => date('d/m/Y', strtotime($startDate)),
                'endDate' => date('d/m/Y', strtotime($endDate)),
                'currency' => optional($settings)['currency']
            ]);
        }
    }
    /***************************************************************/
    public function supplierLedger()
    {
        $this->authenticateRole('reports');
        $suppliers = Supplier::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        return view('reports.purchase.supplier_ledger', compact('suppliers', 'users'));
    }
    /***************************************************************/
    public function supplierLedgerList(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();


            $supplierId = $request->supplier_id;
            $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));

            $reportData = $this->generateSupplierLedger($supplierId, $startDate, $endDate);
            if ($supplierId) {
                // Specific supplier report
                $supplierReport = $reportData[0];
                $supplier = $supplierReport['supplier'];
                $openingBalance = $supplierReport['openingBalance'];
                $transactions = $supplierReport['transactions'];
                $totalBalance = $supplierReport['totalBalance'];

                $settings = SiteSetting::pluck('value', 'key');
                return response()->json([
                    'supplierReport' => $supplierReport,
                    'supplier' => $supplier,
                    'openingBalance' => $openingBalance,
                    'transactions' => $transactions,
                    'totalBalance' => $totalBalance,
                    'startDate' => date('d/m/Y', strtotime($startDate)),
                    'endDate' => date('d/m/Y', strtotime($endDate)),
                    'currency' => optional($settings)['currency']
                ]);
            }


            return response()->json(['html' => '<p>No report available.</p>']);
        }
    }
    /***************************************************************/
    public function generateSupplierLedger(?int $supplierId, string $startDate, string $endDate): array
    {
        $customers = $supplierId
            ? Supplier::where('id', $supplierId)->get()
            : Supplier::all();

        $reports = [];

        // Process customers in chunks
        $customers->chunk(100)->each(function ($supplierChunk) use (&$reports, $startDate, $endDate) {
            foreach ($supplierChunk as $supplier) {

                $openingBalance = $this->supplierOPBalance($startDate, $supplier->id);

                // Fetch transactions
                $transactions = $this->fetchTransactions($supplier->id, $startDate, $endDate);

                // echo "<pre>"; print_r($transactions->toArray()); exit;
                // Calculate balances
                $balance = $openingBalance;
                $finalTransactions = $transactions->map(function ($transaction) use (&$balance) {
                    $isDebit = $transaction['type'] === 'debit';
                    $balance += $isDebit ? -$transaction['amount'] : $transaction['amount'];

                    return [
                        'purchase_no' => $transaction['purchase_no'],
                        'date' => date('d-m-Y | h:i A', strtotime($transaction['date'])),
                        'description' => $transaction['description'],
                        'debit' => $isDebit ? $transaction['amount'] : '-',
                        'credit' => !$isDebit ? $transaction['amount'] : '-',
                        'balance' => $balance < 0 ? abs($balance) . ' DB' : $balance . ' CR',
                    ];
                });

                $reports[] = [
                    'supplier' => $supplier,
                    'openingBalance' => $openingBalance,
                    'transactions' => $finalTransactions,
                    'totalBalance' => $balance,
                ];
            }
        });

        return $reports;
    }
    /***************************************************************/

    private function fetchTransactions(int $supplierId, string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $transactions = collect();

        // Chunk sales, debits, and credits separately to avoid memory overload
        Purchase::select('purchase_no', 'date', 'description', 'grand_total as amount', DB::raw("'credit' as type"))
            ->where('supplier_id', $supplierId)
            ->where('status', 'received')
            ->whereBetween('date', [$startDate, $endDate])
            ->chunk(500, function ($salesChunk) use (&$transactions) {
                $transactions = $transactions->merge($salesChunk);
            });

        SupplierPayment::select('purchase_no', 'date', 'description', 'amount', DB::raw("'debit' as type"))
            ->where('supplier_id', $supplierId)
            ->whereBetween('date', [$startDate, $endDate])
            ->chunk(500, function ($debitsChunk) use (&$transactions) {
                $transactions = $transactions->merge($debitsChunk);
            });

        // Sort all transactions by date
        return $transactions->sortBy('date')->values();
    }
    /***************************************************************/

    public function supplierOPBalance($startDate, $supplier_id)
    {
        // Opening balance from CustomerOpeningBalance table
        $supplierPayment = SupplierPayment::where('date', '<', $startDate)
            ->where('supplier_id', $supplier_id)
            ->sum('amount');

        // Additional factors (optional)
        $totalPurchaseAmount = Purchase::where('supplier_id', $supplier_id)
            ->where('status', 'received')
            ->where('date', '<', $startDate)
            ->sum('grand_total');

        // Calculate the adjusted balance
        $customerOP_blnc = $totalPurchaseAmount - $supplierPayment;

        return $customerOP_blnc;
    }
}
