<?php
include_once(dirname(__FILE__).'/_init.php');

$page_title = '세부 물가·생활비 계산기 | 세부어때';
$page_description = '세부 한달살기, 장기체류, 가족 이주를 준비하는 분들을 위한 월 생활비 계산기입니다. 월세, 식비, 교통, 통신, 비자, 교육비를 한 번에 계산해 보세요.';
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
        <p class="cost-calculator-hero__lead">한달살기, 장기체류, 가족 이주를 준비할 때 필요한 월 생활비를 빠르게 가늠해 보세요. 금액은 참고용이며 실제 비용은 지역, 계약 조건, 생활 방식에 따라 달라질 수 있습니다.</p>
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
            <p>내 상황과 가장 가까운 유형을 고른 뒤 금액을 조정하세요.</p>
        </div>
        <div class="cost-calculator-presets__buttons" role="group" aria-label="생활비 프리셋">
            <button type="button" data-cost-preset="single">혼자 한달살기</button>
            <button type="button" data-cost-preset="couple">커플 장기체류</button>
            <button type="button" data-cost-preset="family">가족 이주</button>
            <button type="button" data-cost-preset="nomad">디지털 노마드</button>
        </div>
    </section>

    <section class="cost-calculator-layout">
        <form class="cost-calculator-form" aria-label="세부 생활비 입력">
            <div class="cost-calculator-card">
                <h2>주거</h2>
                <label class="cost-field">
                    <span>월세·숙소</span>
                    <input type="number" min="0" step="500" data-cost-field="rent" value="25000">
                </label>
                <label class="cost-field">
                    <span>전기·수도·관리비</span>
                    <input type="number" min="0" step="500" data-cost-field="utilities" value="6500">
                </label>
            </div>

            <div class="cost-calculator-card">
                <h2>생활</h2>
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

            <div class="cost-calculator-card">
                <h2>체류·가족</h2>
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

            <div class="cost-calculator-card">
                <h2>여유 비용</h2>
                <label class="cost-field">
                    <span>여가·카페·외식</span>
                    <input type="number" min="0" step="500" data-cost-field="leisure" value="9000">
                </label>
                <label class="cost-field">
                    <span>예비비</span>
                    <input type="number" min="0" step="500" data-cost-field="buffer" value="8000">
                </label>
            </div>
        </form>

        <aside class="cost-result-panel" aria-live="polite">
            <p class="cost-result-panel__label">월 예상 생활비</p>
            <strong data-cost-total>₱0</strong>
            <span data-cost-total-krw>약 0원</span>
            <p data-cost-level class="cost-result-panel__level">생활비 수준을 계산 중입니다.</p>
            <div class="cost-result-panel__bar" aria-hidden="true"><span data-cost-bar></span></div>
            <dl class="cost-result-panel__breakdown" data-cost-breakdown></dl>
            <p class="cost-result-panel__note">환율은 간단 계산용으로 1페소=24원 기준입니다. 실제 환율과 수수료는 환전 시점에 따라 달라집니다.</p>
        </aside>
    </section>

    <section class="cost-calculator-guide" aria-labelledby="costGuideTitle">
        <p class="cost-calculator-section-kicker">Next Step</p>
        <h2 id="costGuideTitle">계산 후 확인하면 좋은 것</h2>
        <div class="cost-calculator-guide__grid">
            <a href="<?php echo get_text($estate_url); ?>">
                <strong>월세 확인</strong>
                <span>세부시티, IT Park, 막탄 등 위치별 매물을 지도에서 비교하세요.</span>
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
