<?php
include_once(dirname(__FILE__).'/_init.php');

$page_title = '세부 물가·생활비 계산기 | 세부어때';
$page_description = '성인·미성년자 구성, 주거 조건, 생활 스타일을 선택하면 세부 월 생활비를 자동 계산합니다. 한달살기, 장기체류, 가족 이주 준비에 활용하세요.';
$page_canonical = G5_URL.'/page/eottae-cost-calculator.php';

$cost_css = G5_PATH.'/css/eottae-cost-calculator.css';
$cost_js = G5_PATH.'/js/eottae-cost-calculator.js';
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-cost-calculator.css?ver='.(is_file($cost_css) ? (int) filemtime($cost_css) : 0).'">', 35);
add_javascript('<script src="'.G5_JS_URL.'/eottae-cost-calculator.js?ver='.(is_file($cost_js) ? (int) filemtime($cost_js) : 0).'" defer></script>', 25);

$map_url = G5_URL.'/cebu-map/';
$estate_url = G5_URL.'/cebu-map/?type=estate';
$shop_url = function_exists('eottae_shop_list_url') ? eottae_shop_list_url() : G5_BBS_URL.'/board.php?bo_table='.(defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');
$job_url = G5_URL.'/cebu-map/?type=job';

g5_page_start('세부 물가·생활비 계산기');
?>
<main class="cost-calculator-page" data-cost-calculator>
    <header class="cost-calculator-hero">
        <p class="cost-calculator-hero__eyebrow">Cebu Cost Calculator</p>
        <h1 class="cost-calculator-hero__title">세부 물가·생활비 계산기</h1>
        <p class="cost-calculator-hero__lead">구성원 → 주거 → 생활 스타일 순으로 선택하면 월 생활비가 자동 계산됩니다. 금액은 참고용이며 실제 비용은 지역, 계약 조건, 생활 방식에 따라 달라질 수 있습니다.</p>
        <div class="cost-calculator-hero__actions">
            <a href="<?php echo get_text($estate_url); ?>">부동산 매물 보기</a>
            <a href="<?php echo get_text($shop_url); ?>">생활 업체 찾기</a>
            <a href="<?php echo get_text($map_url); ?>">생활지도 열기</a>
        </div>
    </header>

    <section class="cost-calculator-presets" aria-labelledby="costPresetTitle">
        <div>
            <p class="cost-calculator-section-kicker">Lifestyle Preset</p>
            <h2 id="costPresetTitle">상황별 프리셋</h2>
            <p>가장 가까운 유형을 고른 뒤 단계별 항목을 조정하세요.</p>
        </div>
        <div class="cost-calculator-presets__buttons" role="group" aria-label="생활비 프리셋">
            <button type="button" data-cost-preset="single">혼자 한달살기</button>
            <button type="button" data-cost-preset="couple">커플 장기체류</button>
            <button type="button" data-cost-preset="family21">성인2·아이1</button>
            <button type="button" data-cost-preset="family22">성인2·아이2</button>
            <button type="button" data-cost-preset="family">가족 이주</button>
            <button type="button" data-cost-preset="nomad">디지털 노마드</button>
            <button type="button" data-cost-preset="retire">은퇴 장기체류</button>
            <button type="button" data-cost-preset="student">유학·어학연수</button>
        </div>
    </section>

    <nav class="cost-wizard-steps" aria-label="계산 단계" data-cost-steps-nav>
        <button type="button" class="is-active" data-cost-step="1">구성원</button>
        <button type="button" data-cost-step="2">주거</button>
        <button type="button" data-cost-step="3">생활 스타일</button>
        <button type="button" data-cost-step="4">결과</button>
    </nav>

    <section class="cost-calculator-layout">
        <form class="cost-calculator-form" aria-label="세부 생활비 입력">
            <section class="cost-wizard-panel is-active" data-cost-step-panel="1" aria-labelledby="costStep1Title">
                <div class="cost-calculator-card">
                    <h2 id="costStep1Title">누가 살 예정인가요?</h2>
                    <p class="cost-card-desc">성인·미성년자 수에 따라 식비, 통신, 교육, 의료비가 자동 반영됩니다.</p>

                    <div class="cost-member-grid">
                        <fieldset class="cost-choice-group cost-choice-group--member">
                            <legend class="cost-choice-group__legend cost-choice-group__legend--adult">
                                <span class="cost-choice-group__legend-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"></circle><path d="M5 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"></path></svg>
                                </span>
                                <span class="cost-choice-group__legend-text">성인</span>
                            </legend>
                            <div class="cost-choice-row" role="radiogroup" aria-label="성인 수">
                                <label class="cost-choice"><input type="radio" name="adults" value="1" checked><span>1인</span></label>
                                <label class="cost-choice"><input type="radio" name="adults" value="2"><span>2인</span></label>
                                <label class="cost-choice"><input type="radio" name="adults" value="3"><span>3인</span></label>
                            </div>
                        </fieldset>

                        <fieldset class="cost-choice-group cost-choice-group--member">
                            <legend class="cost-choice-group__legend cost-choice-group__legend--minor">
                                <span class="cost-choice-group__legend-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="6" r="3"></circle><path d="M8 12h8"></path><path d="M5 20v-1.5a3.5 3.5 0 0 1 3.5-3.5h5a3.5 3.5 0 0 1 3.5 3.5V20"></path></svg>
                                </span>
                                <span class="cost-choice-group__legend-text">미성년자</span>
                            </legend>
                            <div class="cost-choice-row" role="radiogroup" aria-label="미성년자 수">
                                <label class="cost-choice"><input type="radio" name="minors" value="0" checked><span>0명</span></label>
                                <label class="cost-choice"><input type="radio" name="minors" value="1"><span>1명</span></label>
                                <label class="cost-choice"><input type="radio" name="minors" value="2"><span>2명</span></label>
                                <label class="cost-choice"><input type="radio" name="minors" value="3"><span>3명</span></label>
                                <label class="cost-choice"><input type="radio" name="minors" value="4"><span>4명</span></label>
                            </div>
                        </fieldset>
                    </div>

                    <label class="cost-field">
                        <span>체류 형태</span>
                        <select name="stayType" data-cost-select>
                            <option value="monthly">한달살기</option>
                            <option value="longterm">장기체류</option>
                            <option value="relocation">이주 준비</option>
                            <option value="family">가족 이주</option>
                            <option value="nomad">디지털 노마드</option>
                        </select>
                    </label>
                </div>
                <div class="cost-wizard-actions">
                    <button type="button" class="cost-wizard-next" data-cost-next="2">다음: 주거</button>
                </div>
            </section>

            <section class="cost-wizard-panel" data-cost-step-panel="2" aria-labelledby="costStep2Title" hidden>
                <div class="cost-calculator-card">
                    <h2 id="costStep2Title">어디에 살 예정인가요?</h2>
                    <p class="cost-card-desc">방 타입과 생활권, 지역에 따라 월세·공과금이 달라집니다.</p>

                    <fieldset class="cost-choice-group">
                        <legend class="cost-choice-group__legend">방 타입</legend>
                        <div class="cost-choice-row cost-choice-row--wrap">
                            <label class="cost-choice"><input type="radio" name="bedroom" value="studio"><span>스튜디오</span></label>
                            <label class="cost-choice"><input type="radio" name="bedroom" value="bed1" checked><span>1베드</span></label>
                            <label class="cost-choice"><input type="radio" name="bedroom" value="bed2"><span>2베드</span></label>
                            <label class="cost-choice"><input type="radio" name="bedroom" value="bed3"><span>3베드</span></label>
                            <label class="cost-choice"><input type="radio" name="bedroom" value="house"><span>하우스</span></label>
                        </div>
                    </fieldset>

                    <fieldset class="cost-choice-group">
                        <legend class="cost-choice-group__legend">생활권</legend>
                        <div class="cost-choice-row">
                            <label class="cost-choice"><input type="radio" name="zone" value="central" checked><span>중심 생활권</span></label>
                            <label class="cost-choice"><input type="radio" name="zone" value="general"><span>일반 생활권</span></label>
                        </div>
                        <p class="cost-field-hint">중심 생활권: IT Park, Ayala, 세부시티 핵심부 · 일반 생활권: Banilad 외곽, Mandaue, Talisay 등</p>
                    </fieldset>

                    <div class="cost-field-grid">
                        <label class="cost-field">
                            <span>지역</span>
                            <select name="area" data-cost-select>
                                <option value="itpark">IT Park</option>
                                <option value="cebucity">세부시티</option>
                                <option value="mandaue">만다웨</option>
                                <option value="mactan">막탄</option>
                                <option value="talisay">탈리사이</option>
                                <option value="other">기타</option>
                            </select>
                        </label>

                        <label class="cost-field">
                            <span>건물 등급</span>
                            <select name="grade" data-cost-select>
                                <option value="local">로컬형</option>
                                <option value="standard" selected>일반 콘도</option>
                                <option value="premium">고급 콘도</option>
                            </select>
                        </label>

                        <label class="cost-field">
                            <span>계약 형태</span>
                            <select name="contract" data-cost-select>
                                <option value="monthly" selected>월세</option>
                                <option value="shortstay">단기 숙소</option>
                                <option value="airbnb">에어비앤비</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="cost-wizard-actions">
                    <button type="button" class="cost-wizard-prev" data-cost-prev="1">이전</button>
                    <button type="button" class="cost-wizard-next" data-cost-next="3">다음: 생활 스타일</button>
                </div>
            </section>

            <section class="cost-wizard-panel" data-cost-step-panel="3" aria-labelledby="costStep3Title" hidden>
                <div class="cost-calculator-card">
                    <h2 id="costStep3Title">생활 스타일은 어떤가요?</h2>
                    <p class="cost-card-desc">식비·여가·외식 빈도와 교통 방식, 아이 교육 조건을 반영합니다.</p>

                    <fieldset class="cost-choice-group">
                        <legend class="cost-choice-group__legend">생활 수준</legend>
                        <div class="cost-choice-row">
                            <label class="cost-choice"><input type="radio" name="lifestyle" value="budget"><span>절약형</span></label>
                            <label class="cost-choice"><input type="radio" name="lifestyle" value="normal" checked><span>일반형</span></label>
                            <label class="cost-choice"><input type="radio" name="lifestyle" value="comfort"><span>여유형</span></label>
                        </div>
                    </fieldset>

                    <label class="cost-field">
                        <span>교통 방식</span>
                        <select name="transport" data-cost-select>
                            <option value="walk">도보·근거리 위주</option>
                            <option value="grab" selected>그랩·택시 위주</option>
                            <option value="motorcycle">오토바이</option>
                            <option value="car">자차</option>
                        </select>
                    </label>

                    <label class="cost-field">
                        <span>아이 교육 (미성년자 있을 때)</span>
                        <select name="education" data-cost-select>
                            <option value="none" selected>없음 / 해당 없음</option>
                            <option value="local">로컬 학교</option>
                            <option value="academy">학원 중심</option>
                            <option value="international">국제학교</option>
                        </select>
                    </label>
                </div>
                <div class="cost-wizard-actions">
                    <button type="button" class="cost-wizard-prev" data-cost-prev="2">이전</button>
                    <button type="button" class="cost-wizard-next" data-cost-next="4">결과 보기</button>
                </div>
            </section>

            <section class="cost-wizard-panel" data-cost-step-panel="4" aria-labelledby="costStep4Title" hidden>
                <div class="cost-calculator-card">
                    <h2 id="costStep4Title">세부 금액 조정</h2>
                    <p class="cost-card-desc">자동 계산값을 기준으로 항목별 금액을 직접 수정할 수 있습니다.</p>

                    <div class="cost-calculator-card cost-calculator-card--nested">
                        <h3>주거</h3>
                        <label class="cost-field">
                            <span>월세·숙소</span>
                            <input type="number" min="0" step="500" data-cost-field="rent" value="25000">
                        </label>
                        <label class="cost-field">
                            <span>전기·수도·관리비</span>
                            <input type="number" min="0" step="500" data-cost-field="utilities" value="6500">
                        </label>
                    </div>

                    <div class="cost-calculator-card cost-calculator-card--nested">
                        <h3>생활</h3>
                        <label class="cost-field">
                            <span>식비·장보기</span>
                            <input type="number" min="0" step="500" data-cost-field="food" value="18000">
                        </label>
                        <label class="cost-field">
                            <span>교통비</span>
                            <input type="number" min="0" step="200" data-cost-field="transport" value="4500">
                        </label>
                        <label class="cost-field">
                            <span>통신·인터넷</span>
                            <input type="number" min="0" step="200" data-cost-field="mobile" value="2500">
                        </label>
                    </div>

                    <div class="cost-calculator-card cost-calculator-card--nested">
                        <h3>체류·가족</h3>
                        <label class="cost-field">
                            <span>비자·연장·서류</span>
                            <input type="number" min="0" step="500" data-cost-field="visa" value="3500">
                        </label>
                        <label class="cost-field">
                            <span>교육·학원·아이 비용</span>
                            <input type="number" min="0" step="1000" data-cost-field="education" value="0">
                        </label>
                        <label class="cost-field">
                            <span>병원·보험·약</span>
                            <input type="number" min="0" step="500" data-cost-field="health" value="2500">
                        </label>
                    </div>

                    <div class="cost-calculator-card cost-calculator-card--nested">
                        <h3>여유 비용</h3>
                        <label class="cost-field">
                            <span>여가·카페·외식</span>
                            <input type="number" min="0" step="500" data-cost-field="leisure" value="9000">
                        </label>
                        <label class="cost-field">
                            <span>예비비</span>
                            <input type="number" min="0" step="500" data-cost-field="buffer" value="8000">
                        </label>
                    </div>

                    <button type="button" class="cost-reset-auto" data-cost-reset-auto>자동 계산값으로 되돌리기</button>
                </div>
                <div class="cost-wizard-actions">
                    <button type="button" class="cost-wizard-prev" data-cost-prev="3">이전</button>
                </div>
            </section>
        </form>

        <aside class="cost-result-panel" aria-live="polite">
            <div class="cost-result-panel__hero">
                <p class="cost-result-panel__label">월 예상 생활비</p>
                <strong data-cost-total>₱0</strong>
                <div class="cost-result-panel__range">
                    <span data-cost-total-range hidden></span>
                    <span data-cost-total-krw>약 0원</span>
                </div>
            </div>
            <div class="cost-result-panel__chips" data-cost-summary></div>
            <div class="cost-result-panel__insights">
                <p data-cost-level class="cost-result-panel__level">생활비 수준을 계산 중입니다.</p>
                <p data-cost-top class="cost-result-panel__top"></p>
                <p data-cost-family-note class="cost-result-panel__family-note" hidden></p>
            </div>
            <div class="cost-result-panel__bar" aria-hidden="true"><span data-cost-bar></span></div>
            <div class="cost-result-panel__breakdown-head">
                <span>항목별 비중</span>
                <span data-cost-breakdown-total></span>
            </div>
            <dl class="cost-result-panel__breakdown" data-cost-breakdown></dl>
            <p class="cost-result-panel__note">환율은 간단 계산용으로 1페소=24원 기준입니다. 범위는 주거·식비 변동을 반영한 참고치입니다.</p>
        </aside>
    </section>

    <section class="cost-calculator-guide" aria-labelledby="costGuideTitle">
        <p class="cost-calculator-section-kicker">Next Step</p>
        <h2 id="costGuideTitle">계산 후 확인하면 좋은 것</h2>
        <div class="cost-calculator-guide__grid">
            <a href="<?php echo get_text($estate_url); ?>">
                <strong>월세 확인</strong>
                <span>선택한 지역·방 타입에 맞는 매물을 지도에서 비교하세요.</span>
            </a>
            <a href="<?php echo get_text($shop_url); ?>">
                <strong>생활 인프라 확인</strong>
                <span>마트, 병원, 카페, 한식당 등 자주 갈 업체를 미리 저장해 보세요.</span>
            </a>
            <a href="<?php echo get_text($job_url); ?>">
                <strong>수입 계획 확인</strong>
                <span>장기체류라면 구인공고와 근무지역도 함께 확인하는 것이 좋습니다.</span>
            </a>
        </div>
    </section>
</main>
<?php
g5_page_end();
