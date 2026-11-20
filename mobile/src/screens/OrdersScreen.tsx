import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import { useAuth } from '../context/AuthContext';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';

export function OrdersScreen() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const { customer } = useAuth();

    return (
        <View style={styles.screen}>
            <Text style={styles.kicker}>Orders</Text>
            <Text style={styles.title}>My orders</Text>
            {customer ? (
                <>
                    <Text style={styles.copy}>Signed in as {customer.name}. Your orders stay on the Laravel account and are not listed on this screen yet.</Text>
                    <Pressable style={styles.button} onPress={() => navigation.navigate('Account')}>
                        <Text style={styles.buttonText}>Open account</Text>
                    </Pressable>
                </>
            ) : (
                <>
                    <Text style={styles.copy}>You can browse the homepage and services without an account. Sign in when you want to see orders placed on the website.</Text>
                    <Pressable style={styles.button} onPress={() => navigation.navigate('Login')}>
                        <Text style={styles.buttonText}>Sign in</Text>
                    </Pressable>
                </>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.canvas, padding: 20 },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    title: { marginTop: 8, color: colors.navy, fontSize: 32, fontWeight: '800' },
    copy: { marginTop: 12, color: colors.muted, fontSize: 16, lineHeight: 24 },
    button: { marginTop: 20, minHeight: 48, borderRadius: 12, backgroundColor: colors.brand, alignItems: 'center', justifyContent: 'center' },
    buttonText: { color: colors.white, fontSize: 16, fontWeight: '800' },
});
