(function () {
  'use strict';

  var root = document.querySelector('[data-cost-calculator]');
  if (!root) {
    return;
  }

  var KRW_RATE = 24;
  var manualOverride = false;
  var currentStep = 1;

  var LABELS = {
    rent: '월세·숙소',
    utilities: '공과금',
    food: '식비',
    transport: '교통',
    mobile: '통신',
    visa: '비자',
    education: '교육',
    health: '병원·보험',
    leisure: '여가',
    buffer: '예비비'
  };

  var RENT_BASE = {
    studio: { central: 22000, general: 15000 },
    bed1: { central: 28000, general: 20000 },
    bed2: { central: 45000, general: 32000 },
    bed3: { central: 65000, general: 48000 },
    house: { central: 85000, general: 60000 }
  };

  var UTILITIES_BASE = {
    studio: { central: 5500, general: 4200 },
    bed1: { central: 6500, general: 5000 },
    bed2: { central: 9500, general: 7200 },
    bed3: { central: 14000, general: 10500 },
    house: { central: 18000, general: 13000 }
  };

  var GRADE_MULT = { local: 0.85, standard: 1, premium: 1.35 };
  var CONTRACT_MULT = { monthly: 1, shortstay: 1.25, airbnb: 1.5 };
  var AREA_MULT = {
    itpark: 1.08,
    cebucity: 1.05,
    mandaue: 0.96,
    mactan: 1.02,
    talisay: 0.9,
    other: 0.92
  };

  var FOOD_ADULT = { budget: 12000, normal: 18000, comfort: 28000 };
  var LEISURE_BASE = { budget: 5000, normal: 9000, comfort: 16000 };
  var BUFFER_RATE = { budget: 0.08, normal: 0.1, comfort: 0.12 };

  var TRANSPORT = {
    walk: { base: 1500, perAdult: 800, perMinor: 300, carExtra: 0 },
    grab: { base: 2500, perAdult: 1800, perMinor: 500, carExtra: 0 },
    motorcycle: { base: 3500, perAdult: 2200, perMinor: 0, carExtra: 0 },
    car: { base: 12000, perAdult: 3500, perMinor: 0, carExtra: 8000 }
  };

  var MOBILE = { base: 1800, perPerson: 650, internet: 1200 };

  var VISA_PER_ADULT = { monthly: 3500, longterm: 4500, relocation: 5500, family: 6000, nomad: 4000 };

  var HEALTH = { adult: 1800, minor: 1200, familyBase: 800 };

  var EDUCATION = {
    none: 0,
    local: 8000,
    academy: 15000,
    international: 55000
  };

  var PRESETS = {
    single: {
      adults: 1, minors: 0, stayType: 'monthly',
      bedroom: 'bed1', zone: 'central', area: 'itpark', grade: 'standard', contract: 'monthly',
      lifestyle: 'normal', transport: 'grab', education: 'none'
    },
    couple: {
      adults: 2, minors: 0, stayType: 'longterm',
      bedroom: 'bed1', zone: 'central', area: 'cebucity', grade: 'standard', contract: 'monthly',
      lifestyle: 'normal', transport: 'grab', education: 'none'
    },
    family21: {
      adults: 2, minors: 1, stayType: 'family',
      bedroom: 'bed2', zone: 'general', area: 'mandaue', grade: 'standard', contract: 'monthly',
      lifestyle: 'normal', transport: 'grab', education: 'academy'
    },
    family22: {
      adults: 2, minors: 2, stayType: 'family',
      bedroom: 'bed2', zone: 'general', area: 'talisay', grade: 'standard', contract: 'monthly',
      lifestyle: 'normal', transport: 'car', education: 'local'
    },
    family: {
      adults: 2, minors: 2, stayType: 'family',
      bedroom: 'bed3', zone: 'general', area: 'cebucity', grade: 'standard', contract: 'monthly',
      lifestyle: 'comfort', transport: 'car', education: 'international'
    },
    nomad: {
      adults: 1, minors: 0, stayType: 'nomad',
      bedroom: 'bed1', zone: 'central', area: 'itpark', grade: 'standard', contract: 'monthly',
      lifestyle: 'normal', transport: 'grab', education: 'none'
    },
    retire: {
      adults: 2, minors: 0, stayType: 'longterm',
      bedroom: 'bed2', zone: 'general', area: 'mactan', grade: 'standard', contract: 'monthly',
      lifestyle: 'comfort', transport: 'car', education: 'none'
    },
    student: {
      adults: 1, minors: 0, stayType: 'monthly',
      bedroom: 'studio', zone: 'central', area: 'cebucity', grade: 'local', contract: 'monthly',
      lifestyle: 'budget', transport: 'walk', education: 'academy'
    }
  };

  function money(value) {
    var num = Math.max(0, Math.round(parseFloat(value) || 0));
    return '₱' + String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function krw(value) {
    var num = Math.max(0, Math.round(parseFloat(value) || 0)) * KRW_RATE;
    return '약 ' + String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '원';
  }

  function krwRange(minVal, maxVal) {
    return '약 ' + String(Math.round(minVal * KRW_RATE)).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
      + ' ~ '
      + String(Math.round(maxVal * KRW_RATE)).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '원';
  }

  function fields() {
    return root.querySelectorAll('[data-cost-field]');
  }

  function getRadio(name) {
    var checked = root.querySelector('input[name="' + name + '"]:checked');
    return checked ? checked.value : '';
  }

  function setRadio(name, value) {
    var input = root.querySelector('input[name="' + name + '"][value="' + value + '"]');
    if (input) {
      input.checked = true;
    }
  }

  function getSelect(name) {
    var select = root.querySelector('select[name="' + name + '"]');
    return select ? select.value : '';
  }

  function setSelect(name, value) {
    var select = root.querySelector('select[name="' + name + '"]');
    if (select) {
      select.value = value;
    }
  }

  function getConfig() {
    return {
      adults: parseInt(getRadio('adults'), 10) || 1,
      minors: parseInt(getRadio('minors'), 10) || 0,
      stayType: getSelect('stayType') || 'monthly',
      bedroom: getRadio('bedroom') || 'bed1',
      zone: getRadio('zone') || 'central',
      area: getSelect('area') || 'itpark',
      grade: getSelect('grade') || 'standard',
      contract: getSelect('contract') || 'monthly',
      lifestyle: getRadio('lifestyle') || 'normal',
      transport: getSelect('transport') || 'grab',
      education: getSelect('education') || 'none'
    };
  }

  function applyConfig(config) {
    setRadio('adults', String(config.adults));
    setRadio('minors', String(config.minors));
    setSelect('stayType', config.stayType);
    setRadio('bedroom', config.bedroom);
    setRadio('zone', config.zone);
    setSelect('area', config.area);
    setSelect('grade', config.grade);
    setSelect('contract', config.contract);
    setRadio('lifestyle', config.lifestyle);
    setSelect('transport', config.transport);
    setSelect('education', config.education);
  }

  function recommendedBedroom(adults, minors) {
    var total = adults + minors;
    if (total <= 1) return 'studio';
    if (total <= 2) return 'bed1';
    if (total <= 4) return 'bed2';
    if (total <= 5) return 'bed3';
    return 'house';
  }

  function autoValues(config) {
    var adults = config.adults;
    var minors = config.minors;
    var bedroom = config.bedroom;
    var zone = config.zone;
    var gradeMult = GRADE_MULT[config.grade] || 1;
    var contractMult = CONTRACT_MULT[config.contract] || 1;
    var areaMult = AREA_MULT[config.area] || 1;
    var lifestyle = config.lifestyle || 'normal';

    var rentBase = (RENT_BASE[bedroom] && RENT_BASE[bedroom][zone]) ? RENT_BASE[bedroom][zone] : 25000;
    var rent = Math.round(rentBase * gradeMult * contractMult * areaMult);

    var utilBase = (UTILITIES_BASE[bedroom] && UTILITIES_BASE[bedroom][zone]) ? UTILITIES_BASE[bedroom][zone] : 6500;
    var utilities = Math.round(utilBase * (0.9 + (adults + minors * 0.4) * 0.08) * (lifestyle === 'comfort' ? 1.15 : 1));

    var foodPerAdult = FOOD_ADULT[lifestyle] || FOOD_ADULT.normal;
    var food = Math.round(adults * foodPerAdult + minors * foodPerAdult * 0.6);

    var transportCfg = TRANSPORT[config.transport] || TRANSPORT.grab;
    var transport = Math.round(
      transportCfg.base
      + adults * transportCfg.perAdult
      + minors * transportCfg.perMinor
      + transportCfg.carExtra
    );

    var mobile = Math.round(MOBILE.base + MOBILE.internet + (adults + minors) * MOBILE.perPerson);

    var visaRate = VISA_PER_ADULT[config.stayType] || VISA_PER_ADULT.monthly;
    var visa = Math.round(adults * visaRate);

    var educationType = minors > 0 ? config.education : 'none';
    var educationPerChild = EDUCATION[educationType] || 0;
    var education = Math.round(minors * educationPerChild);

    var health = Math.round(
      HEALTH.familyBase
      + adults * HEALTH.adult
      + minors * HEALTH.minor
      + (minors > 0 ? 1500 : 0)
    );

    var leisure = Math.round(
      (LEISURE_BASE[lifestyle] || LEISURE_BASE.normal)
      + adults * 3500 * (lifestyle === 'comfort' ? 1.4 : lifestyle === 'budget' ? 0.7 : 1)
      + minors * 1200
    );

    var subtotal = rent + utilities + food + transport + mobile + visa + education + health + leisure;
    var buffer = Math.round(subtotal * (BUFFER_RATE[lifestyle] || BUFFER_RATE.normal));

    return {
      rent: rent,
      utilities: utilities,
      food: food,
      transport: transport,
      mobile: mobile,
      visa: visa,
      education: education,
      health: health,
      leisure: leisure,
      buffer: buffer
    };
  }

  function applyAutoToFields(values) {
    fields().forEach(function (input) {
      var key = input.getAttribute('data-cost-field');
      if (typeof values[key] !== 'undefined') {
        input.value = values[key];
      }
    });
  }

  function getValues() {
    var values = {};
    fields().forEach(function (input) {
      var key = input.getAttribute('data-cost-field');
      values[key] = Math.max(0, parseInt(input.value, 10) || 0);
    });
    return values;
  }

  function sum(values) {
    return Object.keys(values).reduce(function (total, key) {
      return total + (parseInt(values[key], 10) || 0);
    }, 0);
  }

  function estimateRange(values) {
    var rentVar = Math.round((values.rent || 0) * 0.12);
    var foodVar = Math.round((values.food || 0) * 0.1);
    var total = sum(values);
    var minTotal = Math.max(0, total - rentVar - foodVar);
    var maxTotal = total + rentVar + foodVar;
    return { min: minTotal, max: maxTotal, total: total };
  }

  function householdSummary(config) {
    var parts = ['성인 ' + config.adults + '명'];
    if (config.minors > 0) {
      parts.push('미성년자 ' + config.minors + '명');
    }
    return parts.join(' · ');
  }

  function housingSummary(config) {
    var bedroomLabels = {
      studio: '스튜디오',
      bed1: '1베드',
      bed2: '2베드',
      bed3: '3베드',
      house: '하우스'
    };
    var zoneLabel = config.zone === 'central' ? '중심 생활권' : '일반 생활권';
    return (bedroomLabels[config.bedroom] || config.bedroom) + ' · ' + zoneLabel;
  }

  function levelText(total, config) {
    if (total < 60000) {
      return '절약형 생활비입니다. 숙소 위치와 외식 빈도에 따라 체감 차이가 큽니다.';
    }
    if (total < 120000) {
      return '일반적인 장기체류 예산에 가깝습니다. 월세와 식비가 핵심 변수입니다.';
    }
    if (total < 220000) {
      return '여유 있는 생활비입니다. 가족 체류나 콘도 생활을 고려한 예산입니다.';
    }
    if (config.minors >= 2 && config.education === 'international') {
      return '가족·국제학교 기준의 프리미엄 예산입니다. 학교·차량·의료비를 꼭 별도 확인하세요.';
    }
    return '프리미엄 또는 가족형 예산입니다. 학교·차량·의료비 조건을 꼭 별도로 확인하세요.';
  }

  function topCategory(values, total) {
    var topKey = 'rent';
    var topValue = 0;
    Object.keys(LABELS).forEach(function (key) {
      if ((values[key] || 0) > topValue) {
        topValue = values[key] || 0;
        topKey = key;
      }
    });
    var percent = total > 0 ? Math.round((topValue / total) * 100) : 0;
    return '가장 큰 지출: ' + LABELS[topKey] + ' (' + percent + '%)';
  }

  function familyNote(config, values) {
    if (config.minors < 1) {
      return '';
    }
    var notes = [];
    if ((values.education || 0) > 0) {
      notes.push('교육비');
    }
    if ((values.utilities || 0) >= 10000) {
      notes.push('전기·에어컨');
    }
    if ((values.health || 0) >= 5000) {
      notes.push('의료·보험');
    }
    if (!notes.length) {
      notes.push('식비', '교통');
    }
    return '가족 기준 주의 항목: ' + notes.join(', ');
  }

  function renderBreakdown(values, total) {
    var target = root.querySelector('[data-cost-breakdown]');
    if (!target) {
      return;
    }
    var rows = Object.keys(LABELS).map(function (key) {
      var value = values[key] || 0;
      var percent = total > 0 ? Math.round((value / total) * 100) : 0;
      return '<div><dt>' + LABELS[key] + '</dt><dd>' + money(value) + ' · ' + percent + '%</dd></div>';
    });
    target.innerHTML = rows.join('');
  }

  function calculate() {
    var config = getConfig();
    if (!manualOverride) {
      applyAutoToFields(autoValues(config));
    }

    var values = getValues();
    var range = estimateRange(values);
    var total = range.total;

    var totalEl = root.querySelector('[data-cost-total]');
    var rangeEl = root.querySelector('[data-cost-total-range]');
    var krwEl = root.querySelector('[data-cost-total-krw]');
    var summaryEl = root.querySelector('[data-cost-summary]');
    var levelEl = root.querySelector('[data-cost-level]');
    var topEl = root.querySelector('[data-cost-top]');
    var familyEl = root.querySelector('[data-cost-family-note]');
    var barEl = root.querySelector('[data-cost-bar]');

    if (totalEl) totalEl.textContent = money(total);
    if (rangeEl) {
      rangeEl.hidden = false;
      rangeEl.textContent = money(range.min) + ' ~ ' + money(range.max);
    }
    if (krwEl) krwEl.textContent = krwRange(range.min, range.max);
    if (summaryEl) {
      summaryEl.textContent = householdSummary(config) + ' · ' + housingSummary(config);
    }
    if (levelEl) levelEl.textContent = levelText(total, config);
    if (topEl) topEl.textContent = topCategory(values, total);
    if (familyEl) {
      var note = familyNote(config, values);
      familyEl.hidden = !note;
      familyEl.textContent = note;
    }
    if (barEl) barEl.style.width = Math.max(8, Math.min(100, Math.round((total / 250000) * 100))) + '%';
    renderBreakdown(values, total);
  }

  function goToStep(step) {
    currentStep = step;
    root.querySelectorAll('[data-cost-step]').forEach(function (button) {
      button.classList.toggle('is-active', parseInt(button.getAttribute('data-cost-step'), 10) === step);
    });
    root.querySelectorAll('[data-cost-step-panel]').forEach(function (panel) {
      var panelStep = parseInt(panel.getAttribute('data-cost-step-panel'), 10);
      var active = panelStep === step;
      panel.classList.toggle('is-active', active);
      panel.hidden = !active;
    });
  }

  function applyPreset(key) {
    var preset = PRESETS[key];
    if (!preset) {
      return;
    }
    manualOverride = false;
    applyConfig(preset);
    root.querySelectorAll('[data-cost-preset]').forEach(function (button) {
      button.classList.toggle('is-active', button.getAttribute('data-cost-preset') === key);
    });
    calculate();
    goToStep(1);
  }

  function bindStepNav() {
    root.querySelectorAll('[data-cost-step]').forEach(function (button) {
      button.addEventListener('click', function () {
        goToStep(parseInt(button.getAttribute('data-cost-step'), 10));
      });
    });

    root.querySelectorAll('[data-cost-next]').forEach(function (button) {
      button.addEventListener('click', function () {
        goToStep(parseInt(button.getAttribute('data-cost-next'), 10));
      });
    });

    root.querySelectorAll('[data-cost-prev]').forEach(function (button) {
      button.addEventListener('click', function () {
        goToStep(parseInt(button.getAttribute('data-cost-prev'), 10));
      });
    });
  }

  function bindInputs() {
    root.querySelectorAll('input[type="radio"], select[data-cost-select]').forEach(function (input) {
      input.addEventListener('change', function () {
        if (currentStep < 4) {
          manualOverride = false;
        }
        calculate();
      });
    });

    fields().forEach(function (input) {
      input.addEventListener('input', function () {
        manualOverride = true;
        calculate();
      });
      input.addEventListener('change', function () {
        manualOverride = true;
        calculate();
      });
    });

    var resetBtn = root.querySelector('[data-cost-reset-auto]');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        manualOverride = false;
        calculate();
      });
    }
  }

  root.querySelectorAll('[data-cost-preset]').forEach(function (button) {
    button.addEventListener('click', function () {
      applyPreset(button.getAttribute('data-cost-preset'));
    });
  });

  bindStepNav();
  bindInputs();
  applyPreset('single');
}());
