<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $audience = $this->audience($request);
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();

        $accounts = $this->query($audience)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive', 'suspended'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $summary = $this->query($audience);

        return view('admin.accounts.index', [
            'audience' => $audience,
            'heading' => $audience === 'staff' ? 'Staff Management' : 'Customer Management',
            'summary' => $audience === 'staff'
                ? 'Staff accounts and the roles assigned to them.'
                : 'Customer accounts for the backlink system.',
            'singular' => $audience === 'staff' ? 'staff member' : 'customer',
            'routeBase' => $this->routeBase($audience),
            'accounts' => $accounts,
            'search' => $search,
            'status' => $status,
            'total' => (clone $summary)->count(),
            'activeCount' => (clone $summary)->where('status', 'active')->count(),
            'inactiveCount' => (clone $summary)->where('status', 'inactive')->count(),
            'suspendedCount' => (clone $summary)->where('status', 'suspended')->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $audience = $this->audience($request);

        return view('admin.accounts.form', $this->formData($audience, new User));
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        $audience = $this->audience($request);
        $roleId = $this->roleId($request, $audience);

        if ($roleId === null) {
            return back()->withInput()->with('error', 'The required role is not available.');
        }

        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'status' => $request->validated('status'),
            'role_id' => $roleId,
        ]);

        return redirect()
            ->route($this->routeBase($audience).'.show', $user)
            ->with('success', ucfirst($this->label($audience)).' added.');
    }

    public function show(Request $request, User $user): View
    {
        $audience = $this->audience($request);
        $account = $this->account($user, $audience);

        return view('admin.accounts.show', [
            'audience' => $audience,
            'heading' => $audience === 'staff' ? 'Staff Management' : 'Customer Management',
            'routeBase' => $this->routeBase($audience),
            'account' => $account,
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        $audience = $this->audience($request);

        return view('admin.accounts.form', $this->formData($audience, $this->account($user, $audience)));
    }

    public function update(AccountRequest $request, User $user): RedirectResponse
    {
        $audience = $this->audience($request);
        $account = $this->account($user, $audience);
        $roleId = $this->roleId($request, $audience, $account);

        if ($roleId === null) {
            return back()->withInput()->with('error', 'The required role is not available.');
        }

        $account->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'status' => $request->validated('status'),
            'role_id' => $roleId,
        ]);

        if ($request->filled('password')) {
            $account->password = $request->validated('password');
        }

        $account->save();

        return redirect()
            ->route($this->routeBase($audience).'.show', $account)
            ->with('success', ucfirst($this->label($audience)).' updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $audience = $this->audience($request);
        $account = $this->account($user, $audience);

        if ($request->user()?->is($account)) {
            return back()->with('error', 'You cannot delete the account you are signed in with.');
        }

        $account->delete();

        return redirect()
            ->route($this->routeBase($audience))
            ->with('success', ucfirst($this->label($audience)).' deleted.');
    }

    private function audience(Request $request): string
    {
        return $request->route('audience') === 'staff' ? 'staff' : 'customer';
    }

    private function routeBase(string $audience): string
    {
        return $audience === 'staff' ? 'admin.staff' : 'admin.customers';
    }

    private function label(string $audience): string
    {
        return $audience === 'staff' ? 'staff member' : 'customer';
    }

    private function query(string $audience): Builder
    {
        return User::query()
            ->with('role')
            ->whereHas('role', function ($query) use ($audience): void {
                if ($audience === 'customer') {
                    $query->where('role_name', 'Customer');

                    return;
                }

                $query->whereNotIn('role_name', ['Customer', 'Admin']);
            });
    }

    private function account(User $user, string $audience): User
    {
        $user->load('role');
        $roleName = $user->role?->role_name;
        $allowed = $audience === 'customer'
            ? $roleName === 'Customer'
            : $roleName !== null && ! in_array($roleName, ['Customer', 'Admin'], true);

        abort_unless($allowed, 404);

        return $user;
    }

    private function roleId(AccountRequest $request, string $audience, ?User $account = null): ?int
    {
        if ($audience === 'customer') {
            return Role::query()->where('role_name', 'Customer')->value('id') ?? $account?->role_id;
        }

        return $request->integer('role_id');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(string $audience, User $account): array
    {
        return [
            'audience' => $audience,
            'heading' => $audience === 'staff' ? 'Staff Management' : 'Customer Management',
            'routeBase' => $this->routeBase($audience),
            'account' => $account,
            'roles' => $audience === 'staff'
                ? Role::query()->whereNotIn('role_name', ['Customer', 'Admin'])->orderBy('role_name')->get()
                : collect(),
        ];
    }
}
