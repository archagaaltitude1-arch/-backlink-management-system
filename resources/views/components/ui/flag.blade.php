@props([
    'code',
])

<svg {{ $attributes->merge(['viewBox' => '0 0 60 40', 'width' => '36', 'height' => '24']) }} aria-hidden="true" focusable="false">
    @switch($code)
        @case('za')
            <rect width="60" height="40" fill="#007A4D"/>
            <polygon points="0,0 60,0 60,15 22,15 0,0" fill="#DE3831"/>
            <polygon points="0,40 60,40 60,25 22,25 0,40" fill="#002395"/>
            <polygon points="0,0 24,20 0,40" fill="#000"/>
            <polygon points="0,5 16,20 0,35" fill="#FFB612"/>
            <polygon points="0,8 12,20 0,32" fill="#007A4D"/>
            @break
        @case('ng')
            <rect width="20" height="40" fill="#008751"/>
            <rect x="20" width="20" height="40" fill="#fff"/>
            <rect x="40" width="20" height="40" fill="#008751"/>
            @break
        @case('ke')
            <rect width="60" height="40" fill="#fff"/>
            <rect y="6" width="60" height="8" fill="#000"/>
            <rect y="16" width="60" height="8" fill="#BB0000"/>
            <rect y="26" width="60" height="8" fill="#006600"/>
            <ellipse cx="30" cy="20" rx="6" ry="9" fill="#BB0000" stroke="#fff" stroke-width="1"/>
            @break
        @case('eg')
            <rect width="60" height="13.4" fill="#CE1126"/>
            <rect y="13.4" width="60" height="13.2" fill="#fff"/>
            <rect y="26.6" width="60" height="13.4" fill="#000"/>
            <circle cx="30" cy="20" r="4" fill="#C09300"/>
            @break
        @case('in')
            <rect width="60" height="13.4" fill="#FF9933"/>
            <rect y="13.4" width="60" height="13.2" fill="#fff"/>
            <rect y="26.6" width="60" height="13.4" fill="#138808"/>
            <circle cx="30" cy="20" r="4.2" fill="none" stroke="#000080" stroke-width="1.2"/>
            @break
        @case('lk')
            <rect width="60" height="40" fill="#8D153A"/>
            <rect x="8" width="8" height="40" fill="#FFBE29"/>
            <rect width="8" height="40" fill="#00534E"/>
            <rect x="4" y="8" width="4" height="24" fill="#FFBE29"/>
            <circle cx="36" cy="20" r="7" fill="#FFBE29"/>
            @break
        @case('sg')
            <rect width="60" height="20" fill="#EF3340"/>
            <rect y="20" width="60" height="20" fill="#fff"/>
            <circle cx="16" cy="12" r="6" fill="#fff"/>
            <circle cx="18.2" cy="12" r="5" fill="#EF3340"/>
            <circle cx="26" cy="8" r="1" fill="#fff"/>
            <circle cx="28" cy="11" r="1" fill="#fff"/>
            <circle cx="26" cy="14" r="1" fill="#fff"/>
            <circle cx="23" cy="16" r="1" fill="#fff"/>
            <circle cx="20" cy="15" r="1" fill="#fff"/>
            @break
        @case('my')
            <rect width="60" height="40" fill="#fff"/>
            @for ($stripe = 0; $stripe < 14; $stripe++)
                @if ($stripe % 2 === 0)
                    <rect y="{{ $stripe * 40 / 14 }}" width="60" height="{{ 40 / 14 }}" fill="#CC0001"/>
                @endif
            @endfor
            <rect width="28" height="22" fill="#010066"/>
            <circle cx="12" cy="11" r="6" fill="#FFCC00"/>
            <circle cx="14" cy="11" r="5" fill="#010066"/>
            <polygon points="20,11 21.2,14.4 18,12.3 22,12.3 18.8,14.4" fill="#FFCC00"/>
            @break
        @case('id')
            <rect width="60" height="20" fill="#CE1126"/>
            <rect y="20" width="60" height="20" fill="#fff"/>
            @break
        @case('ph')
            <rect width="60" height="20" fill="#0038A8"/>
            <rect y="20" width="60" height="20" fill="#CE1126"/>
            <polygon points="0,0 26,20 0,40" fill="#fff"/>
            <circle cx="9" cy="20" r="4" fill="#FCD116"/>
            @break
        @case('th')
            <rect width="60" height="40" fill="#A51931"/>
            <rect y="6.6" width="60" height="26.8" fill="#fff"/>
            <rect y="13.3" width="60" height="13.4" fill="#2D2A4A"/>
            @break
        @case('vn')
            <rect width="60" height="40" fill="#DA251D"/>
            <polygon points="30,8 33.5,18.5 44.5,18.5 35.6,25 39,35.5 30,29 21,35.5 24.4,25 15.5,18.5 26.5,18.5" fill="#FF0"/>
            @break
        @case('pk')
            <rect width="15" height="40" fill="#fff"/>
            <rect x="15" width="45" height="40" fill="#01411C"/>
            <circle cx="34" cy="20" r="7" fill="#fff"/>
            <circle cx="36.5" cy="18.5" r="6" fill="#01411C"/>
            <polygon points="42,14 43,17 46,17 43.6,18.8 44.5,21.6 42,19.8 39.5,21.6 40.4,18.8 38,17 41,17" fill="#fff"/>
            @break
        @case('bd')
            <rect width="60" height="40" fill="#006A4E"/>
            <circle cx="26" cy="20" r="9" fill="#F42A41"/>
            @break
        @case('ae')
            <rect width="60" height="13.4" fill="#00732F"/>
            <rect y="13.4" width="60" height="13.2" fill="#fff"/>
            <rect y="26.6" width="60" height="13.4" fill="#000"/>
            <rect width="16" height="40" fill="#FF0000"/>
            @break
        @case('jp')
            <rect width="60" height="40" fill="#fff"/>
            <circle cx="30" cy="20" r="8" fill="#BC002D"/>
            @break
        @case('gb')
            <rect width="60" height="40" fill="#012169"/>
            <path d="M0,0 L60,40 M60,0 L0,40" stroke="#fff" stroke-width="8"/>
            <path d="M0,0 L60,40 M60,0 L0,40" stroke="#C8102E" stroke-width="4"/>
            <path d="M30,0 V40 M0,20 H60" stroke="#fff" stroke-width="12"/>
            <path d="M30,0 V40 M0,20 H60" stroke="#C8102E" stroke-width="7"/>
            @break
        @case('de')
            <rect width="60" height="13.4" fill="#000"/>
            <rect y="13.4" width="60" height="13.2" fill="#DD0000"/>
            <rect y="26.6" width="60" height="13.4" fill="#FFCE00"/>
            @break
        @case('fr')
            <rect width="20" height="40" fill="#0055A4"/>
            <rect x="20" width="20" height="40" fill="#fff"/>
            <rect x="40" width="20" height="40" fill="#EF4135"/>
            @break
        @case('es')
            <rect width="60" height="40" fill="#C60B1E"/>
            <rect y="10" width="60" height="20" fill="#FFC400"/>
            @break
        @case('it')
            <rect width="20" height="40" fill="#009246"/>
            <rect x="20" width="20" height="40" fill="#fff"/>
            <rect x="40" width="20" height="40" fill="#CE2B37"/>
            @break
        @case('nl')
            <rect width="60" height="13.4" fill="#AE1C28"/>
            <rect y="13.4" width="60" height="13.2" fill="#fff"/>
            <rect y="26.6" width="60" height="13.4" fill="#21468B"/>
            @break
        @case('pl')
            <rect width="60" height="20" fill="#fff"/>
            <rect y="20" width="60" height="20" fill="#DC143C"/>
            @break
        @case('se')
            <rect width="60" height="40" fill="#006AA7"/>
            <rect x="16" width="8" height="40" fill="#FECC00"/>
            <rect y="16" width="60" height="8" fill="#FECC00"/>
            @break
        @case('ie')
            <rect width="20" height="40" fill="#169B62"/>
            <rect x="20" width="20" height="40" fill="#fff"/>
            <rect x="40" width="20" height="40" fill="#FF883E"/>
            @break
        @case('pt')
            <rect width="24" height="40" fill="#006600"/>
            <rect x="24" width="36" height="40" fill="#FF0000"/>
            <circle cx="24" cy="20" r="7" fill="#FFCC00" stroke="#fff" stroke-width="1"/>
            @break
        @case('us')
            <rect width="60" height="40" fill="#fff"/>
            @for ($stripe = 0; $stripe < 13; $stripe++)
                @if ($stripe % 2 === 0)
                    <rect y="{{ $stripe * 40 / 13 }}" width="60" height="{{ 40 / 13 }}" fill="#B22234"/>
                @endif
            @endfor
            <rect width="26" height="21.5" fill="#3C3B6E"/>
            @break
        @case('ca')
            <rect width="15" height="40" fill="#FF0000"/>
            <rect x="45" width="15" height="40" fill="#FF0000"/>
            <rect x="15" width="30" height="40" fill="#fff"/>
            <polygon points="30,10 32,16 38,16 33,20 35,26 30,22 25,26 27,20 22,16 28,16" fill="#FF0000"/>
            @break
        @case('mx')
            <rect width="20" height="40" fill="#006847"/>
            <rect x="20" width="20" height="40" fill="#fff"/>
            <rect x="40" width="20" height="40" fill="#CE1126"/>
            <circle cx="30" cy="20" r="4" fill="#C09300"/>
            @break
        @case('br')
            <rect width="60" height="40" fill="#009C3B"/>
            <polygon points="30,6 52,20 30,34 8,20" fill="#FFDF00"/>
            <circle cx="30" cy="20" r="7" fill="#002776"/>
            @break
        @case('ar')
            <rect width="60" height="13.4" fill="#74ACDF"/>
            <rect y="13.4" width="60" height="13.2" fill="#fff"/>
            <rect y="26.6" width="60" height="13.4" fill="#74ACDF"/>
            <circle cx="30" cy="20" r="3.5" fill="#F6B40E"/>
            @break
        @case('cl')
            <rect width="60" height="20" fill="#fff"/>
            <rect y="20" width="60" height="20" fill="#D52B1E"/>
            <rect width="20" height="20" fill="#0039A6"/>
            <polygon points="10,5 11.4,9.2 16,9.2 12.3,11.8 13.7,16 10,13.4 6.3,16 7.7,11.8 4,9.2 8.6,9.2" fill="#fff"/>
            @break
        @case('co')
            <rect width="60" height="20" fill="#FCD116"/>
            <rect y="20" width="60" height="10" fill="#003893"/>
            <rect y="30" width="60" height="10" fill="#CE1126"/>
            @break
        @case('au')
            <rect width="60" height="40" fill="#012169"/>
            <path d="M0,0 L26,18 M26,0 L0,18" stroke="#fff" stroke-width="4"/>
            <path d="M0,0 L26,18 M26,0 L0,18" stroke="#C8102E" stroke-width="2"/>
            <path d="M13,0 V18 M0,9 H26" stroke="#fff" stroke-width="6"/>
            <path d="M13,0 V18 M0,9 H26" stroke="#C8102E" stroke-width="3"/>
            <polygon points="46,28 47,31 50,31 47.6,32.8 48.5,35.6 46,33.8 43.5,35.6 44.4,32.8 42,31 45,31" fill="#fff"/>
            <circle cx="40" cy="14" r="1.4" fill="#fff"/>
            <circle cx="48" cy="12" r="1.2" fill="#fff"/>
            <circle cx="52" cy="18" r="1.2" fill="#fff"/>
            <circle cx="44" cy="20" r="1.2" fill="#fff"/>
            @break
        @case('nz')
            <rect width="60" height="40" fill="#012169"/>
            <path d="M0,0 L26,18 M26,0 L0,18" stroke="#fff" stroke-width="4"/>
            <path d="M0,0 L26,18 M26,0 L0,18" stroke="#C8102E" stroke-width="2"/>
            <path d="M13,0 V18 M0,9 H26" stroke="#fff" stroke-width="6"/>
            <path d="M13,0 V18 M0,9 H26" stroke="#C8102E" stroke-width="3"/>
            <circle cx="44" cy="12" r="1.6" fill="#fff"/>
            <circle cx="50" cy="22" r="1.6" fill="#fff"/>
            <circle cx="42" cy="26" r="1.4" fill="#fff"/>
            <circle cx="48" cy="32" r="1.5" fill="#fff"/>
            @break
        @case('ch')
            <rect width="60" height="40" fill="#D52B1E"/>
            <rect x="25" y="8" width="10" height="24" fill="#fff"/>
            <rect x="18" y="15" width="24" height="10" fill="#fff"/>
            @break
        @case('ro')
            <rect width="20" height="40" fill="#002B7F"/>
            <rect x="20" width="20" height="40" fill="#FCD116"/>
            <rect x="40" width="20" height="40" fill="#CE1126"/>
            @break
        @case('be')
            <rect width="20" height="40" fill="#000"/>
            <rect x="20" width="20" height="40" fill="#FAE042"/>
            <rect x="40" width="20" height="40" fill="#ED2939"/>
            @break
        @case('hu')
            <rect width="60" height="13.4" fill="#CE2939"/>
            <rect y="13.4" width="60" height="13.2" fill="#fff"/>
            <rect y="26.6" width="60" height="13.4" fill="#477050"/>
            @break
        @case('uy')
            <rect width="60" height="40" fill="#fff"/>
            <rect y="5" width="60" height="5" fill="#0038A8"/>
            <rect y="15" width="60" height="5" fill="#0038A8"/>
            <rect y="25" width="60" height="5" fill="#0038A8"/>
            <rect y="35" width="60" height="5" fill="#0038A8"/>
            <rect width="24" height="20" fill="#fff"/>
            <circle cx="12" cy="10" r="4" fill="#FCD116"/>
            @break
        @case('np')
            <rect width="60" height="40" fill="#DC143C"/>
            <polygon points="8,4 36,18 8,18" fill="#003893"/>
            <polygon points="8,20 40,36 8,36" fill="#003893"/>
            <polygon points="12,8 16,14 10,11 18,11 12,14" fill="#fff"/>
            @break
        @case('tw')
            <rect width="60" height="40" fill="#FE0000"/>
            <rect width="30" height="22" fill="#000095"/>
            <circle cx="15" cy="11" r="5" fill="#fff"/>
            @break
        @case('kw')
            <rect width="60" height="13.4" fill="#007A3D"/>
            <rect y="13.4" width="60" height="13.2" fill="#fff"/>
            <rect y="26.6" width="60" height="13.4" fill="#CE1126"/>
            <polygon points="0,0 16,20 0,40" fill="#000"/>
            @break
        @default
            <rect width="60" height="40" fill="#F2F2F2"/>
    @endswitch
</svg>
