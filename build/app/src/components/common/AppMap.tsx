import React, { useEffect, useMemo, useState } from 'react';
import L from 'leaflet';
import { CircleMarker, MapContainer, Marker, Polyline, Popup, TileLayer, useMap } from 'react-leaflet';
import { LoaderCircle, Navigation, X } from 'lucide-react';
import { requestCurrentLocation } from '../../utils/location';
import 'leaflet/dist/leaflet.css';

export interface AppMapPlace {
  id: string;
  name: string;
  category?: string;
  area?: string;
  address?: string;
  lat?: number | string | null;
  lng?: number | string | null;
  rawItem?: any;
}

interface AppMapProps {
  places: AppMapPlace[];
  selectedArea?: string;
  heightClassName?: string;
  onSelectPlace?: (place: AppMapPlace) => void;
  userLocation?: {
    latitude: number;
    longitude: number;
  } | null;
  enableDirections?: boolean;
}

const CEBU_CENTER: [number, number] = [10.3157, 123.8854];

const markerIcon = L.divIcon({
  className: '',
  html: '<span class="app-map-marker"><span></span></span>',
  iconSize: [34, 42],
  iconAnchor: [17, 40],
  popupAnchor: [0, -36],
});

const toCoordinate = (value: number | string | null | undefined) => {
  const parsed = typeof value === 'number' ? value : Number.parseFloat(String(value || ''));
  return Number.isFinite(parsed) ? parsed : null;
};

interface RouteSummary {
  distanceMeters: number;
  durationSeconds: number;
  points: Array<[number, number]>;
}

const formatRouteDistance = (distanceMeters: number) =>
  distanceMeters < 1000
    ? `${Math.round(distanceMeters).toLocaleString('ko-KR')}m`
    : `${(distanceMeters / 1000).toFixed(1)}km`;

const formatRouteDuration = (durationSeconds: number) => {
  const minutes = Math.max(1, Math.round(durationSeconds / 60));
  if (minutes < 60) return `약 ${minutes.toLocaleString('ko-KR')}분`;
  const hours = Math.floor(minutes / 60);
  const remainingMinutes = minutes % 60;
  return `약 ${hours}시간${remainingMinutes ? ` ${remainingMinutes}분` : ''}`;
};

const fetchDrivingRoute = async (
  start: { latitude: number; longitude: number },
  end: { latitude: number; longitude: number }
): Promise<RouteSummary> => {
  const baseUrl =
    String(import.meta.env.VITE_ROUTING_API_URL || 'https://router.project-osrm.org').replace(/\/$/, '');
  const url = `${baseUrl}/route/v1/driving/${start.longitude},${start.latitude};${end.longitude},${end.latitude}?overview=full&geometries=geojson&steps=false`;
  const response = await fetch(url);
  if (!response.ok) throw new Error('경로 서버에 연결할 수 없습니다.');

  const payload = await response.json();
  const route = payload?.routes?.[0];
  const coordinates = route?.geometry?.coordinates;
  if (!route || !Array.isArray(coordinates) || coordinates.length < 2) {
    throw new Error('이 위치까지의 자동차 경로를 찾지 못했습니다.');
  }

  return {
    distanceMeters: Number(route.distance) || 0,
    durationSeconds: Number(route.duration) || 0,
    points: coordinates.map(([longitude, latitude]: [number, number]) => [latitude, longitude]),
  };
};

const MapBounds: React.FC<{ points: Array<[number, number]>; routeActive?: boolean }> = ({
  points,
  routeActive,
}) => {
  const map = useMap();

  React.useEffect(() => {
    if (points.length === 1) {
      map.setView(points[0], 15);
    } else if (points.length > 1) {
      map.fitBounds(L.latLngBounds(points), {
        padding: routeActive ? [42, 42] : [28, 28],
        maxZoom: 14,
      });
    } else {
      map.setView(CEBU_CENTER, 11);
    }
  }, [map, points, routeActive]);

  return null;
};

export const AppMap: React.FC<AppMapProps> = ({
  places,
  selectedArea = '전체 세부',
  heightClassName = 'h-64',
  onSelectPlace,
  userLocation,
  enableDirections = false,
}) => {
  const [activeUserLocation, setActiveUserLocation] = useState(userLocation || null);
  const [route, setRoute] = useState<RouteSummary | null>(null);
  const [routeStatus, setRouteStatus] = useState<'idle' | 'locating' | 'routing' | 'success' | 'error'>('idle');
  const [routeError, setRouteError] = useState('');

  useEffect(() => {
    if (userLocation) setActiveUserLocation(userLocation);
  }, [userLocation]);

  const mappedPlaces = useMemo(
    () =>
      places
        .map((place) => ({
          ...place,
          latitude: toCoordinate(place.lat),
          longitude: toCoordinate(place.lng),
        }))
        .filter(
          (place): place is typeof place & { latitude: number; longitude: number } =>
            place.latitude !== null && place.longitude !== null
        ),
    [places]
  );
  const routeTarget = enableDirections && mappedPlaces.length === 1 ? mappedPlaces[0] : null;
  const points = useMemo<Array<[number, number]>>(
    () =>
      route?.points.length
        ? route.points
        : [
            ...mappedPlaces.map((place) => [place.latitude, place.longitude] as [number, number]),
            ...(activeUserLocation
              ? [[activeUserLocation.latitude, activeUserLocation.longitude] as [number, number]]
              : []),
          ],
    [mappedPlaces, activeUserLocation, route]
  );

  const startDirections = async () => {
    if (!routeTarget || routeStatus === 'locating' || routeStatus === 'routing') return;
    setRouteError('');
    setRouteStatus('locating');

    try {
      const currentLocation = activeUserLocation || (await requestCurrentLocation());
      setActiveUserLocation(currentLocation);
      setRouteStatus('routing');
      const nextRoute = await fetchDrivingRoute(currentLocation, {
        latitude: routeTarget.latitude,
        longitude: routeTarget.longitude,
      });
      setRoute(nextRoute);
      setRouteStatus('success');
    } catch (error) {
      setRoute(null);
      setRouteStatus('error');
      setRouteError(error instanceof Error ? error.message : '길찾기를 시작할 수 없습니다.');
    }
  };

  const closeDirections = () => {
    setRoute(null);
    setRouteError('');
    setRouteStatus('idle');
  };

  return (
    <div className="overflow-hidden rounded-2xl border border-[#C7E9FB] bg-[#F1F9FE]">
      <div className={`${heightClassName} relative z-0 w-full`}>
        <MapContainer
          center={points[0] || CEBU_CENTER}
          zoom={points.length === 1 ? 15 : 11}
          scrollWheelZoom
          className="h-full w-full"
          attributionControl
        >
          <TileLayer
            attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          />
          <MapBounds points={points} routeActive={Boolean(route)} />
          {route && (
            <Polyline
              positions={route.points}
              pathOptions={{ color: '#0879E7', weight: 6, opacity: 0.9 }}
            />
          )}
          {activeUserLocation && (
            <CircleMarker
              center={[activeUserLocation.latitude, activeUserLocation.longitude]}
              radius={9}
              pathOptions={{
                color: '#FFFFFF',
                weight: 3,
                fillColor: '#0879E7',
                fillOpacity: 1,
              }}
            >
              <Popup>
                <strong className="text-sm text-[#183247]">내 현재 위치</strong>
              </Popup>
            </CircleMarker>
          )}
          {mappedPlaces.map((place) => (
            <Marker
              key={place.id}
              position={[place.latitude, place.longitude]}
              icon={markerIcon}
              eventHandlers={{
                click: () => onSelectPlace?.(place),
              }}
            >
              <Popup>
                <div className="min-w-32 text-left">
                  <strong className="block text-sm text-[#183247]">{place.name}</strong>
                  {(place.category || place.area) && (
                    <span className="mt-1 block text-xs text-[#617789]">
                      {[place.category, place.area].filter(Boolean).join(' · ')}
                    </span>
                  )}
                  {onSelectPlace && (
                    <button
                      type="button"
                      onClick={() => onSelectPlace(place)}
                      className="mt-2 font-bold text-[#079BE8]"
                    >
                      앱에서 상세보기
                    </button>
                  )}
                </div>
              </Popup>
            </Marker>
          ))}
        </MapContainer>
      </div>
      <div className="flex items-center justify-between gap-2 border-t border-[#C7E9FB] bg-white px-3 py-2">
        <span className="truncate text-[10px] text-[#617789]">
          {activeUserLocation ? '현재 위치 기준' : `선택 지역: ${selectedArea}`}
        </span>
        <span className="shrink-0 text-[10px] font-bold text-[#079BE8]">
          앱 내 지도 · {mappedPlaces.length}곳
        </span>
      </div>
      {routeTarget && (
        <div className="border-t border-[#E1ECF3] bg-white px-3 py-3">
          {route ? (
            <div className="flex items-center justify-between gap-3">
              <div className="min-w-0">
                <p className="text-[11px] font-bold text-[#183247]">자동차 길찾기</p>
                <p className="mt-0.5 text-xs font-black text-[#0879E7]">
                  {formatRouteDistance(route.distanceMeters)} · {formatRouteDuration(route.durationSeconds)}
                </p>
              </div>
              <button
                type="button"
                onClick={closeDirections}
                className="flex min-h-[36px] shrink-0 items-center gap-1 rounded-xl border border-[#D7E6EF] bg-white px-3 text-[11px] font-bold text-[#617789]"
              >
                <X size={13} />
                경로 닫기
              </button>
            </div>
          ) : (
            <button
              type="button"
              onClick={startDirections}
              disabled={routeStatus === 'locating' || routeStatus === 'routing'}
              className="flex min-h-[42px] w-full items-center justify-center gap-2 rounded-xl bg-[#0879E7] px-4 text-xs font-bold text-white transition-colors hover:bg-[#075A9D] disabled:cursor-wait disabled:opacity-75"
            >
              {routeStatus === 'locating' || routeStatus === 'routing' ? (
                <LoaderCircle size={16} className="animate-spin" />
              ) : (
                <Navigation size={16} />
              )}
              {routeStatus === 'locating'
                ? '현재 위치 확인 중'
                : routeStatus === 'routing'
                  ? '가는 길 찾는 중'
                  : '현재 위치에서 길찾기'}
            </button>
          )}
          {routeError && (
            <p role="alert" className="mt-2 text-[11px] leading-relaxed text-[#B45309]">
              {routeError}
            </p>
          )}
          <p className="mt-2 text-[10px] leading-relaxed text-[#8799A8]">
            교통 상황이 반영되지 않은 참고용 자동차 경로입니다.
          </p>
        </div>
      )}
      {mappedPlaces.length === 0 && (
        <div className="border-t border-[#E1ECF3] bg-white px-4 py-3 text-center text-[11px] text-[#617789]">
          등록된 좌표가 없어 세부 중심 지도를 표시합니다.
        </div>
      )}
    </div>
  );
};
