import React, { useEffect, useMemo, useState } from 'react';
import { Check, Languages, LoaderCircle, RotateCcw } from 'lucide-react';
import { translationStorage } from '../../utils/storage';

export type TranslationLanguage = 'en' | 'ja' | 'zh';

export interface AppliedTranslation {
  language: TranslationLanguage;
  title: string;
  content: string;
}

interface PostTranslationPanelProps {
  item: any;
  onApply: (translation: AppliedTranslation | null) => void;
}

const LANGUAGES: Array<{ id: TranslationLanguage; label: string; shortLabel: string }> = [
  { id: 'en', label: 'English', shortLabel: 'EN' },
  { id: 'ja', label: '日本語', shortLabel: '日本語' },
  { id: 'zh', label: '中文', shortLabel: '中文' },
];

const htmlToText = (value: string) => {
  if (!value) return '';
  if (typeof DOMParser === 'undefined') {
    return value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  }
  const documentNode = new DOMParser().parseFromString(value, 'text/html');
  return (documentNode.body.textContent || '').replace(/\s+/g, ' ').trim();
};

export const PostTranslationPanel: React.FC<PostTranslationPanelProps> = ({
  item,
  onApply,
}) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const [activeLanguage, setActiveLanguage] = useState<'original' | TranslationLanguage>('original');
  const [loadingLanguage, setLoadingLanguage] = useState<TranslationLanguage | null>(null);
  const [failedLanguage, setFailedLanguage] = useState<TranslationLanguage | null>(null);
  const [errorMessage, setErrorMessage] = useState('');
  const [cache, setCache] = useState<Partial<Record<TranslationLanguage, AppliedTranslation>>>({});
  const [preferredLanguage, setPreferredLanguage] = useState<TranslationLanguage>(
    translationStorage.getLanguage()
  );

  const config = useMemo(() => {
    const translation = item?.translation || {};
    const boTable = translation.boTable || translation.bo_table || item?.boTable || item?.bo_table || '';
    const wrId = Number(translation.wrId || translation.wr_id || item?.wrId || item?.wr_id || 0);
    const token =
      translation.token ||
      translation.translationToken ||
      item?.translationToken ||
      item?.translation_token ||
      '';
    const endpoint =
      translation.endpoint ||
      item?.translationEndpoint ||
      '/proc/eottae-post-translate.php';
    const sourceLanguage = translation.sourceLanguage || item?.sourceLanguage || 'ko';

    return { boTable, wrId, token, endpoint, sourceLanguage };
  }, [item]);

  const isAvailable = Boolean(config.boTable && config.wrId > 0 && config.token);

  useEffect(() => {
    setIsExpanded(false);
    setActiveLanguage('original');
    setLoadingLanguage(null);
    setFailedLanguage(null);
    setErrorMessage('');
    setCache({});
    onApply(null);
  }, [item?.id, config.boTable, config.wrId, onApply]);

  if (!isAvailable) return null;

  const restoreOriginal = () => {
    setActiveLanguage('original');
    setErrorMessage('');
    onApply(null);
  };

  const requestTranslation = async (language: TranslationLanguage) => {
    translationStorage.setLanguage(language);
    setPreferredLanguage(language);
    const cached = cache[language];
    if (cached) {
      setActiveLanguage(language);
      setErrorMessage('');
      onApply(cached);
      return;
    }

    const formData = new FormData();
    formData.append('bo_table', config.boTable);
    formData.append('wr_id', String(config.wrId));
    formData.append('source_language', config.sourceLanguage);
    formData.append('target_language', language);
    formData.append('token', config.token);

    setLoadingLanguage(language);
    setFailedLanguage(null);
    setErrorMessage('');

    try {
      const response = await fetch(config.endpoint, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      const payload = await response.json();
      if (!response.ok || !payload?.success) {
        throw new Error(payload?.message || 'translation_failed');
      }

      const translated: AppliedTranslation = {
        language,
        title: String(payload.translatedTitle || ''),
        content: htmlToText(String(payload.plainContent || payload.translatedContent || '')),
      };
      setCache((current) => ({ ...current, [language]: translated }));
      setActiveLanguage(language);
      onApply(translated);
    } catch {
      setFailedLanguage(language);
      setErrorMessage('자동 번역을 불러오지 못했습니다. 잠시 후 다시 시도해주세요.');
    } finally {
      setLoadingLanguage(null);
    }
  };

  return (
    <div className="mt-3 rounded-xl border border-[#B9E5FC] bg-[#F7FBFE] p-2.5">
      <div className="flex items-center justify-between gap-2">
        <div className="flex min-w-0 items-center gap-2">
          <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EAF8FF] text-[#079BE8]">
            <Languages size={16} />
          </span>
          <div className="min-w-0">
            <p className="text-xs font-bold text-[#183247]">게시글 번역</p>
            <p className="truncate text-[10px] text-[#8799A8]">
              {activeLanguage === 'original'
                ? '원문 표시 중'
                : `${LANGUAGES.find((language) => language.id === activeLanguage)?.label} 자동 번역`}
            </p>
          </div>
        </div>
        <button
          type="button"
          onClick={() => setIsExpanded((current) => !current)}
          aria-expanded={isExpanded}
          className="min-h-[36px] rounded-lg bg-white px-3 text-[11px] font-bold text-[#075A9D] ring-1 ring-[#B9E5FC]"
        >
          {isExpanded ? '닫기' : '번역'}
        </button>
      </div>

      {isExpanded && (
        <div className="mt-2.5 border-t border-[#E1ECF3] pt-2.5">
          <div className="grid grid-cols-4 gap-1.5" role="group" aria-label="게시글 번역 언어">
            <button
              type="button"
              onClick={restoreOriginal}
              className={`min-h-[38px] rounded-lg text-[10px] font-bold ${
                activeLanguage === 'original'
                  ? 'bg-[#075A9D] text-white'
                  : 'bg-white text-[#617789] ring-1 ring-[#E1ECF3]'
              }`}
            >
              원문
            </button>
            {LANGUAGES.map((language) => {
              const isLoading = loadingLanguage === language.id;
              const isActive = activeLanguage === language.id;
              return (
                <button
                  key={language.id}
                  type="button"
                  onClick={() => requestTranslation(language.id)}
                  disabled={Boolean(loadingLanguage)}
                  aria-label={`${language.label} 번역${preferredLanguage === language.id ? ', 기본 번역 언어' : ''}`}
                  className={`flex min-h-[38px] items-center justify-center gap-1 rounded-lg text-[10px] font-bold ${
                    isActive
                      ? 'bg-[#079BE8] text-white'
                      : 'bg-white text-[#617789] ring-1 ring-[#E1ECF3]'
                  } disabled:opacity-60`}
                >
                  {isLoading ? (
                    <LoaderCircle size={12} className="animate-spin" />
                  ) : isActive ? (
                    <Check size={12} />
                  ) : null}
                  {language.shortLabel}
                </button>
              );
            })}
          </div>

          {errorMessage ? (
            <div className="mt-2 flex items-center justify-between gap-2 rounded-lg bg-[#FFF3F1] px-2.5 py-2">
              <p className="text-[10px] font-medium text-[#B83B35]" role="status">
                {errorMessage}
              </p>
              {loadingLanguage === null && failedLanguage && (
                <button
                  type="button"
                  onClick={() => requestTranslation(failedLanguage)}
                  className="flex shrink-0 items-center gap-1 text-[10px] font-bold text-[#B83B35]"
                >
                  <RotateCcw size={11} />
                  재시도
                </button>
              )}
            </div>
          ) : activeLanguage !== 'original' ? (
            <p className="mt-2 text-[10px] leading-relaxed text-[#8799A8]">
              자동 번역으로 제공되며 일부 표현이 자연스럽지 않을 수 있습니다.
            </p>
          ) : null}
        </div>
      )}
    </div>
  );
};
