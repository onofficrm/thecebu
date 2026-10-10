import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'kr.co.thecebu.app',
  appName: '세부어때',
  webDir: 'dist',
  backgroundColor: '#F7FBFE',
  ios: {
    contentInset: 'never',
    preferredContentMode: 'mobile',
    scrollEnabled: true,
    allowsLinkPreview: false,
  },
  plugins: {
    Geolocation: {
      enableHighAccuracy: true,
    },
  },
};

export default config;
