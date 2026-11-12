<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportPeriod
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public string $preset,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $preset = $request->string('range')->toString();

        if ($preset === 'today') {
            return new self(now()->startOfDay(), now()->endOfDay(), 'today');
        }

        if ($preset === 'week') {
            return new self(now()->startOfWeek(), now()->endOfDay(), 'week');
        }

        if ($preset === 'custom') {
            $from = self::date($request->string('from')->toString()) ?? now()->startOfMonth();
            $to = self::date($request->string('to')->toString()) ?? now();

            if ($from->greaterThan($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return new self($from->copy()->startOfDay(), $to->copy()->endOfDay(), 'custom');
        }

        return new self(now()->startOfMonth(), now()->endOfDay(), 'month');
    }

    public function label(): string
    {
        return $this->from->format('M j, Y').' – '.$this->to->format('M j, Y');
    }

    private static function date(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
