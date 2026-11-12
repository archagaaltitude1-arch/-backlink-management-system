<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * @var list<string>
     */
    private array $statuses = ['pending', 'successful', 'failed'];

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $requestedStatus = $request->string('status')->toString();
        $status = in_array($requestedStatus, $this->statuses, true) ? $requestedStatus : '';

        $payments = Payment::query()
            ->with(['customer:id,name,email', 'order:id,order_number', 'creditPackage:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($query) => $query->where('order_number', 'like', "%{$search}%"))
                        ->orWhereHas('customer', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'search' => $search,
            'status' => $status,
            'statuses' => $this->statuses,
            'total' => Payment::query()->count(),
            'successfulCount' => Payment::query()->where('status', 'successful')->count(),
            'failedCount' => Payment::query()->where('status', 'failed')->count(),
            'collectedTotal' => Payment::query()->where('status', 'successful')->sum('amount'),
        ]);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['customer', 'order.items', 'creditPackage']);

        return view('admin.payments.show', ['payment' => $payment]);
    }
}
