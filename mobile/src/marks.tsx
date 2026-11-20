import { SvgXml } from 'react-native-svg';

const marks: Record<string, string[]> = {
    check: ['M20 6 9 17l-5-5'],
    shield: ['M12 3 5 6v6c0 4.2 2.8 7.2 7 8.5 4.2-1.3 7-4.3 7-8.5V6Z', 'm9 12 2 2 4-4'],
    chart: ['M4 19V5', 'M4 19h16', 'm8 15 3.5-4 2.5 2.5L19 8'],
    layers: ['m12 3 9 5-9 5L3 8Z', 'm3 13 9 5 9-5'],
    users: ['M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20', 'M20 20v-1.5a3.2 3.2 0 0 0-2.4-3.1', 'M16.5 5.2a3 3 0 0 1 0 5.6'],
    globe: ['M2 12h20', 'M12 4c2.2 2.4 3.3 5.1 3.3 8S14.2 17.6 12 20c-2.2-2.4-3.3-5.1-3.3-8S9.8 6.4 12 4Z'],
    link: ['M10 13a5 5 0 0 0 7.1.1l1.4-1.4a5 5 0 0 0-7.1-7.1L10.2 5.8', 'M14 11a5 5 0 0 0-7.1-.1L5.5 12.3a5 5 0 0 0 7.1 7.1l1.2-1.2'],
    type: ['M5 6h14', 'M12 6v12', 'M8 18h8'],
    article: ['M6 4h9l3 3v13H6Z', 'M15 4v3h3', 'M9 11h6M9 15h4'],
    pen: ['M4 20h4l10-10-4-4L4 16Z', 'm13 7 4 4'],
    edit: ['M4 6h10', 'M4 12h7', 'M4 18h6', 'm14 15 6-6-2-2-6 6v2h2Z'],
};

export function Mark({ name, color = '#FF4917', size = 22 }: { name?: string; color?: string; size?: number }) {
    const key = name && marks[name] ? name : 'link';
    const lines = marks[key];
    const extra = key === 'users'
        ? '<circle cx="10" cy="8" r="3" />'
        : key === 'globe'
            ? '<circle cx="12" cy="12" r="8" />'
            : '';
    const paths = lines.map((line) => `<path d="${line}" />`).join('');
    const xml = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">${extra}${paths}</svg>`;

    return <SvgXml xml={xml} width={size} height={size} />;
}
