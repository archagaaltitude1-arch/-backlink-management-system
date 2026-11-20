import { useEffect, useRef, useState, type ComponentType, type Ref } from 'react';
import { ActivityIndicator, BackHandler, Platform, Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { WebView, type WebViewNavigation, type WebViewProps } from 'react-native-webview';

const SiteWebView = WebView as unknown as ComponentType<WebViewProps & { ref?: Ref<WebView> }>;

import { apiUrl } from '../config';
import { siteAddress } from '../site-url';
import { colors } from '../theme';

function isSiteDocument(url: string): boolean {
    if (!url || url === 'about:blank') {
        return true;
    }

    try {
        const parsed = new URL(url);
        const site = new URL(siteAddress(apiUrl, '/'));

        return parsed.origin === site.origin && !/\.(css|js|png|jpe?g|webp|gif|svg|mp4|ico|woff2?)$/i.test(parsed.pathname);
    } catch {
        return false;
    }
}

const playHeroVideo = `
  (function () {
    document.querySelectorAll('video').forEach(function (video) {
      video.muted = true;
      video.playsInline = true;
      var play = video.play();
      if (play && play.catch) {
        play.catch(function () {});
      }
    });
  })();
  true;
`;

export function HomeScreen() {
    return <SiteScreen path="/" />;
}

export function SiteScreen({ path = '/' }: { path?: string }) {
    const insets = useSafeAreaInsets();
    const web = useRef<WebView | null>(null);
    const canGoBack = useRef(false);
    const holdError = useRef(false);
    const [address, setAddress] = useState(() => siteAddress(apiUrl, path));
    const [loaded, setLoaded] = useState(false);
    const [failed, setFailed] = useState(false);

    useEffect(() => {
        setLoaded(false);
        setFailed(false);
        holdError.current = false;
        setAddress(siteAddress(apiUrl, path));
    }, [path]);

    useEffect(() => {
        if (Platform.OS !== 'android') {
            return;
        }

        const subscription = BackHandler.addEventListener('hardwareBackPress', () => {
            if (!canGoBack.current) {
                return false;
            }

            web.current?.goBack();

            return true;
        });

        return () => subscription.remove();
    }, []);

    function onNavigation(event: WebViewNavigation) {
        canGoBack.current = event.canGoBack;
        const next = siteAddress(apiUrl, event.url);

        if (next !== event.url) {
            setAddress(next);
        }
    }

    function allowRequest(url: string): boolean {
        const next = siteAddress(apiUrl, url);

        if (next !== url) {
            setAddress(next);

            return false;
        }

        return true;
    }

    return (
        <View style={[styles.screen, { paddingTop: insets.top, paddingBottom: insets.bottom }]}>
            <SiteWebView
                ref={web}
                source={{ uri: address }}
                onShouldStartLoadWithRequest={(request) => allowRequest(request.url)}
                style={styles.web}
                originWhitelist={['*']}
                setSupportMultipleWindows={false}
                javaScriptEnabled
                domStorageEnabled
                sharedCookiesEnabled
                thirdPartyCookiesEnabled
                allowsBackForwardNavigationGestures
                allowsInlineMediaPlayback
                mediaPlaybackRequiresUserAction={false}
                mixedContentMode="always"
                onNavigationStateChange={onNavigation}
                onLoadEnd={() => {
                    if (holdError.current) {
                        return;
                    }

                    setLoaded(true);
                    setFailed(false);
                    web.current?.injectJavaScript(playHeroVideo);
                }}
                onError={(event) => {
                    if (!isSiteDocument(event.nativeEvent.url)) {
                        return;
                    }

                    holdError.current = true;
                    setLoaded(true);
                    setFailed(true);
                }}
            />
            {!loaded ? (
                <View style={styles.splash}>
                    <ActivityIndicator color={colors.brand} size="large" />
                </View>
            ) : null}
            {failed ? (
                <View style={styles.splash}>
                    <Text style={styles.error}>The website could not be opened. Check that it is running on this network, then try again.</Text>
                    <Pressable
                        onPress={() => {
                            holdError.current = false;
                            setFailed(false);
                            setLoaded(false);
                            web.current?.reload();
                        }}
                        style={styles.retry}
                    >
                        <Text style={styles.retryLabel}>Try again</Text>
                    </Pressable>
                </View>
            ) : null}
        </View>
    );
}

const styles = StyleSheet.create({
    screen: {
        flex: 1,
        backgroundColor: colors.navy,
    },
    web: {
        flex: 1,
        backgroundColor: colors.navy,
    },
    splash: {
        ...StyleSheet.absoluteFill,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 16,
        paddingHorizontal: 28,
        backgroundColor: colors.navy,
    },
    error: {
        color: colors.white,
        fontSize: 16,
        lineHeight: 24,
        textAlign: 'center',
    },
    retry: {
        backgroundColor: colors.brand,
        borderRadius: 999,
        paddingHorizontal: 18,
        paddingVertical: 12,
    },
    retryLabel: {
        color: colors.white,
        fontSize: 15,
        fontWeight: '700',
    },
});
