<?php
/**
 * =============================================================================
 *  NaukriPatra - Professional ATS Resume Builder  (single file module)
 * =============================================================================
 *  Drop this file anywhere inside your existing PHP job portal and point your
 *  "Resume Builder" button at it, e.g.  <a href="resume.php">Resume Builder</a>
 *
 *  Requirements : PHP 7.4+ (tested through PHP 8.5), no database, no extensions.
 *  Everything    : HTML + CSS + JavaScript + PHP lives in this one file.
 * =============================================================================
 */

declare(strict_types=1);

/* --------------------------------------------------------------------------
 | Session + hardening headers
 * -------------------------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

/* --------------------------------------------------------------------------
 | Configuration - change these three lines to match your portal
 * -------------------------------------------------------------------------- */
$APP_NAME   = 'NaukriPatra';
$APP_TAG    = 'ATS Resume Builder';
$PORTAL_URL = 'index.php';          // "Back to portal" link

/* --------------------------------------------------------------------------
 | Escaping helper - every dynamic value printed below goes through this
 * -------------------------------------------------------------------------- */
function e(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* --------------------------------------------------------------------------
 | Dropdown / table data used by the form
 * -------------------------------------------------------------------------- */
$TITLES = ['Mr.', 'Mrs.', 'Miss', 'Dr.', 'Prof.'];
$GENDERS = ['Male', 'Female', 'Other'];
$MARITAL = ['Single', 'Married', 'Divorced', 'Widowed'];
$CATEGORIES = ['General', 'OBC', 'SC', 'ST', 'EWS'];
$NATIONALITY_DEFAULT = 'Indian';

$EDU_ROWS = ['10th', '12th', 'Diploma', 'ITI', 'Graduation', 'Post Graduation', 'MPhil', 'PhD'];

$DECLARATION = 'I hereby declare that all the information provided above is true and correct to the best of my knowledge and belief.';

$STEPS = [
    ['id' => 'personal',   'label' => 'Personal Details',   'icon' => 'person-vcard'],
    ['id' => 'education',  'label' => 'Education',          'icon' => 'mortarboard'],
    ['id' => 'objective',  'label' => 'Career Objective',   'icon' => 'bullseye'],
    ['id' => 'skills',     'label' => 'Skills',             'icon' => 'gear-wide-connected'],
    ['id' => 'extra',      'label' => 'Extra Qualification','icon' => 'journal-plus'],
    ['id' => 'experience', 'label' => 'Work Experience',    'icon' => 'briefcase'],
    ['id' => 'certs',      'label' => 'Certifications',     'icon' => 'patch-check'],
    ['id' => 'achieve',    'label' => 'Achievements',       'icon' => 'trophy'],
    ['id' => 'hobbies',    'label' => 'Hobbies',            'icon' => 'palette'],
    ['id' => 'declare',    'label' => 'Declaration',        'icon' => 'file-earmark-text'],
    ['id' => 'sign',       'label' => 'Signature',          'icon' => 'pen'],
];

$TOTAL_STEPS = count($STEPS);
$TODAY = date('Y-m-d');
$YEAR  = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Build a free, professional, ATS-friendly resume in minutes. Live preview, A4 print layout and instant PDF download.">
<meta name="robots" content="index, follow">
<title><?= e($APP_TAG) ?> | <?= e($APP_NAME) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
/* ===========================================================================
   1. Design tokens
   =========================================================================== */
:root{
  --brand:#1e4fd8;
  --brand-dark:#173db0;
  --brand-soft:#eef2ff;
  --ink:#111827;
  --ink-2:#374151;
  --muted:#6b7280;
  --line:#e5e7eb;
  --bg:#f4f6fb;
  --card:#ffffff;
  --ok:#16a34a;
  --warn:#d97706;
  --err:#dc2626;
  --radius:16px;
  --shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.06);
  --shadow-lg:0 12px 40px rgba(16,24,40,.12);
}

/* ===========================================================================
   2. Base
   =========================================================================== */
*{box-sizing:border-box}
html,body{height:100%}
body{
  font-family:'Inter','Roboto',system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
  background:var(--bg);
  color:var(--ink);
  font-size:15px;
  -webkit-font-smoothing:antialiased;
}
a{text-decoration:none}
:focus-visible{outline:3px solid rgba(30,79,216,.35);outline-offset:2px}

/* ===========================================================================
   3. Header
   =========================================================================== */
.rb-header{
  position:sticky;top:0;z-index:1040;
  background:rgba(255,255,255,.92);
  backdrop-filter:saturate(180%) blur(10px);
  border-bottom:1px solid var(--line);
}
.rb-header-in{
  display:flex;align-items:center;gap:14px;
  padding:12px 18px;max-width:1600px;margin:0 auto;
}
.rb-brand{display:flex;align-items:center;gap:10px;color:var(--ink);font-weight:700;letter-spacing:-.2px}
.rb-logo{
  width:38px;height:38px;border-radius:11px;flex:0 0 38px;
  background:linear-gradient(135deg,var(--brand),#4f7dff);
  color:#fff;display:grid;place-items:center;font-size:18px;
  box-shadow:0 6px 16px rgba(30,79,216,.32);
}
.rb-brand small{display:block;font-weight:500;font-size:11.5px;color:var(--muted);letter-spacing:.3px}
.rb-actions{margin-left:auto;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.rb-actions .btn{border-radius:10px;font-weight:600;font-size:14px;padding:.5rem .9rem}
.btn-brand{background:var(--brand);border-color:var(--brand);color:#fff}
.btn-brand:hover,.btn-brand:focus{background:var(--brand-dark);border-color:var(--brand-dark);color:#fff}
.btn-soft{background:var(--brand-soft);border-color:transparent;color:var(--brand-dark)}
.btn-soft:hover{background:#e2e8ff;color:var(--brand-dark)}
.save-flag{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;min-width:96px}
.save-flag.on{color:var(--ok)}

/* ===========================================================================
   4. Progress / stepper
   =========================================================================== */
.rb-progress-wrap{background:#fff;border-bottom:1px solid var(--line)}
.rb-progress-in{max-width:1600px;margin:0 auto;padding:12px 18px 6px}
.progress{height:6px;border-radius:99px;background:#e9edf7}
.progress-bar{background:linear-gradient(90deg,var(--brand),#5b8bff);transition:width .35s ease}
.step-strip{display:flex;gap:8px;overflow-x:auto;padding:12px 2px 10px;scrollbar-width:thin}
.step-strip::-webkit-scrollbar{height:5px}
.step-strip::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:99px}
.step-chip{
  flex:0 0 auto;display:flex;align-items:center;gap:7px;
  border:1px solid var(--line);background:#fff;color:var(--muted);
  padding:7px 13px;border-radius:99px;font-size:13px;font-weight:600;
  cursor:pointer;transition:.18s;white-space:nowrap;
}
.step-chip:hover{border-color:#c7d2fe;color:var(--brand-dark)}
.step-chip .num{
  width:20px;height:20px;border-radius:50%;background:#eef2f7;color:var(--muted);
  display:grid;place-items:center;font-size:11px;font-weight:700;
}
.step-chip.active{background:var(--brand);border-color:var(--brand);color:#fff;box-shadow:0 6px 16px rgba(30,79,216,.28)}
.step-chip.active .num{background:rgba(255,255,255,.25);color:#fff}
.step-chip.done{border-color:#bbf7d0;color:var(--ok);background:#f0fdf4}
.step-chip.done .num{background:var(--ok);color:#fff}

/* ===========================================================================
   5. Layout
   =========================================================================== */
.rb-main{max-width:1600px;margin:0 auto;padding:20px 18px 90px}
.rb-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,720px);gap:22px;align-items:start}
/* minmax(0,...) is required: a plain 1fr track defaults to min-width:auto and the
   210mm sheet would push the whole page into horizontal scroll on phones. */
@media (max-width:1199px){.rb-grid{grid-template-columns:minmax(0,1fr)}}
.form-col,.preview-col{min-width:0}

.card-rb{
  background:var(--card);border:1px solid var(--line);
  border-radius:var(--radius);box-shadow:var(--shadow);
}
.card-rb-head{
  display:flex;align-items:center;gap:12px;
  padding:18px 20px;border-bottom:1px solid var(--line);
}
.card-rb-head .ico{
  width:40px;height:40px;flex:0 0 40px;border-radius:12px;
  background:var(--brand-soft);color:var(--brand);
  display:grid;place-items:center;font-size:19px;
}
.card-rb-head h2{font-size:17px;font-weight:700;margin:0;letter-spacing:-.2px}
.card-rb-head p{margin:1px 0 0;font-size:13px;color:var(--muted)}
.card-rb-body{padding:20px}

.step-panel{display:none}
.step-panel.active{display:block;animation:fade .25s ease}
@keyframes fade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}

/* ===========================================================================
   6. Form controls
   =========================================================================== */
.form-label{font-size:13px;font-weight:600;color:var(--ink-2);margin-bottom:5px}
.form-label .req{color:var(--err)}
.form-control,.form-select{
  border-radius:10px;border:1px solid #dfe3ea;padding:.58rem .75rem;font-size:14.5px;
  background:#fdfdff;transition:.15s;
  /* Restated (not just inherited from Bootstrap) so the form still lays out
     correctly if the CDN is slow or blocked. File inputs in particular have a
     wide intrinsic size and would otherwise force horizontal scroll. */
  display:block;width:100%;max-width:100%;min-width:0;
}
.form-control:focus,.form-select:focus{
  border-color:var(--brand);box-shadow:0 0 0 3px rgba(30,79,216,.12);background:#fff;
}
.form-control.is-invalid,.form-select.is-invalid{border-color:var(--err);background:#fff6f6}
.invalid-feedback{font-size:12.5px}
textarea.form-control{line-height:1.6}
.hint{font-size:12px;color:var(--muted);margin-top:4px}
.counter{font-size:12px;color:var(--muted);text-align:right;margin-top:4px}
.counter.over{color:var(--err);font-weight:600}
.form-check-input:checked{background-color:var(--brand);border-color:var(--brand)}
.section-sub{
  font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  color:var(--muted);margin:22px 0 10px;padding-bottom:6px;border-bottom:1px dashed var(--line);
}
.section-sub:first-child{margin-top:0}

/* ===========================================================================
   7. Photo + signature uploaders
   =========================================================================== */
.uploader{
  border:2px dashed #d5dbe6;border-radius:14px;background:#fbfcff;
  padding:16px;display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;
}
.uploader.dragover{border-color:var(--brand);background:var(--brand-soft)}
.photo-frame{
  width:112px;height:144px;flex:0 0 112px;border-radius:10px;overflow:hidden;
  background:#eef1f6;border:1px solid var(--line);display:grid;place-items:center;
  color:#9aa3b2;font-size:26px;position:relative;
}
.photo-frame img{width:100%;height:100%;object-fit:cover;display:block}
.sign-frame{
  width:190px;height:84px;flex:0 0 190px;border-radius:10px;overflow:hidden;
  background:#eef1f6;border:1px solid var(--line);display:grid;place-items:center;
  color:#9aa3b2;font-size:22px;
}
.sign-frame img{max-width:100%;max-height:100%;object-fit:contain;display:block}
/* min-width:0 lets this shrink on narrow phones instead of forcing page scroll */
.uploader-body{flex:1 1 220px;min-width:0}
.crop-stage{
  margin-top:12px;padding:12px;border:1px solid var(--line);border-radius:12px;background:#fff;display:none;
}
.crop-stage.show{display:block}
.crop-canvas{
  width:140px;height:180px;border-radius:8px;border:1px solid var(--line);
  cursor:grab;touch-action:none;background:#f1f3f7;display:block;
}
.crop-canvas:active{cursor:grabbing}
.form-range::-webkit-slider-thumb{background:var(--brand)}
.form-range::-moz-range-thumb{background:var(--brand)}

/* Small phones: reclaim horizontal space so nothing forces a sideways scroll. */
@media (max-width:575.98px){
  .rb-main{padding:14px 10px 90px}
  .rb-header-in,.rb-progress-in{padding-left:10px;padding-right:10px}
  .card-rb-body,.wizard-nav{padding-left:13px;padding-right:13px}
  .uploader{padding:12px;gap:12px}
  .photo-frame{width:96px;height:124px;flex:0 0 96px}
  .sign-frame{width:100%;flex:1 1 100%;height:76px}
  .sheet-scroll{padding:10px}
  .rb-actions .btn{padding:.5rem .7rem}
  .wizard-nav .btn{flex:1 1 auto}
  .step-count{order:3;width:100%;text-align:center}
}

/* ===========================================================================
   8. Education table
   =========================================================================== */
.edu-scroll{overflow-x:auto;border:1px solid var(--line);border-radius:12px;background:#fff}
.edu-table{width:100%;min-width:1080px;border-collapse:separate;border-spacing:0;font-size:13.5px;margin:0}
.edu-table th{
  background:#f7f9fc;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;
  color:var(--muted);font-weight:700;padding:11px 8px;border-bottom:1px solid var(--line);
  position:sticky;top:0;white-space:nowrap;
}
.edu-table td{padding:7px 8px;border-bottom:1px solid #f1f3f7;vertical-align:middle}
.edu-table tr:last-child td{border-bottom:0}
.edu-table .qual{font-weight:700;color:var(--ink);white-space:nowrap;background:#fcfdff}
.edu-table .form-control{padding:.42rem .55rem;font-size:13.5px;border-radius:8px}
.edu-table .pct{background:#f0fdf4;border-color:#bbf7d0;font-weight:700;color:#15803d;text-align:center}
@media (max-width:767px){
  .edu-scroll{border:0;background:transparent;overflow:visible}
  .edu-table{min-width:0;display:block}
  .edu-table thead{display:none}
  .edu-table tbody,.edu-table tr,.edu-table td{display:block;width:100%}
  .edu-table tr{
    border:1px solid var(--line);border-radius:12px;background:#fff;
    margin-bottom:12px;padding:6px 12px 12px;
  }
  .edu-table td{border:0;padding:7px 0;display:grid;grid-template-columns:130px 1fr;gap:10px;align-items:center}
  .edu-table td::before{content:attr(data-label);font-size:12px;font-weight:600;color:var(--muted)}
  .edu-table .qual{
    display:block;grid-template-columns:1fr;font-size:15px;padding:8px 0;
    border-bottom:1px solid var(--line);margin-bottom:4px;background:transparent;
  }
  .edu-table .qual::before{content:none}
}

/* ===========================================================================
   9. Repeaters
   =========================================================================== */
.rep-item{
  border:1px solid var(--line);border-radius:14px;padding:16px;margin-bottom:14px;
  background:#fcfdff;position:relative;
}
.rep-head{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.rep-badge{
  width:26px;height:26px;border-radius:8px;background:var(--brand-soft);color:var(--brand);
  display:grid;place-items:center;font-size:12px;font-weight:700;flex:0 0 26px;
}
.rep-title{font-weight:700;font-size:14px;margin:0}
.btn-del{
  margin-left:auto;border:0;background:#fef2f2;color:var(--err);
  width:30px;height:30px;border-radius:9px;display:grid;place-items:center;font-size:14px;
}
.btn-del:hover{background:#fee2e2}
.btn-add{
  width:100%;border:1.5px dashed #c7d2fe;background:#fbfcff;color:var(--brand-dark);
  border-radius:12px;padding:11px;font-weight:600;font-size:14px;transition:.15s;
}
.btn-add:hover{background:var(--brand-soft);border-color:var(--brand)}
.empty-note{
  text-align:center;color:var(--muted);font-size:13.5px;
  padding:22px 12px;border:1px dashed var(--line);border-radius:12px;margin-bottom:14px;
}

/* ===========================================================================
   10. Wizard footer nav
   =========================================================================== */
.wizard-nav{
  display:flex;align-items:center;gap:10px;
  padding:16px 20px;border-top:1px solid var(--line);background:#fcfdff;
  border-radius:0 0 var(--radius) var(--radius);flex-wrap:wrap;
}
.wizard-nav .spacer{flex:1 1 auto}
.wizard-nav .btn{border-radius:10px;font-weight:600;padding:.55rem 1.1rem}
.step-count{font-size:13px;color:var(--muted);font-weight:600}

/* ===========================================================================
   11. Preview panel + A4 sheet
   =========================================================================== */
.preview-col{position:sticky;top:150px}
@media (max-width:1199px){.preview-col{position:static}}
.preview-head{
  display:flex;align-items:center;gap:10px;padding:14px 18px;
  border-bottom:1px solid var(--line);
}
.preview-head h3{font-size:14px;font-weight:700;margin:0;display:flex;align-items:center;gap:8px}
.live-dot{width:8px;height:8px;border-radius:50%;background:var(--ok);box-shadow:0 0 0 4px rgba(22,163,74,.15)}
.ats-badge{
  margin-left:auto;font-size:11.5px;font-weight:700;color:#15803d;background:#f0fdf4;
  border:1px solid #bbf7d0;padding:4px 10px;border-radius:99px;
}
.sheet-scroll{padding:18px;background:#eef1f7;border-radius:0 0 var(--radius) var(--radius);max-height:calc(100vh - 230px);overflow:auto}
@media (max-width:1199px){.sheet-scroll{max-height:none}}
.sheet-wrap{position:relative;margin:0 auto;overflow:hidden}
.mobile-preview-toggle{display:none}
@media (max-width:1199px){
  .mobile-preview-toggle{
    display:flex;position:fixed;right:16px;bottom:16px;z-index:1050;
    align-items:center;gap:8px;border-radius:99px;padding:.7rem 1.15rem;
    box-shadow:var(--shadow-lg);font-weight:600;
  }
  .preview-card.collapsed .sheet-scroll{display:none}
}

/* ---- The A4 resume sheet (ATS layout) ---- */
.sheet{
  width:210mm;min-height:297mm;background:#fff;color:#000;
  padding:14mm 15mm;transform-origin:top left;
  box-shadow:0 2px 10px rgba(16,24,40,.14);
  font-family:'Inter','Roboto',Arial,Helvetica,sans-serif;
  font-size:10.5pt;line-height:1.45;
}
.sheet .r-top{display:flex;gap:12mm;align-items:flex-start}
.sheet .r-top-main{flex:1 1 auto;min-width:0}
.sheet .r-photo{
  width:32mm;height:41mm;flex:0 0 32mm;border:1px solid #000;overflow:hidden;background:#fff;
}
.sheet .r-photo img{width:100%;height:100%;object-fit:cover;display:block}
.sheet .r-name{
  font-size:19pt;font-weight:700;letter-spacing:.4px;text-transform:uppercase;
  margin:0 0 2mm;line-height:1.15;color:#000;
}
.sheet .r-post{font-size:11pt;font-weight:600;margin:0 0 2mm;color:#000}
/* Never justify the header lines - stretching a short contact line opens up
   ugly word gaps. Justification is only wanted on real paragraphs. */
.sheet .r-contact{font-size:9.8pt;margin:0;color:#000;word-break:break-word;text-align:left}
.sheet .r-post{text-align:left}
.sheet .r-contact span+span::before{content:" | ";color:#000}
.sheet .r-rule{border:0;border-top:1.6pt solid #000;margin:4mm 0 3mm}
.sheet h2.r-h{
  font-size:11pt;font-weight:700;text-transform:uppercase;letter-spacing:.09em;
  margin:5mm 0 2mm;padding-bottom:1mm;border-bottom:1pt solid #000;color:#000;
}
.sheet section{page-break-inside:auto;break-inside:auto}
.sheet p{margin:0 0 2mm;text-align:justify}
.sheet ul{margin:0 0 2mm;padding-left:5.5mm}
.sheet li{margin-bottom:1mm}
.sheet .r-facts{display:grid;grid-template-columns:1fr 1fr;gap:0 8mm}
.sheet .r-fact{display:flex;gap:2mm;padding:.7mm 0;font-size:10pt;break-inside:avoid}
.sheet .r-fact b{flex:0 0 34mm;font-weight:600}
.sheet .r-fact b::after{content:":"}
.sheet .r-fact-wide{grid-column:1 / -1}
.sheet .r-fact-wide b{flex:0 0 34mm}
.sheet table.r-edu{width:100%;border-collapse:collapse;font-size:9.3pt;margin-bottom:2mm}
.sheet table.r-edu th,.sheet table.r-edu td{
  border:.75pt solid #000;padding:1.4mm 1.6mm;text-align:left;vertical-align:top;
}
.sheet table.r-edu th{font-weight:700;background:#f2f2f2}
.sheet .r-block{margin-bottom:3mm;break-inside:avoid}
.sheet .r-block-top{display:flex;justify-content:space-between;gap:6mm;align-items:baseline}
.sheet .r-block-title{font-weight:700;font-size:10.5pt}
.sheet .r-block-meta{font-size:9.5pt;white-space:nowrap}
.sheet .r-block-sub{font-size:10pt;font-style:italic;margin-bottom:.8mm}
.sheet .r-sign{
  display:flex;justify-content:space-between;align-items:flex-end;gap:10mm;margin-top:8mm;
  break-inside:avoid;
}
.sheet .r-sign-left{font-size:10pt}
.sheet .r-sign-right{text-align:center;min-width:52mm}
.sheet .r-sign-img{height:16mm;margin-bottom:1mm}
.sheet .r-sign-img img{max-height:16mm;max-width:52mm;object-fit:contain}
.sheet .r-sign-line{border-top:.75pt solid #000;padding-top:1mm;font-size:10pt;font-weight:600}
.sheet .r-placeholder{color:#9aa3b2;font-style:italic}

/* ===========================================================================
   12. Toast
   =========================================================================== */
.rb-toast{
  position:fixed;left:50%;bottom:26px;transform:translate(-50%,20px);
  background:var(--ink);color:#fff;padding:11px 18px;border-radius:12px;
  font-size:14px;font-weight:500;box-shadow:var(--shadow-lg);
  opacity:0;pointer-events:none;transition:.25s;z-index:2000;max-width:90vw;text-align:center;
}
.rb-toast.show{opacity:1;transform:translate(-50%,0)}
.rb-toast.err{background:var(--err)}
.rb-toast.ok{background:#15803d}

/* ===========================================================================
   13. PRINT - only the A4 sheet is printed
   =========================================================================== */
@page{size:A4;margin:11mm 12mm}
@media print{
  html,body{background:#fff!important;margin:0!important;padding:0!important}
  .rb-header,.rb-progress-wrap,.form-col,.preview-head,.mobile-preview-toggle,
  .rb-toast,.rb-footer,.no-print{display:none!important}
  .rb-main{max-width:none;margin:0;padding:0}
  .rb-grid{display:block}

  /* Neutralise every screen-only layout device on the preview chain. Printing
     re-lays the page out at paper width (~794px), which re-triggers the mobile
     media queries above — so the collapsed-preview toggle, the sticky column,
     the scroll clamp and the inline width/height fitSheet() writes would all
     otherwise conspire to print a blank sheet. */
  .preview-col,.preview-card,.sheet-scroll,.sheet-wrap,.sheet{
    display:block!important;position:static!important;
    width:auto!important;max-width:none!important;
    height:auto!important;max-height:none!important;
    overflow:visible!important;transform:none!important;
    margin:0!important;border:0!important;border-radius:0!important;
    background:#fff!important;box-shadow:none!important;
  }
  .card-rb{border:0!important;box-shadow:none!important;background:#fff!important}
  .sheet-scroll,.sheet-wrap{padding:0!important}
  .sheet{min-height:0!important;padding:0!important;font-size:10.5pt!important;color:#000!important}
  .sheet h2.r-h{page-break-after:avoid;break-after:avoid}
  .sheet .r-block,.sheet .r-sign,.sheet .r-fact{page-break-inside:avoid;break-inside:avoid}
  .sheet table.r-edu{page-break-inside:auto}
  .sheet table.r-edu tr{page-break-inside:avoid}
  .sheet table.r-edu thead{display:table-header-group}
  a{color:#000!important;text-decoration:none!important}
}
</style>
</head>
<body>

<!-- =======================================================================
     HEADER
     ======================================================================= -->
<header class="rb-header no-print">
  <div class="rb-header-in">
    <a class="rb-brand" href="<?= e($PORTAL_URL) ?>">
      <span class="rb-logo"><i class="bi bi-file-earmark-person-fill"></i></span>
      <span><?= e($APP_NAME) ?><small><?= e($APP_TAG) ?></small></span>
    </a>

    <div class="rb-actions">
      <span class="save-flag" id="saveFlag"><i class="bi bi-cloud-check"></i> <span>Draft saved</span></span>
      <a href="<?= e($PORTAL_URL) ?>" class="btn btn-outline-secondary d-none d-md-inline-flex">
        <i class="bi bi-arrow-left"></i> Portal
      </a>
      <button type="button" class="btn btn-outline-danger" id="btnReset">
        <i class="bi bi-arrow-counterclockwise"></i> <span class="d-none d-sm-inline">Reset</span>
      </button>
      <button type="button" class="btn btn-soft" id="btnPrint">
        <i class="bi bi-printer"></i> <span class="d-none d-sm-inline">Print</span>
      </button>
      <button type="button" class="btn btn-brand" id="btnPdf">
        <i class="bi bi-download"></i> Download PDF
      </button>
    </div>
  </div>
</header>

<!-- =======================================================================
     PROGRESS + STEP STRIP
     ======================================================================= -->
<div class="rb-progress-wrap no-print">
  <div class="rb-progress-in">
    <div class="progress" role="progressbar" aria-label="Resume completion" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
      <div class="progress-bar" id="progressBar" style="width:9%"></div>
    </div>
    <nav class="step-strip" id="stepStrip" aria-label="Resume sections">
      <?php foreach ($STEPS as $i => $s): ?>
        <button type="button" class="step-chip<?= $i === 0 ? ' active' : '' ?>" data-goto="<?= $i ?>">
          <span class="num"><?= $i + 1 ?></span>
          <i class="bi bi-<?= e($s['icon']) ?>"></i>
          <?= e($s['label']) ?>
        </button>
      <?php endforeach; ?>
    </nav>
  </div>
</div>

<!-- =======================================================================
     MAIN
     ======================================================================= -->
<main class="rb-main">
<div class="rb-grid">

  <!-- ============================ FORM COLUMN ============================ -->
  <div class="form-col">
    <form id="resumeForm" class="card-rb" novalidate autocomplete="on">

      <div class="card-rb-head">
        <span class="ico"><i class="bi bi-person-vcard" id="headIcon"></i></span>
        <div>
          <h2 id="headTitle">Personal Details</h2>
          <p id="headDesc">Basic information that appears at the top of your resume.</p>
        </div>
      </div>

      <div class="card-rb-body">

        <!-- ------------------------- STEP 1 : PERSONAL ------------------- -->
        <section class="step-panel active" data-step="0" id="step-personal">
          <div class="row g-3">
            <div class="col-6 col-md-2">
              <label class="form-label" for="f_title">Title</label>
              <select class="form-select" id="f_title" name="title">
                <?php foreach ($TITLES as $t): ?>
                  <option value="<?= e($t) ?>"><?= e($t) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label" for="f_first">First Name <span class="req">*</span></label>
              <input type="text" class="form-control" id="f_first" name="first_name" maxlength="40" required
                     data-rule="name" placeholder="Rahul">
              <div class="invalid-feedback">Please enter your first name (letters only).</div>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label" for="f_middle">Middle Name</label>
              <input type="text" class="form-control" id="f_middle" name="middle_name" maxlength="40" placeholder="Kumar">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label" for="f_last">Last Name <span class="req">*</span></label>
              <input type="text" class="form-control" id="f_last" name="last_name" maxlength="40" required
                     data-rule="name" placeholder="Sharma">
              <div class="invalid-feedback">Please enter your last name (letters only).</div>
            </div>

            <div class="col-12">
              <label class="form-label">Passport Size Photograph <span class="text-muted fw-normal">(optional)</span></label>
              <div class="uploader" id="photoDrop">
                <div class="photo-frame" id="photoFrame"><i class="bi bi-person-bounding-box"></i></div>
                <div class="uploader-body">
                  <input type="file" class="form-control" id="f_photo" accept="image/jpeg,image/png">
                  <div class="hint">JPG or PNG, max 2 MB. Cropped to passport ratio (35 x 45 mm) and compressed automatically.</div>
                  <div class="crop-stage" id="cropStage">
                    <div class="d-flex gap-3 flex-wrap align-items-center">
                      <canvas class="crop-canvas" id="cropCanvas" width="140" height="180"
                              aria-label="Drag to reposition your photo"></canvas>
                      <div class="flex-grow-1" style="min-width:180px">
                        <label class="form-label mb-1" for="cropZoom">Zoom</label>
                        <input type="range" class="form-range" id="cropZoom" min="100" max="300" value="100">
                        <div class="hint mb-2"><i class="bi bi-arrows-move"></i> Drag the photo to reposition the face.</div>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnPhotoRemove">
                          <i class="bi bi-trash3"></i> Remove photo
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label" for="f_dob">Date of Birth <span class="req">*</span></label>
              <input type="date" class="form-control" id="f_dob" name="dob" required data-rule="dob" max="<?= e($TODAY) ?>">
              <div class="invalid-feedback">Enter a valid date of birth.</div>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label" for="f_gender">Gender</label>
              <select class="form-select" id="f_gender" name="gender">
                <option value="">Select</option>
                <?php foreach ($GENDERS as $g): ?>
                  <option value="<?= e($g) ?>"><?= e($g) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label" for="f_marital">Marital Status</label>
              <select class="form-select" id="f_marital" name="marital">
                <option value="">Select</option>
                <?php foreach ($MARITAL as $m): ?>
                  <option value="<?= e($m) ?>"><?= e($m) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label" for="f_father">Father's Name</label>
              <input type="text" class="form-control" id="f_father" name="father" maxlength="60" placeholder="Mr. Suresh Sharma">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="f_mother">Mother's Name</label>
              <input type="text" class="form-control" id="f_mother" name="mother" maxlength="60" placeholder="Mrs. Sunita Sharma">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label" for="f_mobile">Mobile Number <span class="req">*</span></label>
              <input type="tel" class="form-control" id="f_mobile" name="mobile" required data-rule="mobile"
                     inputmode="numeric" maxlength="16" placeholder="9876543210">
              <div class="invalid-feedback">Enter a valid 10-digit mobile number.</div>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label" for="f_mobile2">Alternate Mobile</label>
              <input type="tel" class="form-control" id="f_mobile2" name="mobile_alt" data-rule="mobile-opt"
                     inputmode="numeric" maxlength="16" placeholder="9123456780">
              <div class="invalid-feedback">Enter a valid 10-digit mobile number.</div>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label" for="f_email">Email ID <span class="req">*</span></label>
              <input type="email" class="form-control" id="f_email" name="email" required data-rule="email"
                     maxlength="80" placeholder="rahul.sharma@example.com">
              <div class="invalid-feedback">Enter a valid email address.</div>
            </div>

            <div class="col-6 col-md-3">
              <label class="form-label" for="f_nation">Nationality</label>
              <input type="text" class="form-control" id="f_nation" name="nationality" maxlength="30"
                     value="<?= e($NATIONALITY_DEFAULT) ?>">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label" for="f_category">Category</label>
              <select class="form-select" id="f_category" name="category">
                <option value="">Select</option>
                <?php foreach ($CATEGORIES as $c): ?>
                  <option value="<?= e($c) ?>"><?= e($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="f_langs">Languages Known</label>
              <input type="text" class="form-control" id="f_langs" name="languages" maxlength="120"
                     placeholder="Hindi, English, Odia">
            </div>

            <div class="col-12">
              <label class="form-label" for="f_post">Post Applied For</label>
              <input type="text" class="form-control" id="f_post" name="post" maxlength="80"
                     placeholder="Junior Accountant">
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label" for="f_addr1">Present Address</label>
              <textarea class="form-control" id="f_addr1" name="present_address" rows="3" maxlength="300"
                        placeholder="House / Street, Village or Town, District, State - PIN"></textarea>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="f_addr2">Permanent Address</label>
              <textarea class="form-control" id="f_addr2" name="permanent_address" rows="3" maxlength="300"
                        placeholder="House / Street, Village or Town, District, State - PIN"></textarea>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="f_sameaddr" name="same_address">
                <label class="form-check-label" for="f_sameaddr">Same as Present Address</label>
              </div>
            </div>
          </div>
        </section>

        <!-- ------------------------ STEP 2 : EDUCATION ------------------- -->
        <section class="step-panel" data-step="1" id="step-education">
          <div class="alert alert-light border d-flex gap-2 align-items-start py-2 px-3 mb-3" style="border-radius:12px">
            <i class="bi bi-info-circle text-primary mt-1"></i>
            <div class="small mb-0">
              Fill only the qualifications you have — empty rows are skipped in the resume.
              <b>Percentage is calculated automatically</b> from marks, and Division is suggested for you.
            </div>
          </div>
          <div class="edu-scroll">
            <table class="edu-table" id="eduTable">
              <thead>
                <tr>
                  <th>Qualification</th>
                  <th>Board / University</th>
                  <th>Subject / Stream</th>
                  <th>Passing Year</th>
                  <th>Max Marks / OGPA</th>
                  <th>Obtained / CGPA</th>
                  <th>%</th>
                  <th>Grade</th>
                  <th>Division</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($EDU_ROWS as $i => $row): ?>
                <tr data-edu-row="<?= $i ?>">
                  <td class="qual" data-label="Qualification">
                    <?= e($row) ?>
                    <input type="hidden" name="edu_qual[]" value="<?= e($row) ?>">
                  </td>
                  <td data-label="Board / Univ.">
                    <input type="text" class="form-control" name="edu_board[]" maxlength="70" placeholder="e.g. CBSE">
                  </td>
                  <td data-label="Subject">
                    <input type="text" class="form-control" name="edu_subject[]" maxlength="70" placeholder="e.g. Science">
                  </td>
                  <td data-label="Passing Year">
                    <input type="text" class="form-control" name="edu_year[]" maxlength="4" inputmode="numeric"
                           placeholder="2020" data-rule="year">
                  </td>
                  <td data-label="Max Marks">
                    <input type="text" class="form-control edu-max" name="edu_max[]" maxlength="8" inputmode="decimal" placeholder="600">
                  </td>
                  <td data-label="Obtained">
                    <input type="text" class="form-control edu-obt" name="edu_obt[]" maxlength="8" inputmode="decimal" placeholder="480">
                  </td>
                  <td data-label="Percentage">
                    <input type="text" class="form-control pct edu-pct" name="edu_pct[]" readonly tabindex="-1" placeholder="--">
                  </td>
                  <td data-label="Grade">
                    <input type="text" class="form-control" name="edu_grade[]" maxlength="6" placeholder="A">
                  </td>
                  <td data-label="Division">
                    <input type="text" class="form-control edu-div" name="edu_division[]" maxlength="14" placeholder="First">
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- ------------------------ STEP 3 : OBJECTIVE ------------------- -->
        <section class="step-panel" data-step="2" id="step-objective">
          <label class="form-label" for="f_objective">Career Objective</label>
          <textarea class="form-control" id="f_objective" name="objective" rows="7" maxlength="600"
                    data-counter="objCount"
                    placeholder="To work in a challenging environment where I can apply my skills and knowledge, contribute to organisational growth and build a progressive career."></textarea>
          <div class="counter" id="objCount">0 / 600</div>
          <div class="hint">
            <i class="bi bi-lightbulb"></i> Keep it 2–3 lines. Mention the role you want and the value you bring —
            recruiters and ATS software both scan this first.
          </div>
          <div class="mt-3">
            <div class="section-sub">Quick starters</div>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-sm btn-soft objective-preset">To secure a responsible position in a reputed organisation where I can utilise my educational background and skills to contribute to the growth of the company while developing my professional career.</button>
              <button type="button" class="btn btn-sm btn-soft objective-preset">Seeking an entry-level position that offers professional growth, allows me to apply my academic knowledge, and enables me to add measurable value to the organisation.</button>
            </div>
          </div>
        </section>

        <!-- -------------------------- STEP 4 : SKILLS -------------------- -->
        <section class="step-panel" data-step="3" id="step-skills">
          <label class="form-label" for="f_skills">Skills</label>
          <textarea class="form-control" id="f_skills" name="skills" rows="8" maxlength="900"
                    data-counter="skillCount"
                    placeholder="MS Office (Word, Excel, PowerPoint)&#10;Tally ERP 9 / GST Filing&#10;English &amp; Hindi Typing (35 WPM)&#10;Internet, Email &amp; Data Entry&#10;Team Work and Time Management"></textarea>
          <div class="counter" id="skillCount">0 / 900</div>
          <div class="hint">
            <i class="bi bi-list-ul"></i> Write <b>one skill per line</b> — each line becomes a separate bullet point,
            which is exactly how ATS software prefers to read skills.
          </div>
        </section>

        <!-- --------------------- STEP 5 : EXTRA QUALIFICATION ------------ -->
        <section class="step-panel" data-step="4" id="step-extra">
          <div id="extraList" class="rep-list" data-rep="extra"></div>
          <button type="button" class="btn-add" data-add="extra">
            <i class="bi bi-plus-lg"></i> Add Extra Qualification
          </button>
          <div class="hint mt-2">Examples: PGDCA, CCC, DCA, Typing Certificate, Spoken English, Tally Course.</div>
        </section>

        <!-- ---------------------- STEP 6 : WORK EXPERIENCE --------------- -->
        <section class="step-panel" data-step="5" id="step-experience">
          <div id="expList" class="rep-list" data-rep="exp"></div>
          <button type="button" class="btn-add" data-add="exp">
            <i class="bi bi-plus-lg"></i> Add Work Experience
          </button>
          <div class="hint mt-2">Fresher? Leave this section empty — it will be hidden from the resume automatically.</div>
        </section>

        <!-- ----------------------- STEP 7 : CERTIFICATIONS --------------- -->
        <section class="step-panel" data-step="6" id="step-certs">
          <div id="certList" class="rep-list" data-rep="cert"></div>
          <button type="button" class="btn-add" data-add="cert">
            <i class="bi bi-plus-lg"></i> Add Certification
          </button>
        </section>

        <!-- ------------------------ STEP 8 : ACHIEVEMENTS ---------------- -->
        <section class="step-panel" data-step="7" id="step-achieve">
          <div id="achList" class="rep-list" data-rep="ach"></div>
          <button type="button" class="btn-add" data-add="ach">
            <i class="bi bi-plus-lg"></i> Add Achievement
          </button>
          <div class="hint mt-2">Examples: District level sports winner, NSS volunteer, topped the class, scholarship awarded.</div>
        </section>

        <!-- -------------------------- STEP 9 : HOBBIES ------------------- -->
        <section class="step-panel" data-step="8" id="step-hobbies">
          <label class="form-label" for="f_hobbies">Hobbies &amp; Interests</label>
          <textarea class="form-control" id="f_hobbies" name="hobbies" rows="5" maxlength="300"
                    data-counter="hobCount"
                    placeholder="Reading books, Playing cricket, Gardening, Listening to music"></textarea>
          <div class="counter" id="hobCount">0 / 300</div>
          <div class="hint">Separate with commas, or write one per line.</div>
        </section>

        <!-- ------------------------ STEP 10 : DECLARATION ---------------- -->
        <section class="step-panel" data-step="9" id="step-declare">
          <label class="form-label" for="f_declaration">Declaration</label>
          <textarea class="form-control" id="f_declaration" name="declaration" rows="4" maxlength="400"><?= e($DECLARATION) ?></textarea>
          <div class="hint">You can edit this text. Clear the box completely to hide the declaration from your resume.</div>
          <button type="button" class="btn btn-sm btn-soft mt-2" id="btnResetDecl">
            <i class="bi bi-arrow-counterclockwise"></i> Restore default text
          </button>
        </section>

        <!-- ------------------------- STEP 11 : SIGNATURE ----------------- -->
        <section class="step-panel" data-step="10" id="step-sign">
          <div class="section-sub">Signature</div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="sign_mode" id="sign_upload" value="upload">
            <label class="form-check-label" for="sign_upload"><b>Upload signature image</b> — appears on the printed resume</label>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="sign_mode" id="sign_blank" value="blank" checked>
            <label class="form-check-label" for="sign_blank"><b>Leave blank</b> — sign by hand after printing</label>
          </div>

          <div class="uploader" id="signDrop">
            <div class="sign-frame" id="signFrame"><i class="bi bi-vector-pen"></i></div>
            <div class="uploader-body">
              <input type="file" class="form-control" id="f_sign" accept="image/jpeg,image/png" disabled>
              <div class="hint">JPG or PNG, max 2 MB. Sign on white paper, photograph it, and upload — it is resized automatically.</div>
              <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-none" id="btnSignRemove">
                <i class="bi bi-trash3"></i> Remove signature
              </button>
            </div>
          </div>

          <div class="section-sub">Place &amp; Date</div>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label" for="f_place">Place</label>
              <input type="text" class="form-control" id="f_place" name="place" maxlength="50" placeholder="Bhubaneswar">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="f_date">Date</label>
              <input type="date" class="form-control" id="f_date" name="sign_date" value="<?= e($TODAY) ?>">
            </div>
          </div>

          <div class="alert alert-success mt-4 mb-0 d-flex gap-2 align-items-start" style="border-radius:12px">
            <i class="bi bi-check2-circle mt-1"></i>
            <div class="small">
              <b>Your resume is ready.</b> Use <b>Download PDF</b> in the header and choose
              “Save as PDF” as the destination — the file saves as
              <code id="pdfNameHint">Firstname_Lastname_Resume.pdf</code> on A4 with no watermark.
            </div>
          </div>
        </section>

      </div><!-- /card body -->

      <!-- --------------------------- WIZARD NAV ------------------------- -->
      <div class="wizard-nav">
        <button type="button" class="btn btn-outline-secondary" id="btnPrev" disabled>
          <i class="bi bi-arrow-left"></i> Previous
        </button>
        <span class="step-count" id="stepCount">Step 1 of <?= (int) $TOTAL_STEPS ?></span>
        <span class="spacer"></span>
        <button type="button" class="btn btn-outline-primary" id="btnSaveDraft">
          <i class="bi bi-save"></i> Save Draft
        </button>
        <button type="button" class="btn btn-brand" id="btnNext">
          Next <i class="bi bi-arrow-right"></i>
        </button>
      </div>
    </form>
  </div>

  <!-- =========================== PREVIEW COLUMN ========================== -->
  <div class="preview-col">
    <div class="card-rb preview-card collapsed" id="previewCard">
      <div class="preview-head no-print">
        <h3><span class="live-dot"></span> Live Resume Preview</h3>
        <span class="ats-badge"><i class="bi bi-shield-check"></i> ATS Friendly</span>
      </div>
      <div class="sheet-scroll">
        <div class="sheet-wrap" id="sheetWrap">
          <!-- The A4 page. Everything inside is rebuilt by JS on every keystroke. -->
          <article class="sheet" id="sheet"></article>
        </div>
      </div>
    </div>
  </div>

</div>
</main>

<footer class="rb-footer text-center py-4 no-print" style="color:var(--muted);font-size:13px">
  &copy; <?= e($YEAR) ?> <?= e($APP_NAME) ?> — <?= e($APP_TAG) ?>. Your data stays in this browser only.
</footer>

<button type="button" class="btn btn-brand mobile-preview-toggle" id="btnMobilePreview">
  <i class="bi bi-eye"></i> Preview
</button>

<div class="rb-toast" id="toast" role="status" aria-live="polite"></div>

<script>
/* ===========================================================================
   NaukriPatra Resume Builder - application script
   Plain ES5/ES6, no build step, no dependencies beyond Bootstrap's CSS.
   =========================================================================== */
(function () {
  'use strict';

  /* ---------------------------------------------------------------- utils */
  var $  = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  var STORE_KEY   = 'naukripatra_resume_v1';
  var TOTAL_STEPS = <?= (int) $TOTAL_STEPS ?>;
  var DEFAULT_DECLARATION = <?= json_encode($DECLARATION, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var STEP_META = <?= json_encode(array_map(static function ($s) {
        return ['id' => $s['id'], 'label' => $s['label'], 'icon' => $s['icon']];
    }, $STEPS), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  var STEP_DESC = {
    personal:   'Basic information that appears at the top of your resume.',
    education:  'Academic record. Percentage is calculated for you.',
    objective:  'A short, focused statement about the role you want.',
    skills:     'One skill per line so recruiters and ATS can scan them.',
    extra:      'Short courses and diplomas beyond your main degree.',
    experience: 'Jobs, internships and apprenticeships, newest first.',
    certs:      'Certificates issued by institutes, boards or platforms.',
    achieve:    'Awards, ranks, competitions and recognitions.',
    hobbies:    'A brief, genuine list of interests.',
    declare:    'The standard declaration line. Editable.',
    sign:       'Signature, place and date for the printed copy.'
  };

  var form   = $('#resumeForm');
  var sheet  = $('#sheet');
  var toastEl = $('#toast');

  /** HTML-escape everything that reaches the preview. */
  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  /** Escape, then turn newlines into <br>. */
  function escBr(v) { return esc(v).replace(/\r?\n/g, '<br>'); }
  /** Split a textarea into clean non-empty lines. */
  function lines(v) {
    return String(v || '').split(/\r?\n/).map(function (l) { return l.trim(); })
      .filter(function (l) { return l.length > 0; });
  }
  function val(name) {
    var el = form.elements[name];
    if (!el) { return ''; }
    if (el.type === 'checkbox') { return el.checked; }
    return (el.value || '').trim();
  }
  function valAll(name) {
    var els = form.elements[name];
    if (!els) { return []; }
    if (!els.length) { return [(els.value || '').trim()]; }
    return Array.prototype.map.call(els, function (el) { return (el.value || '').trim(); });
  }
  function toast(msg, kind) {
    toastEl.textContent = msg;
    toastEl.className = 'rb-toast show' + (kind ? ' ' + kind : '');
    clearTimeout(toast._t);
    toast._t = setTimeout(function () { toastEl.className = 'rb-toast'; }, 2600);
  }
  function debounce(fn, ms) {
    var t; return function () {
      var a = arguments, c = this;
      clearTimeout(t); t = setTimeout(function () { fn.apply(c, a); }, ms);
    };
  }
  /** 2024-05-09 -> 09/05/2024 (dd/mm/yyyy, the Indian resume convention). */
  function fmtDate(iso) {
    if (!iso) { return ''; }
    var p = String(iso).split('-');
    if (p.length !== 3) { return iso; }
    return p[2] + '/' + p[1] + '/' + p[0];
  }
  /** 2024-05 or 2024-05-09 -> May 2024, for job durations. */
  function fmtMonth(iso) {
    if (!iso) { return ''; }
    var p = String(iso).split('-');
    var m = ['January','February','March','April','May','June',
             'July','August','September','October','November','December'];
    if (p.length >= 2) {
      var i = parseInt(p[1], 10) - 1;
      if (i >= 0 && i < 12) { return m[i] + ' ' + p[0]; }
    }
    return iso;
  }

  /* ------------------------------------------------------- media storage */
  /* Photo and signature are kept as compressed data URLs so the whole
     resume (including images) survives a page refresh via localStorage.   */
  var media = { photo: '', signature: '' };

  /* =======================================================================
     1. STEP NAVIGATION
     ======================================================================= */
  var current = 0;

  function showStep(i, skipScroll) {
    if (i < 0) { i = 0; }
    if (i > TOTAL_STEPS - 1) { i = TOTAL_STEPS - 1; }
    current = i;

    $$('.step-panel').forEach(function (p) {
      p.classList.toggle('active', parseInt(p.getAttribute('data-step'), 10) === i);
    });
    $$('.step-chip').forEach(function (c, idx) {
      c.classList.toggle('active', idx === i);
      c.classList.toggle('done', idx < i);
    });

    var meta = STEP_META[i];
    $('#headTitle').textContent = meta.label;
    $('#headDesc').textContent  = STEP_DESC[meta.id] || '';
    $('#headIcon').className    = 'bi bi-' + meta.icon;

    $('#stepCount').textContent = 'Step ' + (i + 1) + ' of ' + TOTAL_STEPS;
    $('#btnPrev').disabled = (i === 0);

    var next = $('#btnNext');
    if (i === TOTAL_STEPS - 1) {
      next.innerHTML = '<i class="bi bi-download"></i> Download PDF';
      next.dataset.final = '1';
    } else {
      next.innerHTML = 'Next <i class="bi bi-arrow-right"></i>';
      delete next.dataset.final;
    }

    var pct = Math.round(((i + 1) / TOTAL_STEPS) * 100);
    $('#progressBar').style.width = pct + '%';
    $('#progressBar').parentNode.setAttribute('aria-valuenow', String(pct));

    // Keep the active chip in view on narrow screens.
    var chip = $$('.step-chip')[i];
    if (chip && chip.scrollIntoView) {
      chip.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
    }
    if (!skipScroll) {
      var top = form.getBoundingClientRect().top + window.pageYOffset - 150;
      window.scrollTo({ top: top < 0 ? 0 : top, behavior: 'smooth' });
    }
  }

  $('#btnNext').addEventListener('click', function () {
    if (!validateStep(current)) {
      toast('Please correct the highlighted fields.', 'err');
      return;
    }
    if (this.dataset.final) { downloadPdf(); return; }
    showStep(current + 1);
  });
  $('#btnPrev').addEventListener('click', function () { showStep(current - 1); });
  $('#stepStrip').addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-goto]');
    if (!b) { return; }
    var target = parseInt(b.getAttribute('data-goto'), 10);
    // Moving forward past step 1 requires the mandatory fields to be valid.
    if (target > current && !validateStep(current)) {
      toast('Please correct the highlighted fields first.', 'err');
      return;
    }
    showStep(target);
  });

  /* =======================================================================
     2. VALIDATION
     ======================================================================= */
  var RULES = {
    name: {
      test: function (v) { return /^[A-Za-z][A-Za-z .'-]{1,39}$/.test(v); },
      msg: 'Use letters only, at least 2 characters.'
    },
    mobile: {
      test: function (v) { return /^(\+?\d{1,3}[ -]?)?[6-9]\d{9}$/.test(v.replace(/[ -]/g, '')); },
      msg: 'Enter a valid 10-digit mobile number.'
    },
    email: {
      test: function (v) { return /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/.test(v); },
      msg: 'Enter a valid email address.'
    },
    dob: {
      test: function (v) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) { return false; }
        var d = new Date(v + 'T00:00:00');
        if (isNaN(d.getTime())) { return false; }
        var age = (Date.now() - d.getTime()) / 31557600000;
        return age >= 14 && age <= 100;
      },
      msg: 'Date of birth must be a real date (age 14–100).'
    },
    year: {
      test: function (v) {
        var n = parseInt(v, 10);
        return /^\d{4}$/.test(v) && n >= 1950 && n <= (new Date().getFullYear() + 6);
      },
      msg: 'Enter a 4-digit passing year.'
    }
  };

  function fieldError(el, message) {
    el.classList.add('is-invalid');
    var fb = el.parentNode.querySelector('.invalid-feedback');
    if (fb && message) { fb.textContent = message; }
  }
  function fieldOk(el) { el.classList.remove('is-invalid'); }

  /** Validate one control. Returns true when acceptable. */
  function checkField(el) {
    if (el.disabled || el.type === 'hidden') { return true; }
    var v = (el.value || '').trim();
    var ruleName = el.getAttribute('data-rule') || '';
    var optional = ruleName.endsWith('-opt');
    var rule = RULES[optional ? ruleName.slice(0, -4) : ruleName];

    if (el.required && !v) { fieldError(el, 'This field is required.'); return false; }
    if (!v) { fieldOk(el); return true; }              // empty + not required = fine
    if (rule && !rule.test(v)) { fieldError(el, rule.msg); return false; }
    fieldOk(el);
    return true;
  }

  /** Validate every control inside a step panel. */
  function validateStep(i) {
    var panel = $('.step-panel[data-step="' + i + '"]');
    if (!panel) { return true; }
    var ok = true, firstBad = null;
    $$('input, select, textarea', panel).forEach(function (el) {
      if (!checkField(el)) { ok = false; if (!firstBad) { firstBad = el; } }
    });
    if (firstBad) { firstBad.focus({ preventScroll: false }); }
    return ok;
  }

  /** Validate the whole form (used before print / PDF). */
  function validateAll() {
    for (var i = 0; i < TOTAL_STEPS; i++) {
      if (!validateStep(i)) { showStep(i); return false; }
    }
    return true;
  }

  form.addEventListener('blur', function (ev) {
    if (ev.target.matches('input, select, textarea')) { checkField(ev.target); }
  }, true);
  form.addEventListener('input', function (ev) {
    if (ev.target.classList.contains('is-invalid')) { checkField(ev.target); }
  });

  /* =======================================================================
     3. EDUCATION TABLE - auto percentage + division
     ======================================================================= */
  function recalcRow(tr) {
    var maxEl = $('.edu-max', tr), obtEl = $('.edu-obt', tr);
    var pctEl = $('.edu-pct', tr), divEl = $('.edu-div', tr);
    if (!maxEl || !obtEl || !pctEl) { return; }

    var max = parseFloat(maxEl.value), obt = parseFloat(obtEl.value);
    var pct = '';

    if (isFinite(max) && isFinite(obt) && max > 0 && obt >= 0) {
      var raw = (obt / max) * 100;
      if (raw > 100) { raw = 100; }                    // guard against typos
      pct = (Math.round(raw * 100) / 100).toFixed(2);
    }
    pctEl.value = pct ? pct + '%' : '';

    // Suggest a division only while the user has not typed their own.
    if (divEl && !divEl.dataset.touched) {
      var p = parseFloat(pct);
      divEl.value = !pct ? '' : (p >= 60 ? 'First' : (p >= 45 ? 'Second' : (p >= 33 ? 'Third' : 'Pass')));
    }
  }
  function recalcAll() { $$('#eduTable tbody tr').forEach(recalcRow); }

  $('#eduTable').addEventListener('input', function (ev) {
    var t = ev.target;
    if (t.classList.contains('edu-div')) { t.dataset.touched = '1'; }
    if (t.classList.contains('edu-max') || t.classList.contains('edu-obt')) {
      recalcRow(t.closest('tr'));
    }
  });

  /* =======================================================================
     4. REPEATERS (extra qualification / experience / certification / achievement)
     ======================================================================= */
  var REPEATERS = {
    extra: {
      list: '#extraList', title: 'Extra Qualification',
      empty: 'No extra qualification added yet. Add PGDCA, CCC, typing or any short course.',
      row: function (n) {
        return '' +
          '<div class="row g-3">' +
            '<div class="col-12 col-md-5"><label class="form-label">Course / Qualification</label>' +
              '<input type="text" class="form-control" name="ex_name[]" maxlength="80" placeholder="PGDCA"></div>' +
            '<div class="col-12 col-md-5"><label class="form-label">Institute / Board</label>' +
              '<input type="text" class="form-control" name="ex_inst[]" maxlength="80" placeholder="NIELIT"></div>' +
            '<div class="col-12 col-md-2"><label class="form-label">Year</label>' +
              '<input type="text" class="form-control" name="ex_year[]" maxlength="4" inputmode="numeric" data-rule="year" placeholder="2022">' +
              '<div class="invalid-feedback">4-digit year.</div></div>' +
          '</div>';
      }
    },
    exp: {
      list: '#expList', title: 'Experience',
      empty: 'No work experience added. Freshers can skip this step.',
      row: function (n) {
        return '' +
          '<div class="row g-3">' +
            '<div class="col-12 col-md-6"><label class="form-label">Company / Organisation</label>' +
              '<input type="text" class="form-control" name="exp_company[]" maxlength="80" placeholder="ABC Enterprises Pvt. Ltd."></div>' +
            '<div class="col-12 col-md-6"><label class="form-label">Designation</label>' +
              '<input type="text" class="form-control" name="exp_role[]" maxlength="80" placeholder="Accounts Assistant"></div>' +
            '<div class="col-6 col-md-3"><label class="form-label">From</label>' +
              '<input type="month" class="form-control" name="exp_from[]"></div>' +
            '<div class="col-6 col-md-3"><label class="form-label">To</label>' +
              '<input type="month" class="form-control" name="exp_to[]"></div>' +
            '<div class="col-12 col-md-6 d-flex align-items-end">' +
              '<div class="form-check mb-2"><input class="form-check-input exp-current" type="checkbox" name="exp_current[]" value="1" id="expcur' + n + '">' +
              '<label class="form-check-label" for="expcur' + n + '">I currently work here</label></div></div>' +
            '<div class="col-12"><label class="form-label">Responsibilities</label>' +
              '<textarea class="form-control" name="exp_duty[]" rows="3" maxlength="500" placeholder="One responsibility per line — each line becomes a bullet point."></textarea></div>' +
          '</div>';
      }
    },
    cert: {
      list: '#certList', title: 'Certification',
      empty: 'No certification added yet.',
      row: function (n) {
        return '' +
          '<div class="row g-3">' +
            '<div class="col-12 col-md-5"><label class="form-label">Certification Name</label>' +
              '<input type="text" class="form-control" name="ct_name[]" maxlength="90" placeholder="Advanced Excel"></div>' +
            '<div class="col-12 col-md-5"><label class="form-label">Issuing Authority</label>' +
              '<input type="text" class="form-control" name="ct_by[]" maxlength="90" placeholder="NIIT"></div>' +
            '<div class="col-12 col-md-2"><label class="form-label">Year</label>' +
              '<input type="text" class="form-control" name="ct_year[]" maxlength="4" inputmode="numeric" data-rule="year" placeholder="2023">' +
              '<div class="invalid-feedback">4-digit year.</div></div>' +
          '</div>';
      }
    },
    ach: {
      list: '#achList', title: 'Achievement',
      empty: 'No achievement added yet.',
      row: function (n) {
        return '' +
          '<label class="form-label">Achievement</label>' +
          '<input type="text" class="form-control" name="ach_text[]" maxlength="160" placeholder="Secured 1st position in State Level Quiz Competition, 2023">';
      }
    }
  };

  function repCount(key) { return $$('.rep-item', $(REPEATERS[key].list)).length; }

  function repRenumber(key) {
    var cfg = REPEATERS[key], list = $(cfg.list);
    var items = $$('.rep-item', list);
    items.forEach(function (it, idx) {
      $('.rep-badge', it).textContent = idx + 1;
      $('.rep-title', it).textContent = cfg.title + ' ' + (idx + 1);
    });
    var note = $('.empty-note', list);
    if (!items.length) {
      if (!note) {
        note = document.createElement('div');
        note.className = 'empty-note';
        note.innerHTML = '<i class="bi bi-inbox"></i> ' + esc(cfg.empty);
        list.appendChild(note);
      }
    } else if (note) {
      note.remove();
    }
  }

  function repAdd(key, focus) {
    var cfg = REPEATERS[key], list = $(cfg.list);
    var n = repCount(key) + 1;
    var item = document.createElement('div');
    item.className = 'rep-item';
    item.innerHTML =
      '<div class="rep-head">' +
        '<span class="rep-badge">' + n + '</span>' +
        '<p class="rep-title">' + esc(cfg.title) + ' ' + n + '</p>' +
        '<button type="button" class="btn-del" data-del title="Remove"><i class="bi bi-trash3"></i></button>' +
      '</div>' + cfg.row(Date.now() + '_' + n);
    list.appendChild(item);
    repRenumber(key);
    if (focus) {
      var first = $('input, textarea', item);
      if (first) { first.focus(); }
    }
    return item;
  }

  $$('[data-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      repAdd(btn.getAttribute('data-add'), true);
      render(); scheduleSave();
    });
  });

  document.addEventListener('click', function (ev) {
    var del = ev.target.closest('[data-del]');
    if (!del) { return; }
    var item = del.closest('.rep-item');
    var list = item.parentNode;
    var key  = list.getAttribute('data-rep');
    item.remove();
    repRenumber(key);
    render(); scheduleSave();
  });

  // "I currently work here" disables the To date.
  document.addEventListener('change', function (ev) {
    if (!ev.target.classList.contains('exp-current')) { return; }
    var row = ev.target.closest('.rep-item');
    var to  = row.querySelector('[name="exp_to[]"]');
    if (to) { to.disabled = ev.target.checked; if (ev.target.checked) { to.value = ''; } }
  });

  /* =======================================================================
     5. IMAGE HANDLING - photo crop/resize/compress + signature
     ======================================================================= */
  var PHOTO_W = 350, PHOTO_H = 450;                    // 35 x 45 mm passport
  var MAX_BYTES = 2 * 1024 * 1024;

  function readImage(file, onDone, onErr) {
    if (!file) { return; }
    if (['image/jpeg', 'image/png'].indexOf(file.type) === -1) {
      onErr('Only JPG and PNG images are allowed.'); return;
    }
    if (file.size > MAX_BYTES) {
      onErr('Image is larger than 2 MB. Please choose a smaller file.'); return;
    }
    var fr = new FileReader();
    fr.onerror = function () { onErr('Could not read that file.'); };
    fr.onload = function () {
      var img = new Image();
      img.onerror = function () { onErr('That file is not a valid image.'); };
      img.onload  = function () { onDone(img); };
      img.src = fr.result;
    };
    fr.readAsDataURL(file);
  }

  /* ---- passport photo cropper (drag to pan, slider to zoom) ---- */
  var crop = {
    img: null, zoom: 1, x: 0, y: 0, drag: false, px: 0, py: 0,
    canvas: $('#cropCanvas'), ctx: null
  };
  crop.ctx = crop.canvas.getContext('2d');

  function cropDraw() {
    var c = crop.canvas, ctx = crop.ctx;
    ctx.clearRect(0, 0, c.width, c.height);
    if (!crop.img) { return; }
    var base = Math.max(c.width / crop.img.width, c.height / crop.img.height);
    var s = base * crop.zoom;
    var w = crop.img.width * s, h = crop.img.height * s;
    // Keep the image covering the frame at all times.
    crop.x = Math.min(0, Math.max(c.width - w, crop.x));
    crop.y = Math.min(0, Math.max(c.height - h, crop.y));
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, c.width, c.height);
    ctx.drawImage(crop.img, crop.x, crop.y, w, h);
  }

  /** Render the crop at full passport resolution and compress to JPEG. */
  var cropExport = debounce(function () {
    if (!crop.img) { return; }
    var out = document.createElement('canvas');
    out.width = PHOTO_W; out.height = PHOTO_H;
    var octx = out.getContext('2d');
    octx.fillStyle = '#ffffff';
    octx.fillRect(0, 0, PHOTO_W, PHOTO_H);
    var k = PHOTO_W / crop.canvas.width;                // preview -> output scale
    var base = Math.max(crop.canvas.width / crop.img.width, crop.canvas.height / crop.img.height);
    var s = base * crop.zoom * k;
    octx.imageSmoothingQuality = 'high';
    octx.drawImage(crop.img, crop.x * k, crop.y * k, crop.img.width * s, crop.img.height * s);
    media.photo = out.toDataURL('image/jpeg', 0.85);
    $('#photoFrame').innerHTML = '<img src="' + media.photo + '" alt="Passport photograph preview">';
    render(); scheduleSave();
  }, 120);

  $('#f_photo').addEventListener('change', function () {
    var file = this.files && this.files[0];
    var input = this;
    readImage(file, function (img) {
      crop.img = img; crop.zoom = 1; crop.x = 0; crop.y = 0;
      $('#cropZoom').value = 100;
      $('#cropStage').classList.add('show');
      cropDraw(); cropExport();
      toast('Photo added. Drag to reposition, use zoom to frame the face.', 'ok');
    }, function (msg) {
      input.value = '';
      toast(msg, 'err');
    });
  });

  $('#cropZoom').addEventListener('input', function () {
    crop.zoom = parseInt(this.value, 10) / 100;
    cropDraw(); cropExport();
  });

  crop.canvas.addEventListener('pointerdown', function (ev) {
    if (!crop.img) { return; }
    crop.drag = true; crop.px = ev.clientX; crop.py = ev.clientY;
    this.setPointerCapture(ev.pointerId);
  });
  crop.canvas.addEventListener('pointermove', function (ev) {
    if (!crop.drag) { return; }
    crop.x += ev.clientX - crop.px;
    crop.y += ev.clientY - crop.py;
    crop.px = ev.clientX; crop.py = ev.clientY;
    cropDraw();
  });
  ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (t) {
    crop.canvas.addEventListener(t, function (ev) {
      if (!crop.drag) { return; }
      crop.drag = false;
      try { this.releasePointerCapture(ev.pointerId); } catch (e) {}
      cropExport();
    });
  });

  $('#btnPhotoRemove').addEventListener('click', function () {
    crop.img = null; media.photo = '';
    $('#f_photo').value = '';
    $('#cropStage').classList.remove('show');
    $('#photoFrame').innerHTML = '<i class="bi bi-person-bounding-box"></i>';
    render(); scheduleSave();
  });

  /* ---- signature ---- */
  function setSignMode(mode) {
    var up = (mode === 'upload');
    $('#f_sign').disabled = !up;
    if (!up) {
      media.signature = '';
      $('#f_sign').value = '';
      $('#signFrame').innerHTML = '<i class="bi bi-vector-pen"></i>';
      $('#btnSignRemove').classList.add('d-none');
    }
    render(); scheduleSave();
  }
  $$('input[name="sign_mode"]').forEach(function (r) {
    r.addEventListener('change', function () { setSignMode(this.value); });
  });

  $('#f_sign').addEventListener('change', function () {
    var file = this.files && this.files[0];
    var input = this;
    readImage(file, function (img) {
      var maxW = 600, maxH = 220;
      var s = Math.min(maxW / img.width, maxH / img.height, 1);
      var c = document.createElement('canvas');
      c.width  = Math.max(1, Math.round(img.width * s));
      c.height = Math.max(1, Math.round(img.height * s));
      var ctx = c.getContext('2d');
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(img, 0, 0, c.width, c.height);
      media.signature = c.toDataURL('image/png');       // PNG keeps transparency
      $('#signFrame').innerHTML = '<img src="' + media.signature + '" alt="Signature preview">';
      $('#btnSignRemove').classList.remove('d-none');
      render(); scheduleSave();
      toast('Signature added.', 'ok');
    }, function (msg) {
      input.value = '';
      toast(msg, 'err');
    });
  });

  $('#btnSignRemove').addEventListener('click', function () {
    media.signature = '';
    $('#f_sign').value = '';
    $('#signFrame').innerHTML = '<i class="bi bi-vector-pen"></i>';
    this.classList.add('d-none');
    render(); scheduleSave();
  });

  /* ---- drag & drop onto the uploader boxes ---- */
  [['#photoDrop', '#f_photo'], ['#signDrop', '#f_sign']].forEach(function (pair) {
    var zone = $(pair[0]), input = $(pair[1]);
    ['dragenter', 'dragover'].forEach(function (t) {
      zone.addEventListener(t, function (ev) {
        ev.preventDefault(); zone.classList.add('dragover');
      });
    });
    ['dragleave', 'drop'].forEach(function (t) {
      zone.addEventListener(t, function (ev) {
        ev.preventDefault(); zone.classList.remove('dragover');
      });
    });
    zone.addEventListener('drop', function (ev) {
      if (input.disabled) { toast('Choose "Upload signature image" first.', 'err'); return; }
      var f = ev.dataTransfer && ev.dataTransfer.files && ev.dataTransfer.files[0];
      if (!f) { return; }
      var dt = new DataTransfer();
      dt.items.add(f);
      input.files = dt.files;
      input.dispatchEvent(new Event('change'));
    });
  });

  /* =======================================================================
     6. LIVE PREVIEW - builds the ATS resume
     ======================================================================= */
  function fullName() {
    return [val('title'), val('first_name'), val('middle_name'), val('last_name')]
      .filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
  }
  function nameNoTitle() {
    return [val('first_name'), val('middle_name'), val('last_name')]
      .filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
  }

  function collectEducation() {
    var quals = valAll('edu_qual[]'), boards = valAll('edu_board[]'),
        subs  = valAll('edu_subject[]'), years = valAll('edu_year[]'),
        maxs  = valAll('edu_max[]'), obts = valAll('edu_obt[]'),
        pcts  = valAll('edu_pct[]'), grades = valAll('edu_grade[]'),
        divs  = valAll('edu_division[]');
    var out = [];
    for (var i = 0; i < quals.length; i++) {
      // A row counts only when the student actually filled something in.
      if (!(boards[i] || subs[i] || years[i] || maxs[i] || obts[i] || grades[i] || divs[i])) { continue; }
      out.push({
        qual: quals[i], board: boards[i], subject: subs[i], year: years[i],
        max: maxs[i], obt: obts[i], pct: pcts[i], grade: grades[i], div: divs[i]
      });
    }
    return out;
  }

  function collectRepeater(names) {
    var cols = names.map(valAll);
    var len = cols.reduce(function (m, c) { return Math.max(m, c.length); }, 0);
    var rows = [];
    for (var i = 0; i < len; i++) {
      var o = {};
      names.forEach(function (n, k) { o[n] = (cols[k][i] || ''); });
      rows.push(o);
    }
    return rows;
  }

  function section(title, body) {
    if (!body) { return ''; }
    return '<section><h2 class="r-h">' + esc(title) + '</h2>' + body + '</section>';
  }
  function bullets(arr) {
    if (!arr.length) { return ''; }
    return '<ul>' + arr.map(function (l) { return '<li>' + escBr(l) + '</li>'; }).join('') + '</ul>';
  }

  function render() {
    var name = nameNoTitle();
    var out = [];

    /* ---------------- header: name, post, contacts, photo ---------------- */
    var contacts = [];
    if (val('mobile'))     { contacts.push('<span>' + esc(val('mobile')) + '</span>'); }
    if (val('mobile_alt')) { contacts.push('<span>' + esc(val('mobile_alt')) + '</span>'); }
    if (val('email'))      { contacts.push('<span>' + esc(val('email')) + '</span>'); }
    var addrLine = val('present_address').replace(/\s*\n\s*/g, ', ');
    if (addrLine) { contacts.push('<span>' + esc(addrLine) + '</span>'); }

    out.push('<div class="r-top"><div class="r-top-main">');
    out.push('<h1 class="r-name">' + (name ? esc(name) : '<span class="r-placeholder">Your Name</span>') + '</h1>');
    if (val('post')) { out.push('<p class="r-post">' + esc(val('post')) + '</p>'); }
    out.push('<p class="r-contact">' + (contacts.length ? contacts.join('') :
      '<span class="r-placeholder">Mobile | Email | Address</span>') + '</p>');
    out.push('</div>');
    if (media.photo) {
      out.push('<div class="r-photo"><img src="' + media.photo + '" alt="Photograph of ' + esc(name) + '"></div>');
    }
    out.push('</div><hr class="r-rule">');

    /* ---------------------------- objective ------------------------------ */
    if (val('objective')) {
      out.push(section('Career Objective', '<p>' + escBr(val('objective')) + '</p>'));
    }

    /* ------------------------- personal details -------------------------- */
    var facts = [];
    function fact(label, value, wide) {
      if (!value) { return; }
      facts.push('<div class="r-fact' + (wide ? ' r-fact-wide' : '') + '"><b>' + esc(label) + '</b>' +
                 '<span>' + escBr(value) + '</span></div>');
    }
    fact("Father's Name", val('father'));
    fact("Mother's Name", val('mother'));
    fact('Date of Birth', fmtDate(val('dob')));
    fact('Gender', val('gender'));
    fact('Marital Status', val('marital'));
    fact('Nationality', val('nationality'));
    fact('Category', val('category'));
    fact('Languages Known', val('languages'));
    var pres = val('present_address').replace(/\s*\n\s*/g, ', ');
    var perm = val('permanent_address').replace(/\s*\n\s*/g, ', ');
    fact('Present Address', pres, true);
    fact('Permanent Address', perm, true);
    if (facts.length) {
      out.push(section('Personal Details', '<div class="r-facts">' + facts.join('') + '</div>'));
    }

    /* ---------------------------- education ------------------------------ */
    var edu = collectEducation();
    if (edu.length) {
      var t = '<table class="r-edu"><thead><tr>' +
        '<th>Qualification</th><th>Board / University</th><th>Subject</th>' +
        '<th>Year</th><th>Marks</th><th>%</th><th>Grade</th><th>Division</th>' +
        '</tr></thead><tbody>';
      edu.forEach(function (r) {
        var marks = (r.obt && r.max) ? (r.obt + ' / ' + r.max) : (r.obt || r.max || '-');
        t += '<tr>' +
          '<td>' + esc(r.qual) + '</td>' +
          '<td>' + esc(r.board || '-') + '</td>' +
          '<td>' + esc(r.subject || '-') + '</td>' +
          '<td>' + esc(r.year || '-') + '</td>' +
          '<td>' + esc(marks) + '</td>' +
          '<td>' + esc(r.pct || '-') + '</td>' +
          '<td>' + esc(r.grade || '-') + '</td>' +
          '<td>' + esc(r.div || '-') + '</td>' +
        '</tr>';
      });
      t += '</tbody></table>';
      out.push(section('Educational Qualification', t));
    }

    /* ------------------------------ skills ------------------------------- */
    var sk = lines(val('skills'));
    if (sk.length) { out.push(section('Skills', bullets(sk))); }

    /* ------------------------ extra qualification ------------------------ */
    var extra = collectRepeater(['ex_name[]', 'ex_inst[]', 'ex_year[]'])
      .filter(function (r) { return r['ex_name[]'] || r['ex_inst[]']; });
    if (extra.length) {
      var eh = '<ul>' + extra.map(function (r) {
        var s = '<b>' + esc(r['ex_name[]'] || '') + '</b>';
        if (r['ex_inst[]']) { s += ' — ' + esc(r['ex_inst[]']); }
        if (r['ex_year[]']) { s += ' (' + esc(r['ex_year[]']) + ')'; }
        return '<li>' + s + '</li>';
      }).join('') + '</ul>';
      out.push(section('Extra Qualification', eh));
    }

    /* --------------------------- work experience ------------------------- */
    var currentFlags = $$('.exp-current').map(function (c) { return c.checked; });
    var exp = collectRepeater(['exp_company[]', 'exp_role[]', 'exp_from[]', 'exp_to[]', 'exp_duty[]']);
    var expHtml = '';
    exp.forEach(function (r, i) {
      if (!(r['exp_company[]'] || r['exp_role[]'] || r['exp_duty[]'])) { return; }
      var from = fmtMonth(r['exp_from[]']);
      var to   = currentFlags[i] ? 'Present' : fmtMonth(r['exp_to[]']);
      var span = from && to ? from + ' – ' + to : (from || to || '');
      expHtml += '<div class="r-block">' +
        '<div class="r-block-top">' +
          '<span class="r-block-title">' + esc(r['exp_role[]'] || 'Role') + '</span>' +
          (span ? '<span class="r-block-meta">' + esc(span) + '</span>' : '') +
        '</div>' +
        (r['exp_company[]'] ? '<div class="r-block-sub">' + esc(r['exp_company[]']) + '</div>' : '') +
        bullets(lines(r['exp_duty[]'])) +
      '</div>';
    });
    if (expHtml) { out.push(section('Work Experience', expHtml)); }

    /* ---------------------------- certifications ------------------------- */
    var certs = collectRepeater(['ct_name[]', 'ct_by[]', 'ct_year[]'])
      .filter(function (r) { return r['ct_name[]']; });
    if (certs.length) {
      var ch = '<ul>' + certs.map(function (r) {
        var s = '<b>' + esc(r['ct_name[]']) + '</b>';
        if (r['ct_by[]'])   { s += ' — ' + esc(r['ct_by[]']); }
        if (r['ct_year[]']) { s += ' (' + esc(r['ct_year[]']) + ')'; }
        return '<li>' + s + '</li>';
      }).join('') + '</ul>';
      out.push(section('Certifications', ch));
    }

    /* ----------------------------- achievements -------------------------- */
    var ach = valAll('ach_text[]').filter(Boolean);
    if (ach.length) { out.push(section('Achievements', bullets(ach))); }

    /* ------------------------------- hobbies ----------------------------- */
    var hob = lines(val('hobbies'));
    if (hob.length) {
      out.push(section('Hobbies & Interests',
        hob.length === 1 ? '<p>' + esc(hob[0]) + '</p>' : bullets(hob)));
    }

    /* ----------------------------- declaration --------------------------- */
    if (val('declaration')) {
      out.push(section('Declaration', '<p>' + escBr(val('declaration')) + '</p>'));
    }

    /* ------------------------- signature / place / date ------------------ */
    var left = '';
    if (val('place'))     { left += '<div><b>Place:</b> ' + esc(val('place')) + '</div>'; }
    if (val('sign_date')) { left += '<div><b>Date:</b> ' + esc(fmtDate(val('sign_date'))) + '</div>'; }
    var signImg = media.signature
      ? '<div class="r-sign-img"><img src="' + media.signature + '" alt="Signature"></div>'
      : '<div class="r-sign-img"></div>';
    var right = signImg + '<div class="r-sign-line">' +
      (name ? esc('(' + name + ')') : '(Signature)') + '</div>';
    out.push('<div class="r-sign"><div class="r-sign-left">' + left + '</div>' +
             '<div class="r-sign-right">' + right + '</div></div>');

    sheet.innerHTML = out.join('');
    fitSheet();

    // Keep the PDF filename hint honest.
    $('#pdfNameHint').textContent = pdfFileName() + '.pdf';
  }

  /* ---- scale the A4 sheet down to fit the preview column ---- */
  function fitSheet() {
    var wrap = $('#sheetWrap');
    if (!wrap || !wrap.parentNode) { return; }
    var avail = wrap.parentNode.clientWidth - 2;
    sheet.style.transform = 'none';
    var natural = sheet.offsetWidth;
    if (!natural) { return; }
    var s = Math.min(1, avail / natural);
    sheet.style.transform = 'scale(' + s + ')';
    wrap.style.height = (sheet.offsetHeight * s) + 'px';
    wrap.style.width  = (natural * s) + 'px';
  }
  window.addEventListener('resize', debounce(fitSheet, 120));

  /* =======================================================================
     7. FORM WIRING - live updates, address copy, counters
     ======================================================================= */
  var renderSoon = debounce(render, 60);

  form.addEventListener('input', function () { renderSoon(); scheduleSave(); });
  form.addEventListener('change', function () { renderSoon(); scheduleSave(); });

  // Same as present address
  function syncAddress() {
    var same = $('#f_sameaddr').checked;
    var a1 = $('#f_addr1'), a2 = $('#f_addr2');
    a2.readOnly = same;
    a2.style.background = same ? '#f4f6fb' : '';
    if (same) { a2.value = a1.value; }
  }
  $('#f_sameaddr').addEventListener('change', function () { syncAddress(); render(); scheduleSave(); });
  $('#f_addr1').addEventListener('input', function () {
    if ($('#f_sameaddr').checked) { $('#f_addr2').value = this.value; }
  });

  // Character counters
  $$('[data-counter]').forEach(function (el) {
    var out = $('#' + el.getAttribute('data-counter'));
    var max = parseInt(el.getAttribute('maxlength'), 10) || 0;
    var upd = function () {
      var n = el.value.length;
      out.textContent = n + ' / ' + max;
      out.classList.toggle('over', max && n >= max);
    };
    el.addEventListener('input', upd);
    el._updCounter = upd;
    upd();
  });
  function refreshCounters() {
    $$('[data-counter]').forEach(function (el) { if (el._updCounter) { el._updCounter(); } });
  }

  // Objective quick starters
  $$('.objective-preset').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var t = $('#f_objective');
      t.value = this.textContent.trim();
      t.dispatchEvent(new Event('input', { bubbles: true }));
      t.focus();
    });
  });

  $('#btnResetDecl').addEventListener('click', function () {
    var d = $('#f_declaration');
    d.value = DEFAULT_DECLARATION;
    d.dispatchEvent(new Event('input', { bubbles: true }));
  });

  /* =======================================================================
     8. DRAFT PERSISTENCE (localStorage - no server, no database)
     ======================================================================= */
  function snapshot() {
    var data = { fields: {}, arrays: {}, media: media, step: current, v: 1 };
    $$('input, select, textarea', form).forEach(function (el) {
      if (!el.name || el.type === 'file') { return; }
      if (el.name.endsWith('[]')) {
        if (!data.arrays[el.name]) { data.arrays[el.name] = []; }
        data.arrays[el.name].push(el.type === 'checkbox' ? (el.checked ? '1' : '') : el.value);
      } else if (el.type === 'checkbox') {
        data.fields[el.name] = el.checked;
      } else if (el.type === 'radio') {
        if (el.checked) { data.fields[el.name] = el.value; }
      } else {
        data.fields[el.name] = el.value;
      }
    });
    data.counts = {
      extra: repCount('extra'), exp: repCount('exp'),
      cert: repCount('cert'), ach: repCount('ach')
    };
    return data;
  }

  function saveDraft(silent) {
    try {
      localStorage.setItem(STORE_KEY, JSON.stringify(snapshot()));
      var flag = $('#saveFlag');
      flag.classList.add('on');
      flag.querySelector('span').textContent = 'Draft saved';
      if (!silent) { toast('Draft saved in this browser.', 'ok'); }
      return true;
    } catch (err) {
      // Almost always a quota error caused by very large images.
      if (!silent) { toast('Could not save draft — storage is full. Try a smaller photo.', 'err'); }
      return false;
    }
  }
  var scheduleSave = debounce(function () { saveDraft(true); }, 700);

  function restoreDraft() {
    var raw;
    try { raw = localStorage.getItem(STORE_KEY); } catch (err) { return false; }
    if (!raw) { return false; }

    var data;
    try { data = JSON.parse(raw); } catch (err) { return false; }
    if (!data || typeof data !== 'object') { return false; }

    // Rebuild repeater rows first so their inputs exist.
    Object.keys(REPEATERS).forEach(function (key) {
      $$('.rep-item', $(REPEATERS[key].list)).forEach(function (n) { n.remove(); });
      var n = (data.counts && parseInt(data.counts[key], 10)) || 0;
      for (var i = 0; i < n; i++) { repAdd(key, false); }
      repRenumber(key);
    });

    // Scalars
    Object.keys(data.fields || {}).forEach(function (name) {
      var el = form.elements[name];
      if (!el) { return; }
      var v = data.fields[name];
      if (el.length && el[0] && el[0].type === 'radio') {
        Array.prototype.forEach.call(el, function (r) { r.checked = (r.value === v); });
      } else if (el.type === 'checkbox') {
        el.checked = !!v;
      } else {
        el.value = v == null ? '' : v;
      }
    });

    // Arrays
    Object.keys(data.arrays || {}).forEach(function (name) {
      var els = form.elements[name];
      if (!els) { return; }
      var list = els.length ? Array.prototype.slice.call(els) : [els];
      (data.arrays[name] || []).forEach(function (v, i) {
        if (!list[i]) { return; }
        if (list[i].type === 'checkbox') { list[i].checked = (v === '1'); }
        else { list[i].value = v == null ? '' : v; }
      });
    });

    // Images
    media.photo     = (data.media && data.media.photo)     || '';
    media.signature = (data.media && data.media.signature) || '';
    if (media.photo) {
      $('#photoFrame').innerHTML = '<img src="' + media.photo + '" alt="Passport photograph preview">';
    }
    if (media.signature) {
      $('#signFrame').innerHTML = '<img src="' + media.signature + '" alt="Signature preview">';
      $('#btnSignRemove').classList.remove('d-none');
    }

    // Dependent UI state
    var mode = $$('input[name="sign_mode"]').filter(function (r) { return r.checked; })[0];
    $('#f_sign').disabled = !(mode && mode.value === 'upload');
    $$('.exp-current').forEach(function (c) {
      var to = c.closest('.rep-item').querySelector('[name="exp_to[]"]');
      if (to) { to.disabled = c.checked; }
    });
    $$('.edu-div').forEach(function (d) { if (d.value) { d.dataset.touched = '1'; } });

    syncAddress();
    recalcAll();
    refreshCounters();
    return true;
  }

  $('#btnSaveDraft').addEventListener('click', function () { saveDraft(false); });

  $('#btnReset').addEventListener('click', function () {
    if (!confirm('Reset the whole form? Your saved draft, photo and signature will be deleted.')) { return; }
    try { localStorage.removeItem(STORE_KEY); } catch (err) {}
    form.reset();
    media.photo = ''; media.signature = '';
    crop.img = null;
    $('#cropStage').classList.remove('show');
    $('#photoFrame').innerHTML = '<i class="bi bi-person-bounding-box"></i>';
    $('#signFrame').innerHTML = '<i class="bi bi-vector-pen"></i>';
    $('#btnSignRemove').classList.add('d-none');
    $('#f_sign').disabled = true;
    $$('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
    $$('.edu-div').forEach(function (d) { delete d.dataset.touched; });

    Object.keys(REPEATERS).forEach(function (key) {
      $$('.rep-item', $(REPEATERS[key].list)).forEach(function (n) { n.remove(); });
      repAdd(key, false);
    });

    $('#f_declaration').value = DEFAULT_DECLARATION;
    syncAddress(); recalcAll(); refreshCounters();
    $('#saveFlag').classList.remove('on');
    $('#saveFlag').querySelector('span').textContent = 'Not saved';
    showStep(0);
    render();
    toast('Form reset.', 'ok');
  });

  /* =======================================================================
     9. PRINT + PDF
     ======================================================================= */
  function pdfFileName() {
    var f = val('first_name').replace(/[^A-Za-z0-9]/g, '') || 'My';
    var l = val('last_name').replace(/[^A-Za-z0-9]/g, '')  || 'Resume';
    return f + '_' + l + '_Resume';
  }

  var savedTitle = document.title;
  function printSheet(fileName) {
    // Browsers use document.title as the default "Save as PDF" filename.
    document.title = fileName || savedTitle;
    fitSheet();
    window.print();
  }
  window.addEventListener('afterprint', function () { document.title = savedTitle; fitSheet(); });

  function ensureReady() {
    if (!validateAll()) {
      toast('Fill the required fields (name, mobile, email, date of birth) first.', 'err');
      return false;
    }
    render();
    return true;
  }

  $('#btnPrint').addEventListener('click', function () {
    if (!ensureReady()) { return; }
    saveDraft(true);
    printSheet(pdfFileName());
  });

  function downloadPdf() {
    if (!ensureReady()) { return; }
    saveDraft(true);
    toast('In the print dialog choose "Save as PDF" — A4, no watermark.', 'ok');
    setTimeout(function () { printSheet(pdfFileName()); }, 350);
  }
  $('#btnPdf').addEventListener('click', downloadPdf);

  // Ctrl/Cmd + P prints the resume, never the form.
  document.addEventListener('keydown', function (ev) {
    if ((ev.ctrlKey || ev.metaKey) && ev.key.toLowerCase() === 'p') {
      ev.preventDefault();
      $('#btnPrint').click();
    }
  });

  /* ------------------------ mobile preview toggle ---------------------- */
  var deskQuery = window.matchMedia('(min-width:1200px)');

  /** Single source of truth for preview visibility + button label. */
  function setPreviewOpen(open, scroll) {
    var card = $('#previewCard');
    card.classList.toggle('collapsed', !open);
    $('#btnMobilePreview').innerHTML = open
      ? '<i class="bi bi-pencil-square"></i> Edit'
      : '<i class="bi bi-eye"></i> Preview';
    if (open) { fitSheet(); }
    if (scroll) {
      (open ? card : form).scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  $('#btnMobilePreview').addEventListener('click', function () {
    setPreviewOpen($('#previewCard').classList.contains('collapsed'), true);
  });

  // On desktop the preview is always shown; below 1200px it starts collapsed so
  // the phone view opens on the form. Only fires when the breakpoint changes,
  // so it never overrides a choice the user just made.
  function applyBreakpoint() { setPreviewOpen(deskQuery.matches, false); }
  if (deskQuery.addEventListener) { deskQuery.addEventListener('change', applyBreakpoint); }
  else if (deskQuery.addListener) { deskQuery.addListener(applyBreakpoint); }

  /* =======================================================================
     10. BOOT
     ======================================================================= */
  function boot() {
    var restored = restoreDraft();

    if (!restored) {
      // Start every repeater with one empty row.
      Object.keys(REPEATERS).forEach(function (key) { repAdd(key, false); });
    }
    Object.keys(REPEATERS).forEach(repRenumber);

    applyBreakpoint();

    syncAddress();
    recalcAll();
    refreshCounters();
    showStep(0, true);
    render();
    setTimeout(fitSheet, 150);   // after webfonts settle

    if (restored) { toast('Your saved draft was restored.', 'ok'); }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
</script>
</body>
</html>
