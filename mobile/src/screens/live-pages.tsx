import { useMemo, useState } from 'react';
import { Linking, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

import { apiRequest, errorText, hasErrors } from '../api/client';
import { money } from '../api/home';
import { Flag } from '../flags';
import { colors } from '../theme';

export function PublisherCatalog({ data, onOpen }: { data: Record<string, unknown>; onOpen: (path: string) => void }) {
    const country = record(data.country);
    const source = rows(data.rows);
    const [query, setQuery] = useState('');
    const [dr, setDr] = useState(text(data.defaultDr));
    const [type, setType] = useState(text(data.defaultType));
    const [niche, setNiche] = useState('');
    const niches = useMemo(() => categoryNames(data.categories), [data.categories]);
    const title = text(country.label) !== '' ? `${text(country.label)} publisher websites` : 'List of posting websites (All publishers)';
    const lead = text(country.label) !== ''
        ? `This chart lists only the publisher websites whose strongest traffic comes from ${text(country.label)}.`
        : 'Every publisher website on this list is included. Filter by the niche, language, metrics and price stored on each site, then open the one you want.';
    const shown = source.filter((row) => {
        const search = `${text(row.domain)} ${strings(row.niches).join(' ')}`.toLowerCase();

        if (query.trim() !== '' && !search.includes(query.trim().toLowerCase())) {
            return false;
        }

        if (dr !== '' && number(row.dr) < Number(dr)) {
            return false;
        }

        if (type !== '' && text(row.link_type) !== type) {
            return false;
        }

        if (niche !== '' && !strings(row.niches).includes(niche)) {
            return false;
        }

        return true;
    });

    return (
        <View>
            <Text style={styles.crumbs}>Home / {text(country.label, 'Publisher list')}</Text>
            <Text style={styles.h1}>{title}</Text>
            <Text style={styles.copy}>{lead}</Text>
            {text(data.paidLabel) !== '' ? <Text style={styles.note}>Payment confirmed. These websites match your {text(data.paidLabel)}.</Text> : null}
            {data.demo === true ? <Text style={styles.note}>Sample data for preview. These figures belong to fictional .example publishers.</Text> : null}
            <TextInput style={styles.field} value={query} onChangeText={setQuery} placeholder="Search by website or niche" placeholderTextColor="#8B939C" autoCapitalize="none" />
            <View style={styles.wrap}>
                {['', '20', '30', '40', '50', '60', '70'].map((value) => (
                    <Pressable key={value || 'any'} style={[styles.chip, dr === value && styles.chipOn]} onPress={() => setDr(value)}>
                        <Text style={styles.chipText}>{value === '' ? 'DR' : `DR ${value}+`}</Text>
                    </Pressable>
                ))}
            </View>
            <View style={styles.wrap}>
                {['', 'Guest post', 'Niche edit', 'Contextual', 'Text link'].map((value) => (
                    <Pressable key={value || 'type'} style={[styles.chip, type === value && styles.chipOn]} onPress={() => setType(value)}>
                        <Text style={styles.chipText}>{value === '' ? 'Link type' : value}</Text>
                    </Pressable>
                ))}
            </View>
            <View style={styles.wrap}>
                <Pressable style={[styles.chip, niche === '' && styles.chipOn]} onPress={() => setNiche('')}><Text style={styles.chipText}>Niche</Text></Pressable>
                {niches.map((name) => (
                    <Pressable key={name} style={[styles.chip, niche === name && styles.chipOn]} onPress={() => setNiche(name)}>
                        <Text style={styles.chipText}>{name}</Text>
                    </Pressable>
                ))}
            </View>
            <Text style={styles.showing}>Showing {shown.length} of {source.length} publishers</Text>
            {shown.length === 0 ? <Text style={styles.copy}>No publisher matches these filters.</Text> : shown.map((row) => {
                const domain = text(row.domain);
                const countries = pairs(row.top_countries);

                return (
                    <Pressable key={domain} style={styles.card} onPress={() => onOpen(`/publish/${encodeURIComponent(domain)}`)}>
                        <Text style={styles.cardTitle}>{domain}</Text>
                        <Text style={styles.muted}>{strings(row.niches).join(', ')}{text(row.language) ? ` · ${text(row.language)}` : ''}</Text>
                        <Text style={styles.copy}>AS {text(row.as)} · DA {text(row.da)} · DR {text(row.dr)} · {row.price == null ? '' : money(row.price)}</Text>
                        <View style={styles.flagRow}>
                            {countries.slice(0, 3).map((item) => (
                                <View key={item[0]} style={styles.flagLine}>
                                    <Flag code={item[0].toUpperCase() === 'UK' ? 'gb' : item[0]} />
                                    <Text style={styles.muted}>{Number(item[1] || 0).toFixed(1)}%</Text>
                                </View>
                            ))}
                        </View>
                        <Text style={styles.publish}>Publish</Text>
                    </Pressable>
                );
            })}
        </View>
    );
}

export function PublishOrder({ data, flash, onOpen }: { data: Record<string, unknown>; flash?: Record<string, unknown>; onOpen: (path: string) => void }) {
    const offer = record(data.offer);
    const groups = record(offer.order_types);
    const placement = rows(groups.content_placement);
    const insertion = rows(groups.link_insertion);
    const topics = strings(offer.content_topics);
    const topicList = topics.length > 0 ? topics : ['General'];
    const [service, setService] = useState(placement.length > 0 ? 'content_placement' : 'link_insertion');
    const types = service === 'link_insertion' ? insertion : placement;
    const [choice, setChoice] = useState(text(types[0]?.key));
    const [topic, setTopic] = useState(topicList.includes('General') ? 'General' : topicList[0]);
    const [quantity, setQuantity] = useState(1);
    const [pending, setPending] = useState(false);
    const [problem, setProblem] = useState('');
    const selected = [...placement, ...insertion].find((type) => text(type.key) === choice) ?? types[0] ?? {};
    const unit = typeof offer.price === 'number' ? offer.price : null;
    const line = unit === null ? null : unit * quantity;
    const metrics = record(offer.metrics);

    async function add() {
        setPending(true);
        setProblem('');
        const result = await apiRequest('/cart', {
            method: 'POST',
            body: {
                domain: text(offer.domain),
                site: text(offer.key),
                website_id: offer.website_id,
                product_key: text(selected.product_key),
                order_type: text(selected.key),
                order_label: text(selected.label),
                content_topic: topic,
                project: 'Default',
                quantity,
            },
        });
        setPending(false);

        if (followed(result.body, onOpen)) {
            return;
        }

        if (hasErrors(result.body) || result.status >= 400) {
            setProblem(errorText(result.body));
        }
    }

    return (
        <View>
            {text(flash?.shopping_status) !== '' ? <Text style={styles.saved}>{text(flash?.shopping_status)}</Text> : null}
            <Text style={styles.crumbs}>{text(offer.primary_category, 'Publisher')} / {text(offer.display_name)}</Text>
            {offer.demo === true ? <Text style={styles.note}>Sample data for preview</Text> : null}
            <Text style={styles.h1}>Publish a Guest Post on {text(offer.display_name)}</Text>
            <Text style={styles.price}>{text(offer.price_label, unit === null ? '—' : money(unit))}</Text>
            <Text style={styles.copy}>{text(offer.primary_category)} · {text(offer.language)} · DR {text(metrics.dr)} · DA {text(metrics.da)} · Traffic {text(metrics.traffic)}</Text>
            <View style={styles.wrap}>
                <Pressable style={[styles.chip, service === 'content_placement' && styles.chipOn]} onPress={() => { setService('content_placement'); setChoice(text(placement[0]?.key)); }}><Text style={styles.chipText}>Content placement</Text></Pressable>
                <Pressable style={[styles.chip, service === 'link_insertion' && styles.chipOn]} onPress={() => { setService('link_insertion'); setChoice(text(insertion[0]?.key)); }}><Text style={styles.chipText}>Link insertion</Text></Pressable>
            </View>
            <Text style={styles.h2}>Order type</Text>
            {types.map((type) => (
                <Pressable key={text(type.key)} style={styles.choice} onPress={() => setChoice(text(type.key))}>
                    <Text style={styles.choiceMark}>{choice === text(type.key) ? '●' : '○'}</Text>
                    <Text style={styles.copy}>{text(type.label)} ({text(type.help)})</Text>
                </Pressable>
            ))}
            <Text style={styles.h2}>Your article topic</Text>
            <View style={styles.wrap}>
                {topicList.slice(0, 8).map((item) => (
                    <Pressable key={item} style={[styles.chip, topic === item && styles.chipOn]} onPress={() => setTopic(item)}><Text style={styles.chipText}>{item}</Text></Pressable>
                ))}
            </View>
            <View style={styles.quantity}>
                <Pressable style={styles.step} onPress={() => setQuantity((value) => Math.max(1, value - 1))}><Text style={styles.stepText}>−</Text></Pressable>
                <Text style={styles.qty}>{quantity}</Text>
                <Pressable style={styles.step} onPress={() => setQuantity((value) => Math.min(10, value + 1))}><Text style={styles.stepText}>+</Text></Pressable>
            </View>
            <Text style={styles.cardTitle}>Subtotal {line === null ? '—' : money(line)}</Text>
            <Text style={styles.copy}>After checkout you'll add the topic, anchor text, and target URL. Our team writes and publishes the placement.</Text>
            {strings(offer.rules).map((rule) => <Text key={rule} style={styles.copy}>• {rule}</Text>)}
            {problem !== '' ? <Text style={styles.error}>{problem}</Text> : null}
            <Pressable style={styles.primary} disabled={pending} onPress={() => { void add(); }}><Text style={styles.primaryText}>{pending ? 'Adding…' : 'Add to cart'}</Text></Pressable>
            <Pressable onPress={() => onOpen('/contact')}><Text style={styles.link}>Questions? Contact us</Text></Pressable>
        </View>
    );
}

export function ContactPage() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [message, setMessage] = useState('');

    function sendMail() {
        const subject = encodeURIComponent(`BacklinkPro enquiry from ${name || 'a visitor'}`);
        const body = encodeURIComponent(`${message}\n\n${name}\n${email}`);
        void Linking.openURL(`mailto:?subject=${subject}&body=${body}`);
    }

    return (
        <View>
            <Text style={styles.kicker}>Contact</Text>
            <Text style={styles.h1}>Let’s plan your next backlink campaign</Text>
            <Text style={styles.copy}>Prime One Global (PVT) LTD. #146B, Goodshed Road, Thonikkal, Vavuniya, NP, Sri Lanka.</Text>
            <Text style={styles.label}>Name</Text>
            <TextInput style={styles.field} value={name} onChangeText={setName} placeholder="Name" placeholderTextColor="#8B939C" />
            <Text style={styles.label}>Email</Text>
            <TextInput style={styles.field} value={email} onChangeText={setEmail} autoCapitalize="none" keyboardType="email-address" placeholder="Email" placeholderTextColor="#8B939C" />
            <Text style={styles.label}>How can we help?</Text>
            <TextInput style={[styles.field, styles.area]} multiline value={message} onChangeText={setMessage} placeholder="How can we help?" placeholderTextColor="#8B939C" />
            <Pressable style={styles.primary} onPress={sendMail}><Text style={styles.primaryText}>Send message</Text></Pressable>
        </View>
    );
}

export function CartPage({ data, flash, onOpen }: { data: Record<string, unknown>; flash?: Record<string, unknown>; onOpen: (path: string) => void }) {
    const items = rows(data.items);
    const [coupon, setCoupon] = useState('');
    const [pending, setPending] = useState(false);
    const [problem, setProblem] = useState(text(flash?.shopping_error));
    const status = text(flash?.shopping_status);

    async function post(path: string, method: string, body: Record<string, unknown>) {
        setPending(true);
        setProblem('');
        const result = await apiRequest(path, { method, body });
        setPending(false);

        if (followed(result.body, onOpen)) {
            return;
        }

        if (hasErrors(result.body) || result.status >= 400) {
            setProblem(errorText(result.body));

            return;
        }

        onOpen(`/cart?t=${Date.now()}`);
    }

    return (
        <View>
            <Text style={styles.h1}>Cart</Text>
            {status !== '' ? <Text style={styles.saved}>{status}</Text> : null}
            {problem !== '' ? <Text style={styles.error}>{problem}</Text> : null}
            {items.length === 0 ? <Text style={styles.copy}>The cart is empty. Add a publisher or a package first.</Text> : items.map((item) => (
                <View key={text(item.line, text(item.domain))} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(item.display_name, text(item.domain))}</Text>
                    <Text style={styles.muted}>{text(item.quantity, '1')} × {money(item.price)}{strings(item.websites).length > 0 ? ` · ${strings(item.websites).join(', ')}` : ''}</Text>
                    <Pressable onPress={() => { void post('/cart', 'DELETE', { line: text(item.line), domain: text(item.domain) }); }}><Text style={styles.link}>Remove</Text></Pressable>
                </View>
            ))}
            <Text style={styles.cardTitle}>Subtotal {money(data.subtotal)}</Text>
            {number(data.packageDiscount) > 0 ? <Text style={styles.copy}>Package discount -{money(data.packageDiscount)}</Text> : null}
            {number(data.discount) > 0 ? <Text style={styles.saved}>{text(data.offerLabel, 'Coupon')} {text(data.coupon)} -{money(data.discount)}</Text> : null}
            <Text style={styles.price}>Total {money(data.total)}</Text>
            {text(data.coupon) !== '' ? (
                <Pressable onPress={() => { void post('/cart/coupon', 'DELETE', {}); }}><Text style={styles.link}>Remove coupon {text(data.coupon)}</Text></Pressable>
            ) : (
                <View>
                    <TextInput style={styles.field} value={coupon} onChangeText={setCoupon} autoCapitalize="characters" placeholder="Coupon code" placeholderTextColor="#8B939C" />
                    <Pressable style={styles.navy} disabled={pending} onPress={() => { void post('/cart/coupon', 'POST', { coupon_code: coupon }); }}><Text style={styles.primaryText}>Apply</Text></Pressable>
                </View>
            )}
            <Pressable style={styles.primary} onPress={() => onOpen('/cart/checkout')}><Text style={styles.primaryText}>Checkout</Text></Pressable>
        </View>
    );
}

export function CheckoutPage({ data, flash, onOpen }: { data: Record<string, unknown>; flash?: Record<string, unknown>; onOpen: (path: string) => void }) {
    const items = rows(data.items);
    const [details, setDetails] = useState(() => items.map((item) => ({
        line: text(item.line),
        target_url: '',
        anchor_text: '',
        article_topic: text(item.content_topic),
        keywords: '',
        requirements: '',
    })));
    const [pending, setPending] = useState(false);
    const [problem, setProblem] = useState(text(flash?.shopping_error));

    function update(index: number, key: 'target_url' | 'anchor_text' | 'article_topic' | 'keywords' | 'requirements', value: string) {
        setDetails((current) => current.map((item, itemIndex) => itemIndex === index ? { ...item, [key]: value } : item));
    }

    async function place() {
        setPending(true);
        setProblem('');
        const result = await apiRequest('/cart/checkout', {
            method: 'POST',
            body: { items: details, coupon_code: text(data.coupon) },
        });
        setPending(false);

        if (followed(result.body, onOpen)) {
            return;
        }

        setProblem(errorText(result.body));
    }

    return (
        <View>
            <Text style={styles.h1}>Checkout</Text>
            <Text style={styles.copy}>Add your website URL, anchor text and target details for each item.</Text>
            {problem !== '' ? <Text style={styles.error}>{problem}</Text> : null}
            {items.map((item, index) => (
                <View key={text(item.line, String(index))} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(item.kind) === 'package' ? text(item.display_name) : `Publish on ${text(item.display_name, text(item.domain))}`}</Text>
                    <Text style={styles.muted}>{text(item.order_label)} · Qty {text(item.quantity, '1')}</Text>
                    <Text style={styles.price}>{money(number(item.price) * number(item.quantity || 1))}</Text>
                    <Text style={styles.label}>Target URL</Text>
                    <TextInput style={styles.field} autoCapitalize="none" value={details[index]?.target_url ?? ''} onChangeText={(value) => update(index, 'target_url', value)} placeholder="https://your-site.com/page" placeholderTextColor="#8B939C" />
                    <Text style={styles.label}>Anchor text</Text>
                    <TextInput style={styles.field} value={details[index]?.anchor_text ?? ''} onChangeText={(value) => update(index, 'anchor_text', value)} placeholder="15–20 characters" placeholderTextColor="#8B939C" />
                    <Text style={styles.label}>Article topic</Text>
                    <TextInput style={styles.field} value={details[index]?.article_topic ?? ''} onChangeText={(value) => update(index, 'article_topic', value)} placeholder="Topic" placeholderTextColor="#8B939C" />
                    <Text style={styles.label}>Keywords</Text>
                    <TextInput style={styles.field} value={details[index]?.keywords ?? ''} onChangeText={(value) => update(index, 'keywords', value)} placeholder="keyword one, keyword two" placeholderTextColor="#8B939C" />
                    <Text style={styles.label}>Additional requirements</Text>
                    <TextInput style={[styles.field, styles.area]} multiline value={details[index]?.requirements ?? ''} onChangeText={(value) => update(index, 'requirements', value)} placeholder="Optional" placeholderTextColor="#8B939C" />
                </View>
            ))}
            <Text style={styles.price}>Total {money(data.total)}</Text>
            <Pressable style={styles.primary} disabled={pending || items.length === 0} onPress={() => { void place(); }}><Text style={styles.primaryText}>{pending ? 'Placing…' : 'Place order'}</Text></Pressable>
            <Pressable onPress={() => onOpen('/cart')}><Text style={styles.link}>Back to cart</Text></Pressable>
        </View>
    );
}

export function PaymentPage({ data, flash, payments, onOpen }: { data: Record<string, unknown>; flash?: Record<string, unknown>; payments?: Record<string, unknown>; onOpen: (path: string) => void }) {
    const order = record(data.order);
    const [pending, setPending] = useState(false);
    const [problem, setProblem] = useState('');
    const [result, setResult] = useState('success');

    async function pay(body: Record<string, unknown>) {
        setPending(true);
        setProblem('');
        const response = await apiRequest(`/orders/${text(order.id)}/payment`, { method: 'POST', body });
        setPending(false);

        if (followed(response.body, onOpen)) {
            return;
        }

        setProblem(errorText(response.body));
    }

    return (
        <View>
            <Text style={styles.h1}>Complete payment</Text>
            {text(flash?.success) !== '' ? <Text style={styles.saved}>{text(flash?.success)}</Text> : null}
            {problem !== '' ? <Text style={styles.error}>{problem}</Text> : null}
            <Text style={styles.copy}>Order {text(order.order_number)}</Text>
            <Text style={styles.price}>Amount due {money(order.total_amount)}</Text>
            {payments?.stripe === true ? <Pressable style={styles.primary} disabled={pending} onPress={() => { void pay({ payment_method: 'stripe' }); }}><Text style={styles.primaryText}>Pay with Stripe</Text></Pressable> : null}
            {payments?.demo === true ? (
                <View>
                    <Text style={styles.copy}>Dummy payment gateway. No money is charged.</Text>
                    <View style={styles.wrap}>
                        <Pressable style={[styles.chip, result === 'success' && styles.chipOn]} onPress={() => setResult('success')}><Text style={styles.chipText}>Successful payment</Text></Pressable>
                        <Pressable style={[styles.chip, result === 'declined' && styles.chipOn]} onPress={() => setResult('declined')}><Text style={styles.chipText}>Declined payment</Text></Pressable>
                    </View>
                    <Pressable style={styles.primary} disabled={pending} onPress={() => { void pay({ payment_method: 'demo_card', demo_result: result }); }}><Text style={styles.primaryText}>Pay with dummy gateway</Text></Pressable>
                </View>
            ) : null}
            {payments?.stripe !== true && payments?.demo !== true ? <Text style={styles.copy}>Stripe checkout is currently unavailable.</Text> : null}
        </View>
    );
}

export function ThanksPage({ data, flash }: { data: Record<string, unknown>; flash?: Record<string, unknown> }) {
    const order = record(data.order);

    return (
        <View>
            <Text style={styles.h1}>Thank you</Text>
            {text(flash?.success) !== '' ? <Text style={styles.saved}>{text(flash?.success)}</Text> : null}
            <Text style={styles.copy}>Order {text(order.order_number, text(data.order_number))}</Text>
            <Text style={styles.price}>{text(order.total_amount) !== '' || typeof order.total_amount === 'number' ? money(order.total_amount) : ''}</Text>
        </View>
    );
}

export function couponOffer(view: string, data: Record<string, unknown>, flash?: Record<string, unknown>): { coupons: Record<string, unknown>[]; title: string; copy: string } | null {
    if (flash?.saved_coupon_popup) {
        return null;
    }

    if (view === 'orders.payment') {
        const coupons = couponList(data.availableCoupons).filter((coupon) => isOrderReward(coupon));

        if (coupons.length === 0) {
            return null;
        }

        return {
            coupons,
            title: 'Your 2% coupon',
            copy: coupons.length === 1
                ? 'Apply this coupon before you pay, or leave it for a later order.'
                : 'Choose one of these coupons before you pay, or leave them for a later order.',
        };
    }

    if (view === 'customer.shopping.checkout') {
        const applied = text(data.coupon);
        const choices = couponList(data.couponChoices);
        const source = choices.length > 0 ? choices : couponList(data.orderRewards);
        const coupons = source.filter((coupon) => {
            const code = text(coupon.code);

            return code !== '' && code !== applied;
        });

        if (coupons.length === 0) {
            return null;
        }

        return {
            coupons,
            title: coupons.every((coupon) => isOrderReward(coupon)) ? 'Your 2% coupon' : 'Select your saved coupon',
            copy: coupons.length === 1
                ? 'Apply this coupon before you pay, or leave it for a later order.'
                : 'Choose one of these coupons before you pay, or leave them for a later order.',
        };
    }

    return null;
}

export function CouponOverlay({ coupons, pending, problem, title, copy, onApply, onClose }: {
    coupons: Record<string, unknown>[];
    pending: boolean;
    problem: string;
    title: string;
    copy: string;
    onApply: (code: string) => void;
    onClose: () => void;
}) {
    return (
        <View style={styles.popupWrap}>
            <View style={styles.popupCard}>
                <Text style={styles.h2}>{title}</Text>
                <Text style={styles.copy}>{copy}</Text>
                {problem !== '' ? <Text style={styles.error}>{problem}</Text> : null}
                {coupons.map((coupon) => (
                    <View key={text(coupon.id, text(coupon.code))} style={styles.popupOffer}>
                        <Text style={styles.popupCode}>{text(coupon.code)}</Text>
                        <Text style={styles.muted}>{text(coupon.discount_summary, '2% off')} for this order.{text(coupon.expires_at) !== '' ? ` Use it before ${day(coupon.expires_at)}.` : ''}</Text>
                        <Pressable style={styles.primary} disabled={pending} onPress={() => onApply(text(coupon.code))}><Text style={styles.primaryText}>{pending ? 'Applying…' : 'Apply'}</Text></Pressable>
                    </View>
                ))}
                <Pressable style={styles.navy} disabled={pending} onPress={onClose}><Text style={styles.primaryText}>Not now</Text></Pressable>
            </View>
        </View>
    );
}

function isOrderReward(coupon: Record<string, unknown>): boolean {
    if (text(coupon.code) === '') {
        return false;
    }

    if (text(coupon.type) === 'order_reward') {
        return true;
    }

    if (text(coupon.discount_summary).toLowerCase().startsWith('2%')) {
        return true;
    }

    return text(coupon.discount_type) === 'percentage' && Number(coupon.discount_value) === 2;
}

function couponList(value: unknown): Record<string, unknown>[] {
    if (Array.isArray(value)) {
        return rows(value);
    }

    if (value && typeof value === 'object') {
        return rows(Object.values(value));
    }

    return [];
}

function day(value: unknown): string {
    const date = new Date(text(value));

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function followed(body: Record<string, unknown>, onOpen: (path: string) => void): boolean {
    const redirect = body.redirect;

    if (typeof redirect !== 'string' || redirect === '') {
        return false;
    }

    if (redirect.startsWith('http')) {
        try {
            const url = new URL(redirect);
            onOpen(`${url.pathname}${url.search}`);

            return true;
        } catch {
            return false;
        }
    }

    onOpen(redirect);

    return true;
}

function rows(value: unknown): Record<string, unknown>[] {
    return Array.isArray(value) ? value.filter((item): item is Record<string, unknown> => Boolean(item) && typeof item === 'object' && !Array.isArray(item)) : [];
}

function record(value: unknown): Record<string, unknown> {
    return value && typeof value === 'object' && !Array.isArray(value) ? value as Record<string, unknown> : {};
}

function strings(value: unknown): string[] {
    return Array.isArray(value) ? value.filter((item): item is string => typeof item === 'string' && item !== '') : [];
}

function text(value: unknown, fallback = ''): string {
    if (typeof value === 'string') {
        return value;
    }

    if (typeof value === 'number' && Number.isFinite(value)) {
        return String(value);
    }

    return fallback;
}

function number(value: unknown): number {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : 0;
}

function pairs(value: unknown): [string, number][] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.flatMap((item) => {
        if (!Array.isArray(item) || typeof item[0] !== 'string') {
            return [];
        }

        return [[item[0], Number(item[1]) || 0] as [string, number]];
    });
}

function categoryNames(value: unknown): string[] {
    if (Array.isArray(value)) {
        return value.filter((item): item is string => typeof item === 'string');
    }

    if (value && typeof value === 'object') {
        return Object.keys(value).sort();
    }

    return [];
}

const styles = StyleSheet.create({
    h1: { color: colors.navy, fontSize: 32, lineHeight: 38, fontWeight: '800' },
    h2: { marginTop: 18, color: colors.navy, fontSize: 18, fontWeight: '800' },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    crumbs: { marginBottom: 10, color: colors.muted, fontSize: 14, fontWeight: '700' },
    copy: { marginTop: 8, color: colors.muted, fontSize: 15, lineHeight: 22 },
    card: { marginTop: 12, borderRadius: 16, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 14 },
    cardTitle: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    muted: { marginTop: 4, color: colors.muted, fontSize: 13, fontWeight: '700' },
    price: { marginTop: 8, color: colors.brandDark, fontSize: 20, fontWeight: '800' },
    note: { marginTop: 12, color: colors.brandDark, fontSize: 14, fontWeight: '700' },
    showing: { marginTop: 16, color: colors.navy, fontSize: 15, fontWeight: '800' },
    choice: { marginTop: 8, flexDirection: 'row', alignItems: 'flex-start', gap: 8 },
    choiceMark: { color: colors.brandDark, fontSize: 16 },
    quantity: { marginTop: 14, flexDirection: 'row', alignItems: 'center', gap: 8 },
    step: { width: 44, height: 44, borderRadius: 12, borderWidth: 1, borderColor: colors.line, alignItems: 'center', justifyContent: 'center' },
    stepText: { color: colors.navy, fontSize: 20, fontWeight: '700' },
    qty: { minWidth: 32, color: colors.navy, fontSize: 18, fontWeight: '800', textAlign: 'center' },
    primary: { marginTop: 16, minHeight: 48, borderRadius: 12, backgroundColor: colors.brand, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    navy: { marginTop: 12, minHeight: 44, borderRadius: 12, backgroundColor: colors.navy, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    primaryText: { color: colors.white, fontSize: 16, fontWeight: '800' },
    link: { marginTop: 12, color: colors.brandDark, fontSize: 15, fontWeight: '800' },
    error: { marginTop: 12, backgroundColor: colors.dangerBg, color: colors.danger, borderRadius: 12, padding: 12, overflow: 'hidden' },
    saved: { marginTop: 12, color: '#17663A', fontSize: 14, fontWeight: '700' },
    field: { marginTop: 8, minHeight: 46, borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, color: colors.navy, fontSize: 16, paddingHorizontal: 12 },
    area: { minHeight: 100, textAlignVertical: 'top', paddingTop: 12 },
    label: { marginTop: 12, color: colors.navy, fontSize: 13, fontWeight: '800' },
    wrap: { marginTop: 12, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    chip: { borderRadius: 999, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, paddingHorizontal: 12, paddingVertical: 8 },
    chipOn: { borderColor: colors.brand, backgroundColor: '#FFF1EC' },
    chipText: { color: colors.navy, fontSize: 13, fontWeight: '700' },
    popupWrap: { position: 'absolute', top: 0, right: 0, bottom: 0, left: 0, zIndex: 80, elevation: 24, justifyContent: 'center', backgroundColor: 'rgba(21,32,41,0.45)', padding: 16 },
    popupCard: { borderRadius: 16, backgroundColor: colors.white, padding: 20 },
    popupOffer: { marginTop: 12, borderRadius: 12, backgroundColor: '#F2F2F2', padding: 12 },
    popupCode: { color: colors.navy, fontSize: 22, fontWeight: '800', textAlign: 'center' },
    flagRow: { marginTop: 8, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    flagLine: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    publish: { marginTop: 10, alignSelf: 'flex-start', overflow: 'hidden', borderRadius: 10, backgroundColor: colors.brandDark, color: colors.white, fontSize: 11, fontWeight: '800', letterSpacing: 0.4, textTransform: 'uppercase', paddingHorizontal: 12, lineHeight: 36 },
});
