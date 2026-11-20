import * as SecureStore from 'expo-secure-store';

import { CookieJar } from './cookies';

const storageKey = 'backlinkpro.laravel.cookies';

export async function loadCookies(): Promise<CookieJar> {
    const raw = await SecureStore.getItemAsync(storageKey);

    if (!raw) {
        return {};
    }

    try {
        const parsed = JSON.parse(raw) as unknown;

        if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
            return {};
        }

        const jar: CookieJar = {};

        for (const [name, value] of Object.entries(parsed)) {
            if (typeof value === 'string') {
                jar[name] = value;
            }
        }

        return jar;
    } catch {
        return {};
    }
}

export async function saveCookies(jar: CookieJar): Promise<void> {
    await SecureStore.setItemAsync(storageKey, JSON.stringify(jar));
}

export async function clearCookies(): Promise<void> {
    await SecureStore.deleteItemAsync(storageKey);
}
