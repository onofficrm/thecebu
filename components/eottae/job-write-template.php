<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_job_board') || !eottae_is_job_board($bo_table ?? '')) {
    return;
}

if (function_exists('eottae_job_template_load_assets')) {
    eottae_job_template_load_assets();
}

$sebu_job_work_types = array(
    ''           => '선택',
    'fulltime'   => '정규직',
    'contract'   => '계약직',
    'parttime'   => '파트타임',
    'part'       => '아르바이트',
    'freelance'  => '프리랜서',
    'other'      => '기타',
);

$sebu_job_pay_types = array(
    ''       => '선택',
    'month'  => '월급',
    'week'   => '주급',
    'day'    => '일급',
    'hour'   => '시급',
    'nego'   => '협의',
);

$sebu_job_genders = array(
    'any'    => '무관',
    'male'   => '남성',
    'female' => '여성',
);

$sebu_job_careers = array(
    'any'      => '무관',
    'new'      => '신입',
    'prefer'   => '경력자 우대',
    'required' => '경력 필수',
);

$sebu_job_languages = array(
    'any'      => '무관',
    'ko'       => '한국어',
    'en'       => '영어',
    'ceb'      => '세부아노',
    'tl'       => '타갈로그어',
    'ko_en'    => '한국어+영어',
    'other'    => '기타',
);
?>

<section class="sebu-job-template" id="sebuJobTemplate" aria-labelledby="sebuJobTemplateTitle">
    <header class="sebu-job-template__head">
        <h2 class="sebu-job-template__title" id="sebuJobTemplateTitle">구인구직 템플릿 작성</h2>
        <p class="sebu-job-template__desc">간단한 정보를 입력하면 구인구직 게시글 제목과 본문이 자동으로 정리됩니다.</p>
    </header>

    <div class="sebu-job-template__body">
        <fieldset class="sebu-job-template__group">
            <legend class="sebu-job-template__legend">기본정보</legend>
            <div class="sebu-job-template__grid">
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">업체명/상호명 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-job-template__input" data-job-field="company" maxlength="120" placeholder="예) 세부 한식당" autocomplete="organization">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">모집직종 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-job-template__input" data-job-field="job_type" maxlength="120" placeholder="예) 홀서빙, 주방보조, 리셉션">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">모집인원 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-job-template__input" data-job-field="headcount" maxlength="40" placeholder="예) 2명">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">근무지역 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-job-template__input" data-job-field="region" maxlength="120" placeholder="예) 세부시티, IT Park">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">근무형태</span>
                    <select class="sebu-job-template__select" data-job-field="work_type">
                        <?php foreach ($sebu_job_work_types as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">근무시간</span>
                    <input type="text" class="sebu-job-template__input" data-job-field="work_hours" maxlength="120" placeholder="예) 09:00–18:00, 주 5일">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">급여 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-job-template__input" data-job-field="salary" maxlength="120" placeholder="예) 월 25,000페소 / 협의 가능">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">급여형태</span>
                    <select class="sebu-job-template__select" data-job-field="pay_type">
                        <?php foreach ($sebu_job_pay_types as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>
        </fieldset>

        <fieldset class="sebu-job-template__group">
            <legend class="sebu-job-template__legend">상세내용</legend>
            <div class="sebu-job-template__grid">
                <label class="sebu-job-template__field sebu-job-template__field--full">
                    <span class="sebu-job-template__label">업무내용 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <textarea class="sebu-job-template__textarea" data-job-field="work_desc" rows="4" maxlength="4000" placeholder="예) 고객 응대, 매장 관리, 예약 안내"></textarea>
                </label>
                <label class="sebu-job-template__field sebu-job-template__field--full">
                    <span class="sebu-job-template__label">지원자격</span>
                    <textarea class="sebu-job-template__textarea" data-job-field="qualification" rows="3" maxlength="2000" placeholder="예) 성실함, 기본 영어 가능"></textarea>
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">나이</span>
                    <input type="text" class="sebu-job-template__input" data-job-field="age" maxlength="60" placeholder="예) 20–45세">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">성별</span>
                    <select class="sebu-job-template__select" data-job-field="gender">
                        <?php foreach ($sebu_job_genders as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">경력</span>
                    <select class="sebu-job-template__select" data-job-field="career">
                        <?php foreach ($sebu_job_careers as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">언어조건</span>
                    <select class="sebu-job-template__select" data-job-field="language">
                        <?php foreach ($sebu_job_languages as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-job-template__field sebu-job-template__field--full">
                    <span class="sebu-job-template__label">복리후생</span>
                    <textarea class="sebu-job-template__textarea" data-job-field="benefits" rows="3" maxlength="2000" placeholder="예) 식사 제공, 비자 스폰서십"></textarea>
                </label>
                <label class="sebu-job-template__field sebu-job-template__field--full">
                    <span class="sebu-job-template__label">우대사항</span>
                    <textarea class="sebu-job-template__textarea" data-job-field="preferred" rows="3" maxlength="2000" placeholder="예) 유사 업종 경력자"></textarea>
                </label>
            </div>
        </fieldset>

        <fieldset class="sebu-job-template__group">
            <legend class="sebu-job-template__legend">지원정보</legend>
            <div class="sebu-job-template__grid">
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">지원방법 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-job-template__input" data-job-field="apply_method" maxlength="200" placeholder="예) 카카오톡 또는 전화 문의">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">연락처 <span class="sebu-job-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-job-template__input" data-job-field="contact" maxlength="80" placeholder="예) 09XX-XXX-XXXX" inputmode="tel" autocomplete="tel">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">카카오톡 ID</span>
                    <input type="text" class="sebu-job-template__input" data-job-field="kakao_id" maxlength="80" placeholder="예) cebu_job">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">이메일</span>
                    <input type="email" class="sebu-job-template__input" data-job-field="email" maxlength="120" placeholder="예) hr@example.com" autocomplete="email">
                </label>
                <label class="sebu-job-template__field">
                    <span class="sebu-job-template__label">마감일</span>
                    <input type="text" class="sebu-job-template__input" data-job-field="deadline" maxlength="80" placeholder="예) 2026-06-30 또는 채용시">
                </label>
                <label class="sebu-job-template__field sebu-job-template__field--full">
                    <span class="sebu-job-template__label">기타 안내사항</span>
                    <textarea class="sebu-job-template__textarea" data-job-field="extra" rows="3" maxlength="2000" placeholder="추가 안내가 있으면 입력해 주세요"></textarea>
                </label>
            </div>
            <p class="sebu-job-template__privacy">연락처, 이메일, 카카오톡 ID 등 개인정보가 포함될 수 있으니 공개 범위를 확인한 후 등록해주세요.</p>
        </fieldset>
    </div>

    <p class="sebu-job-template__error" id="sebuJobTemplateError" role="alert" hidden>필수 정보를 입력해주세요.</p>

    <div class="sebu-job-template__actions">
        <button type="button" class="sebu-job-template__btn sebu-job-template__btn--primary" id="sebuJobTemplateApply">구인구직 글 자동작성</button>
        <button type="button" class="sebu-job-template__btn sebu-job-template__btn--ghost" id="sebuJobTemplateReset">입력내용 초기화</button>
    </div>
</section>
