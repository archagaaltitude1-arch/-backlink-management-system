@if ($records->hasPages())
    <div class="flex flex-col gap-3 border-t border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <p class="text-sm text-navy/60">Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }}</p>
        <div class="flex gap-2">
            @if ($records->onFirstPage())
                <span class="rounded-xl px-3 py-2 text-sm text-navy/35">Previous</span>
            @else
                <a href="{{ $records->previousPageUrl() }}" class="rounded-xl bg-canvas px-3 py-2 text-sm font-medium hover:bg-brand/10">Previous</a>
            @endif
            @if ($records->hasMorePages())
                <a href="{{ $records->nextPageUrl() }}" class="rounded-xl bg-canvas px-3 py-2 text-sm font-medium hover:bg-brand/10">Next</a>
            @else
                <span class="rounded-xl px-3 py-2 text-sm text-navy/35">Next</span>
            @endif
        </div>
    </div>
@endif
