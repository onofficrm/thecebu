export const colors = {
  brandBlue: '#079BE8',
  tropicalCyan: '#00C5E8',
  deepBlue: '#0879E7',
  accessibleBlue: '#075A9D',
  mainBackground: '#F7FBFE',
  cardBackground: '#FFFFFF',
  lightSkyBackground: '#EAF8FF',
  softBlueBackground: '#F1F9FE',
  primaryText: '#183247',
  secondaryText: '#617789',
  mutedText: '#8799A8',
  defaultBorder: '#E1ECF3',
  lightDivider: '#ECF2F6',
} as const;

export const radius = {
  control: 12,
  card: 16,
  sheet: 24,
} as const;

export const touchTarget = {
  minimum: 44,
  navigation: 48,
} as const;

export const brandGradient = `linear-gradient(135deg, ${colors.tropicalCyan}, ${colors.brandBlue}, ${colors.deepBlue})`;
