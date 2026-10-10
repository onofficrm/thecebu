import { Geolocation } from '@capacitor/geolocation';

export interface UserLocation {
  latitude: number;
  longitude: number;
}

const locationErrorMessage = (error: unknown) => {
  const message = error instanceof Error ? error.message.toLowerCase() : '';

  if (
    message.includes('denied') ||
    message.includes('permission') ||
    message.includes('unauthorized')
  ) {
    return '위치 권한이 꺼져 있습니다. 앱 설정에서 위치 권한을 허용해주세요.';
  }
  if (message.includes('timeout')) {
    return '위치 확인 시간이 초과되었습니다. 다시 시도해주세요.';
  }
  return '현재 위치를 확인할 수 없습니다.';
};

/**
 * Call only from an explicit user action. Capacitor requests the native iOS
 * location permission when getCurrentPosition runs for the first time.
 */
export async function requestCurrentLocation(): Promise<UserLocation> {
  try {
    const position = await Geolocation.getCurrentPosition({
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 300000,
    });

    return {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude,
    };
  } catch (error) {
    throw new Error(locationErrorMessage(error));
  }
}
