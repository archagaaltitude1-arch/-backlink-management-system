import { useEffect, useLayoutEffect, useState } from 'react';
import { BrowserRouter, useLocation, useNavigate } from 'react-router-dom';
import { loadPage, submitForm } from './api';
import { PublicChrome } from './chrome';
import { screenFor } from './pages';

export default function App() {
    return (
        <BrowserRouter>
            <Shell />
        </BrowserRouter>
    );
}

function Shell() {
    const location = useLocation();
    const navigate = useNavigate();
    const [page, setPage] = useState(null);
    const [shownPath, setShownPath] = useState('');
    const [error, setError] = useState('');
    const [nonce, setNonce] = useState(0);
    const path = `${location.pathname}${location.search}`;

    useEffect(() => {
        if ('scrollRestoration' in window.history) {
            window.history.scrollRestoration = 'manual';
        }
    }, []);

    useLayoutEffect(() => {
        if (shownPath !== path) {
            return;
        }

        const id = decodeURIComponent(location.hash.replace(/^#/, ''));
        const target = id ? document.getElementById(id) : null;

        if (target) {
            target.scrollIntoView();

            return;
        }

        if (location.hash) {
            return;
        }

        window.scrollTo(0, 0);
    }, [shownPath, path, location.hash]);

    useEffect(() => {
        let active = true;
        setError('');
        loadPage(path).then((payload) => {
            if (!active) {
                return;
            }

            if (payload.redirect) {
                follow(payload, navigate);

                return;
            }

            setPage(payload);
            setShownPath(path);
        }).catch((reason) => {
            if (active) {
                setError(reason.message || 'The page could not be loaded.');
            }
        });

        return () => {
            active = false;
        };
    }, [path, nonce, navigate]);

    async function send(action, method, fields) {
        const result = await submitForm(action, method, fields);

        if (result.redirect) {
            const destination = result.redirect.split('#')[0];

            if (result.external) {
                window.location.assign(result.redirect);
            } else if (destination === path) {
                setNonce((value) => value + 1);
            } else {
                follow(result, navigate);
            }
        } else if (result.view) {
            setPage(result);
            setShownPath(path);
        } else {
            setNonce((value) => value + 1);
        }

        return result;
    }

    if (error) {
        return <p className="p-8 text-sm font-bold text-brand-dark" role="alert">{error}</p>;
    }

    if (!page?.view) {
        return <p className="p-8 text-sm text-navy/60">Loading…</p>;
    }

    const Screen = screenFor(page.view);

    return (
        <PublicChrome shell={page.shell} path={location.pathname} view={page.view} flash={page.flash} send={send}>
            <Screen page={page} path={location.pathname} search={location.search} send={send} />
        </PublicChrome>
    );
}

function follow(payload, navigate) {
    if (payload.external) {
        window.location.assign(payload.redirect);

        return;
    }

    const target = payload.redirect || '/';
    const path = target.split('#')[0];

    if (/\/download(\?|$)/.test(path)) {
        window.location.assign(target);

        return;
    }

    const react = /^\/($|home|contact|login|register|services|packages|publishers|niches|sites|tools|wishlist|cart|marketplace|publish)/.test(path)
        || /^\/customer(\/|$|\?)/.test(path)
        || /^\/orders(\/|$|\?)/.test(path);

    if (!react) {
        window.location.assign(target);

        return;
    }

    navigate(target, { replace: true });
}
