const configured = process.env.EXPO_PUBLIC_API_URL?.trim();

export const apiUrl = (configured && configured.length > 0 ? configured : 'http://10.66.193.47:8090').replace(/\/$/, '');
