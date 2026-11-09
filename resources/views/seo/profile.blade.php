@extends('layouts.seo')

@section('title', 'Staff Profile & Management')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="panel p-6 sm:p-8 border-l-4 border-l-brand">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Member 4 · Staff Management</p>
                <h1 class="mt-1 text-2xl font-bold text-navy">Staff Profile & Settings</h1>
                <p class="mt-1.5 text-sm text-navy/70">
                    Manage your SEO team credentials, designation, and contact details.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge bg-brand/10 text-brand ring-1 ring-brand/20 px-3 py-1.5">
                    {{ $profile['role'] }}
                </span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 p-4 text-sm font-medium text-rose-800 ring-1 ring-rose-200">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Summary Card -->
        <div class="panel p-6 space-y-6">
            <div class="flex items-center gap-4 pb-5 border-b border-navy/10">
                <div class="grid h-16 w-16 place-items-center rounded-2xl bg-navy text-2xl font-bold text-white shadow-md">
                    {{ strtoupper(substr($profile['name'], 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-navy">{{ $profile['name'] }}</h2>
                    <p class="text-xs font-medium text-brand">{{ $profile['designation'] }}</p>
                    <p class="text-xs text-navy/55 mt-0.5">Joined {{ $profile['joined_at'] }}</p>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-1.5 border-b border-navy/5">
                    <span class="text-navy/60">Module Scope</span>
                    <span class="font-medium text-navy text-right">Order Fulfillment & Verification</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-navy/5">
                    <span class="text-navy/60">Branch</span>
                    <span class="font-mono text-xs bg-canvas px-2 py-0.5 rounded text-navy">feature/staff-verification</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-navy/5">
                    <span class="text-navy/60">Database Tables</span>
                    <span class="font-medium text-navy">7 Owned Tables</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-navy/60">Assigned Orders</span>
                    <span class="font-semibold text-brand">4 Active</span>
                </div>
            </div>

            <div class="rounded-xl bg-canvas p-4 text-xs text-navy/70 leading-relaxed">
                <strong class="text-navy block mb-1">Responsibilities (Backlink.pdf):</strong>
                Managing staff orders, reviewing articles & target URLs, verifying live published backlinks, generating verification reports, and customer order updates.
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="panel p-6 sm:p-8 lg:col-span-2 space-y-8">
            <div>
                <h3 class="text-lg font-bold text-navy">Personal & Staff Information</h3>
                <p class="text-xs text-navy/60 mt-1">Updates your account in <code class="text-navy font-semibold">users</code> and <code class="text-navy font-semibold">staff_profiles</code>.</p>
                
                <form action="{{ route('seo.profile.update') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-navy uppercase tracking-wider">Full Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $profile['name']) }}"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                            >
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-semibold text-navy uppercase tracking-wider">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $profile['email']) }}"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                            >
                        </div>
                        <div>
                            <label for="designation" class="block text-xs font-semibold text-navy uppercase tracking-wider">Designation / Title</label>
                            <input
                                type="text"
                                id="designation"
                                name="designation"
                                value="{{ old('designation', $profile['designation']) }}"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                            >
                        </div>
                        <div>
                            <label for="contact_number" class="block text-xs font-semibold text-navy uppercase tracking-wider">Contact Number</label>
                            <input
                                type="text"
                                id="contact_number"
                                name="contact_number"
                                value="{{ old('contact_number', $profile['contact_number']) }}"
                                class="mt-1.5 block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="btn-primary">
                            Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change -->
            <div class="pt-6 border-t border-navy/10">
                <h3 class="text-lg font-bold text-navy">Security & Password</h3>
                <p class="text-xs text-navy/60 mt-1">Ensure your staff account uses a strong password.</p>

                <form action="{{ route('seo.profile.password') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="current_password" class="block text-xs font-semibold text-navy uppercase tracking-wider">Current Password</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                            >
                        </div>
                        <div>
                            <label for="password" class="block text-xs font-semibold text-navy uppercase tracking-wider">New Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                            >
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-navy uppercase tracking-wider">Confirm New Password</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="btn-navy">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
