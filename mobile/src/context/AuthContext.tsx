import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';

import { currentCustomer, login as loginRequest, logout as logoutRequest, prepareAuth, register as registerRequest, type Customer } from '../api/auth';

type AuthContextValue = {
    customer: Customer | null;
    ready: boolean;
    login: (email: string, password: string) => Promise<void>;
    register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
    logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [customer, setCustomer] = useState<Customer | null>(null);
    const [ready, setReady] = useState(false);

    useEffect(() => {
        let active = true;

        prepareAuth()
            .then(() => currentCustomer())
            .then((next) => {
                if (active) {
                    setCustomer(next);
                }
            })
            .catch(() => {
                if (active) {
                    setCustomer(null);
                }
            })
            .finally(() => {
                if (active) {
                    setReady(true);
                }
            });

        return () => {
            active = false;
        };
    }, []);

    const value = useMemo<AuthContextValue>(() => ({
        customer,
        ready,
        async login(email, password) {
            setCustomer(await loginRequest(email, password));
        },
        async register(name, email, password, passwordConfirmation) {
            setCustomer(await registerRequest(name, email, password, passwordConfirmation));
        },
        async logout() {
            await logoutRequest();
            setCustomer(null);
        },
    }), [customer, ready]);

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
    const value = useContext(AuthContext);

    if (!value) {
        throw new Error('useAuth must be used inside AuthProvider');
    }

    return value;
}
