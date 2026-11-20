export const launchRouteName = 'Home' as const;

/**
 * The app opens on the public Home page from the website.
 * A saved session never chooses the first screen.
 */
export function routeForLaunch(_session: { signedIn: boolean }): typeof launchRouteName {
    return launchRouteName;
}
