@props(['items' => []])

<ul class="wd-bars" style="display:grid;gap:12px;margin:0;padding:0;list-style:none">
    @foreach ($items as $item)
        @php
            $percent = max(0, min(100, (float) ($item['percent'] ?? 0)));
            $shown = rtrim(rtrim(number_format($percent, 1, '.', ''), '0'), '.');
        @endphp
        <li>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:6px">
                <span style="display:flex;align-items:center;gap:8px;min-width:0;color:#152029;font-size:15px;font-weight:700">
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $item['name'] }}</span>
                    @if (! empty($item['code']))
                        <span style="flex:none;border-radius:999px;background:#F2F2F2;padding:2px 8px;color:#4A5560;font-size:12px;font-weight:700">{{ $item['code'] }}</span>
                    @endif
                </span>
                <span style="flex:none;color:#4A5560;font-size:14px;font-weight:700">{{ $shown }}%</span>
            </div>
            <div style="height:10px;overflow:hidden;border-radius:999px;background:#F2F2F2" aria-hidden="true">
                <div style="width:{{ $percent }}%;height:100%;border-radius:999px;background:#152029"></div>
            </div>
        </li>
    @endforeach
</ul>
