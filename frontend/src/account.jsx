import { Children, useState } from 'react';
import { A, money } from './chrome';

const addressFields = [
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

const serviceStyles = {
    'guest-post': 'bg-orange-50 text-brand-dark',
    'niche-edit': 'bg-navy text-white',
    'contextual-link': 'bg-canvas text-navy',
    'text-link': 'bg-white text-navy ring-1 ring-navy/10',
};

export const accountScreens = {
    'customer.dashboard': DashboardScreen,
    'customer.profile': ProfileScreen,
    'customer.wallet': WalletScreen,
    'customer.payment-methods': PaymentMethodsScreen,
    'customer.payment-history': PaymentHistoryScreen,
    'customer.invoices': InvoicesScreen,
    'customer.addresses': AddressesScreen,
    'customer.saved-websites': SavedWebsitesScreen,
    'customer.coupons': CouponsScreen,
    'customer.notifications': NotificationsScreen,
    'customer.projects': ProjectsScreen,
    'customer.backlinks': BacklinksScreen,
    'customer.reports': ReportsScreen,
    'customer.websites': WebsitesScreen,
    'customer.services': CustomerServicesScreen,
    'orders.order-history': OrderHistoryScreen,
    'orders.order-detail': OrderDetailScreen,
    'orders.cart': OrderCartScreen,
    'orders.checkout': OrderCheckoutScreen,
};

function DashboardScreen({ page, send }) {
    const data = page.data || {};
    const wallet = data.wallet || {};
    const orders = rows(data.orders);
    const projects = rows(data.projects);
    const coupons = rows(data.availableCoupons);
    const card = data.savedCard;

    return (
        <div className="space-y-4 sm:space-y-6">
            <section className="overflow-hidden rounded-2xl bg-navy text-white shadow-sm">
                <div className="h-1.5 bg-brand" />
                <div className="flex flex-col gap-6 p-6 sm:p-8 xl:flex-row xl:items-center xl:justify-between">
                    <div className="min-w-0">
                        <p className="text-xs font-medium tracking-widest text-brand uppercase">Customer dashboard</p>
                        <h1 className="mt-3 break-words text-2xl font-semibold sm:text-3xl">Welcome, {data.customer?.name || page.shell?.auth?.name || 'Customer'}</h1>
                        <p className="mt-3 text-sm leading-6 text-white/65">Manage your orders, credits, and publishing activity.</p>
                    </div>
                    <div className="flex shrink-0 flex-wrap gap-3">
                        <A href="/publishers" className="btn-primary">Browse publishers</A>
                        <A href="/cart" className="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/20 bg-white/5 px-5 text-sm font-semibold text-white hover:bg-white/10">View cart</A>
                    </div>
                </div>
            </section>
            <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <A href="/customer/wallet" className="panel min-w-0 p-3 sm:p-5"><p className="text-sm text-navy/55">Available credits</p><p className="mt-2 text-2xl font-semibold break-words tabular-nums sm:text-3xl">{count(wallet.available_balance)}</p><p className="mt-2 text-xs text-navy/55">{count(wallet.reserved_balance)} reserved</p></A>
                <A href="/orders" className="panel min-w-0 p-3 sm:p-5"><p className="text-sm text-navy/55">Orders</p><p className="mt-2 text-2xl font-semibold break-words tabular-nums sm:text-3xl">{count(data.orderCount)}</p><p className="mt-2 text-xs text-navy/55">View order progress</p></A>
                <A href="/customer/saved-websites" className="panel min-w-0 p-3 sm:p-5"><p className="text-sm text-navy/55">Saved websites</p><p className="mt-2 text-2xl font-semibold break-words tabular-nums sm:text-3xl">{count(data.savedWebsiteCount)}</p><p className="mt-2 text-xs text-navy/55">Your marketplace shortlist</p></A>
                <A href="/customer/notifications" className="panel min-w-0 p-3 sm:p-5"><p className="text-sm text-navy/55">Unread notifications</p><p className="mt-2 text-2xl font-semibold break-words tabular-nums sm:text-3xl">{count(data.unreadNotificationCount)}</p><p className="mt-2 text-xs text-navy/55">Order and account updates</p></A>
            </div>

            {data.firstOrder ? (
                <section className="panel p-4 sm:p-5">
                    <p className="eyebrow text-brand">New customer offer</p>
                    <h2 className="mt-2 text-lg font-semibold">{data.firstOrder.summary} on your first order</h2>
                    <p className="mt-1 text-sm text-navy/65">It is applied automatically at checkout. You can use it once, after payment is confirmed.</p>
                    <A className="mt-3 inline-flex text-sm font-semibold text-brand-dark hover:underline" href="/customer/coupons">View coupons</A>
                </section>
            ) : null}
            {coupons.length > 0 ? (
                <section className="panel overflow-hidden">
                    <div className="border-b border-navy/10 px-5 py-4">
                        <h2 className="font-semibold">Available coupons</h2>
                        <p className="mt-1 text-sm text-navy/65">Use one on your next package or individual backlink. It is applied at checkout.</p>
                    </div>
                    <div className="divide-y divide-navy/10">
                        {coupons.map((coupon) => (
                            <article key={coupon.id || coupon.code} className="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                                <RewardCopy coupon={coupon} />
                                <PostForm action={`/customer/coupons/${coupon.id}/apply`} send={send}>
                                    <button className="btn-primary" type="submit">Use on next purchase</button>
                                </PostForm>
                            </article>
                        ))}
                    </div>
                </section>
            ) : null}
            <section className="panel flex flex-col items-start justify-between gap-2 p-4 sm:flex-row sm:items-center sm:gap-4 sm:p-5">
                <div>
                    <h2 className="font-semibold">Saved payment card</h2>
                    <p className="mt-1 text-sm text-navy/65">{card ? `${card.brand} ending in ${card.last_four}` : 'No demo card saved yet.'}</p>
                </div>
                <A className="text-sm font-semibold text-brand-dark hover:underline" href="/customer/payment-methods">Manage payment methods</A>
            </section>
            <div className="grid gap-4 sm:gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                <section className="panel overflow-hidden">
                    <div className="flex flex-wrap items-center justify-between gap-2 border-b border-navy/10 px-5 py-4"><h2 className="font-semibold">Recent orders</h2><A className="text-sm font-semibold text-brand hover:underline" href="/orders">All orders</A></div>
                    <div className="divide-y divide-navy/10">
                        {orders.length === 0 ? <p className="p-8 text-center text-sm text-navy/55">No orders yet. Browse the marketplace to get started.</p> : orders.map((order) => (
                            <A key={order.id} href={`/orders/${order.id}`} className="flex flex-wrap items-center justify-between gap-3 px-5 py-4 hover:bg-canvas/70">
                                <div className="min-w-0">
                                    <p className="font-medium break-words">{order.order_number}</p>
                                    <p className="mt-1 text-sm break-words text-navy/70">{(order.items || []).map((item) => item.item_name).filter(Boolean).join(', ')}</p>
                                    <p className="mt-1 text-xs text-navy/55">{day(order.created_at)} · {(order.items || []).reduce((sum, item) => sum + Number(item.quantity || 0), 0)} item(s)</p>
                                </div>
                                <div className="sm:text-right"><p className="font-semibold">{money(order.total_amount)}</p><p className="mt-1 text-xs text-navy/55">{orderState(order)}</p></div>
                            </A>
                        ))}
                    </div>
                </section>
                <section className="panel overflow-hidden">
                    <div className="border-b border-navy/10 px-5 py-4"><h2 className="font-semibold">Project progress</h2></div>
                    <div className="divide-y divide-navy/10">
                        {projects.length === 0 ? <p className="p-8 text-center text-sm text-navy/55">Your projects will appear here after your first order.</p> : projects.map((project) => (
                            <div key={project.id} className="px-5 py-4">
                                <div className="flex flex-wrap items-start justify-between gap-2"><p className="font-medium break-words">{project.name}</p><span className="badge bg-orange-100 text-orange-800">{ucfirst(project.status)}</span></div>
                                <p className="mt-2 text-xs text-navy/55">{project.verification_status === 'verified' ? 'Backlink verified' : 'Verification pending'}</p>
                            </div>
                        ))}
                    </div>
                </section>
            </div>
            <ServiceCards services={data.linkServices || []} />
        </div>
    );
}

function ServiceCards({ services }) {
    return (
        <section aria-labelledby="guest-posting-title" className="panel space-y-4 p-4 sm:space-y-5 sm:p-6 lg:p-7">
            <A href="/services" className="flex items-center gap-3 rounded-xl bg-orange-50 px-4 py-3 text-sm font-semibold text-navy hover:bg-orange-100">
                <svg className="size-5 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" /></svg>
                <span>Compare guest posts, niche edits, contextual links and text links before you order.</span>
            </A>
            <div className="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 id="guest-posting-title" className="text-xl font-semibold text-navy">Link building services</h2>
                <A href="/publishers" className="btn-primary min-h-11 w-full sm:w-auto">Search sites</A>
            </div>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {services.map((service) => (
                    <A key={service.slug} href={`/services/${service.slug}`} className={`${serviceStyles[service.slug] || 'bg-white'} flex min-w-0 items-center justify-between gap-3 rounded-xl p-4 transition hover:ring-1 hover:ring-brand sm:min-h-36 sm:p-5`}>
                        <div className="min-w-0">
                            <h3 className="text-base font-bold">{service.name}</h3>
                            <p className="mt-2 text-sm leading-5">{service.line}.{service.price ? ` From ${service.price}.` : ''}</p>
                        </div>
                    </A>
                ))}
            </div>
        </section>
    );
}

function ProfileScreen({ page, send }) {
    const customer = page.data.customer || {};

    return (
        <div className="mx-auto max-w-4xl space-y-6">
            <div>
                <p className="eyebrow text-brand">My account</p>
                <h1 className="mt-2 text-3xl font-semibold">Account details</h1>
            </div>
            <Notices page={page} />
            <section className="panel p-6">
                <h2 className="text-lg font-semibold">Personal information</h2>
                <PostForm action="/customer/profile" method="PATCH" className="mt-5 grid gap-4 sm:grid-cols-2" send={send}>
                    <Field label="Full name" name="name" defaultValue={old(page, 'name', customer.name)} required />
                    <Field label="Email" name="email" type="email" defaultValue={old(page, 'email', customer.email)} required />
                    <Field label="Phone" name="phone" type="tel" defaultValue={old(page, 'phone', customer.phone)} />
                    <div className="flex items-end"><button className="btn-primary" type="submit">Save details</button></div>
                </PostForm>
            </section>
            <section id="security-settings" className="panel p-6">
                <h2 className="text-lg font-semibold">Security settings</h2>
                <PostForm action="/customer/password" method="PATCH" className="mt-5 grid gap-4 sm:grid-cols-2" send={send}>
                    <Field label="Current password" name="current_password" type="password" autoComplete="current-password" required className="sm:col-span-2" />
                    <Field label="New password" name="password" type="password" autoComplete="new-password" required />
                    <Field label="Confirm new password" name="password_confirmation" type="password" autoComplete="new-password" required />
                    <div><button className="btn-navy" type="submit">Update password</button></div>
                </PostForm>
            </section>
        </div>
    );
}

function WalletScreen({ page, send, search }) {
    const wallet = page.data.wallet || {};
    const packages = rows(page.data.packages);
    const transactions = page.data.transactions || {};
    const demo = Boolean(page.payments?.demo);
    const stripe = Boolean(page.payments?.stripe);

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div className="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p className="eyebrow text-brand">My account</p>
                    <h1 className="mt-2 text-3xl font-semibold">Credit wallet</h1>
                    <p className="mt-2 text-sm text-navy/60">Credits are reserved at submission, charged after verified publication, and released when publishing fails.</p>
                </div>
                <A className="text-sm font-semibold text-brand hover:underline" href="/customer/payment-methods">Payment methods</A>
            </div>
            <Notices page={page} />
            {search.includes('stripe=returned') ? <p className="rounded-xl bg-sky-50 p-4 text-sm text-sky-900" role="status">Credits appear in your wallet once Stripe confirms payment.</p> : null}
            <div className="grid gap-4 sm:grid-cols-2">
                <section className="panel p-6"><p className="text-sm text-navy/55">Available credits</p><p className="mt-2 text-4xl font-semibold">{count(wallet.available_balance)}</p></section>
                <section className="panel p-6"><p className="text-sm text-navy/55">Reserved credits</p><p className="mt-2 text-4xl font-semibold">{count(wallet.reserved_balance)}</p></section>
            </div>
            <section id="credit-packages" className="space-y-4">
                <h2 className="text-xl font-semibold">Credit packages</h2>
                {demo ? <p className="text-sm text-navy/65">Dummy payment gateway. No money is charged. Use this until Stripe is connected.</p> : null}
                {!stripe && !demo ? <p className="text-sm text-navy/65" role="status">Stripe checkout is currently unavailable.</p> : null}
                <div className="grid gap-4 md:grid-cols-3">
                    {packages.length === 0 ? <p className="text-sm text-navy/55">No active credit packages.</p> : packages.map((item) => (
                        <article key={item.id} className="panel p-5">
                            <div className="flex justify-between gap-3"><h3 className="font-semibold">{item.name}</h3><span className="font-sans text-xl font-semibold">{count(item.credits)}</span></div>
                            <p className="mt-1 text-xs text-navy/55">credits</p>
                            <p className="mt-4 text-2xl font-semibold">{money(item.price)}</p>
                            <div className="mt-4 space-y-2">
                                {stripe ? <PostForm action={`/customer/wallet/packages/${item.id}/purchase`} send={send}><input type="hidden" name="payment_method" value="stripe" /><button className="btn-primary w-full" type="submit">Buy with Stripe</button></PostForm> : null}
                                {demo ? <PostForm action={`/customer/wallet/packages/${item.id}/purchase`} send={send}><button className="btn-primary w-full" type="submit">Buy with dummy gateway</button></PostForm> : null}
                            </div>
                        </article>
                    ))}
                </div>
            </section>
            <section className="panel overflow-hidden">
                <div className="border-b border-navy/10 px-5 py-4"><h2 className="font-semibold">Credit transactions</h2></div>
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-navy text-white"><tr><th className="px-5 py-3">Date</th><th className="px-5 py-3">Activity</th><th className="px-5 py-3">Credits</th></tr></thead>
                        <tbody>
                            {rows(transactions).length === 0 ? <tr><td colSpan="3" className="px-5 py-8 text-center text-navy/55">No credit activity yet.</td></tr> : rows(transactions).map((transaction) => {
                                const credit = ['purchase', 'refund', 'release'].includes(transaction.transaction_type);

                                return (
                                    <tr key={transaction.id} className="border-b border-navy/10">
                                        <td className="px-5 py-3">{day(transaction.created_at)}</td>
                                        <td className="px-5 py-3">{transaction.description}</td>
                                        <td className={`px-5 py-3 font-semibold ${credit ? 'text-green-700' : 'text-navy'}`}>{credit ? '+' : '-'}{count(transaction.amount)}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                <div className="p-4"><Pager pager={transactions} /></div>
            </section>
        </div>
    );
}

function PaymentMethodsScreen({ page, send }) {
    const methods = rows(page.data.paymentMethods);

    return (
        <div className="mx-auto max-w-5xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">Payment methods</h1></div>
            <div className="rounded-xl border border-orange-300 bg-orange-50 p-4 text-sm text-orange-950">Demo references only. The application never stores full card numbers or security codes.</div>
            <Notices page={page} />
            <div className="grid gap-6 lg:grid-cols-[1fr_340px]">
                <section className="space-y-3">
                    {methods.length === 0 ? <div className="panel p-8 text-center text-sm text-navy/55">No saved payment methods.</div> : methods.map((method) => (
                        <article key={method.id} className="panel flex items-center justify-between gap-4 p-5">
                            <div>
                                <div className="flex items-center gap-2"><h2 className="font-semibold">{method.label}</h2>{method.is_default ? <span className="badge bg-green-100 text-green-800">Default</span> : null}</div>
                                <p className="mt-1 text-sm text-navy/55">Saved demo token</p>
                            </div>
                            <PostForm action={`/customer/payment-methods/${method.id}`} method="DELETE" send={send}><button className="rounded-xl px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">Remove</button></PostForm>
                        </article>
                    ))}
                </section>
                <section className="panel p-5">
                    <h2 className="font-semibold">Add demo method</h2>
                    <PostForm action="/customer/payment-methods" className="mt-4 space-y-4" send={send}>
                        <label className="block text-sm font-medium" htmlFor="brand">Card brand<select id="brand" name="brand" className="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm"><option>Visa</option><option>Mastercard</option><option>American Express</option></select></label>
                        <Field label="Last four digits" name="last_four" inputMode="numeric" pattern="[0-9]{4}" maxLength="4" required />
                        <label className="flex items-center gap-2 text-sm"><input name="is_default" type="checkbox" value="1" className="rounded border-navy/30 text-brand" />Set as default</label>
                        <button className="btn-primary w-full" type="submit">Save reference</button>
                    </PostForm>
                </section>
            </div>
        </div>
    );
}

function PaymentHistoryScreen({ page }) {
    const payments = page.data.payments || {};

    return (
        <AccountTable title="Payment history" pager={payments} empty="No payment records yet." headers={['Transaction', 'For', 'Date', 'Method', 'Amount', 'Status']}>
            {rows(payments).map((payment) => (
                <tr key={payment.id} className="border-b border-navy/10">
                    <td className="px-5 py-4 font-medium">{payment.transaction_id}</td>
                    <td className="px-5 py-4">{payment.order?.order_number || payment.credit_package?.name || 'Account payment'}</td>
                    <td className="px-5 py-4">{day(payment.created_at)}</td>
                    <td className="px-5 py-4">{payment.payment_method}</td>
                    <td className="px-5 py-4">{money(payment.amount)}</td>
                    <td className="px-5 py-4">{ucfirst(payment.status)}</td>
                </tr>
            ))}
        </AccountTable>
    );
}

function InvoicesScreen({ page }) {
    const invoices = page.data.invoices || {};

    return (
        <AccountTable title="Invoices" pager={invoices} empty="Invoices will be available after successful payment." headers={['Invoice', 'Order', 'Issued', 'Total', '']}>
            {rows(invoices).map((invoice) => (
                <tr key={invoice.id} className="border-b border-navy/10">
                    <td className="px-5 py-4 font-semibold">{invoice.invoice_number}</td>
                    <td className="px-5 py-4">{invoice.order?.order_number}</td>
                    <td className="px-5 py-4">{day(invoice.issued_at)}</td>
                    <td className="px-5 py-4">{money(invoice.total_amount)}</td>
                    <td className="px-5 py-4 text-right"><a className="font-semibold text-brand hover:underline" href={`/customer/invoices/${invoice.id}/download`}>Download</a></td>
                </tr>
            ))}
        </AccountTable>
    );
}

function AddressesScreen({ page, send }) {
    const addresses = rows(page.data.addresses);

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">Billing addresses</h1></div>
            <Notices page={page} />
            <div className="grid gap-6 lg:grid-cols-[1fr_380px]">
                <section className="space-y-3">
                    {addresses.length === 0 ? <div className="panel p-8 text-center text-sm text-navy/55">No billing addresses saved.</div> : addresses.map((address) => (
                        <article key={address.id} className="panel space-y-4 p-5">
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div className="flex items-center gap-2"><h2 className="font-semibold">{address.label}</h2>{address.is_default ? <span className="badge bg-green-100 text-green-800">Default</span> : null}</div>
                                    <p className="mt-2 text-sm">{address.full_name}<br />{address.address_line_1} {address.address_line_2}<br />{address.city} {address.state} {address.postal_code}<br />{address.country}</p>
                                </div>
                                <PostForm action={`/customer/addresses/${address.id}`} method="DELETE" send={send}><button className="rounded-xl px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">Remove</button></PostForm>
                            </div>
                            <details className="border-t border-navy/10 pt-3">
                                <summary className="cursor-pointer text-sm font-semibold text-brand">Edit address</summary>
                                <PostForm action={`/customer/addresses/${address.id}`} method="PATCH" className="mt-4 grid gap-3 sm:grid-cols-2" send={send}>
                                    {addressFields.map(([name, label, required]) => (
                                        <Field key={name} label={label} name={name} id={`${name}-${address.id}`} defaultValue={address[name] || ''} required={required} maxLength={name === 'country' ? 2 : undefined} className={name.startsWith('address_line') ? 'sm:col-span-2' : undefined} />
                                    ))}
                                    <label className="flex items-center gap-2 text-sm sm:col-span-2"><input name="is_default" type="checkbox" value="1" defaultChecked={Boolean(address.is_default)} className="rounded border-navy/30 text-brand" />Set as default</label>
                                    <div className="sm:col-span-2"><button className="btn-navy" type="submit">Save changes</button></div>
                                </PostForm>
                            </details>
                        </article>
                    ))}
                </section>
                <section className="panel h-fit p-5">
                    <h2 className="font-semibold">Add an address</h2>
                    <PostForm action="/customer/addresses" className="mt-4 space-y-3" send={send}>
                        {addressFields.map(([name, label, required]) => <Field key={name} label={label} name={name} id={`new-${name}`} defaultValue={old(page, name)} required={required} maxLength={name === 'country' ? 2 : undefined} placeholder={name === 'country' ? 'US' : undefined} />)}
                        <label className="flex items-center gap-2 text-sm"><input name="is_default" type="checkbox" value="1" className="rounded border-navy/30 text-brand" />Set as default</label>
                        <button className="btn-primary w-full" type="submit">Save address</button>
                    </PostForm>
                </section>
            </div>
        </div>
    );
}

function SavedWebsitesScreen({ page, send }) {
    const websites = rows(page.data.websites);

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">Saved websites</h1></div>
            <Notices page={page} />
            <div className="grid gap-6 lg:grid-cols-[1fr_340px]">
                <section className="grid gap-3">
                    {websites.length === 0 ? <div className="panel p-8 text-center text-sm text-navy/55">Save publisher sites here to compare them later.</div> : websites.map((website) => (
                        <article key={website.id} className="panel flex flex-wrap items-center justify-between gap-4 p-5">
                            <div>
                                <h2 className="font-semibold">{website.website_name}</h2>
                                <a className="mt-1 block text-sm text-brand hover:underline" href={website.url} target="_blank" rel="noopener noreferrer">{website.domain}</a>
                                <p className="mt-2 text-xs text-navy/55">DR {website.domain_rating ?? '—'} · {count(website.monthly_traffic)} visits/mo · {website.price ? money(website.price) : 'Price on request'}</p>
                            </div>
                            <PostForm action={`/customer/saved-websites/${website.id}`} method="DELETE" send={send}><button className="rounded-xl px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">Remove</button></PostForm>
                        </article>
                    ))}
                </section>
                <section className="panel p-5">
                    <h2 className="font-semibold">Save a website</h2>
                    <PostForm action="/customer/saved-websites" className="mt-4 space-y-3" send={send}>
                        <Field label="Website name" name="website_name" required />
                        <Field label="Domain" name="domain" placeholder="publisher.example" required />
                        <Field label="Website URL" name="url" type="url" placeholder="https://publisher.example" required />
                        <div className="grid grid-cols-2 gap-3"><Field label="DR" name="domain_rating" type="number" min="0" max="100" /><Field label="Price" name="price" type="number" min="0" step="0.01" /></div>
                        <Field label="Monthly traffic" name="monthly_traffic" type="number" min="0" />
                        <button className="btn-primary w-full" type="submit">Save website</button>
                    </PostForm>
                </section>
            </div>
        </div>
    );
}

function CouponsScreen({ page, send }) {
    const data = page.data || {};
    const groups = [
        ['Available coupons', rows(data.available), 'Available'],
        ['Used coupons', rows(data.used), 'Used'],
        ['Expired coupons', rows(data.expired), 'Expired'],
    ];

    return (
        <div className="mx-auto max-w-5xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">Coupons</h1></div>
            <Notices page={page} />
            {data.firstOrder ? (
                <section className="panel p-5">
                    <p className="eyebrow text-brand">First order</p>
                    <h2 className="mt-2 text-xl font-semibold">{data.firstOrder.summary} on your first purchase</h2>
                    <p className="mt-2 text-sm text-navy/70">This discount is applied automatically at checkout. No code is required. {Number(data.firstOrder.minimum_order_amount) > 0 ? `Minimum purchase ${money(data.firstOrder.minimum_order_amount)}. ` : ''}It can be used once, and only after payment succeeds.</p>
                </section>
            ) : data.redeemedFirstOrder ? (
                <section className="panel p-5">
                    <h2 className="font-semibold">First-order discount used</h2>
                    <p className="mt-2 text-sm text-navy/70">You saved {money(data.redeemedFirstOrder.discount_amount)} on your first paid order.</p>
                </section>
            ) : null}
            {groups.map(([heading, group, badge]) => (
                <section key={heading} className="panel overflow-hidden">
                    <div className="border-b border-navy/10 px-5 py-4"><h2 className="font-semibold">{heading}</h2></div>
                    <div className="divide-y divide-navy/10">
                        {group.length === 0 ? <p className="p-6 text-sm text-navy/55">None in this list.</p> : group.map((coupon) => (
                            <article key={coupon.id || coupon.code} className="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                                <div>
                                    <p className="font-semibold">{coupon.code}</p>
                                    <p className="mt-1 text-sm text-navy/70">{coupon.discount_summary} · {headline(coupon.type || 'promotional')}</p>
                                    <p className="mt-1 text-sm text-navy/70">{coupon.package_label} · Minimum {money(coupon.minimum_order_amount)}</p>
                                    <p className="mt-1 text-xs text-navy/55">{coupon.expires_at ? `Expires ${day(coupon.expires_at)}` : 'No expiry date'}</p>
                                </div>
                                <div className="flex flex-wrap items-center gap-3">
                                    <span className="badge bg-orange-100 text-orange-800">{badge}</span>
                                    {heading === 'Available coupons' && coupon.type === 'order_reward' ? <RewardCopy coupon={coupon} compact actions send={send} /> : null}
                                    {heading === 'Available coupons' && coupon.type !== 'order_reward' ? <CopyCode code={coupon.code} /> : null}
                                </div>
                            </article>
                        ))}
                    </div>
                </section>
            ))}
        </div>
    );
}

function NotificationsScreen({ page, send }) {
    const notifications = page.data.notifications || {};

    return (
        <div className="mx-auto max-w-5xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">Notifications</h1></div>
            <section className="panel divide-y divide-navy/10">
                {rows(notifications).length === 0 ? <p className="p-8 text-center text-sm text-navy/55">You’re all caught up.</p> : rows(notifications).map((notification) => {
                    const data = noteData(notification);

                    return (
                        <article key={notification.id} className="flex flex-wrap items-start justify-between gap-4 p-5">
                            <div>
                                <div className="flex items-center gap-2"><h2 className="font-semibold">{data.title || 'Account update'}</h2>{notification.read_at ? null : <span className="h-2 w-2 rounded-full bg-brand" aria-label="Unread" />}</div>
                                <p className="mt-1 text-sm text-navy/65">{data.message || ''}</p>
                                <p className="mt-2 text-xs text-navy/45">{ago(notification.created_at)}</p>
                            </div>
                            {notification.read_at ? null : <PostForm action={`/customer/notifications/${notification.id}/read`} method="PATCH" send={send}><button className="btn-navy" type="submit">Mark read</button></PostForm>}
                        </article>
                    );
                })}
                <div className="p-4"><Pager pager={notifications} /></div>
            </section>
        </div>
    );
}

function ProjectsScreen({ page }) {
    const projects = page.data.projects || {};

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">Projects</h1><p className="mt-2 text-sm text-navy/60">Track each backlink order through publishing and verification.</p></div>
            <section className="grid gap-4">
                {rows(projects).length === 0 ? <div className="panel p-8 text-center text-sm text-navy/55">Projects are created when an order payment succeeds.</div> : rows(projects).map((project) => (
                    <article key={project.id} className="panel p-5">
                        <div className="flex flex-wrap justify-between gap-3"><div><h2 className="font-semibold">{project.name}</h2><p className="mt-1 text-xs text-navy/55">Order {project.order?.order_number || 'Pending order link'}</p></div><span className="badge bg-orange-100 text-orange-800">{ucfirst(project.status)}</span></div>
                        <div className="mt-5 grid gap-3 text-sm sm:grid-cols-3">
                            <div><p className="text-xs text-navy/50">Target URL</p><p className="mt-1 break-all">{project.target_url || 'Not set'}</p></div>
                            <div><p className="text-xs text-navy/50">Published URL</p><p className="mt-1 break-all">{project.published_url || 'Not published'}</p></div>
                            <div><p className="text-xs text-navy/50">Verification</p><p className="mt-1">{ucfirst(project.verification_status)}</p></div>
                        </div>
                    </article>
                ))}
            </section>
            <Pager pager={projects} />
        </div>
    );
}

function BacklinksScreen({ page, send }) {
    const backlinks = page.data.backlinks || {};

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div>
                <p className="eyebrow text-brand">My account</p>
                <h1 className="mt-2 text-3xl font-semibold">My backlinks</h1>
                <p className="mt-2 text-sm text-navy/60">Review each published placement and approve it, or send it back to the assigned staff.</p>
            </div>
            {page.flash?.success ? <p className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">{page.flash.success}</p> : null}
            <section className="panel overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[760px] text-left text-sm">
                        <thead className="bg-navy text-white"><tr>{['Project', 'Target URL', 'Published URL', 'Approval'].map((heading) => <th key={heading} className="px-5 py-4 font-semibold">{heading}</th>)}</tr></thead>
                        <tbody>
                            {rows(backlinks).length === 0 ? <tr><td colSpan="4" className="px-5 py-8 text-center text-navy/55">Published backlinks will appear here for your approval.</td></tr> : rows(backlinks).map((backlink) => {
                                const placement = backlink.order_item?.article?.published_url || backlink.published_url;
                                const external = !placement || placement === backlink.published_url;

                                return (
                                    <tr key={backlink.id} className="border-b border-navy/10 align-top">
                                        <td className="px-5 py-4 font-medium">{backlink.name}<p className="mt-1 text-xs font-normal text-navy/55">{backlink.order?.order_number}</p></td>
                                        <td className="max-w-xs px-5 py-4 break-all">{backlink.target_url}</td>
                                        <td className="max-w-xs px-5 py-4 break-all"><a className="text-brand hover:underline" href={placement} {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : {})}>{placement}</a></td>
                                        <td className="px-5 py-4">
                                            <p className="font-medium">{ucfirst(String(backlink.verification_status || '').replaceAll('_', ' '))}</p>
                                            {backlink.verification_status === 'approved' ? null : (
                                                <PostForm action={`/customer/backlinks/${backlink.id}/decision`} className="mt-3 space-y-2" send={send}>
                                                    <label htmlFor={`feedback-${backlink.id}`} className="sr-only">Feedback</label>
                                                    <textarea id={`feedback-${backlink.id}`} name="feedback" rows="2" placeholder="What should staff change?" className="w-full rounded-xl border border-navy/15 px-3 py-2 text-xs outline-none focus:border-brand focus:ring-2 focus:ring-brand/30" />
                                                    <div className="flex flex-wrap gap-2">
                                                        <button type="submit" name="decision" value="approve" className="btn-primary px-3 py-1.5 text-xs">Approve</button>
                                                        <button type="submit" name="decision" value="changes" className="rounded-xl bg-canvas px-3 py-1.5 text-xs font-semibold text-navy">Request changes</button>
                                                    </div>
                                                </PostForm>
                                            )}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                <div className="p-4"><Pager pager={backlinks} /></div>
            </section>
        </div>
    );
}

function ReportsScreen({ page }) {
    const reports = page.data.reports || {};

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">SEO reports</h1><p className="mt-2 text-sm text-navy/60">Verification summaries and published backlink results.</p></div>
            <section className="grid gap-4">
                {rows(reports).length === 0 ? <div className="panel p-8 text-center text-sm text-navy/55">Reports will appear after backlink verification is completed.</div> : rows(reports).map((report) => (
                    <article key={report.id} className="panel p-5">
                        <div className="flex flex-wrap justify-between gap-3"><div><h2 className="font-semibold">{report.name}</h2><p className="mt-1 text-xs text-navy/55">{day(report.updated_at)}</p></div><span className="badge bg-green-100 text-green-800">{ucfirst(report.verification_status)}</span></div>
                        <p className="mt-4 text-sm leading-6 whitespace-pre-line text-navy/75">{report.report_summary}</p>
                    </article>
                ))}
            </section>
            <Pager pager={reports} />
        </div>
    );
}

function WebsitesScreen({ page, send, search }) {
    const websites = page.data.websites || {};
    const niches = rows(page.data.niches);
    const filters = page.data.filters || {};
    const params = new URLSearchParams(search);

    return (
        <div className="mx-auto max-w-7xl space-y-6">
            <div className="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p className="eyebrow text-brand">Publisher marketplace</p>
                    <h1 className="mt-2 text-3xl font-semibold">Browse websites</h1>
                    <p className="mt-2 text-sm text-navy/65">{count(websites.total)} publishers available across {count(niches.length)} niches.</p>
                </div>
                <A className="btn-navy" href="/orders/cart">View cart</A>
            </div>
            <form method="GET" action="/customer/websites" className="panel grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_150px_180px_auto] lg:items-end">
                <Field label="Search sites" name="search" type="search" defaultValue={filters.search || params.get('search') || ''} placeholder="Name or domain" />
                <label className="block text-xs font-medium" htmlFor="niche">Niche
                    <select id="niche" name="niche" defaultValue={filters.niche || ''} className="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                        <option value="">All niches</option>
                        {niches.map((niche) => <option key={niche} value={niche}>{niche}</option>)}
                    </select>
                </label>
                <Field label="Minimum DR" name="min_dr" type="number" min="0" max="100" defaultValue={filters.min_dr ?? ''} />
                <label className="block text-xs font-medium" htmlFor="sort">Sort by
                    <select id="sort" name="sort" defaultValue={filters.sort || 'recommended'} className="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                        <option value="recommended">Recommended</option>
                        <option value="dr">Domain rating</option>
                        <option value="traffic">Traffic</option>
                        <option value="price">Price</option>
                    </select>
                </label>
                <div className="flex gap-2"><button className="btn-primary" type="submit">Filter</button><A className="btn-navy" href="/customer/websites">Reset</A></div>
            </form>
            {rows(websites).length === 0 ? <div className="panel p-10 text-center text-sm text-navy/60">No publishers match those filters.</div> : (
                <>
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {rows(websites).map((website) => (
                            <article key={website.id} className="panel flex h-full flex-col p-5">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0"><span className="badge bg-orange-100 text-orange-800">{website.niche}</span><h2 className="mt-3 truncate text-lg font-semibold">{website.website_name}</h2><a className="mt-1 block truncate text-sm text-brand hover:underline" href={website.url} target="_blank" rel="noopener noreferrer">{website.domain}</a></div>
                                    <div className="shrink-0 text-right"><p className="text-xs text-navy/50">DR</p><p className="text-2xl font-semibold">{website.domain_rating}</p></div>
                                </div>
                                <p className="mt-3 line-clamp-2 text-sm leading-5 text-navy/65">{website.description}</p>
                                <dl className="mt-4 grid grid-cols-3 gap-x-3 gap-y-3 border-y border-navy/10 py-4 text-sm">
                                    <div><dt className="text-xs text-navy/50">DA</dt><dd className="mt-1 font-semibold">{website.domain_authority}</dd></div>
                                    <div><dt className="text-xs text-navy/50">TF / PA</dt><dd className="mt-1 font-semibold">{website.trust_flow} / {website.page_authority}</dd></div>
                                    <div><dt className="text-xs text-navy/50">Spam</dt><dd className="mt-1 font-semibold">{Number(website.spam_score || 0).toFixed(1)}%</dd></div>
                                    <div><dt className="text-xs text-navy/50">Traffic / mo</dt><dd className="mt-1 font-semibold">{count(website.monthly_traffic)}</dd></div>
                                    <div><dt className="text-xs text-navy/50">Backlinks</dt><dd className="mt-1 font-semibold">{count(website.backlinks)}</dd></div>
                                    <div><dt className="text-xs text-navy/50">Ref. domains</dt><dd className="mt-1 font-semibold">{count(website.referring_domains)}</dd></div>
                                </dl>
                                <div className="mt-auto flex flex-wrap items-end justify-between gap-3 pt-4">
                                    <div><p className="text-xs text-navy/50">Guest post from</p><p className="text-xl font-semibold">{money(website.price)}</p><p className="text-xs text-navy/55">{website.credit_cost} credits</p></div>
                                    <div className="flex flex-wrap gap-2">
                                        {page.shell?.auth ? (
                                            <>
                                                <PostForm action="/customer/saved-websites" send={send}>
                                                    <input type="hidden" name="website_name" value={website.website_name || ''} />
                                                    <input type="hidden" name="domain" value={website.domain || ''} />
                                                    <input type="hidden" name="url" value={website.url || ''} />
                                                    <input type="hidden" name="domain_rating" value={website.domain_rating ?? ''} />
                                                    <input type="hidden" name="monthly_traffic" value={website.monthly_traffic ?? ''} />
                                                    <input type="hidden" name="price" value={website.price ?? ''} />
                                                    <button className="btn-navy" type="submit">Save</button>
                                                </PostForm>
                                                <PostForm action="/orders/cart/items" send={send}>
                                                    <input type="hidden" name="product_key" value="guest_post" />
                                                    <input type="hidden" name="website_id" value={website.id} />
                                                    <input type="hidden" name="quantity" value="1" />
                                                    <button className="btn-primary" type="submit">Add to cart</button>
                                                </PostForm>
                                            </>
                                        ) : <A className="btn-primary" href="/login">Sign in to order</A>}
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>
                    <Pager pager={websites} />
                </>
            )}
        </div>
    );
}

function CustomerServicesScreen() {
    return (
        <section className="panel p-6 sm:p-8">
            <h1 className="text-2xl font-semibold">Guest posting services</h1>
            <p className="mt-3 max-w-2xl text-sm leading-6 text-navy/70">Browse publisher sites, compare prices, and choose where to place your backlink.</p>
            <A className="btn-primary mt-5" href="/customer/websites">Browse sites</A>
        </section>
    );
}

function OrderHistoryScreen({ page }) {
    const orders = page.data.orders || {};

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <h1 className="mt-2 text-3xl font-semibold">Order history</h1>
            <Notices page={page} />
            <section className="panel overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-navy text-white"><tr>{['Order', 'Items', 'Total', 'Status', ''].map((heading) => <th key={heading || 'action'} className="px-5 py-4">{heading}</th>)}</tr></thead>
                        <tbody>
                            {rows(orders).length === 0 ? <tr><td className="px-5 py-8 text-center text-navy/60" colSpan="5">No orders yet.</td></tr> : rows(orders).map((order) => (
                                <tr key={order.id} className="border-b border-navy/10">
                                    <td className="px-5 py-4 font-semibold">{order.order_number}<div className="text-xs font-normal text-navy/50">{day(order.created_at)}</div></td>
                                    <td className="px-5 py-4">{(order.items || []).reduce((sum, item) => sum + Number(item.quantity || 0), 0)}</td>
                                    <td className="px-5 py-4">{money(order.total_amount)}{Number(order.discount_amount) > 0 ? <div className="text-xs font-normal text-navy/50">-{money(order.discount_amount)}{order.coupon_code ? ` · ${order.coupon_code}` : ''}</div> : null}</td>
                                    <td className="px-5 py-4"><span className="badge bg-orange-100 text-orange-800">{ucfirst(order.order_status)}</span></td>
                                    <td className="px-5 py-4 text-right"><A className="font-semibold text-brand hover:underline" href={`/orders/${order.id}`}>View</A></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <div className="p-4"><Pager pager={orders} /></div>
            </section>
        </div>
    );
}

function OrderDetailScreen({ page, search }) {
    const order = page.data.order || {};
    const unpaid = ['pending', 'failed'].includes(order.payment_status);

    return (
        <div className="mx-auto max-w-5xl space-y-6">
            <OrderTracking order={order} />
            <div><h1 className="mt-2 text-3xl font-semibold">{order.order_number}</h1><p className="mt-2 text-sm text-navy/60">Placed {stamp(order.created_at)}</p></div>
            <section className="panel overflow-hidden">
                <div className="grid gap-4 border-b border-navy/10 p-6 sm:grid-cols-3">
                    <div><p className="text-xs text-navy/50 uppercase">Order status</p><p className="mt-1 font-semibold">{headline(order.order_status)}</p></div>
                    <div><p className="text-xs text-navy/50 uppercase">Payment status</p><p className="mt-1 font-semibold">{ucfirst(order.payment_status)}</p></div>
                    <div><p className="text-xs text-navy/50 uppercase">Total</p><p className="mt-1 font-semibold">{money(order.total_amount)}</p>{Number(order.discount_amount) > 0 ? <p className="mt-1 text-xs text-navy/55">{money(order.discount_amount)} off{order.coupon_code ? ` · ${order.coupon_code}` : ''}</p> : null}</div>
                </div>
                {(order.items || []).map((item) => (
                    <div key={item.id} className="space-y-3 border-b border-navy/10 p-6">
                        <div className="flex justify-between gap-4"><h2 className="font-semibold">{item.item_name}</h2><span>{money(Number(item.unit_price) * Number(item.quantity))}</span></div>
                        <dl className="grid gap-3 text-sm sm:grid-cols-2">
                            <div><dt className="text-navy/50">Target URL</dt><dd className="font-medium break-all">{item.target_url}</dd></div>
                            <div><dt className="text-navy/50">Anchor text</dt><dd className="font-medium">{item.anchor_text}</dd></div>
                            <div><dt className="text-navy/50">Article topic</dt><dd className="font-medium">{item.article_topic}</dd></div>
                            <div><dt className="text-navy/50">Requirements</dt><dd className="font-medium">{item.requirements || 'None provided'}</dd></div>
                        </dl>
                    </div>
                ))}
            </section>
            <A className="btn-navy" href="/orders">Back to orders</A>
            {search.includes('stripe=returned') && order.payment_status !== 'paid' ? <p className="rounded-xl bg-sky-50 p-4 text-sm text-sky-900" role="status">Waiting for Stripe to confirm payment. Refresh this page in a moment.</p> : null}
            {order.order_status === 'awaiting_approval' ? <p className="rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Payment confirmed. Your order is awaiting admin approval.</p> : null}
            {order.invoice ? <a className="btn-primary" href={`/customer/invoices/${order.invoice.id}/download`}>Download invoice</a> : null}
            <Notices page={page} />
            {unpaid ? <A className="btn-primary" href={`/orders/${order.id}/payment`}>{order.payment_status === 'failed' ? 'Retry payment' : 'Complete payment'}</A> : null}
        </div>
    );
}

function OrderTracking({ order }) {
    const status = String(order.order_status || 'pending');
    const paid = order.payment_status === 'paid';
    const paymentFailed = order.payment_status === 'failed';
    const items = Array.isArray(order.items) ? order.items : [];
    const isAccepted = ['processing', 'completed'].includes(status);
    const isDelivered = status === 'completed';
    const isInProgress = status === 'completed' || items.some((item) => ['in_progress', 'published', 'completed'].includes(item.assignment?.status));
    const isOnTheWay = isDelivered || items.some((item) => Boolean(item.project?.published_url) || ['published', 'completed'].includes(item.assignment?.status));
    const hasTeamAssignment = items.some((item) => Boolean(item.assignment?.id));
    const startedAt = items.map((item) => item.assignment?.status === 'in_progress' ? item.assignment.updated_at : null).filter(Boolean).sort()[0];
    const publishedAt = items.map((item) => item.article?.published_at).filter(Boolean).sort()[0];
    const deliveredAt = items.map((item) => item.project?.completed_at).filter(Boolean).sort().at(-1);

    const completedSteps = isDelivered ? 5 : isOnTheWay ? 4 : isInProgress ? 3 : isAccepted ? 2 : 1;
    const currentStep = Math.min(completedSteps + 1, 5);
    const progressPercent = ((completedSteps - 1) / 4) * 100;
    const statusLabel = paymentFailed ? 'Payment needs attention'
        : !paid ? 'Awaiting payment'
            : status === 'awaiting_details' ? 'Campaign details needed'
                : status === 'awaiting_approval' ? 'Awaiting admin approval'
                    : isDelivered ? 'Delivered'
                        : isOnTheWay ? 'Placement published'
                            : isInProgress ? 'In progress' : isAccepted ? 'Accepted' : 'Awaiting approval';
    const currentTitle = paymentFailed ? 'Payment needs another try'
        : !paid ? 'Complete payment to confirm your order'
            : status === 'awaiting_details' ? 'Add your campaign details'
                : status === 'awaiting_approval' ? 'Your order is waiting for approval'
                    : isDelivered ? 'Your order has been delivered'
                        : isOnTheWay ? 'Your placement is ready for review'
                            : isInProgress ? 'The team is working on your placement' : isAccepted ? (hasTeamAssignment ? 'Your order is with the SEO team' : 'Your order is approved and getting started') : 'Your order is waiting for approval';
    const currentDescription = paymentFailed ? 'Retry payment to move your order forward.'
        : !paid ? 'Once payment clears, the team can accept and prepare your order.'
            : status === 'awaiting_details' ? 'Share the target URL and brief so the admin team can review the order.'
                : status === 'awaiting_approval' ? 'Payment is confirmed. Work begins after the admin team approves the order.'
                    : isDelivered ? 'Your backlink has passed review and the order is complete.'
                        : isOnTheWay ? 'The placement has been published. Review the live link in your order details.'
                            : isInProgress ? 'Your campaign is approved and moving through content and placement.' : isAccepted ? (hasTeamAssignment ? 'Your SEO team is assigned and will begin content work next.' : 'The admin team approved your order. The SEO team assignment is next.') : 'Your order is confirmed and will move into fulfillment next.';

    const steps = [
        { title: 'Order placed', detail: trackingStamp(order.created_at) || 'Order created' },
        { title: 'Accepted', detail: isAccepted ? 'Approved by the team' : (paymentFailed ? 'Payment failed' : status === 'awaiting_details' ? 'Campaign details needed' : paid ? 'Awaiting approval' : 'Awaiting payment') },
        { title: 'In progress', detail: isInProgress ? (startedAt ? trackingStamp(startedAt) : 'Content work started') : (status === 'awaiting_details' ? 'After campaign details' : status === 'awaiting_approval' ? 'After admin approval' : isAccepted ? (hasTeamAssignment ? 'Assigned · work starts next' : 'Awaiting team assignment') : 'After acceptance') },
        { title: 'On the way', detail: isOnTheWay ? (publishedAt ? trackingStamp(publishedAt) : 'Published') : (isInProgress ? 'Preparing placement' : 'After content review') },
        { title: 'Delivered', detail: isDelivered ? (trackingStamp(deliveredAt) || 'Order complete') : 'After customer approval' },
    ];

    return (
        <section className="overflow-hidden rounded-3xl border border-navy/10 bg-white shadow-sm" aria-label="Order tracking">
            <header className="flex flex-wrap items-start justify-between gap-4 border-b border-navy/10 px-5 py-5 sm:px-7">
                <div>
                    <p className="text-[10px] font-bold tracking-[0.18em] text-brand uppercase">Your order journey</p>
                    <h2 className="mt-1 text-xl font-semibold tracking-tight text-navy sm:text-2xl">Track your placement</h2>
                    <p className="mt-1 text-sm text-navy/55">Follow each milestone from checkout to a verified live link.</p>
                </div>
                <span className={`inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold ${isDelivered ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : paymentFailed ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-brand/20 bg-[#fff3ed] text-[#c0340d]'}`}>
                    {statusLabel}
                </span>
            </header>
            <div className="p-5 sm:p-7">
                <div className="relative">
                    <div className="absolute top-5 right-[10%] left-[10%] hidden h-0.5 bg-navy/10 sm:block" aria-hidden="true">
                        <span className="block h-full bg-brand transition-all duration-500" style={{ width: `${progressPercent}%` }} />
                    </div>
                    <div className="absolute top-5 bottom-5 left-5 border-l-2 border-dotted border-navy/15 sm:hidden" aria-hidden="true" />
                    <ol className="relative grid gap-5 sm:grid-cols-5 sm:gap-1" aria-label={`Order progress: ${statusLabel}`}>
                        {steps.map((step, index) => {
                            const stepNumber = index + 1;
                            const isComplete = stepNumber <= completedSteps;
                            const isCurrent = !isComplete && stepNumber === currentStep;

                            return (
                                <li key={step.title} aria-current={isCurrent ? 'step' : undefined} className="flex items-start gap-3 sm:block sm:px-1 sm:text-center">
                                    <span className={`relative z-10 flex size-10 shrink-0 items-center justify-center rounded-full border transition sm:mx-auto ${isCurrent ? 'border-brand bg-brand text-white shadow-[0_0_0_5px_rgba(255,73,23,0.12)]' : isComplete ? 'border-brand bg-brand text-white' : 'border-navy/15 bg-white text-navy/40'}`} aria-hidden="true">
                                        <OrderTrackingIcon step={stepNumber} />
                                    </span>
                                    <span className="min-w-0 pb-1 sm:mt-3 sm:block">
                                        <span className={`block text-sm font-semibold ${isComplete || isCurrent ? 'text-navy' : 'text-navy/45'}`}>{step.title}</span>
                                        <span className="mt-1 block text-xs leading-5 text-navy/50">{step.detail}</span>
                                    </span>
                                </li>
                            );
                        })}
                    </ol>
                </div>
                <div className="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-brand/15 bg-[#fff5f0] p-4 sm:p-5">
                    <div className="min-w-0 flex-1 basis-64">
                        <p className="text-[10px] font-bold tracking-[0.16em] text-brand uppercase">Latest update</p>
                        <h3 className="mt-1 text-base font-semibold text-navy">{currentTitle}</h3>
                        <p className="mt-1 text-sm leading-6 text-navy/65">{currentDescription}</p>
                    </div>
                </div>
            </div>
        </section>
    );
}

function OrderTrackingIcon({ step }) {
    const shared = { className: 'size-5', fill: 'none', stroke: 'currentColor', strokeWidth: 1.8, strokeLinecap: 'round', strokeLinejoin: 'round', viewBox: '0 0 24 24' };

    if (step === 1) {
        return <svg {...shared}><path d="M5 7.5 12 4l7 3.5v9L12 20l-7-3.5z" /><path d="m5.5 7.8 6.5 3.4 6.5-3.4M12 11.2V20" /></svg>;
    }

    if (step === 2) {
        return <svg {...shared}><path d="M12 3.5 19 6v5.2c0 4.3-2.8 7.7-7 9.3-4.2-1.6-7-5-7-9.3V6z" /><path d="m8.5 11.8 2.2 2.2 4.8-4.8" /></svg>;
    }

    if (step === 3) {
        return <svg {...shared}><path d="m12 3.5 8 4.3v8.4l-8 4.3-8-4.3V7.8z" /><path d="m4.4 8 7.6 4 7.6-4M12 12v8.2" /></svg>;
    }

    if (step === 4) {
        return <svg {...shared}><path d="M3 6.5h11v10H3zM14 10h4l3 3v3.5h-7z" /><circle cx="7" cy="18" r="1.8" /><circle cx="18" cy="18" r="1.8" /><path d="M16.5 10v3h4" /></svg>;
    }

    return <svg {...shared}><path d="M5 8 12 4l7 4v8l-7 4-7-4z" /><path d="m5.5 8.3 6.5 3.5 6.5-3.5M12 11.8V20" /><path d="m9.4 15.2 1.7 1.7 3.7-3.7" /></svg>;
}

function OrderCartScreen({ page, send }) {
    const cart = page.data.cart || {};
    const items = rows(cart.items);
    const offer = page.data.offer || {};
    const catalog = page.data.catalog || {};

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div><h1 className="mt-2 text-3xl font-semibold">Shopping cart</h1><p className="mt-2 text-sm text-navy/70">Review your backlink services before adding the order details.</p></div>
            <Notices page={page} />
            <div className="grid gap-6 lg:grid-cols-[1fr_320px]">
                <section className="panel overflow-hidden">
                    {items.length === 0 ? <div className="p-8 text-center text-sm text-navy/60">Your cart is empty.</div> : items.map((item) => (
                        <div key={item.id} className="flex flex-col gap-4 border-b border-navy/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div><h2 className="font-semibold">{item.item_name}</h2><p className="mt-1 text-sm text-navy/60">{money(item.unit_price)} each · {item.credit_cost} credits</p></div>
                            <div className="flex items-center gap-3">
                                <PostForm action={`/orders/cart/items/${item.id}`} method="PATCH" className="flex items-center gap-2" send={send}>
                                    <label className="sr-only" htmlFor={`quantity-${item.id}`}>Quantity</label>
                                    <input id={`quantity-${item.id}`} name="quantity" type="number" min="1" max="99" defaultValue={item.quantity} className="w-20 rounded-lg border border-navy/15 px-3 py-2 text-sm" />
                                    <button className="btn-navy" type="submit">Update</button>
                                </PostForm>
                                <PostForm action={`/orders/cart/items/${item.id}`} method="DELETE" send={send}><button className="rounded-xl px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" type="submit">Remove</button></PostForm>
                            </div>
                        </div>
                    ))}
                </section>
                <aside className="panel h-fit p-6">
                    <h2 className="text-lg font-semibold">Add a service</h2>
                    <PostForm action="/orders/cart/items" className="mt-4 space-y-4" send={send}>
                        <label className="block text-sm font-medium" htmlFor="product_key">Service
                            <select id="product_key" name="product_key" required className="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                                {Object.entries(catalog).map(([key, product]) => <option key={key} value={key}>{product.name} · {money(product.price)}</option>)}
                            </select>
                        </label>
                        <Field label="Quantity" name="quantity" type="number" min="1" max="99" defaultValue="1" required />
                        <button className="btn-primary w-full" type="submit">Add to cart</button>
                    </PostForm>
                    <div className="mt-6 border-t border-navy/10 pt-5">
                        {Number(offer.offer_discount) > 0 && !cart.coupon_code ? <div className="mb-3 flex justify-between text-sm text-green-700"><span>{offer.offer_label}</span><span>−{money(offer.offer_discount)}</span></div> : null}
                        {cart.coupon_code ? (
                            <>
                                <div className="mb-3 flex justify-between text-sm"><span>Subtotal</span><span>{money(page.data.subtotal)}</span></div>
                                <div className="mb-3 flex justify-between text-sm text-green-700"><span>Coupon {cart.coupon_code}</span><span>−{money(cart.coupon_discount)}</span></div>
                                <PostForm action="/orders/cart/coupon" method="DELETE" className="mb-4" send={send}><button className="text-xs font-semibold text-brand hover:underline" type="submit">Remove coupon</button></PostForm>
                            </>
                        ) : (
                            <PostForm action="/orders/cart/coupon" className="mb-4 flex gap-2" send={send}>
                                <label className="sr-only" htmlFor="coupon_code">Coupon code</label>
                                <input id="coupon_code" name="coupon_code" placeholder="Coupon code" className="min-w-0 flex-1 rounded-lg border border-navy/15 px-3 py-2 text-sm" />
                                <button className="btn-navy" type="submit">Apply</button>
                            </PostForm>
                        )}
                        {page.errors?.coupon_code ? <p className="mb-3 text-xs text-red-700">{page.errors.coupon_code[0]}</p> : null}
                        <div className="flex justify-between font-semibold"><span>Total</span><span>{money(page.data.total)}</span></div>
                        {items.length > 0 ? <A className="btn-primary mt-4 w-full" href="/orders/checkout">Continue to checkout</A> : null}
                    </div>
                </aside>
            </div>
        </div>
    );
}

function OrderCheckoutScreen({ page, send }) {
    const items = rows(page.data.cart?.items);

    return (
        <div className="mx-auto max-w-5xl space-y-6">
            <div><h1 className="mt-2 text-3xl font-semibold">Checkout</h1><p className="mt-2 text-sm text-navy/70">Add the publishing details for each item in your cart.</p></div>
            {Object.keys(page.errors || {}).length > 0 ? <div className="rounded-xl bg-red-50 p-4 text-sm text-red-800">Please check the fields and try again.</div> : null}
            <PostForm action="/orders" className="space-y-6" send={send}>
                {items.map((item, index) => (
                    <section key={item.id || index} className="panel p-6">
                        <div className="flex flex-wrap items-baseline justify-between gap-2"><h2 className="text-lg font-semibold">{item.item_name}</h2><span className="text-sm text-navy/60">{item.quantity} × {money(item.unit_price)}</span></div>
                        <div className="mt-5 grid gap-4 md:grid-cols-2">
                            <Field label="Target URL" name={`items[${index}][target_url]`} id={`target_url_${index}`} type="url" required placeholder="https://your-site.com/page" defaultValue={old(page, `items.${index}.target_url`)} error={page.errors?.[`items.${index}.target_url`]?.[0]} />
                            <Field label="Anchor text (15–20 characters)" name={`items[${index}][anchor_text]`} id={`anchor_text_${index}`} required minLength="15" maxLength="20" defaultValue={old(page, `items.${index}.anchor_text`)} error={page.errors?.[`items.${index}.anchor_text`]?.[0]} />
                            <Field label="Article topic" name={`items[${index}][article_topic]`} id={`article_topic_${index}`} required defaultValue={old(page, `items.${index}.article_topic`)} error={page.errors?.[`items.${index}.article_topic`]?.[0]} />
                            <div className="md:col-span-2">
                                <KeywordField index={index} defaultValue={old(page, `items.${index}.keywords`)} error={page.errors?.[`items.${index}.keywords`]?.[0]} />
                            </div>
                            <div className="md:col-span-2">
                                <label className="text-sm font-medium" htmlFor={`requirements_${index}`}>Additional requirements</label>
                                <textarea id={`requirements_${index}`} name={`items[${index}][requirements]`} rows="2" defaultValue={old(page, `items.${index}.requirements`)} className="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm" />
                                {page.errors?.[`items.${index}.requirements`] ? <p className="mt-1 text-sm text-red-800">{page.errors[`items.${index}.requirements`][0]}</p> : null}
                            </div>
                        </div>
                    </section>
                ))}
                <div className="panel flex flex-wrap items-center justify-between gap-4 p-6">
                    <div><p className="text-sm text-navy/60">Order total</p><p className="text-2xl font-semibold">{money(page.data.total)}</p></div>
                    <button className="btn-primary" type="submit">Create order</button>
                </div>
            </PostForm>
        </div>
    );
}

function AccountTable({ title, headers, children, empty, pager }) {
    const list = Children.toArray(children).filter(Boolean);

    return (
        <div className="mx-auto max-w-6xl space-y-6">
            <div><p className="eyebrow text-brand">My account</p><h1 className="mt-2 text-3xl font-semibold">{title}</h1></div>
            <section className="panel overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-navy text-white"><tr>{headers.map((heading) => <th key={heading || 'action'} className="px-5 py-4">{heading}</th>)}</tr></thead>
                        <tbody>{list.length === 0 ? <tr><td colSpan={headers.length} className="px-5 py-8 text-center text-navy/55">{empty}</td></tr> : children}</tbody>
                    </table>
                </div>
                <div className="p-4"><Pager pager={pager} /></div>
            </section>
        </div>
    );
}

function RewardCopy({ coupon, compact = false, actions = false, send }) {
    return (
        <div>
            {compact ? null : (
                <>
                    <p className="font-semibold">{coupon.code}</p>
                    <p className="mt-1 text-sm text-navy/70">{coupon.discount_summary} on a package or an individual backlink.</p>
                </>
            )}
            {coupon.saved_for_later_at ? <p className="mt-1 text-sm text-navy/70">Saved for later. Use it before {day(coupon.expires_at)}.</p> : compact ? null : <p className="mt-1 text-sm text-navy/70">Deadline {day(coupon.expires_at)}.</p>}
            {actions && !coupon.saved_for_later_at ? (
                <div className="mt-3 flex flex-wrap gap-2">
                    <PostForm action={`/customer/coupons/${coupon.id}/apply`} send={send}><button className="btn-primary" type="submit">Apply</button></PostForm>
                    <PostForm action={`/customer/coupons/${coupon.id}/save`} send={send}><button className="btn-navy" type="submit">Save for later</button></PostForm>
                </div>
            ) : null}
        </div>
    );
}

function CopyCode({ code }) {
    const [label, setLabel] = useState('Copy code');

    return <button className="btn-navy" type="button" onClick={() => { navigator.clipboard?.writeText(code); setLabel('Copied'); }}>{label}</button>;
}

function KeywordField({ index, defaultValue, error }) {
    const [count, setCount] = useState(filledCount(String(defaultValue || '').split(',')));

    function limit(input) {
        const max = 5;
        const parts = String(input.value || '').split(',');
        const kept = [];
        let filled = 0;
        parts.forEach((part, partIndex) => {
            const trimmed = part.trim();
            const last = partIndex === parts.length - 1;
            if (trimmed === '') {
                if (last && filled < max) {
                    kept.push(part);
                }
                return;
            }
            if (filled >= max) {
                return;
            }
            kept.push(part);
            filled += 1;
        });
        const next = kept.join(',');
        if (input.value !== next) {
            input.value = next;
        }
        setCount(filled);
    }

    return (
        <div>
            <label className="text-sm font-medium" htmlFor={`keywords_${index}`}>Keywords <span className="text-navy/50">({count} / 5)</span></label>
            <input id={`keywords_${index}`} name={`items[${index}][keywords]`} defaultValue={defaultValue} maxLength="500" placeholder="keyword one, keyword two" autoComplete="off" data-max-keywords="5" className="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm" onInput={(event) => limit(event.currentTarget)} onKeyDown={(event) => {
                if (count < 5 || event.ctrlKey || event.metaKey || event.altKey) {
                    return;
                }
                const edit = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'].includes(event.key);
                if (!edit && (event.key === ',' || /,\s*$/.test(event.currentTarget.value))) {
                    event.preventDefault();
                }
            }} />
            <p className="mt-1 text-xs text-navy/55">Separate with commas. You can’t type more than 5 keywords.</p>
            {error ? <p className="mt-1 text-sm text-red-800">{error}</p> : null}
        </div>
    );
}

function PostForm({ action, method = 'POST', className, children, send }) {
    return (
        <form className={className} onSubmit={(event) => {
            event.preventDefault();
            send(action, method, fieldsFrom(event.currentTarget, event.nativeEvent.submitter));
        }}
        >
            {children}
        </form>
    );
}

function Field({ label, name, id, error, className, ...props }) {
    const fieldId = id || name;

    return (
        <div className={className}>
            <label className="text-xs font-medium" htmlFor={fieldId}>{label}</label>
            <input id={fieldId} name={name} className="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm" {...props} />
            {error ? <p className="mt-1 text-sm text-red-800">{error}</p> : null}
        </div>
    );
}

function Notices({ page }) {
    const error = page.flash?.error || firstError(page.errors);

    return (
        <>
            {page.flash?.success ? <div className="rounded-xl bg-green-50 p-4 text-sm text-green-800" role="status">{page.flash.success}</div> : null}
            {error ? <div className="rounded-xl bg-red-50 p-4 text-sm text-red-800" role="alert">{error}</div> : null}
        </>
    );
}

function Pager({ pager }) {
    const links = Array.isArray(pager?.links) ? pager.links : [];

    if (links.length < 4 && Number(pager?.last_page || 1) < 2) {
        return null;
    }

    return (
        <nav className="flex flex-wrap gap-2 text-sm" aria-label="Pagination">
            {links.map((link, index) => {
                const label = String(link.label || '').replace(/<[^>]+>/g, '').replace('&laquo;', '').replace('&raquo;', '').trim();
                const text = label.toLowerCase().includes('previous') ? 'Previous' : label.toLowerCase().includes('next') ? 'Next' : label;
                if (!link.url) {
                    return <span key={`${text}-${index}`} className="inline-flex min-h-10 items-center px-2 text-navy/40">{text}</span>;
                }

                return <A key={`${text}-${index}`} href={link.url} className={`inline-flex min-h-10 items-center rounded-lg px-3 font-semibold ${link.active ? 'bg-navy text-white' : 'hover:bg-canvas'}`} aria-current={link.active ? 'page' : undefined}>{text}</A>;
            })}
        </nav>
    );
}

function rows(value) {
    if (Array.isArray(value)) {
        return value;
    }

    if (Array.isArray(value?.data)) {
        return value.data;
    }

    return [];
}

function fieldsFrom(form, submitter) {
    const data = new FormData(form, submitter);
    const result = {};

    for (const [key, value] of data.entries()) {
        const parts = [...key.matchAll(/[^[\]]+/g)].map((match) => match[0]);
        let cursor = result;
        parts.forEach((part, index) => {
            if (index === parts.length - 1) {
                cursor[part] = value;
                return;
            }
            if (cursor[part] == null) {
                cursor[part] = /^\d+$/.test(parts[index + 1]) ? [] : {};
            }
            cursor = cursor[part];
        });
    }

    return result;
}

function old(page, key, fallback = '') {
    const source = page.old || {};
    if (source[key] != null) {
        return source[key];
    }
    let cursor = source;
    for (const part of key.split('.')) {
        if (cursor == null || typeof cursor !== 'object') {
            return fallback ?? '';
        }
        cursor = cursor[part];
    }

    return cursor ?? fallback ?? '';
}

function firstError(errors) {
    const value = Object.values(errors || {})[0];

    return Array.isArray(value) ? value[0] : value || '';
}

function noteData(notification) {
    if (typeof notification.data === 'string') {
        try {
            return JSON.parse(notification.data);
        } catch {
            return {};
        }
    }

    return notification.data || {};
}

function orderState(order) {
    if (order.payment_status !== 'paid') {
        return 'Awaiting payment';
    }

    if (order.order_status === 'awaiting_approval') {
        return 'Awaiting approval';
    }

    return headline(order.order_status);
}

function day(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function stamp(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function trackingStamp(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function ago(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }
    const seconds = Math.round((Date.now() - date.getTime()) / 1000);
    const steps = [[31536000, 'year'], [2592000, 'month'], [86400, 'day'], [3600, 'hour'], [60, 'minute']];
    for (const [size, label] of steps) {
        const amount = Math.floor(seconds / size);
        if (amount >= 1) {
            return `${amount} ${label}${amount === 1 ? '' : 's'} ago`;
        }
    }

    return 'just now';
}

function count(value) {
    return Math.round(Number(value || 0)).toLocaleString('en-US');
}

function ucfirst(value) {
    const text = String(value || '');

    return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
}

function headline(value) {
    return String(value || '').replace(/[_-]+/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function filledCount(parts) {
    return parts.reduce((total, part) => total + (part.trim() !== '' ? 1 : 0), 0);
}
