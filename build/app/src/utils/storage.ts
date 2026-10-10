import { AppMode, ServiceId } from '../types';

const STORAGE_KEYS = {
  MODE: 'thecebu_user_mode',
  ONBOARDING_COMPLETED: 'thecebu_onboarding_completed',
  SAVED_ITEMS: 'thecebu_saved_items',
  DOWNLOADED_COUPONS: 'thecebu_downloaded_coupons',
  SHORTCUTS_RESIDENT: 'thecebu_shortcuts_resident',
  SHORTCUTS_TOURIST: 'thecebu_shortcuts_tourist',
  TRANSLATION_LANGUAGE: 'thecebu_translation_language',
};
export const DEFAULT_RESIDENT_SHORTCUTS: ServiceId[] = [
  'news',
  'life_info',
  'marketplace',
  'chatrooms',
];

export const DEFAULT_TOURIST_SHORTCUTS: ServiceId[] = [
  'restaurants',
  'businesses',
  'events',
  'coupons',
];

export const SHORTCUT_SERVICE_IDS: ServiceId[] = [
  'news',
  'life_info',
  'marketplace',
  'jobs',
  'real_estate',
  'chatrooms',
  'restaurants',
  'businesses',
  'events',
  'coupons',
];

export const getDefaultShortcuts = (mode: AppMode): ServiceId[] =>
  mode === 'resident'
    ? [...DEFAULT_RESIDENT_SHORTCUTS]
    : [...DEFAULT_TOURIST_SHORTCUTS];

// In-memory fallback if localStorage is disabled or throws QuotaExceeded/Security error
const memoryStore = new Map<string, string>();

export const safeStorage = {
  get(key: string): string | null {
    try {
      if (typeof window !== 'undefined' && window.localStorage) {
        return window.localStorage.getItem(key);
      }
    } catch {
      // Fallback to in-memory store
    }
    return memoryStore.get(key) || null;
  },

  set(key: string, value: string): void {
    try {
      if (typeof window !== 'undefined' && window.localStorage) {
        window.localStorage.setItem(key, value);
      }
    } catch {
      // Storage unavailable or disabled
    }
    memoryStore.set(key, value);
  },

  remove(key: string): void {
    try {
      if (typeof window !== 'undefined' && window.localStorage) {
        window.localStorage.removeItem(key);
      }
    } catch {
      // Ignore
    }
    memoryStore.delete(key);
  },
};

export const modeStorage = {
  getMode(): AppMode | null {
    const val = safeStorage.get(STORAGE_KEYS.MODE);
    if (val === 'resident' || val === 'tourist') {
      return val;
    }
    return null;
  },

  setMode(mode: AppMode): void {
    safeStorage.set(STORAGE_KEYS.MODE, mode);
  },

  hasCompletedOnboarding(): boolean {
    return safeStorage.get(STORAGE_KEYS.ONBOARDING_COMPLETED) === 'true';
  },

  setOnboardingCompleted(): void {
    safeStorage.set(STORAGE_KEYS.ONBOARDING_COMPLETED, 'true');
  },

  resetOnboarding(): void {
    safeStorage.remove(STORAGE_KEYS.ONBOARDING_COMPLETED);
    safeStorage.remove(STORAGE_KEYS.MODE);
  },
};

export const shortcutStorage = {
  getShortcuts(mode: AppMode): ServiceId[] {
    const key =
      mode === 'resident'
        ? STORAGE_KEYS.SHORTCUTS_RESIDENT
        : STORAGE_KEYS.SHORTCUTS_TOURIST;
    const defaults =
      getDefaultShortcuts(mode);

    try {
      const raw = safeStorage.get(key);
      if (raw) {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) {
          if (parsed.length === 0) return [];

          const filtered = parsed.filter(
            (id): id is ServiceId =>
              typeof id === 'string' && SHORTCUT_SERVICE_IDS.includes(id as ServiceId)
          );
          const unique = [...new Set(filtered)].slice(0, 4);
          if (unique.length > 0) return unique;
        }
      }
    } catch {
      // fallback
    }

    return [...defaults];
  },

  setShortcuts(mode: AppMode, shortcuts: ServiceId[]): void {
    const key =
      mode === 'resident'
        ? STORAGE_KEYS.SHORTCUTS_RESIDENT
        : STORAGE_KEYS.SHORTCUTS_TOURIST;
    const validUnique = [...new Set(shortcuts)]
      .filter((id) => SHORTCUT_SERVICE_IDS.includes(id))
      .slice(0, 4);
    safeStorage.set(key, JSON.stringify(validUnique));
  },

  resetShortcuts(mode: AppMode): ServiceId[] {
    const key =
      mode === 'resident'
        ? STORAGE_KEYS.SHORTCUTS_RESIDENT
        : STORAGE_KEYS.SHORTCUTS_TOURIST;
    safeStorage.remove(key);
    return getDefaultShortcuts(mode);
  },
};

export type PreferredTranslationLanguage = 'en' | 'ja' | 'zh';

export const translationStorage = {
  getLanguage(): PreferredTranslationLanguage {
    const language = safeStorage.get(STORAGE_KEYS.TRANSLATION_LANGUAGE);
    return language === 'ja' || language === 'zh' ? language : 'en';
  },

  setLanguage(language: PreferredTranslationLanguage): void {
    safeStorage.set(STORAGE_KEYS.TRANSLATION_LANGUAGE, language);
  },
};
