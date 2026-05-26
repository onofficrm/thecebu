<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_hero_art_season')) {
    /**
     * 세부 기후감 장식 — 건기 / 우기 / 연말
     *
     * @return string dry|wet|festive
     */
    function eottae_calendar_hero_art_season($month = null)
    {
        $month = $month === null ? (int) date('n') : max(1, min(12, (int) $month));
        if ($month === 12 || $month <= 1) {
            return 'festive';
        }
        if ($month >= 6 && $month <= 11) {
            return 'wet';
        }

        return 'dry';
    }
}

if (!function_exists('eottae_calendar_hero_art_season_label')) {
    function eottae_calendar_hero_art_season_label($season)
    {
        switch ((string) $season) {
            case 'wet':
                return '우기';
            case 'festive':
                return '연말';
            default:
                return '건기';
        }
    }
}

if (!function_exists('eottae_calendar_hero_art_svg_dry')) {
    function eottae_calendar_hero_art_svg_dry()
    {
        return <<<'SVG'
<svg class="sebu-cal-hero-art__svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">
  <defs>
    <linearGradient id="sebuCalArtSky" x1="100" y1="8" x2="100" y2="90" gradientUnits="userSpaceOnUse">
      <stop stop-color="#7dd3fc" stop-opacity="0.35"/>
      <stop offset="1" stop-color="#a7f3d0" stop-opacity="0"/>
    </linearGradient>
    <linearGradient id="sebuCalArtSun" x1="158" y1="28" x2="178" y2="48" gradientUnits="userSpaceOnUse">
      <stop stop-color="#fde68a" stop-opacity="0.9"/>
      <stop offset="1" stop-color="#fbbf24" stop-opacity="0.5"/>
    </linearGradient>
  </defs>
  <ellipse cx="100" cy="52" rx="72" ry="40" fill="url(#sebuCalArtSky)"/>
  <circle cx="168" cy="38" r="14" fill="url(#sebuCalArtSun)" opacity="0.85"/>
  <g stroke="#0ea5e9" stroke-opacity="0.22" stroke-width="1.2" stroke-linecap="round">
    <path d="M168 18v6M168 58v6M148 38h6M182 38h6M153 23l4 4M179 53l4 4M153 53l4-4M179 23l-4 4"/>
  </g>
  <g transform="translate(28 58)">
    <rect x="0" y="0" width="108" height="88" rx="12" fill="#fff" fill-opacity="0.72" stroke="#0ea5e9" stroke-opacity="0.18" stroke-width="1.2"/>
    <rect x="0" y="0" width="108" height="22" rx="12" fill="#e0f2fe" fill-opacity="0.85"/>
    <rect x="0" y="10" width="108" height="12" fill="#e0f2fe" fill-opacity="0.85"/>
    <circle cx="18" cy="11" r="2.2" fill="#38bdf8" fill-opacity="0.45"/>
    <circle cx="28" cy="11" r="2.2" fill="#38bdf8" fill-opacity="0.35"/>
    <circle cx="38" cy="11" r="2.2" fill="#38bdf8" fill-opacity="0.25"/>
    <g fill="#0ea5e9" fill-opacity="0.12">
      <rect x="10" y="30" width="14" height="12" rx="3"/>
      <rect x="30" y="30" width="14" height="12" rx="3"/>
      <rect x="50" y="30" width="14" height="12" rx="3"/>
      <rect x="70" y="30" width="14" height="12" rx="3"/>
      <rect x="30" y="48" width="14" height="12" rx="3" fill-opacity="0.22"/>
      <rect x="50" y="48" width="14" height="12" rx="3"/>
    </g>
    <circle cx="64" cy="54" r="5" fill="#fbbf24" fill-opacity="0.55"/>
  </g>
  <path d="M152 118c8-18 22-28 38-30 6-1 12 0 18 3-6 14-18 24-34 28-8 2-15 1-22-1z" fill="#34d399" fill-opacity="0.14" stroke="#10b981" stroke-opacity="0.2" stroke-width="1"/>
  <path d="M158 95c-2 8-1 16 4 24 3 5 8 9 14 11-3-10-2-20 3-29 2-4 5-7 9-9-5-1-10 0-15 2-9 4-13 11-15 1z" fill="#6ee7b7" fill-opacity="0.18"/>
</svg>
SVG;
    }
}

if (!function_exists('eottae_calendar_hero_art_svg_wet')) {
    function eottae_calendar_hero_art_svg_wet()
    {
        return <<<'SVG'
<svg class="sebu-cal-hero-art__svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">
  <defs>
    <linearGradient id="sebuCalArtWetSky" x1="100" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
      <stop stop-color="#94a3b8" stop-opacity="0.2"/>
      <stop offset="1" stop-color="#bae6fd" stop-opacity="0.05"/>
    </linearGradient>
  </defs>
  <ellipse cx="100" cy="48" rx="78" ry="42" fill="url(#sebuCalArtWetSky)"/>
  <ellipse cx="62" cy="36" rx="28" ry="14" fill="#cbd5e1" fill-opacity="0.35"/>
  <ellipse cx="118" cy="32" rx="34" ry="16" fill="#e2e8f0" fill-opacity="0.4"/>
  <ellipse cx="155" cy="44" rx="22" ry="12" fill="#cbd5e1" fill-opacity="0.28"/>
  <g transform="translate(28 58)">
    <rect x="0" y="0" width="108" height="88" rx="12" fill="#fff" fill-opacity="0.7" stroke="#64748b" stroke-opacity="0.15" stroke-width="1.2"/>
    <rect x="0" y="0" width="108" height="22" rx="12" fill="#e2e8f0" fill-opacity="0.75"/>
    <g fill="#64748b" fill-opacity="0.14">
      <rect x="10" y="30" width="14" height="12" rx="3"/>
      <rect x="30" y="30" width="14" height="12" rx="3"/>
      <rect x="50" y="30" width="14" height="12" rx="3"/>
      <rect x="30" y="48" width="14" height="12" rx="3" fill-opacity="0.22"/>
    </g>
    <circle cx="64" cy="54" r="5" fill="#38bdf8" fill-opacity="0.4"/>
  </g>
  <g stroke="#38bdf8" stroke-opacity="0.25" stroke-width="1.2" stroke-linecap="round">
    <path d="M24 108l2 6M32 104l2 6M40 110l2 6M48 106l2 6"/>
    <path d="M130 112l2 6M138 108l2 6M146 114l2 6"/>
  </g>
  <path d="M8 132c12-6 24-4 34 4 4 3 7 8 8 14-14-2-26-8-36-18-3-3-5-6-6-0z" fill="#7dd3fc" fill-opacity="0.1"/>
</svg>
SVG;
    }
}

if (!function_exists('eottae_calendar_hero_art_svg_festive')) {
    function eottae_calendar_hero_art_svg_festive()
    {
        return <<<'SVG'
<svg class="sebu-cal-hero-art__svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">
  <defs>
    <linearGradient id="sebuCalArtFestSky" x1="100" y1="8" x2="100" y2="90" gradientUnits="userSpaceOnUse">
      <stop stop-color="#fde68a" stop-opacity="0.22"/>
      <stop offset="1" stop-color="#e0f2fe" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <ellipse cx="100" cy="52" rx="72" ry="40" fill="url(#sebuCalArtFestSky)"/>
  <circle cx="168" cy="38" r="12" fill="#fde68a" fill-opacity="0.55"/>
  <g fill="#fbbf24" fill-opacity="0.45">
    <circle cx="24" cy="28" r="1.5"/><circle cx="52" cy="18" r="1.2"/><circle cx="140" cy="22" r="1.4"/><circle cx="178" cy="52" r="1.3"/>
  </g>
  <g transform="translate(28 58)">
    <rect x="0" y="0" width="108" height="88" rx="12" fill="#fff" fill-opacity="0.74" stroke="#0ea5e9" stroke-opacity="0.16" stroke-width="1.2"/>
    <rect x="0" y="0" width="108" height="22" rx="12" fill="#fef3c7" fill-opacity="0.7"/>
    <g fill="#0ea5e9" fill-opacity="0.11">
      <rect x="10" y="30" width="14" height="12" rx="3"/>
      <rect x="30" y="30" width="14" height="12" rx="3"/>
      <rect x="50" y="30" width="14" height="12" rx="3"/>
      <rect x="50" y="48" width="14" height="12" rx="3" fill-opacity="0.2"/>
    </g>
    <circle cx="64" cy="54" r="5" fill="#f59e0b" fill-opacity="0.45"/>
  </g>
  <path d="M152 118c8-18 22-28 38-30 6-1 12 0 18 3-6 14-18 24-34 28-8 2-15 1-22-1z" fill="#34d399" fill-opacity="0.12"/>
</svg>
SVG;
    }
}

if (!function_exists('eottae_calendar_hero_art_svg')) {
    function eottae_calendar_hero_art_svg($season = null)
    {
        if ($season === null) {
            $season = eottae_calendar_hero_art_season();
        }
        switch ((string) $season) {
            case 'wet':
                return eottae_calendar_hero_art_svg_wet();
            case 'festive':
                return eottae_calendar_hero_art_svg_festive();
            default:
                return eottae_calendar_hero_art_svg_dry();
        }
    }
}

if (!function_exists('eottae_render_calendar_hero_art')) {
    function eottae_render_calendar_hero_art()
    {
        $season = eottae_calendar_hero_art_season();
        $label = eottae_calendar_hero_art_season_label($season);
        echo '<div class="sebu-cal-page__hero-art sebu-cal-hero-art sebu-cal-hero-art--'.htmlspecialchars($season, ENT_QUOTES, 'UTF-8').'" data-season="'.htmlspecialchars($season, ENT_QUOTES, 'UTF-8').'">';
        echo '<span class="sebu-cal-hero-art__season" aria-hidden="true">'.$label.'</span>';
        echo eottae_calendar_hero_art_svg($season);
        echo '</div>';
    }
}
