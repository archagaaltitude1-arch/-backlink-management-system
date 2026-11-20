import { useCallback, useEffect, useState } from 'react';

import { apiRequest } from '../api/client';
import { parseHome, parseShell, type HomeData, type Shell } from '../api/home';

const emptyShell = parseShell(null);

export function useHome(): { data: HomeData | null; shell: Shell; error: string; loading: boolean; reload: () => void } {
    const [data, setData] = useState<HomeData | null>(null);
    const [shell, setShell] = useState<Shell>(emptyShell);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);
    const [nonce, setNonce] = useState(0);

    const reload = useCallback(() => setNonce((value) => value + 1), []);

    useEffect(() => {
        let active = true;

        apiRequest('/home', { timeoutMs: 20000 })
            .then((result) => {
                if (!active) {
                    return;
                }
                if (result.body.view !== 'customer.pages.home') {
                    throw new Error('The website did not return the public home page.');
                }
                setData(parseHome(result.body, result.status));
                setShell(parseShell(result.body.shell));
            })
            .catch((reason: unknown) => {
                const message = reason instanceof Error ? reason.message : 'The homepage data could not be loaded.';
                console.warn('[home]', message);
                if (active) {
                    setError(message);
                }
            })
            .finally(() => {
                if (active) {
                    setLoading(false);
                }
            });

        return () => {
            active = false;
        };
    }, [nonce]);

    return { data, shell, error, loading, reload };
}
