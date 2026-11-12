<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\ToolLookup;
use App\Support\PublicTools;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function __construct(private ToolLookup $lookup) {}

    public function index(): View
    {
        return view('customer.tools.index', [
            'tools' => PublicTools::items(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $tool = PublicTools::find($slug);
        abort_if($tool === null, 404);

        $catalog = $this->lookup->catalog();
        $domain = mb_substr(trim((string) $request->query('domain', '')), 0, 253);
        $compare = mb_substr(trim((string) $request->query('compare', '')), 0, 253);
        $anchors = mb_substr((string) $request->query('anchors', ''), 0, 5000);
        $checked = $domain !== '' || $compare !== '' || trim($anchors) !== '';

        $site = $domain !== '' ? $this->lookup->find($domain) : null;
        $other = $compare !== '' ? $this->lookup->find($compare) : null;
        $anchorReport = $tool['kind'] === 'anchor' && trim($anchors) !== '' ? $this->lookup->anchors($anchors) : null;

        return view('customer.tools.show', [
            'tool' => $tool,
            'tools' => PublicTools::items(),
            'demo' => $catalog['demo'],
            'suggestions' => $this->lookup->suggestions(),
            'domain' => $domain,
            'compare' => $compare,
            'anchors' => $anchors,
            'checked' => $checked,
            'site' => $site,
            'other' => $other,
            'anchorReport' => $anchorReport,
        ]);
    }
}
