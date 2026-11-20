import { NavigationContainer, DefaultTheme, createNavigationContainerRef, type NavigationState, useRoute, type RouteProp } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useEffect, useRef, useState } from 'react';

import { HomeScreen, SiteScreen } from '../screens/HomeScreen';
import { colors } from '../theme';
import { launchRouteName } from './launch';
import type { RootStack } from './types';

const Stack = createNativeStackNavigator<RootStack>();

function ServiceSite() {
    const route = useRoute<RouteProp<RootStack, 'Service'>>();

    return <SiteScreen path={`/services/${encodeURIComponent(route.params.slug)}`} />;
}

function MenuSite() {
    const route = useRoute<RouteProp<RootStack, 'MenuPage'>>();

    return <SiteScreen path={route.params.path} />;
}
const navigationRef = createNavigationContainerRef<RootStack>();

const navigationTheme = {
    ...DefaultTheme,
    colors: {
        ...DefaultTheme.colors,
        background: colors.white,
        primary: colors.brand,
        card: colors.white,
        text: colors.navy,
        border: colors.line,
    },
};

const homeState = {
    index: 0,
    routes: [{ name: launchRouteName }],
};

function rootName(state: NavigationState | undefined): string | undefined {
    if (!state) {
        return undefined;
    }

    return state.routes[state.index]?.name;
}

export function RootNavigator() {
    const [navigationReady, setNavigationReady] = useState(false);
    const launchSettled = useRef(false);

    useEffect(() => {
        if (!navigationReady || launchSettled.current || !navigationRef.isReady()) {
            return;
        }

        const name = rootName(navigationRef.getRootState());
        if (name && name !== launchRouteName) {
            navigationRef.reset(homeState);
        }

        launchSettled.current = true;
    }, [navigationReady]);

    return (
        <NavigationContainer
            ref={navigationRef}
            theme={navigationTheme}
            initialState={homeState}
            linking={{ enabled: false, prefixes: [] }}
            onReady={() => {
                if (rootName(navigationRef.getRootState()) !== launchRouteName) {
                    navigationRef.reset(homeState);
                }
                setNavigationReady(true);
            }}
            onStateChange={(state) => {
                if (launchSettled.current) {
                    return;
                }

                if (rootName(state) !== launchRouteName && navigationRef.isReady()) {
                    navigationRef.reset(homeState);
                }
            }}
        >
            <Stack.Navigator initialRouteName={launchRouteName} screenOptions={{ headerShown: false, headerStyle: { backgroundColor: colors.white }, headerTintColor: colors.navy, headerShadowVisible: false }}>
                <Stack.Screen name="Home" component={HomeScreen} />
                <Stack.Screen name="Services">{() => <SiteScreen path="/services" />}</Stack.Screen>
                <Stack.Screen name="Service" component={ServiceSite} />
                <Stack.Screen name="MenuPage" component={MenuSite} />
                <Stack.Screen name="Login">{() => <SiteScreen path="/login" />}</Stack.Screen>
                <Stack.Screen name="Register">{() => <SiteScreen path="/register" />}</Stack.Screen>
                <Stack.Screen name="Account">{() => <SiteScreen path="/customer/dashboard" />}</Stack.Screen>
            </Stack.Navigator>
        </NavigationContainer>
    );
}
