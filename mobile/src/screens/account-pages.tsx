import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useEffect, useState } from 'react';
import { Linking, Pressable, Text, TextInput, View, StyleSheet } from 'react-native';

import { apiRequest, errorText, hasErrors } from '../api/client';
import { money } from '../api/home';
import { apiUrl } from '../config';
import { useAuth } from '../context/AuthContext';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';

const accountLinks: [string, string][] = [
    ['Dashboard', '/customer/dashboard'],
    ['Wallet', '/customer/wallet'],
    ['Invoices', '/customer/invoices'],
    ['Service orders', '/orders'],
    ['Available coupons', '/customer/coupons'],
    ['Projects', '/customer/projects'],
    ['Addresses', '/customer/addresses'],
    ['Notifications', '/customer/notifications'],
    ['Payment methods', '/customer/payment-methods'],
    ['Account details', '/customer/profile'],
    ['Security settings', '/customer/profile#security-settings'],
];

const addressFields: [string, string, boolean][] = [
    ['label', 'Label', true],
    ['full_name', 'Full name', true],
    ['company', 'Company', false],
    ['address_line_1', 'Address line 1', true],
    ['address_line_2', 'Address line 2', false],
    ['city', 'City', true],
    ['state', 'State / Province', false],
    ['postal_code', 'Postal code', true],
    ['country', 'Country code', true],
];

const accountViews = new Set([
    'customer.dashboard',
    'customer.profile',
    'customer.wallet',
    'customer.payment-methods',
    'customer.payment-history',
    'customer.invoices',
    'customer.addresses',
    'customer.saved-websites',
    'customer.coupons',
    'customer.notifications',
    'customer.projects',
    'orders.order-history',
    'orders.order-detail',
]);

export function isAccountView(view: string): boolean {
    return accountViews.has(view);
}

export function isAccountPath(path: string): boolean {
    const base = path.split('?')[0].split('#')[0];

    return base === '/orders' || base.startsWith('/orders/') || base.startsWith('/customer/');
}

type Open = (path: string) => void;

export function openDashboard(navigation: NativeStackNavigationProp<RootStack>): void {
    navigation.reset({
        index: 1,
        routes: [
            { name: 'Home' },
            { name: 'MenuPage', params: { path: '/customer/dashboard' } },
        ],
    });
}

export function AccountFlow({ view, data, flash, errors, payments, path, onOpen, onReload }: {
    view: string;
    data: Record<string, unknown>;
    flash?: Record<string, unknown>;
    errors?: Record<string, unknown>;
    payments?: Record<string, unknown>;
    path: string;
    onOpen: Open;
    onReload: () => void;
}) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const { logout } = useAuth();
    const [notice, setNotice] = useState('');
    const [ok, setOk] = useState('');

    function go(next: string) {
        const target = next.split('#')[0];
        const service = target.match(/^\/services\/([^/?]+)/);

        if (target === '/services' || target.startsWith('/services?')) {
            navigation.navigate('Services');

            return;
        }

        if (service) {
            navigation.navigate('Service', { slug: decodeURIComponent(service[1]) });

            return;
        }

        if (target === '/login' || target.startsWith('/login?')) {
            navigation.navigate('Login');

            return;
        }

        onOpen(target);
    }

    async function send(action: string, method: string, body: Record<string, unknown>) {
        setNotice('');
        setOk('');
        const result = await apiRequest(action, { method, json: true, body: method === 'DELETE' ? undefined : body });
        const redirect = typeof result.body.redirect === 'string' ? result.body.redirect : '';

        if (result.status >= 400 || hasErrors(result.body)) {
            setNotice(errorText(result.body));

            return;
        }

        if (redirect !== '') {
            if (result.body.external === true) {
                await Linking.openURL(redirect);

                return;
            }

            const target = redirect.split('#')[0];

            if (target.split('?')[0] === path.split('?')[0].split('#')[0]) {
                onReload();

                return;
            }

            go(target);

            return;
        }

        setOk('Saved.');
        onReload();
    }

    async function signOut() {
        await logout();
        navigation.navigate('Login');
    }

    return (
        <View>
            <Text style={styles.crumbs}><Text style={styles.crumbLink} onPress={() => go('/customer/dashboard')}>Home</Text>  ›  My account</Text>
            <Text style={styles.h1}>My account</Text>
            <View style={styles.nav}>
                {accountLinks.map(([label, href]) => {
                    const current = isCurrent(label, href, path);

                    return (
                        <Pressable key={label} onPress={() => go(href)} style={current ? styles.navOn : undefined}>
                            <Text style={styles.navText}>{label}</Text>
                        </Pressable>
                    );
                })}
            </View>
            <Pressable onPress={() => { void signOut(); }}><Text style={styles.link}>Logout</Text></Pressable>
            <Notice flash={flash} errors={errors} extra={notice} ok={ok} />
            <CouponPopup flash={flash} />
            {view === 'customer.dashboard' ? <Dashboard data={data} onOpen={go} send={send} /> : null}
            {view === 'customer.profile' ? <Profile data={data} send={send} /> : null}
            {view === 'customer.wallet' ? <Wallet data={data} payments={payments} path={path} onOpen={go} send={send} /> : null}
            {view === 'customer.payment-methods' ? <PaymentMethods data={data} send={send} /> : null}
            {view === 'customer.payment-history' ? <PaymentHistory data={data} onOpen={go} /> : null}
            {view === 'customer.invoices' ? <Invoices data={data} onOpen={go} /> : null}
            {view === 'customer.addresses' ? <Addresses data={data} send={send} /> : null}
            {view === 'customer.saved-websites' ? <SavedWebsites data={data} send={send} /> : null}
            {view === 'customer.coupons' ? <Coupons data={data} send={send} /> : null}
            {view === 'customer.notifications' ? <Notifications data={data} onOpen={go} send={send} /> : null}
            {view === 'customer.projects' ? <Projects data={data} onOpen={go} /> : null}
            {view === 'orders.order-history' ? <OrderHistory data={data} onOpen={go} /> : null}
            {view === 'orders.order-detail' ? <OrderDetail data={data} path={path} onOpen={go} /> : null}
        </View>
    );
}

function Dashboard({ data, onOpen, send }: { data: Record<string, unknown>; onOpen: Open; send: Send }) {
    const wallet = record(data.wallet);
    const orders = rows(data.orders);
    const projects = rows(data.projects);
    const coupons = rows(data.availableCoupons);
    const card = record(data.savedCard);
    const first = record(data.firstOrder);
    const services = rows(data.linkServices);

    return (
        <View>
            <Pressable style={styles.wash} onPress={() => onOpen('/services')}>
                <Text style={styles.body}>Compare guest posts, niche edits, contextual links and text links before you order.</Text>
            </Pressable>
            <View style={styles.rowBetween}>
                <Text style={styles.h2}>Link building services</Text>
                <Pressable style={styles.primary} onPress={() => onOpen('/publishers')}><Text style={styles.primaryText}>Search sites</Text></Pressable>
            </View>
            {services.map((service) => (
                <Pressable key={text(service.slug)} style={styles.card} onPress={() => onOpen(`/services/${text(service.slug)}`)}>
                    <Text style={styles.cardTitle}>{text(service.name)}</Text>
                    <Text style={styles.muted}>{text(service.line)}.{text(service.price) !== '' ? ` From ${text(service.price)}.` : ''}</Text>
                </Pressable>
            ))}
            {text(first.summary) !== '' ? (
                <View style={styles.card}>
                    <Text style={styles.kicker}>New customer offer</Text>
                    <Text style={styles.cardTitle}>{text(first.summary)} on your first order</Text>
                    <Text style={styles.muted}>It is applied automatically at checkout. You can use it once, after payment is confirmed.</Text>
                    <Pressable onPress={() => onOpen('/customer/coupons')}><Text style={styles.link}>View coupons</Text></Pressable>
                </View>
            ) : null}
            {coupons.length > 0 ? (
                <View style={styles.card}>
                    <Text style={styles.cardTitle}>Available coupons</Text>
                    <Text style={styles.muted}>Use one on your next package or individual backlink. It is applied at checkout.</Text>
                    {coupons.map((coupon) => (
                        <View key={text(coupon.id, text(coupon.code))} style={styles.split}>
                            <View style={styles.flex}>
                                <Text style={styles.cardTitle}>{text(coupon.code)}</Text>
                                <Text style={styles.muted}>{text(coupon.discount_summary)} on a package or an individual backlink.</Text>
                                <Text style={styles.muted}>{text(coupon.saved_for_later_at) !== '' ? `Saved for later. Use it before ${day(coupon.expires_at)}.` : `Deadline ${day(coupon.expires_at)}.`}</Text>
                            </View>
                            <Pressable style={styles.primary} onPress={() => { void send(`/customer/coupons/${text(coupon.id)}/apply`, 'POST', {}); }}>
                                <Text style={styles.primaryText}>Use on next purchase</Text>
                            </Pressable>
                        </View>
                    ))}
                </View>
            ) : null}
            <View style={styles.card}>
                <Text style={styles.cardTitle}>Saved payment card</Text>
                <Text style={styles.muted}>{text(card.brand) !== '' ? `${text(card.brand)} ending in ${text(card.last_four)}` : 'No demo card saved yet.'}</Text>
                <Pressable onPress={() => onOpen('/customer/payment-methods')}><Text style={styles.link}>Manage payment methods</Text></Pressable>
            </View>
            <View style={styles.grid}>
                <Stat label="Available credits" value={count(wallet.available_balance)} note={`${count(wallet.reserved_balance)} reserved`} onPress={() => onOpen('/customer/wallet')} />
                <Stat label="Orders" value={count(data.orderCount)} note="View order progress" onPress={() => onOpen('/orders')} />
                <Stat label="Saved websites" value={count(data.savedWebsiteCount)} note="Your marketplace shortlist" onPress={() => onOpen('/customer/saved-websites')} />
                <Stat label="Unread notifications" value={count(data.unreadNotificationCount)} note="Order and account updates" onPress={() => onOpen('/customer/notifications')} />
            </View>
            <Text style={styles.h2}>Recent orders</Text>
            <Pressable onPress={() => onOpen('/orders')}><Text style={styles.link}>All orders</Text></Pressable>
            {orders.length === 0 ? <Text style={styles.muted}>No orders yet. Browse the marketplace to get started.</Text> : orders.map((order) => (
                <Pressable key={text(order.id)} style={styles.card} onPress={() => onOpen(`/orders/${text(order.id)}`)}>
                    <Text style={styles.cardTitle}>{text(order.order_number)}</Text>
                    <Text style={styles.muted}>{rows(order.items).map((item) => text(item.item_name)).filter(Boolean).join(', ')}</Text>
                    <Text style={styles.muted}>{day(order.created_at)} · {rows(order.items).reduce((sum, item) => sum + number(item.quantity), 0)} item(s)</Text>
                    <Text style={styles.price}>{money(order.total_amount)}</Text>
                    <Text style={styles.muted}>{orderState(order)}</Text>
                </Pressable>
            ))}
            <Text style={styles.h2}>Project progress</Text>
            {projects.length === 0 ? <Text style={styles.muted}>Your projects will appear here after your first order.</Text> : projects.map((project) => (
                <View key={text(project.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(project.name)}</Text>
                    <Text style={styles.badge}>{headline(text(project.status))}</Text>
                    <Text style={styles.muted}>{text(project.verification_status) === 'verified' ? 'Backlink verified' : 'Verification pending'}</Text>
                </View>
            ))}
        </View>
    );
}

function Profile({ data, send }: { data: Record<string, unknown>; send: Send }) {
    const customer = record(data.customer);
    const [name, setName] = useState(text(customer.name));
    const [email, setEmail] = useState(text(customer.email));
    const [phone, setPhone] = useState(text(customer.phone));
    const [current, setCurrent] = useState('');
    const [password, setPassword] = useState('');
    const [confirmation, setConfirmation] = useState('');

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Account details</Text>
            <View style={styles.card}>
                <Text style={styles.cardTitle}>Personal information</Text>
                <Field label="Full name" value={name} onChangeText={setName} />
                <Field label="Email" value={email} onChangeText={setEmail} keyboard="email-address" />
                <Field label="Phone" value={phone} onChangeText={setPhone} keyboard="phone-pad" />
                <Pressable style={styles.primary} onPress={() => { void send('/customer/profile', 'PATCH', { name, email, phone }); }}>
                    <Text style={styles.primaryText}>Save details</Text>
                </Pressable>
            </View>
            <View style={styles.card}>
                <Text style={styles.cardTitle}>Security settings</Text>
                <Field label="Current password" value={current} onChangeText={setCurrent} secure />
                <Field label="New password" value={password} onChangeText={setPassword} secure />
                <Field label="Confirm new password" value={confirmation} onChangeText={setConfirmation} secure />
                <Pressable style={styles.navy} onPress={() => { void send('/customer/password', 'PATCH', { current_password: current, password, password_confirmation: confirmation }); }}>
                    <Text style={styles.primaryText}>Update password</Text>
                </Pressable>
            </View>
        </View>
    );
}

function Wallet({ data, payments, path, onOpen, send }: { data: Record<string, unknown>; payments?: Record<string, unknown>; path: string; onOpen: Open; send: Send }) {
    const wallet = record(data.wallet);
    const packages = rows(data.packages);
    const transactions = rows(record(data.transactions).data ?? data.transactions);
    const demo = payments?.demo === true;
    const stripe = payments?.stripe === true;

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Credit wallet</Text>
            <Text style={styles.muted}>Credits are reserved at submission, charged after verified publication, and released when publishing fails.</Text>
            <Pressable onPress={() => onOpen('/customer/payment-methods')}><Text style={styles.link}>Payment methods</Text></Pressable>
            {path.includes('stripe=returned') ? <Text style={styles.note}>Credits appear in your wallet once Stripe confirms payment.</Text> : null}
            <View style={styles.grid}>
                <Stat label="Available credits" value={count(wallet.available_balance)} note="" onPress={() => undefined} />
                <Stat label="Reserved credits" value={count(wallet.reserved_balance)} note="" onPress={() => undefined} />
            </View>
            <Text style={styles.h2}>Credit packages</Text>
            {demo ? <Text style={styles.muted}>Dummy payment gateway. No money is charged. Use this until Stripe is connected.</Text> : null}
            {!stripe && !demo ? <Text style={styles.muted}>Stripe checkout is currently unavailable.</Text> : null}
            {packages.length === 0 ? <Text style={styles.muted}>No active credit packages.</Text> : packages.map((item) => (
                <View key={text(item.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(item.name)}</Text>
                    <Text style={styles.price}>{count(item.credits)} credits</Text>
                    <Text style={styles.price}>{money(item.price)}</Text>
                    {stripe ? <Pressable style={styles.primary} onPress={() => { void send(`/customer/wallet/packages/${text(item.id)}/purchase`, 'POST', { payment_method: 'stripe' }); }}><Text style={styles.primaryText}>Buy with Stripe</Text></Pressable> : null}
                    {demo ? <Pressable style={styles.primary} onPress={() => { void send(`/customer/wallet/packages/${text(item.id)}/purchase`, 'POST', {}); }}><Text style={styles.primaryText}>Buy with dummy gateway</Text></Pressable> : null}
                </View>
            ))}
            <Text style={styles.h2}>Credit transactions</Text>
            {transactions.length === 0 ? <Text style={styles.muted}>No credit activity yet.</Text> : transactions.map((transaction) => {
                const credit = ['purchase', 'refund', 'release'].includes(text(transaction.transaction_type));

                return (
                    <View key={text(transaction.id)} style={styles.card}>
                        <Text style={styles.muted}>{day(transaction.created_at)}</Text>
                        <Text style={styles.cardTitle}>{text(transaction.description)}</Text>
                        <Text style={styles.price}>{credit ? '+' : '-'}{count(transaction.amount)}</Text>
                    </View>
                );
            })}
            <Pager pager={record(data.transactions)} onOpen={onOpen} />
        </View>
    );
}

function PaymentMethods({ data, send }: { data: Record<string, unknown>; send: Send }) {
    const methods = rows(data.paymentMethods);
    const [brand, setBrand] = useState('Visa');
    const [lastFour, setLastFour] = useState('');
    const [isDefault, setIsDefault] = useState(false);

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Payment methods</Text>
            <Text style={styles.note}>Demo references only. The application never stores full card numbers or security codes.</Text>
            {methods.length === 0 ? <Text style={styles.muted}>No saved payment methods.</Text> : methods.map((method) => (
                <View key={text(method.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(method.label)}{method.is_default === true ? ' · Default' : ''}</Text>
                    <Text style={styles.muted}>Saved demo token</Text>
                    <Pressable onPress={() => { void send(`/customer/payment-methods/${text(method.id)}`, 'DELETE', {}); }}><Text style={styles.remove}>Remove</Text></Pressable>
                </View>
            ))}
            <View style={styles.card}>
                <Text style={styles.cardTitle}>Add demo method</Text>
                <Text style={styles.label}>Card brand</Text>
                <View style={styles.wrap}>
                    {['Visa', 'Mastercard', 'American Express'].map((option) => (
                        <Pressable key={option} style={[styles.chip, brand === option && styles.chipOn]} onPress={() => setBrand(option)}>
                            <Text style={styles.chipText}>{option}</Text>
                        </Pressable>
                    ))}
                </View>
                <Field label="Last four digits" value={lastFour} onChangeText={setLastFour} keyboard="number-pad" />
                <Pressable onPress={() => setIsDefault((value) => !value)}><Text style={styles.body}>{isDefault ? '☑' : '☐'} Set as default</Text></Pressable>
                <Pressable style={styles.primary} onPress={() => { void send('/customer/payment-methods', 'POST', { brand, last_four: lastFour, is_default: isDefault }); }}>
                    <Text style={styles.primaryText}>Save reference</Text>
                </Pressable>
            </View>
        </View>
    );
}

function PaymentHistory({ data, onOpen }: { data: Record<string, unknown>; onOpen: Open }) {
    const payments = record(data.payments);
    const list = rows(payments);

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Payment history</Text>
            {list.length === 0 ? <Text style={styles.muted}>No payment records yet.</Text> : list.map((payment) => (
                <View key={text(payment.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(payment.transaction_id)}</Text>
                    <Text style={styles.muted}>{text(record(payment.order).order_number, text(record(payment.credit_package).name, 'Account payment'))}</Text>
                    <Text style={styles.muted}>{day(payment.created_at)} · {text(payment.payment_method)} · {headline(text(payment.status))}</Text>
                    <Text style={styles.price}>{money(payment.amount)}</Text>
                </View>
            ))}
            <Pager pager={payments} onOpen={onOpen} />
        </View>
    );
}

function Invoices({ data, onOpen }: { data: Record<string, unknown>; onOpen: Open }) {
    const invoices = record(data.invoices);
    const list = rows(invoices);

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Invoices</Text>
            {list.length === 0 ? <Text style={styles.muted}>Invoices will be available after successful payment.</Text> : list.map((invoice) => (
                <View key={text(invoice.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(invoice.invoice_number)}</Text>
                    <Text style={styles.muted}>Order {text(record(invoice.order).order_number)} · {day(invoice.issued_at)}</Text>
                    <Text style={styles.price}>{money(invoice.total_amount)}</Text>
                    <Pressable onPress={() => { void Linking.openURL(`${apiUrl}/customer/invoices/${text(invoice.id)}/download`); }}><Text style={styles.link}>Download</Text></Pressable>
                </View>
            ))}
            <Pager pager={invoices} onOpen={onOpen} />
        </View>
    );
}

function Addresses({ data, send }: { data: Record<string, unknown>; send: Send }) {
    const addresses = rows(data.addresses);
    const [fresh, setFresh] = useState<Record<string, string>>({});
    const [isDefault, setIsDefault] = useState(false);

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Billing addresses</Text>
            {addresses.length === 0 ? <Text style={styles.muted}>No billing addresses saved.</Text> : addresses.map((address) => (
                <AddressCard key={text(address.id)} address={address} send={send} />
            ))}
            <View style={styles.card}>
                <Text style={styles.cardTitle}>Add an address</Text>
                {addressFields.map(([name, label]) => <Field key={name} label={label} value={fresh[name] ?? ''} onChangeText={(value) => setFresh((current) => ({ ...current, [name]: value }))} />)}
                <Pressable onPress={() => setIsDefault((value) => !value)}><Text style={styles.body}>{isDefault ? '☑' : '☐'} Set as default</Text></Pressable>
                <Pressable style={styles.primary} onPress={() => { void send('/customer/addresses', 'POST', { ...fresh, is_default: isDefault }); }}>
                    <Text style={styles.primaryText}>Save address</Text>
                </Pressable>
            </View>
        </View>
    );
}

function AddressCard({ address, send }: { address: Record<string, unknown>; send: Send }) {
    const [editing, setEditing] = useState(false);
    const [fields, setFields] = useState<Record<string, string>>(() => Object.fromEntries(addressFields.map(([name]) => [name, text(address[name])])));
    const [isDefault, setIsDefault] = useState(address.is_default === true);

    return (
        <View style={styles.card}>
            <Text style={styles.cardTitle}>{text(address.label)}{address.is_default === true ? ' · Default' : ''}</Text>
            <Text style={styles.muted}>{text(address.full_name)}</Text>
            <Text style={styles.muted}>{text(address.address_line_1)} {text(address.address_line_2)}</Text>
            <Text style={styles.muted}>{text(address.city)} {text(address.state)} {text(address.postal_code)}</Text>
            <Text style={styles.muted}>{text(address.country)}</Text>
            <Pressable onPress={() => { void send(`/customer/addresses/${text(address.id)}`, 'DELETE', {}); }}><Text style={styles.remove}>Remove</Text></Pressable>
            <Pressable onPress={() => setEditing((value) => !value)}><Text style={styles.link}>Edit address</Text></Pressable>
            {editing ? (
                <View>
                    {addressFields.map(([name, label]) => <Field key={name} label={label} value={fields[name] ?? ''} onChangeText={(value) => setFields((current) => ({ ...current, [name]: value }))} />)}
                    <Pressable onPress={() => setIsDefault((value) => !value)}><Text style={styles.body}>{isDefault ? '☑' : '☐'} Set as default</Text></Pressable>
                    <Pressable style={styles.navy} onPress={() => { void send(`/customer/addresses/${text(address.id)}`, 'PATCH', { ...fields, is_default: isDefault }); }}>
                        <Text style={styles.primaryText}>Save changes</Text>
                    </Pressable>
                </View>
            ) : null}
        </View>
    );
}

function SavedWebsites({ data, send }: { data: Record<string, unknown>; send: Send }) {
    const websites = rows(data.websites);
    const [form, setForm] = useState<Record<string, string>>({});

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Saved websites</Text>
            {websites.length === 0 ? <Text style={styles.muted}>Save publisher sites here to compare them later.</Text> : websites.map((website) => (
                <View key={text(website.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(website.website_name)}</Text>
                    <Text style={styles.link} onPress={() => { if (text(website.url) !== '') { void Linking.openURL(text(website.url)); } }}>{text(website.domain)}</Text>
                    <Text style={styles.muted}>DR {text(website.domain_rating, '—')} · {count(website.monthly_traffic)} visits/mo · {text(website.price) !== '' ? money(website.price) : 'Price on request'}</Text>
                    <Pressable onPress={() => { void send(`/customer/saved-websites/${text(website.id)}`, 'DELETE', {}); }}><Text style={styles.remove}>Remove</Text></Pressable>
                </View>
            ))}
            <View style={styles.card}>
                <Text style={styles.cardTitle}>Save a website</Text>
                <Field label="Website name" value={form.website_name ?? ''} onChangeText={(value) => setForm((current) => ({ ...current, website_name: value }))} />
                <Field label="Domain" value={form.domain ?? ''} onChangeText={(value) => setForm((current) => ({ ...current, domain: value }))} />
                <Field label="Website URL" value={form.url ?? ''} onChangeText={(value) => setForm((current) => ({ ...current, url: value }))} />
                <Field label="DR" value={form.domain_rating ?? ''} onChangeText={(value) => setForm((current) => ({ ...current, domain_rating: value }))} keyboard="number-pad" />
                <Field label="Price" value={form.price ?? ''} onChangeText={(value) => setForm((current) => ({ ...current, price: value }))} keyboard="decimal-pad" />
                <Field label="Monthly traffic" value={form.monthly_traffic ?? ''} onChangeText={(value) => setForm((current) => ({ ...current, monthly_traffic: value }))} keyboard="number-pad" />
                <Pressable style={styles.primary} onPress={() => {
                    const body: Record<string, unknown> = { website_name: form.website_name ?? '', domain: form.domain ?? '', url: form.url ?? '' };

                    if (form.domain_rating) {
                        body.domain_rating = Number(form.domain_rating);
                    }

                    if (form.monthly_traffic) {
                        body.monthly_traffic = Number(form.monthly_traffic);
                    }

                    if (form.price) {
                        body.price = Number(form.price);
                    }

                    void send('/customer/saved-websites', 'POST', body);
                }}><Text style={styles.primaryText}>Save website</Text></Pressable>
            </View>
        </View>
    );
}

function Coupons({ data, send }: { data: Record<string, unknown>; send: Send }) {
    const first = record(data.firstOrder);
    const redeemed = record(data.redeemedFirstOrder);
    const groups: [string, Record<string, unknown>[], string][] = [
        ['Available coupons', rows(data.available), 'Available'],
        ['Used coupons', rows(data.used), 'Used'],
        ['Expired coupons', rows(data.expired), 'Expired'],
    ];

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Coupons</Text>
            {text(first.summary) !== '' ? (
                <View style={styles.card}>
                    <Text style={styles.kicker}>First order</Text>
                    <Text style={styles.cardTitle}>{text(first.summary)} on your first purchase</Text>
                    <Text style={styles.muted}>This discount is applied automatically at checkout. No code is required.{number(first.minimum_order_amount) > 0 ? ` Minimum purchase ${money(first.minimum_order_amount)}.` : ''} It can be used once, and only after payment succeeds.</Text>
                </View>
            ) : text(redeemed.discount_amount) !== '' ? (
                <View style={styles.card}>
                    <Text style={styles.cardTitle}>First-order discount used</Text>
                    <Text style={styles.muted}>You saved {money(redeemed.discount_amount)} on your first paid order.</Text>
                </View>
            ) : null}
            {groups.map(([heading, group, badge]) => (
                <View key={heading} style={styles.card}>
                    <Text style={styles.cardTitle}>{heading}</Text>
                    {group.length === 0 ? <Text style={styles.muted}>None in this list.</Text> : group.map((coupon) => (
                        <View key={text(coupon.id, text(coupon.code))} style={styles.split}>
                            <View style={styles.flex}>
                                <Text style={styles.cardTitle}>{text(coupon.code)}</Text>
                                <Text style={styles.muted}>{text(coupon.discount_summary)} · {headline(text(coupon.type, 'promotional'))}</Text>
                                <Text style={styles.muted}>{text(coupon.package_label)} · Minimum {money(coupon.minimum_order_amount)}</Text>
                                <Text style={styles.muted}>{text(coupon.expires_at) !== '' ? `Expires ${day(coupon.expires_at)}` : 'No expiry date'}</Text>
                            </View>
                            <Text style={styles.badge}>{badge}</Text>
                            {heading === 'Available coupons' && text(coupon.type) === 'order_reward' ? (
                                <View>
                                    <Pressable style={styles.primary} onPress={() => { void send(`/customer/coupons/${text(coupon.id)}/apply`, 'POST', {}); }}><Text style={styles.primaryText}>Apply</Text></Pressable>
                                    {text(coupon.saved_for_later_at) === '' ? <Pressable style={styles.navy} onPress={() => { void send(`/customer/coupons/${text(coupon.id)}/save`, 'POST', {}); }}><Text style={styles.primaryText}>Save for later</Text></Pressable> : null}
                                </View>
                            ) : null}
                        </View>
                    ))}
                </View>
            ))}
        </View>
    );
}

function Notifications({ data, onOpen, send }: { data: Record<string, unknown>; onOpen: Open; send: Send }) {
    const notifications = record(data.notifications);
    const list = rows(notifications);

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Notifications</Text>
            {list.length === 0 ? <Text style={styles.muted}>You’re all caught up.</Text> : list.map((notification) => {
                const payload = noteData(notification);

                return (
                    <View key={text(notification.id)} style={styles.card}>
                        <Text style={styles.cardTitle}>{text(payload.title, 'Account update')}{text(notification.read_at) === '' ? ' · Unread' : ''}</Text>
                        <Text style={styles.muted}>{text(payload.message)}</Text>
                        <Text style={styles.muted}>{ago(notification.created_at)}</Text>
                        {text(notification.read_at) === '' ? <Pressable style={styles.navy} onPress={() => { void send(`/customer/notifications/${text(notification.id)}/read`, 'PATCH', {}); }}><Text style={styles.primaryText}>Mark read</Text></Pressable> : null}
                    </View>
                );
            })}
            <Pager pager={notifications} onOpen={onOpen} />
        </View>
    );
}

function Projects({ data, onOpen }: { data: Record<string, unknown>; onOpen: Open }) {
    const projects = record(data.projects);
    const list = rows(projects);

    return (
        <View>
            <Text style={styles.kicker}>My account</Text>
            <Text style={styles.h2}>Projects</Text>
            <Text style={styles.muted}>Track each backlink order through publishing and verification.</Text>
            {list.length === 0 ? <Text style={styles.muted}>Projects are created when an order payment succeeds.</Text> : list.map((project) => (
                <View key={text(project.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(project.name)}</Text>
                    <Text style={styles.badge}>{headline(text(project.status))}</Text>
                    <Text style={styles.muted}>Order {text(record(project.order).order_number, 'Pending order link')}</Text>
                    <Text style={styles.muted}>Target URL {text(project.target_url, 'Not set')}</Text>
                    <Text style={styles.muted}>Published URL {text(project.published_url, 'Not published')}</Text>
                    <Text style={styles.muted}>Verification {headline(text(project.verification_status))}</Text>
                </View>
            ))}
            <Pager pager={projects} onOpen={onOpen} />
        </View>
    );
}

function OrderHistory({ data, onOpen }: { data: Record<string, unknown>; onOpen: Open }) {
    const orders = record(data.orders);
    const list = rows(orders);

    return (
        <View>
            <Text style={styles.h2}>Order history</Text>
            {list.length === 0 ? <Text style={styles.muted}>No orders yet.</Text> : list.map((order) => (
                <Pressable key={text(order.id)} style={styles.card} onPress={() => onOpen(`/orders/${text(order.id)}`)}>
                    <Text style={styles.cardTitle}>{text(order.order_number)}</Text>
                    <Text style={styles.muted}>{day(order.created_at)} · {rows(order.items).reduce((sum, item) => sum + number(item.quantity), 0)} items</Text>
                    <Text style={styles.price}>{money(order.total_amount)}</Text>
                    {number(order.discount_amount) > 0 ? <Text style={styles.muted}>-{money(order.discount_amount)}{text(order.coupon_code) !== '' ? ` · ${text(order.coupon_code)}` : ''}</Text> : null}
                    <Text style={styles.badge}>{headline(text(order.order_status))}</Text>
                </Pressable>
            ))}
            <Pager pager={orders} onOpen={onOpen} />
        </View>
    );
}

function OrderDetail({ data, path, onOpen }: { data: Record<string, unknown>; path: string; onOpen: Open }) {
    const order = record(data.order);
    const unpaid = ['pending', 'failed'].includes(text(order.payment_status));
    const invoice = record(order.invoice);

    return (
        <View>
            <Text style={styles.h2}>{text(order.order_number)}</Text>
            <Text style={styles.muted}>Placed {stamp(order.created_at)}</Text>
            <View style={styles.card}>
                <Text style={styles.muted}>Order status</Text>
                <Text style={styles.cardTitle}>{headline(text(order.order_status))}</Text>
                <Text style={styles.muted}>Payment status</Text>
                <Text style={styles.cardTitle}>{headline(text(order.payment_status))}</Text>
                <Text style={styles.price}>{money(order.total_amount)}</Text>
                {number(order.discount_amount) > 0 ? <Text style={styles.muted}>{money(order.discount_amount)} off{text(order.coupon_code) !== '' ? ` · ${text(order.coupon_code)}` : ''}</Text> : null}
            </View>
            {rows(order.items).map((item) => (
                <View key={text(item.id)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(item.item_name)}</Text>
                    <Text style={styles.price}>{money(number(item.unit_price) * number(item.quantity))}</Text>
                    <Text style={styles.muted}>Target URL {text(item.target_url)}</Text>
                    <Text style={styles.muted}>Anchor text {text(item.anchor_text)}</Text>
                    <Text style={styles.muted}>Article topic {text(item.article_topic)}</Text>
                    <Text style={styles.muted}>Requirements {text(item.requirements, 'None provided')}</Text>
                </View>
            ))}
            <Pressable style={styles.navy} onPress={() => onOpen('/orders')}><Text style={styles.primaryText}>Back to orders</Text></Pressable>
            {path.includes('stripe=returned') && text(order.payment_status) !== 'paid' ? <Text style={styles.note}>Waiting for Stripe to confirm payment. Refresh this page in a moment.</Text> : null}
            {text(order.order_status) === 'awaiting_approval' ? <Text style={styles.note}>Payment confirmed. Your order is awaiting admin approval.</Text> : null}
            {text(invoice.id) !== '' ? <Pressable onPress={() => { void Linking.openURL(`${apiUrl}/customer/invoices/${text(invoice.id)}/download`); }}><Text style={styles.link}>Download invoice</Text></Pressable> : null}
            {unpaid ? <Pressable style={styles.primary} onPress={() => onOpen(`/orders/${text(order.id)}/payment`)}><Text style={styles.primaryText}>{text(order.payment_status) === 'failed' ? 'Retry payment' : 'Complete payment'}</Text></Pressable> : null}
        </View>
    );
}

function Stat({ label, value, note, onPress }: { label: string; value: string; note: string; onPress: () => void }) {
    return (
        <Pressable style={styles.stat} onPress={onPress}>
            <Text style={styles.muted}>{label}</Text>
            <Text style={styles.statValue}>{value}</Text>
            {note !== '' ? <Text style={styles.muted}>{note}</Text> : null}
        </Pressable>
    );
}

function Field({ label, value, onChangeText, secure = false, keyboard = 'default' }: {
    label: string;
    value: string;
    onChangeText: (value: string) => void;
    secure?: boolean;
    keyboard?: 'default' | 'email-address' | 'phone-pad' | 'number-pad' | 'decimal-pad';
}) {
    return (
        <View>
            <Text style={styles.label}>{label}</Text>
            <TextInput style={styles.input} value={value} onChangeText={onChangeText} secureTextEntry={secure} keyboardType={keyboard} autoCapitalize="none" placeholderTextColor="#8B939C" />
        </View>
    );
}

function Notice({ flash, errors, extra, ok }: { flash?: Record<string, unknown>; errors?: Record<string, unknown>; extra: string; ok: string }) {
    const success = text(flash?.success) || text(flash?.status) || ok;
    const error = text(flash?.error) || firstError(errors) || extra;

    return (
        <View>
            {success !== '' ? <Text style={styles.success}>{success}</Text> : null}
            {error !== '' ? <Text style={styles.error}>{error}</Text> : null}
        </View>
    );
}

function CouponPopup({ flash }: { flash?: Record<string, unknown> }) {
    const popup = record(flash?.saved_coupon_popup);
    const code = text(popup.code);
    const [open, setOpen] = useState(code !== '');

    useEffect(() => {
        setOpen(code !== '');
    }, [code]);

    if (!open || code === '') {
        return null;
    }

    return (
        <View style={styles.card}>
            <Text style={styles.cardTitle}>Coupon saved for later</Text>
            <Text style={styles.price}>{code}</Text>
            <Text style={styles.muted}>{text(popup.summary)}</Text>
            <Text style={styles.muted}>Use it before {text(popup.deadline, 'the listed date')}.</Text>
            <Pressable onPress={() => setOpen(false)}><Text style={styles.link}>Close</Text></Pressable>
        </View>
    );
}

function Pager({ pager, onOpen }: { pager: Record<string, unknown>; onOpen: Open }) {
    const links = Array.isArray(pager.links) ? pager.links : [];

    if (links.length < 4 && number(pager.last_page || 1) < 2) {
        return null;
    }

    return (
        <View style={styles.wrap}>
            {links.map((link, index) => {
                const item = record(link);
                const label = plain(text(item.label));
                const name = label.toLowerCase().includes('previous') ? 'Previous' : label.toLowerCase().includes('next') ? 'Next' : label;

                if (text(item.url) === '') {
                    return <Text key={`${name}-${index}`} style={styles.muted}>{name}</Text>;
                }

                return <Pressable key={`${name}-${index}`} onPress={() => onOpen(text(item.url))}><Text style={styles.link}>{name}</Text></Pressable>;
            })}
        </View>
    );
}

type Send = (action: string, method: string, body: Record<string, unknown>) => Promise<void>;

function isCurrent(label: string, href: string, path: string): boolean {
    if (label === 'Security settings') {
        return false;
    }

    const base = path.split('?')[0].split('#')[0];

    if (href === '/orders') {
        return base === '/orders' || /^\/orders\/[^/]+$/.test(base) || /^\/orders\/[^/]+\/(payment|thanks)$/.test(base);
    }

    return base === href;
}

function rows(value: unknown): Record<string, unknown>[] {
    if (Array.isArray(value)) {
        return value.filter((item): item is Record<string, unknown> => Boolean(item) && typeof item === 'object' && !Array.isArray(item));
    }

    if (value && typeof value === 'object' && Array.isArray((value as { data?: unknown }).data)) {
        return rows((value as { data: unknown }).data);
    }

    return [];
}

function record(value: unknown): Record<string, unknown> {
    return value && typeof value === 'object' && !Array.isArray(value) ? value as Record<string, unknown> : {};
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

function count(value: unknown): string {
    return Math.round(number(value)).toLocaleString('en-US');
}

function headline(value: string): string {
    return value.replace(/[_-]+/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function day(value: unknown): string {
    const date = new Date(text(value));

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function stamp(value: unknown): string {
    const date = new Date(text(value));

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function ago(value: unknown): string {
    const date = new Date(text(value));

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const seconds = Math.round((Date.now() - date.getTime()) / 1000);
    const steps: [number, string][] = [[31536000, 'year'], [2592000, 'month'], [86400, 'day'], [3600, 'hour'], [60, 'minute']];

    for (const [size, label] of steps) {
        const amount = Math.floor(seconds / size);

        if (amount >= 1) {
            return `${amount} ${label}${amount === 1 ? '' : 's'} ago`;
        }
    }

    return 'just now';
}

function noteData(notification: Record<string, unknown>): Record<string, unknown> {
    if (typeof notification.data === 'string') {
        try {
            return record(JSON.parse(notification.data) as unknown);
        } catch {
            return {};
        }
    }

    return record(notification.data);
}

function plain(value: string): string {
    return value.replace(/<[^>]+>/g, '').replace('&laquo;', '').replace('&raquo;', '').trim();
}

function firstError(errors: Record<string, unknown> | undefined): string {
    if (!errors) {
        return '';
    }

    for (const value of Object.values(errors)) {
        if (Array.isArray(value) && typeof value[0] === 'string') {
            return value[0];
        }

        if (typeof value === 'string') {
            return value;
        }
    }

    return '';
}

function orderState(order: Record<string, unknown>): string {
    if (text(order.payment_status) !== 'paid') {
        return 'Awaiting payment';
    }

    if (text(order.order_status) === 'awaiting_approval') {
        return 'Awaiting approval';
    }

    return headline(text(order.order_status));
}

const styles = StyleSheet.create({
    crumbs: { color: colors.muted, fontSize: 12, fontWeight: '700' },
    crumbLink: { color: colors.brandDark, fontSize: 12, fontWeight: '700' },
    h1: { marginTop: 12, color: colors.black, fontSize: 30, fontWeight: '800' },
    h2: { marginTop: 18, color: colors.navy, fontSize: 22, fontWeight: '800' },
    kicker: { marginTop: 16, color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    nav: { marginTop: 12, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    navOn: { borderBottomWidth: 2, borderBottomColor: colors.navy },
    navText: { color: colors.brandDark, fontSize: 14, fontWeight: '700', paddingVertical: 8 },
    body: { color: colors.navy, fontSize: 14, lineHeight: 20, fontWeight: '600' },
    wash: { marginTop: 16, borderRadius: 12, backgroundColor: '#FFF1EC', padding: 14 },
    rowBetween: { marginTop: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
    card: { marginTop: 12, borderRadius: 16, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 14 },
    cardTitle: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    muted: { marginTop: 4, color: colors.muted, fontSize: 13, lineHeight: 18 },
    price: { marginTop: 6, color: colors.navy, fontSize: 18, fontWeight: '800' },
    link: { marginTop: 8, color: colors.brandDark, fontSize: 14, fontWeight: '800' },
    primary: { marginTop: 12, minHeight: 44, borderRadius: 12, backgroundColor: colors.brand, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 14 },
    navy: { marginTop: 12, minHeight: 44, borderRadius: 12, backgroundColor: colors.navy, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 14 },
    primaryText: { color: colors.white, fontSize: 14, fontWeight: '800' },
    badge: { marginTop: 8, alignSelf: 'flex-start', overflow: 'hidden', borderRadius: 999, backgroundColor: '#FFEDD5', color: '#9A3412', fontSize: 12, fontWeight: '800', paddingHorizontal: 10, paddingVertical: 4 },
    grid: { marginTop: 8, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    stat: { width: '48%', borderRadius: 16, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 12 },
    statValue: { marginTop: 6, color: colors.navy, fontSize: 26, fontWeight: '800' },
    split: { marginTop: 12, gap: 8 },
    flex: { flex: 1 },
    note: { marginTop: 12, borderRadius: 12, backgroundColor: '#FFF7ED', color: '#9A3412', padding: 12, overflow: 'hidden' },
    success: { marginTop: 12, borderRadius: 12, backgroundColor: colors.successBg, color: colors.success, padding: 12, overflow: 'hidden' },
    error: { marginTop: 12, borderRadius: 12, backgroundColor: colors.dangerBg, color: colors.danger, padding: 12, overflow: 'hidden' },
    label: { marginTop: 12, color: colors.navy, fontSize: 13, fontWeight: '700' },
    input: { marginTop: 6, minHeight: 46, borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, paddingHorizontal: 12, color: colors.navy, fontSize: 15 },
    wrap: { marginTop: 8, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    chip: { borderRadius: 999, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, paddingHorizontal: 12, paddingVertical: 8 },
    chipOn: { borderColor: colors.brand, backgroundColor: '#FFF1EC' },
    chipText: { color: colors.navy, fontSize: 13, fontWeight: '700' },
    remove: { marginTop: 8, color: '#B91C1C', fontSize: 14, fontWeight: '800' },
});
