import { apiRequest, errorText, forgetSession, hasErrors, restoreSession } from './client';

export type Customer = {
    id?: number;
    name: string;
    email: string;
};

export async function prepareAuth(): Promise<void> {
    await restoreSession();
}

export async function currentCustomer(): Promise<Customer | null> {
    const result = await apiRequest('/customer/dashboard');
    const redirect = typeof result.body.redirect === 'string' ? result.body.redirect : '';

    if (result.status === 401 || result.status === 419 || redirect.includes('/login')) {
        return null;
    }

    const data = result.body.data;
    if (!data || typeof data !== 'object' || Array.isArray(data)) {
        return null;
    }

    const customer = (data as { customer?: unknown }).customer;
    if (!customer || typeof customer !== 'object' || Array.isArray(customer)) {
        return null;
    }

    const record = customer as { id?: unknown; name?: unknown; email?: unknown };
    if (typeof record.email !== 'string' || record.email === '') {
        return null;
    }

    return {
        id: typeof record.id === 'number' ? record.id : undefined,
        name: typeof record.name === 'string' ? record.name : record.email,
        email: record.email,
    };
}

export async function login(email: string, password: string): Promise<Customer> {
    const result = await apiRequest('/login', {
        method: 'POST',
        json: true,
        body: { email: email.trim(), password },
    });
    if (result.body.redirect === '/login') {
        throw new Error('The provided credentials do not match our records.');
    }
    assertOk(result.status, result.body);

    const customer = await currentCustomer();
    if (!customer) {
        throw new Error('Signed in, but the account session was not kept. Check the API address and try again.');
    }

    return customer;
}

export async function register(name: string, email: string, password: string, passwordConfirmation: string): Promise<Customer> {
    const result = await apiRequest('/register', {
        method: 'POST',
        json: true,
        body: {
            name: name.trim(),
            email: email.trim(),
            password,
            password_confirmation: passwordConfirmation,
        },
    });
    assertOk(result.status, result.body);

    const customer = await currentCustomer();
    if (!customer) {
        throw new Error('The account was created, but the session was not kept.');
    }

    return customer;
}

export async function logout(): Promise<void> {
    try {
        await apiRequest('/logout', { method: 'POST', json: true, body: {} });
    } finally {
        await forgetSession();
    }
}

function assertOk(status: number, body: Record<string, unknown>): void {
    if (status >= 400 || hasErrors(body)) {
        throw new Error(errorText(body));
    }
}
