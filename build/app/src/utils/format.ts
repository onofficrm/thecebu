const PHONE_OR_DATE_PATTERN = /(?:\+?\d[\d -]{6,}\d)/g;
const LARGE_NUMBER_PATTERN = /(?<![\p{L}\p{N},.-])\d{4,}(?![\p{L}\p{N},.-])/gu;

export const formatNumericText = (value: unknown): string => {
  const source = String(value ?? '');
  const protectedValues: string[] = [];
  const protectedText = source.replace(PHONE_OR_DATE_PATTERN, (match) => {
    const token = `\uE000${protectedValues.length}\uE001`;
    protectedValues.push(match);
    return token;
  });

  const formatted = protectedText.replace(LARGE_NUMBER_PATTERN, (match) => {
    const numeric = Number(match);
    if (!Number.isFinite(numeric)) return match;
    if (numeric >= 1900 && numeric <= 2099 && match.length === 4) return match;
    if (match.length > 1 && match.startsWith('0')) return match;
    return numeric.toLocaleString('en-US');
  });

  return formatted.replace(/\uE000(\d+)\uE001/g, (_, index) => protectedValues[Number(index)] || '');
};

export const formatCount = (value: number): string =>
  Number.isFinite(value) ? value.toLocaleString('en-US') : '0';
