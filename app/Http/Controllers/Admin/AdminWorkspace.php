<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminWorkspace
{
    /**
     * @return array<string, mixed>
     */
    public function report(ReportPeriod $period): array
    {
        return [
            'period' => $period,
            'windows' => [
                'today' => $this->orderWindow(now()->startOfDay(), now()->endOfDay()),
                'week' => $this->orderWindow(now()->startOfWeek(), now()->endOfDay()),
                'month' => $this->orderWindow(now()->startOfMonth(), now()->endOfDay()),
            ],
            'business' => $this->orderWindow($period->from, $period->to),
            'paymentStats' => $this->paymentStats($period->from, $period->to),
            'revenueChart' => $this->monthlyRevenue(),
            'orderChart' => $this->orderChart($period->from, $period->to),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function staffPerformance(ReportPeriod $period): Collection
    {
        return User::query()
            ->with('role:id,role_name')
            ->whereHas('role', fn ($query) => $query->whereIn('role_name', ['Staff', 'SEO']))
            ->withCount([
                'assignedOrders as assigned_count' => fn ($query) => $query->whereBetween('created_at', [$period->from, $period->to]),
                'assignedOrders as pending_count' => fn ($query) => $query->where('status', 'assigned')->whereBetween('created_at', [$period->from, $period->to]),
                'assignedOrders as progress_count' => fn ($query) => $query->where('status', 'in_progress')->whereBetween('created_at', [$period->from, $period->to]),
                'assignedOrders as review_count' => fn ($query) => $query->where('status', 'published')->whereBetween('created_at', [$period->from, $period->to]),
                'assignedOrders as completed_count' => fn ($query) => $query->where('status', 'completed')->whereBetween('created_at', [$period->from, $period->to]),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (User $staff): array => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role?->role_name ?? 'Staff',
                'status' => $staff->status,
                'assigned' => (int) $staff->assigned_count,
                'pending' => (int) $staff->pending_count,
                'progress' => (int) $staff->progress_count,
                'review' => (int) $staff->review_count,
                'completed' => (int) $staff->completed_count,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function customerStatistics(ReportPeriod $period): array
    {
        $customers = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Customer'));

        return [
            'period' => $period,
            'total' => (clone $customers)->count(),
            'today' => (clone $customers)->whereDate('created_at', today())->count(),
            'week' => (clone $customers)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfDay()])->count(),
            'month' => (clone $customers)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfDay()])->count(),
            'inRange' => (clone $customers)->whereBetween('created_at', [$period->from, $period->to])->count(),
            'active' => (clone $customers)->where('status', 'active')->count(),
            'inactive' => (clone $customers)->where('status', 'inactive')->count(),
            'suspended' => (clone $customers)->where('status', 'suspended')->count(),
            'withOrders' => (clone $customers)->whereHas('orders')->count(),
            'chart' => $this->registrationChart(),
            'recent' => (clone $customers)->withCount('orders')->latest()->limit(8)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $admin): array
    {
        $this->rememberSync($admin);
        $month = $this->orderWindow(now()->startOfMonth(), now()->endOfDay());
        $customers = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Customer'));

        return [
            'todayOrders' => Order::query()->whereDate('created_at', today())->count(),
            'weekOrders' => Order::query()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfDay()])->count(),
            'monthOrders' => $month['orders'],
            'monthRevenue' => $month['revenue'],
            'completed' => $month['completed'],
            'pending' => $month['pending'],
            'unread' => $admin->unreadNotifications()->where('type', 'admin.insight')->count(),
            'notifications' => $admin->notifications()->where('type', 'admin.insight')->latest()->limit(4)->get(),
            'staff' => $this->staffPerformance(new ReportPeriod(now()->startOfMonth(), now()->endOfDay(), 'month'))->take(4),
            'customerTotal' => (clone $customers)->count(),
            'customerMonth' => (clone $customers)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfDay()])->count(),
            'customerActive' => (clone $customers)->where('status', 'active')->count(),
        ];
    }

    public function rememberSync(User $admin): void
    {
        $key = 'admin-insight-sync-'.$admin->id;

        if (Cache::has($key)) {
            return;
        }

        $this->sync($admin);
        Cache::put($key, true, 60);
    }

    public function sync(User $admin): void
    {
        Order::query()->with('customer:id,name')->latest()->limit(12)->get()->each(function (Order $order) use ($admin): void {
            $this->store($admin, 'order-'.$order->id, 'New customer order', ($order->customer?->name ?? 'A customer').' placed '.$order->order_number.'.', route('admin.orders.show', $order), $order->created_at);
        });

        Order::query()->with('customer:id,name')->where('order_status', 'pending')->latest()->limit(12)->get()->each(function (Order $order) use ($admin): void {
            $this->store($admin, 'pending-'.$order->id, 'Pending order', $order->order_number.' is still pending and needs attention.', route('admin.orders.show', $order), $order->created_at);
        });

        Order::query()->with('customer:id,name')->whereHas('items', fn ($query) => $query->whereDoesntHave('assignment'))->latest()->limit(12)->get()->each(function (Order $order) use ($admin): void {
            $this->store($admin, 'unassigned-'.$order->id, 'Waiting for staff assignment', $order->order_number.' has a placement that is not assigned to SEO staff.', route('admin.orders.show', $order), $order->created_at);
        });

        Order::query()->with('customer:id,name')->where('order_status', 'completed')->latest()->limit(12)->get()->each(function (Order $order) use ($admin): void {
            $this->store($admin, 'completed-'.$order->id, 'Completed order', $order->order_number.' is completed.', route('admin.orders.show', $order), $order->updated_at);
        });

        Payment::query()->with(['customer:id,name', 'order:id,order_number'])->latest()->limit(12)->get()->each(function (Payment $payment) use ($admin): void {
            $orderNumber = $payment->order?->order_number ?? 'an account payment';
            $url = $payment->order ? route('admin.orders.show', $payment->order) : route('admin.payments.show', $payment);

            if ($payment->status === 'successful') {
                $this->store($admin, 'payment-'.$payment->id, 'Payment received', '$'.number_format((float) $payment->amount, 2).' was received for '.$orderNumber.'.', $url, $payment->paid_at ?? $payment->created_at);
            }

            if ($payment->status === 'pending') {
                $this->store($admin, 'payment-pending-'.$payment->id, 'Payment verification pending', 'Payment for '.$orderNumber.' is still pending.', route('admin.payments.show', $payment), $payment->created_at);
            }
        });

        User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Customer'))->latest()->limit(12)->get()->each(function (User $customer) use ($admin): void {
            $this->store($admin, 'customer-'.$customer->id, 'New customer registration', $customer->name.' registered with '.$customer->email.'.', route('admin.customers.show', $customer), $customer->created_at);
        });
    }

    public function storePhoto(User $user, UploadedFile $file): void
    {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            Storage::disk('local')->delete($this->photoDirectory($user->id, $extension));
        }

        $extension = strtolower($file->guessExtension() ?: 'jpg');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        Storage::disk('local')->putFileAs('admin-profiles', $file, $user->id.'.'.$extension);
    }

    public function photoPath(User $user): ?string
    {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            $path = $this->photoDirectory($user->id, $extension);
            if (Storage::disk('local')->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function photoDirectory(int $userId, string $extension): string
    {
        return 'admin-profiles/'.$userId.'.'.$extension;
    }

    /**
     * @return array{orders: int, completed: int, pending: int, processing: int, cancelled: int, revenue: float}
     */
    private function orderWindow(Carbon $from, Carbon $to): array
    {
        $orders = Order::query()->whereBetween('created_at', [$from, $to]);

        return [
            'orders' => (clone $orders)->count(),
            'completed' => (clone $orders)->where('order_status', 'completed')->count(),
            'pending' => (clone $orders)->where('order_status', 'pending')->count(),
            'processing' => (clone $orders)->where('order_status', 'processing')->count(),
            'cancelled' => (clone $orders)->where('order_status', 'cancelled')->count(),
            'revenue' => $this->revenue($from, $to),
        ];
    }

    /**
     * @return array{successful: int, pending: int, failed: int, revenue: float}
     */
    private function paymentStats(Carbon $from, Carbon $to): array
    {
        $payments = Payment::query()->whereBetween('created_at', [$from, $to]);

        return [
            'successful' => (clone $payments)->where('status', 'successful')->count(),
            'pending' => (clone $payments)->where('status', 'pending')->count(),
            'failed' => (clone $payments)->where('status', 'failed')->count(),
            'revenue' => $this->revenue($from, $to),
        ];
    }

    private function revenue(Carbon $from, Carbon $to): float
    {
        return (float) Payment::query()
            ->where('status', 'successful')
            ->where(function ($query) use ($from, $to): void {
                $query->whereBetween('paid_at', [$from, $to])
                    ->orWhere(function ($query) use ($from, $to): void {
                        $query->whereNull('paid_at')->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->sum('amount');
    }

    /**
     * @return list<array{label: string, value: float, display: string}>
     */
    private function monthlyRevenue(): array
    {
        $from = now()->startOfMonth()->subMonths(5);
        $expression = $this->monthExpression('COALESCE(paid_at, created_at)');
        $rows = Payment::query()
            ->where('status', 'successful')
            ->where(function ($query) use ($from): void {
                $query->where('paid_at', '>=', $from)->orWhere(function ($query) use ($from): void {
                    $query->whereNull('paid_at')->where('created_at', '>=', $from);
                });
            })
            ->selectRaw($expression.' as bucket, SUM(amount) as total')
            ->groupBy(DB::raw($expression))
            ->pluck('total', 'bucket');

        return $this->monthSeries($from, $rows, true);
    }

    /**
     * @return list<array{label: string, value: int, display: string}>
     */
    private function orderChart(Carbon $from, Carbon $to): array
    {
        $counts = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return collect([
            'completed' => 'Completed',
            'processing' => 'Processing',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
        ])->map(fn (string $label, string $status): array => [
            'label' => $label,
            'value' => (int) ($counts[$status] ?? 0),
            'display' => (string) ($counts[$status] ?? 0),
        ])->values()->all();
    }

    /**
     * @return list<array{label: string, value: int, display: string}>
     */
    private function registrationChart(): array
    {
        $from = now()->startOfMonth()->subMonths(5);
        $expression = $this->monthExpression('created_at');
        $rows = User::query()
            ->whereHas('role', fn ($query) => $query->where('role_name', 'Customer'))
            ->where('created_at', '>=', $from)
            ->selectRaw($expression.' as bucket, COUNT(*) as total')
            ->groupBy(DB::raw($expression))
            ->pluck('total', 'bucket');

        return $this->monthSeries($from, $rows, false);
    }

    /**
     * @param  Collection<string, mixed>  $rows
     * @return list<array{label: string, value: int|float, display: string}>
     */
    private function monthSeries(Carbon $from, Collection $rows, bool $money): array
    {
        $series = [];

        for ($month = $from->copy(); $month->lessThanOrEqualTo(now()->startOfMonth()); $month->addMonth()) {
            $key = $month->format('Y-m');
            $value = (float) ($rows[$key] ?? 0);
            $series[] = [
                'label' => $month->format('M'),
                'value' => $money ? $value : (int) $value,
                'display' => $money ? '$'.number_format($value, 0) : (string) (int) $value,
            ];
        }

        return $series;
    }

    private function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    private function store(User $admin, string $key, string $title, string $message, ?string $url, mixed $at): void
    {
        DatabaseNotification::query()->firstOrCreate(
            ['id' => $this->notificationId($admin->id, $key)],
            [
                'type' => 'admin.insight',
                'notifiable_type' => $admin->getMorphClass(),
                'notifiable_id' => $admin->id,
                'data' => [
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                ],
                'created_at' => $at ?? now(),
                'updated_at' => $at ?? now(),
            ],
        );
    }

    private function notificationId(int $adminId, string $key): string
    {
        $hash = md5($adminId.'|'.$key);

        return sprintf(
            '%s-%s-4%s-a%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            substr($hash, 17, 3),
            substr($hash, 20, 12),
        );
    }
}
