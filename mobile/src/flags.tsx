import { SvgXml } from 'react-native-svg';

const horizontal = (colors: string[]) => colors.map((color, index) => `<rect y="${(40 / colors.length) * index}" width="60" height="${40 / colors.length}" fill="${color}"/>`).join('');
const vertical = (colors: string[]) => colors.map((color, index) => `<rect x="${(60 / colors.length) * index}" width="${60 / colors.length}" height="40" fill="${color}"/>`).join('');

const flags: Record<string, string> = {
    ar: horizontal(['#74ACDF', '#fff', '#74ACDF']) + '<circle cx="30" cy="20" r="3.5" fill="#F6B40E"/>',
    au: '<rect width="60" height="40" fill="#012169"/><path d="M0,0 L26,18 M26,0 L0,18" stroke="#fff" stroke-width="4"/><path d="M13,0 V18 M0,9 H26" stroke="#fff" stroke-width="6"/><path d="M13,0 V18 M0,9 H26" stroke="#C8102E" stroke-width="3"/><polygon points="46,28 47,31 50,31 47.6,32.8 48.5,35.6 46,33.8 43.5,35.6 44.4,32.8 42,31 45,31" fill="#fff"/>',
    bd: '<rect width="60" height="40" fill="#006A4E"/><circle cx="26" cy="20" r="9" fill="#F42A41"/>',
    by: '<rect width="60" height="20" fill="#CE1720"/><rect y="20" width="60" height="20" fill="#007C30"/><rect width="10" height="40" fill="#fff"/>',
    be: vertical(['#000', '#FAE042', '#ED2939']),
    br: '<rect width="60" height="40" fill="#009C3B"/><polygon points="30,6 52,20 30,34 8,20" fill="#FFDF00"/><circle cx="30" cy="20" r="7" fill="#002776"/>',
    bg: horizontal(['#fff', '#00966E', '#D62612']),
    ca: '<rect width="15" height="40" fill="#FF0000"/><rect x="45" width="15" height="40" fill="#FF0000"/><rect x="15" width="30" height="40" fill="#fff"/><polygon points="30,10 32,16 38,16 33,20 35,26 30,22 25,26 27,20 22,16 28,16" fill="#FF0000"/>',
    cn: '<rect width="60" height="40" fill="#DE2910"/><polygon points="12,6 13.4,10.2 17.8,10.2 14.2,12.8 15.6,17 12,14.4 8.4,17 9.8,12.8 6.2,10.2 10.6,10.2" fill="#FFDE00"/>',
    cy: '<rect width="60" height="40" fill="#fff"/><polygon points="18,24 30,14 42,24 36,22 30,26 24,22" fill="#D57800"/>',
    cz: '<rect width="60" height="20" fill="#fff"/><rect y="20" width="60" height="20" fill="#D7141A"/><polygon points="0,0 26,20 0,40" fill="#11457E"/>',
    dk: '<rect width="60" height="40" fill="#C60C30"/><rect x="16" width="8" height="40" fill="#fff"/><rect y="16" width="60" height="8" fill="#fff"/>',
    fi: '<rect width="60" height="40" fill="#fff"/><rect x="16" width="8" height="40" fill="#003580"/><rect y="16" width="60" height="8" fill="#003580"/>',
    fr: vertical(['#0055A4', '#fff', '#EF4135']),
    ge: '<rect width="60" height="40" fill="#fff"/><rect x="26" width="8" height="40" fill="#FF0000"/><rect y="16" width="60" height="8" fill="#FF0000"/>',
    de: horizontal(['#000', '#DD0000', '#FFCE00']),
    hk: '<rect width="60" height="40" fill="#DE2910"/><circle cx="30" cy="20" r="7" fill="none" stroke="#fff" stroke-width="1.6"/>',
    hu: horizontal(['#CE2939', '#fff', '#477050']),
    in: horizontal(['#FF9933', '#fff', '#138808']) + '<circle cx="30" cy="20" r="4.2" fill="none" stroke="#000080" stroke-width="1.2"/>',
    id: horizontal(['#CE1126', '#fff']),
    ie: vertical(['#169B62', '#fff', '#FF883E']),
    il: '<rect width="60" height="40" fill="#fff"/><rect y="6" width="60" height="5" fill="#0038B8"/><rect y="29" width="60" height="5" fill="#0038B8"/>',
    it: vertical(['#009246', '#fff', '#CE2B37']),
    lt: horizontal(['#FDB913', '#006A44', '#C1272D']),
    my: '<rect width="60" height="40" fill="#CC0001"/><rect width="28" height="22" fill="#010066"/><circle cx="12" cy="11" r="6" fill="#FFCC00"/>',
    mt: '<rect width="30" height="40" fill="#fff"/><rect x="30" width="30" height="40" fill="#CF142B"/>',
    md: vertical(['#003DA5', '#FFD200', '#C8102E']),
    me: '<rect width="60" height="40" fill="#C40308"/><rect x="4" y="3" width="52" height="34" fill="none" stroke="#D4A017" stroke-width="2"/>',
    nl: horizontal(['#AE1C28', '#fff', '#21468B']),
    nz: '<rect width="60" height="40" fill="#012169"/><path d="M0,0 L26,18 M26,0 L0,18" stroke="#fff" stroke-width="4"/><circle cx="48" cy="24" r="1.6" fill="#fff"/>',
    pk: '<rect width="15" height="40" fill="#fff"/><rect x="15" width="45" height="40" fill="#01411C"/><circle cx="34" cy="20" r="7" fill="#fff"/><circle cx="36.5" cy="18.5" r="6" fill="#01411C"/>',
    ph: '<rect width="60" height="20" fill="#0038A8"/><rect y="20" width="60" height="20" fill="#CE1126"/><polygon points="0,0 26,20 0,40" fill="#fff"/>',
    pl: horizontal(['#fff', '#DC143C']),
    pt: '<rect width="24" height="40" fill="#006600"/><rect x="24" width="36" height="40" fill="#FF0000"/><circle cx="24" cy="20" r="7" fill="#FFCC00"/>',
    ro: vertical(['#002B7F', '#FCD116', '#CE1126']),
    ru: horizontal(['#fff', '#0039A6', '#D52B1E']),
    rs: horizontal(['#C6363C', '#0C4076', '#fff']),
    sg: '<rect width="60" height="20" fill="#EF3340"/><rect y="20" width="60" height="20" fill="#fff"/><circle cx="16" cy="12" r="6" fill="#fff"/><circle cx="18.2" cy="12" r="5" fill="#EF3340"/>',
    si: horizontal(['#fff', '#003DA5', '#ED1C24']),
    za: '<rect width="60" height="40" fill="#007A4D"/><polygon points="0,0 60,0 60,15 22,15 0,0" fill="#DE3831"/><polygon points="0,40 60,40 60,25 22,25 0,40" fill="#002395"/><polygon points="0,0 24,20 0,40" fill="#000"/>',
    es: '<rect width="60" height="40" fill="#C60B1E"/><rect y="10" width="60" height="20" fill="#FFC400"/>',
    se: '<rect width="60" height="40" fill="#006AA7"/><rect x="16" width="8" height="40" fill="#FECC00"/><rect y="16" width="60" height="8" fill="#FECC00"/>',
    tw: '<rect width="60" height="40" fill="#FE0000"/><rect width="30" height="22" fill="#000095"/><circle cx="15" cy="11" r="5" fill="#fff"/>',
    th: '<rect width="60" height="40" fill="#A51931"/><rect y="6.6" width="60" height="26.8" fill="#fff"/><rect y="13.3" width="60" height="13.4" fill="#2D2A4A"/>',
    tr: '<rect width="60" height="40" fill="#E30A17"/><circle cx="24" cy="20" r="8" fill="#fff"/><circle cx="27" cy="20" r="6.4" fill="#E30A17"/>',
    ua: horizontal(['#005BBB', '#FFD500']),
    ae: horizontal(['#00732F', '#fff', '#000']) + '<rect width="16" height="40" fill="#FF0000"/>',
    gb: '<rect width="60" height="40" fill="#012169"/><path d="M0,0 L60,40 M60,0 L0,40" stroke="#fff" stroke-width="8"/><path d="M30,0 V40 M0,20 H60" stroke="#fff" stroke-width="12"/><path d="M30,0 V40 M0,20 H60" stroke="#C8102E" stroke-width="7"/>',
    uk: '<rect width="60" height="40" fill="#012169"/><path d="M0,0 L60,40 M60,0 L0,40" stroke="#fff" stroke-width="8"/><path d="M30,0 V40 M0,20 H60" stroke="#fff" stroke-width="12"/><path d="M30,0 V40 M0,20 H60" stroke="#C8102E" stroke-width="7"/>',
    us: '<rect width="60" height="40" fill="#B22234"/><rect y="3.08" width="60" height="3.08" fill="#fff"/><rect y="9.23" width="60" height="3.08" fill="#fff"/><rect y="15.38" width="60" height="3.08" fill="#fff"/><rect y="21.54" width="60" height="3.08" fill="#fff"/><rect y="27.69" width="60" height="3.08" fill="#fff"/><rect y="33.85" width="60" height="3.08" fill="#fff"/><rect width="26" height="21.5" fill="#3C3B6E"/>',
    vn: '<rect width="60" height="40" fill="#DA251D"/><polygon points="30,8 33.5,18.5 44.5,18.5 35.6,25 39,35.5 30,29 21,35.5 24.4,25 15.5,18.5 26.5,18.5" fill="#FF0"/>',
    ch: '<rect width="60" height="40" fill="#D52B1E"/><rect x="25" y="8" width="10" height="24" fill="#fff"/><rect x="18" y="15" width="24" height="10" fill="#fff"/>',
};

export function Flag({ code }: { code: string }) {
    const key = code.toLowerCase() === 'uk' ? 'gb' : code.toLowerCase();
    const markup = flags[key] || '<rect width="60" height="40" fill="#F2F2F2"/>';

    return <SvgXml xml={`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 40">${markup}</svg>`} width={22} height={15} />;
}
