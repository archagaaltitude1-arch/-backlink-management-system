<form method="GET" action="{{ $action }}" class="grid gap-3 rounded-2xl border border-navy/10 bg-white p-4 sm:p-5 lg:grid-cols-[12rem_11rem_11rem_auto] lg:items-end">
    <div>
        <label for="range" class="text-sm font-medium">Date range</label>
        <select id="range" name="range" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
            <option value="today" @selected($period->preset === 'today')>Today</option>
            <option value="week" @selected($period->preset === 'week')>This week</option>
            <option value="month" @selected($period->preset === 'month')>This month</option>
            <option value="custom" @selected($period->preset === 'custom')>Custom</option>
        </select>
    </div>
    <div>
        <label for="from" class="text-sm font-medium">From</label>
        <input id="from" name="from" type="date" value="{{ $period->preset === 'custom' ? $period->from->toDateString() : '' }}" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
    </div>
    <div>
        <label for="to" class="text-sm font-medium">To</label>
        <input id="to" name="to" type="date" value="{{ $period->preset === 'custom' ? $period->to->toDateString() : '' }}" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
    </div>
    <button type="submit" class="btn-navy">Apply</button>
</form>
