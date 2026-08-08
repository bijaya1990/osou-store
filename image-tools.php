<?php
/**
 * =============================================================================
 *  NaukriPatra Image Tools  —  Image Resizer + Signature Scanner
 * =============================================================================
 *  Single-file utility. Upload this one file anywhere on your hosting and open
 *  it in a browser — nothing else to install, configure or build.
 *
 *  Requirements : PHP 7.4+ (tested through PHP 8.5). No database. No Composer.
 *                 No Node. No npm. No external CSS/JS/CDN. No build step.
 *
 *  ---------------------------------------------------------------------------
 *  PRIVACY / ARCHITECTURE NOTE  (please read before changing anything)
 *  ---------------------------------------------------------------------------
 *  Every image operation runs in the visitor's own browser using <canvas>.
 *  The chosen file is NEVER uploaded — there is deliberately no upload
 *  endpoint, no $_FILES handling and no temp directory in this file.
 *
 *  That is a security decision, not a shortcut. Users of this tool upload
 *  photographs, ID documents and signatures. The safest way to handle such a
 *  file is to never receive it: with no upload path there is no MIME-spoofing,
 *  path-traversal, or uploaded-PHP-execution risk to mitigate, and nothing
 *  can be left behind on disk.
 *
 *  PHP GD is therefore NOT required for the tool to work. GD status is
 *  detected below and reported in the footer purely so you can confirm what
 *  your hosting supports.
 * =============================================================================
 */

declare(strict_types=1);

/* --------------------------------------------------------------------------
 | Hardening headers
 |
 | The CSP allows inline <style>/<script> because this is a single-file tool by
 | requirement, but blocks every external origin, so no image or data can be
 | sent anywhere. blob: and data: are needed for canvas previews/downloads.
 * -------------------------------------------------------------------------- */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header(
        "Content-Security-Policy: default-src 'none'; " .
        "img-src 'self' data: blob:; " .
        "style-src 'self' 'unsafe-inline'; " .
        "script-src 'self' 'unsafe-inline'; " .
        "connect-src 'none'; form-action 'none'; base-uri 'none'; frame-ancestors 'self'"
    );
}

/* --------------------------------------------------------------------------
 | Configuration
 * -------------------------------------------------------------------------- */
$SITE_NAME   = 'NaukriPatra';
$TOOL_NAME   = 'NaukriPatra Image Tools';
$TOOL_SUB    = 'Resize images and prepare clean signatures for applications, exams and documents.';
$HOME_URL    = 'https://naukripatra.in/';
$MAX_MB      = 10;

/** Escape helper for anything printed into the page. */
function e(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* --------------------------------------------------------------------------
 | GD capability report (informational only — the tool does not depend on it)
 * -------------------------------------------------------------------------- */
$gd = ['loaded' => extension_loaded('gd'), 'jpeg' => false, 'png' => false, 'webp' => false];
if ($gd['loaded'] && function_exists('gd_info')) {
    $info = gd_info();
    $gd['jpeg'] = !empty($info['JPEG Support']);
    $gd['png']  = !empty($info['PNG Support']);
    $gd['webp'] = !empty($info['WebP Support']);
}
$gdSummary = $gd['loaded']
    ? sprintf(
        'PHP GD detected (JPEG %s, PNG %s, WebP %s)',
        $gd['jpeg'] ? 'yes' : 'no',
        $gd['png'] ? 'yes' : 'no',
        $gd['webp'] ? 'yes' : 'no'
    )
    : 'PHP GD not detected';

/* Size presets shared by both tools, in kilobytes. */
$SIZE_PRESETS = [10, 20, 30, 40, 50, 100, 150, 200, 250, 300, 500, 750, 1024, 1536, 2048];

/* Dimension presets. */
$IMG_PRESETS = [
    [100, 100], [150, 200], [200, 200], [300, 300],
    [350, 400], [400, 400], [600, 600],
];
$SIGN_PRESETS = [[200, 80], [300, 100], [400, 150], [500, 200]];

$YEAR = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Free online image resizer and signature scanner. Resize to exact pixel size and target file size in KB, and clean your signature for online forms. Runs entirely in your browser.">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#1d4ed8">
<title><?= e($TOOL_NAME) ?> — Image Resizer &amp; Signature Scanner</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='22' fill='%231d4ed8'/%3E%3Cpath d='M26 66l16-18 12 13 8-9 12 14z' fill='%23fff'/%3E%3Ccircle cx='36' cy='34' r='7' fill='%23fff'/%3E%3C/svg%3E">

<style>
/* ===========================================================================
   1. Tokens + reset
   =========================================================================== */
:root{
  --brand:#1d4ed8;
  --brand-dark:#1739a8;
  --brand-soft:#eef3ff;
  --ink:#0f172a;
  --ink-2:#334155;
  --muted:#64748b;
  --line:#e2e8f0;
  --bg:#f1f5f9;
  --card:#fff;
  --ok:#15803d;
  --ok-soft:#f0fdf4;
  --warn:#b45309;
  --warn-soft:#fffbeb;
  --err:#dc2626;
  --err-soft:#fef2f2;
  --r:16px;
  --shadow:0 1px 2px rgba(15,23,42,.04),0 8px 24px rgba(15,23,42,.06);
  --shadow-lg:0 16px 44px rgba(15,23,42,.16);
}
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{
  margin:0;background:var(--bg);color:var(--ink);
  font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
  font-size:15px;line-height:1.55;-webkit-font-smoothing:antialiased;
  overflow-x:hidden;
}
img{max-width:100%;display:block}
button{font:inherit;color:inherit}
:focus-visible{outline:3px solid rgba(29,78,216,.4);outline-offset:2px}
.sr{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0}
.hidden{display:none!important}

/* ===========================================================================
   2. Shell
   =========================================================================== */
.wrap{max-width:1180px;margin:0 auto;padding:0 16px}
.site-head{background:#fff;border-bottom:1px solid var(--line)}
.site-head-in{display:flex;align-items:center;gap:12px;padding:14px 0}
.logo{
  width:40px;height:40px;border-radius:12px;flex:0 0 40px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--brand),#4f83ff);color:#fff;
  box-shadow:0 6px 16px rgba(29,78,216,.3);
}
.logo svg{width:22px;height:22px}
.brand b{display:block;font-size:16px;font-weight:700;letter-spacing:-.2px;line-height:1.2}
.brand span{display:block;font-size:12px;color:var(--muted)}
.site-head-in .home{margin-left:auto;font-size:14px;font-weight:600;color:var(--brand);text-decoration:none}

.hero{text-align:center;padding:34px 0 22px}
.hero h1{margin:0 0 8px;font-size:30px;font-weight:800;letter-spacing:-.6px}
.hero p{margin:0 auto;max-width:640px;color:var(--muted);font-size:15.5px}

/* ===========================================================================
   3. Tool chooser
   =========================================================================== */
.chooser{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:20px 0 26px}
.tool-card{
  display:flex;gap:14px;align-items:flex-start;text-align:left;
  background:var(--card);border:2px solid var(--line);border-radius:var(--r);
  padding:18px;cursor:pointer;transition:.18s;box-shadow:var(--shadow);width:100%;
}
.tool-card:hover{border-color:#c3d4ff;transform:translateY(-2px)}
.tool-card[aria-selected="true"]{border-color:var(--brand);background:var(--brand-soft)}
.tool-card .ic{
  width:46px;height:46px;flex:0 0 46px;border-radius:13px;display:grid;place-items:center;
  background:var(--brand);color:#fff;
}
.tool-card[aria-selected="true"] .ic{background:var(--brand-dark)}
.tool-card .ic svg{width:24px;height:24px}
.tool-card h2{margin:0 0 3px;font-size:16.5px;font-weight:700}
.tool-card p{margin:0;font-size:13.5px;color:var(--muted);line-height:1.45}

/* ===========================================================================
   4. Panels / cards
   =========================================================================== */
.panel{display:none}
.panel.active{display:block;animation:fade .22s ease}
@keyframes fade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}

.card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);box-shadow:var(--shadow);margin-bottom:16px}
.card-h{display:flex;align-items:center;gap:10px;padding:15px 18px;border-bottom:1px solid var(--line);flex-wrap:wrap}
.card-h .n{
  width:26px;height:26px;flex:0 0 26px;border-radius:8px;background:var(--brand-soft);color:var(--brand);
  display:grid;place-items:center;font-size:12.5px;font-weight:800;
}
.card-h h3{margin:0;font-size:15.5px;font-weight:700}
.card-h .sub{margin:0;font-size:12.5px;color:var(--muted);flex:1 1 100%}
.card-b{padding:18px}

/* ===========================================================================
   5. Dropzone
   =========================================================================== */
.drop{
  border:2px dashed #c3cfe2;border-radius:14px;background:#fafcff;
  padding:30px 18px;text-align:center;cursor:pointer;transition:.18s;
}
.drop:hover,.drop.over{border-color:var(--brand);background:var(--brand-soft)}
.drop .di{
  width:54px;height:54px;margin:0 auto 10px;border-radius:15px;display:grid;place-items:center;
  background:var(--brand-soft);color:var(--brand);
}
.drop .di svg{width:28px;height:28px}
.drop b{display:block;font-size:16px;margin-bottom:3px}
/* Scoped to .dsub on purpose: a bare `.drop span` also matches the button
   span inside the dropzone and would override .btn-p's white text and
   inline-flex layout, leaving grey-on-blue text. */
.drop .dsub{display:block;font-size:13.5px;color:var(--muted)}
.drop .or{margin:10px 0 8px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em}
.drop .fmts{margin-top:12px;font-size:12px;color:var(--muted)}

/* ===========================================================================
   6. Buttons
   =========================================================================== */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  border:1px solid var(--line);background:#fff;color:var(--ink-2);
  border-radius:11px;padding:11px 16px;font-size:14.5px;font-weight:600;
  cursor:pointer;transition:.15s;text-decoration:none;min-height:44px;
}
.btn:hover{background:#f8fafc;border-color:#cbd5e1}
.btn:disabled{opacity:.5;cursor:not-allowed}
.btn svg{width:17px;height:17px;flex:0 0 17px}
.btn-p{background:var(--brand);border-color:var(--brand);color:#fff}
.btn-p:hover{background:var(--brand-dark);border-color:var(--brand-dark)}
.btn-s{background:var(--brand-soft);border-color:transparent;color:var(--brand-dark)}
.btn-s:hover{background:#e2ebff}
.btn-d{color:var(--err);border-color:#fecaca;background:var(--err-soft)}
.btn-d:hover{background:#fee2e2}
.btn-lg{width:100%;padding:15px 20px;font-size:16px;border-radius:13px;min-height:52px}
.btn-row{display:flex;gap:9px;flex-wrap:wrap}
.btn-row .btn{flex:1 1 auto}

/* ===========================================================================
   7. Form controls
   =========================================================================== */
.f-label{display:block;font-size:13px;font-weight:700;color:var(--ink-2);margin-bottom:6px}
.f-help{font-size:12.2px;color:var(--muted);margin-top:5px;line-height:1.45}
.inp,select.inp{
  width:100%;border:1px solid #d7dfea;border-radius:10px;padding:11px 12px;
  font-size:15px;background:#fdfeff;color:var(--ink);min-height:44px;
}
.inp:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(29,78,216,.12);background:#fff}
select.inp{appearance:none;background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4 6l4 4 4-4z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:16px;padding-right:32px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.field{margin-bottom:16px}
.field:last-child{margin-bottom:0}
.suffix{position:relative}
.suffix .inp{padding-right:38px}
.suffix i{position:absolute;right:12px;top:50%;transform:translateY(-50%);font-style:normal;font-size:12.5px;color:var(--muted);pointer-events:none}

/* toggle */
.tgl{display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none;padding:6px 0;min-height:44px}
.tgl input{position:absolute;opacity:0;width:0;height:0}
.tgl .track{
  width:44px;height:26px;flex:0 0 44px;border-radius:99px;background:#cbd5e1;position:relative;transition:.2s;
}
.tgl .track::after{content:"";position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#fff;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.25)}
.tgl input:checked+.track{background:var(--brand)}
.tgl input:checked+.track::after{transform:translateX(18px)}
.tgl input:focus-visible+.track{box-shadow:0 0 0 3px rgba(29,78,216,.35)}
.tgl .tl{font-size:14px;font-weight:600}
.tgl .td{font-size:12.2px;color:var(--muted);font-weight:400}

/* range */
input[type=range]{
  -webkit-appearance:none;appearance:none;width:100%;height:6px;border-radius:99px;
  background:#dbe3ee;outline:none;margin:12px 0;
}
input[type=range]::-webkit-slider-thumb{
  -webkit-appearance:none;appearance:none;width:24px;height:24px;border-radius:50%;
  background:var(--brand);cursor:pointer;border:3px solid #fff;box-shadow:0 2px 6px rgba(15,23,42,.3);
}
input[type=range]::-moz-range-thumb{
  width:24px;height:24px;border-radius:50%;background:var(--brand);cursor:pointer;
  border:3px solid #fff;box-shadow:0 2px 6px rgba(15,23,42,.3);
}
.rng-head{display:flex;justify-content:space-between;align-items:baseline;gap:10px}
.rng-val{font-size:13.5px;font-weight:800;color:var(--brand)}

/* chips */
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chip{
  border:1px solid var(--line);background:#fff;color:var(--ink-2);
  border-radius:99px;padding:9px 14px;font-size:13.5px;font-weight:600;cursor:pointer;transition:.15s;min-height:40px;
}
.chip:hover{border-color:#c3d4ff;color:var(--brand-dark)}
.chip[aria-pressed="true"]{background:var(--brand);border-color:var(--brand);color:#fff}

/* segmented */
.seg{display:flex;gap:6px;background:#f1f5f9;padding:5px;border-radius:12px;flex-wrap:wrap}
.seg button{
  flex:1 1 90px;border:0;background:transparent;border-radius:9px;padding:10px 8px;
  font-size:14px;font-weight:600;color:var(--ink-2);cursor:pointer;transition:.15s;min-height:42px;
}
.seg button:hover{background:#e6ecf5}
.seg button[aria-pressed="true"]{background:#fff;color:var(--brand);box-shadow:0 1px 3px rgba(15,23,42,.14)}

/* ===========================================================================
   8. Previews
   =========================================================================== */
.previews{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.pv{border:1px solid var(--line);border-radius:13px;overflow:hidden;background:#fff;display:flex;flex-direction:column}
.pv-h{
  padding:9px 13px;font-size:11.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;
  color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--line);
  display:flex;align-items:center;gap:7px;
}
.pv-h .dot{width:7px;height:7px;border-radius:50%;background:var(--muted)}
.pv-out .pv-h .dot{background:var(--ok)}
.pv-stage{
  flex:1;min-height:190px;display:grid;place-items:center;padding:14px;
  background:#f8fafc;
}
.pv-stage img{max-height:280px;width:auto;object-fit:contain;border-radius:6px}
.pv-stage.checker{
  background-color:#fff;
  background-image:linear-gradient(45deg,#e2e8f0 25%,transparent 25%),linear-gradient(-45deg,#e2e8f0 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#e2e8f0 75%),linear-gradient(-45deg,transparent 75%,#e2e8f0 75%);
  background-size:18px 18px;background-position:0 0,0 9px,9px -9px,-9px 0;
}
.pv-empty{color:#94a3b8;font-size:13.5px;text-align:center;padding:20px}
.pv-f{padding:10px 13px;border-top:1px solid var(--line);font-size:12.5px;color:var(--muted);background:#fff}
.pv-f b{color:var(--ink);font-weight:700}

/* meta strip */
.meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.meta span{
  background:#f1f5f9;border-radius:8px;padding:6px 11px;font-size:12.5px;color:var(--ink-2);font-weight:600;
}
.meta span b{color:var(--ink);font-weight:800}

/* notes */
.note{
  display:flex;gap:9px;align-items:flex-start;border-radius:11px;padding:11px 13px;
  font-size:13px;line-height:1.5;margin-top:12px;
}
.note svg{width:17px;height:17px;flex:0 0 17px;margin-top:1px}
.note-i{background:var(--brand-soft);color:#1e3a8a}
.note-w{background:var(--warn-soft);color:#92400e}
.note-e{background:var(--err-soft);color:#991b1b}
.note-o{background:var(--ok-soft);color:#166534}

/* result */
.result{border:1px solid #bbf7d0;background:var(--ok-soft);border-radius:13px;padding:15px;margin-top:14px}
.result h4{margin:0 0 9px;font-size:14.5px;font-weight:800;color:#166534;display:flex;align-items:center;gap:7px}
.result h4 svg{width:18px;height:18px}

/* busy */
.busy{display:flex;align-items:center;justify-content:center;gap:11px;padding:16px;color:var(--muted);font-size:14px;font-weight:600}
.spin{width:20px;height:20px;border:3px solid #dbe3ee;border-top-color:var(--brand);border-radius:50%;animation:sp .7s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){.spin{animation-duration:2s}.panel.active{animation:none}}

/* privacy + footer */
.privacy{
  display:flex;gap:11px;align-items:flex-start;background:#fff;border:1px solid var(--line);
  border-left:4px solid var(--ok);border-radius:12px;padding:14px 16px;margin:6px 0 20px;box-shadow:var(--shadow);
}
.privacy svg{width:20px;height:20px;flex:0 0 20px;color:var(--ok);margin-top:2px}
.privacy b{display:block;font-size:14px;margin-bottom:2px}
.privacy p{margin:0;font-size:13px;color:var(--muted);line-height:1.5}
.site-foot{text-align:center;padding:22px 0 34px;color:var(--muted);font-size:12.5px}
.site-foot code{background:#e8edf5;padding:2px 6px;border-radius:5px;font-size:11.5px}

/* toast */
.toast{
  position:fixed;left:50%;bottom:22px;transform:translate(-50%,18px);
  background:var(--ink);color:#fff;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:600;
  box-shadow:var(--shadow-lg);opacity:0;pointer-events:none;transition:.24s;z-index:99;max-width:92vw;text-align:center;
}
.toast.show{opacity:1;transform:translate(-50%,0)}
.toast.err{background:var(--err)}
.toast.ok{background:var(--ok)}

/* ===========================================================================
   9. Responsive
   =========================================================================== */
@media (max-width:860px){
  .previews{grid-template-columns:1fr}
  .grid3{grid-template-columns:1fr}
}
@media (max-width:640px){
  .wrap{padding:0 12px}
  .hero{padding:24px 0 16px}
  .hero h1{font-size:24px}
  .hero p{font-size:14.5px}
  .chooser{grid-template-columns:1fr;gap:11px;margin:14px 0 20px}
  .tool-card{padding:15px;gap:12px}
  .card-b{padding:15px}
  .card-h{padding:13px 15px}
  .grid2{grid-template-columns:1fr;gap:12px}
  .drop{padding:24px 14px}
  .btn-row .btn{flex:1 1 100%}
  .pv-stage img{max-height:220px}
}
</style>
</head>
<body>

<!-- ======================================================================= -->
<header class="site-head">
  <div class="wrap site-head-in">
    <span class="logo" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
      </svg>
    </span>
    <span class="brand">
      <b><?= e($SITE_NAME) ?> Image Tools</b>
      <span>Resizer &amp; Signature Scanner</span>
    </span>
    <a class="home" href="<?= e($HOME_URL) ?>">&larr; Home</a>
  </div>
</header>

<main class="wrap">

  <div class="hero">
    <h1><?= e($TOOL_NAME) ?></h1>
    <p><?= e($TOOL_SUB) ?></p>
  </div>

  <!-- ============================ TOOL CHOOSER ============================ -->
  <div class="chooser" role="tablist" aria-label="Choose a tool">
    <button type="button" class="tool-card" role="tab" id="tab-resizer"
            aria-selected="true" aria-controls="panel-resizer" data-tool="resizer">
      <span class="ic" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
        </svg>
      </span>
      <span>
        <h2>Image Resizer</h2>
        <p>Resize your image to the exact width, height and file size you need.</p>
      </span>
    </button>

    <button type="button" class="tool-card" role="tab" id="tab-signature"
            aria-selected="false" aria-controls="panel-signature" data-tool="signature">
      <span class="ic" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 17c3.5 0 3.5-9 7-9s3.5 9 7 9c1.7 0 2.7-1 3.5-2"/><path d="M3 21h18"/>
        </svg>
      </span>
      <span>
        <h2>Signature Scanner</h2>
        <p>Clean, resize and prepare your signature for online applications.</p>
      </span>
    </button>
  </div>

  <div class="privacy">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>
    </svg>
    <div>
      <b>Your files are processed securely and are not publicly displayed.</b>
      <p>
        Everything happens inside your own browser. Your image is never uploaded to our server,
        never stored, and never sent to any third-party or AI service. Closing this page erases it.
      </p>
    </div>
  </div>

<!-- ======================================================================= -->
<!--  PANEL 1 : IMAGE RESIZER                                                -->
<!-- ======================================================================= -->
<section class="panel active" id="panel-resizer" role="tabpanel" aria-labelledby="tab-resizer">

  <!-- STEP 1 : UPLOAD -->
  <div class="card">
    <div class="card-h"><span class="n">1</span><h3>Upload Your Image</h3></div>
    <div class="card-b">
      <div class="drop" id="r-drop" tabindex="0" role="button" aria-label="Upload your image">
        <span class="di" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v13"/>
          </svg>
        </span>
        <b>Drag &amp; Drop your image here</b>
        <span class="dsub">Tap or click to browse your device</span>
        <div class="or">or</div>
        <span class="btn btn-p" id="r-choose-fake">Choose Image</span>
        <div class="fmts">JPG &middot; JPEG &middot; PNG &middot; WEBP &nbsp;|&nbsp; Maximum recommended file size: <?= (int) $MAX_MB ?> MB</div>
      </div>
      <input type="file" id="r-file" class="sr" accept="image/jpeg,image/png,image/webp">
      <div id="r-src-meta" class="meta hidden"></div>
      <div id="r-upload-note"></div>
    </div>
  </div>

  <!-- STEP 2 : PREVIEW -->
  <div class="card hidden" id="r-work">
    <div class="card-h"><span class="n">2</span><h3>Preview</h3>
      <p class="sub">Output preview updates automatically as you change the settings.</p>
    </div>
    <div class="card-b">
      <div class="previews">
        <div class="pv">
          <div class="pv-h"><span class="dot"></span> Original</div>
          <div class="pv-stage" id="r-pv-src"></div>
          <div class="pv-f" id="r-pv-src-f">&nbsp;</div>
        </div>
        <div class="pv pv-out">
          <div class="pv-h"><span class="dot"></span> Output</div>
          <div class="pv-stage" id="r-pv-out"><div class="pv-empty">Adjust the settings below</div></div>
          <div class="pv-f" id="r-pv-out-f">&nbsp;</div>
        </div>
      </div>
    </div>
  </div>

  <!-- STEP 3-4 : DIMENSIONS -->
  <div class="card hidden" id="r-dims-card">
    <div class="card-h"><span class="n">3</span><h3>Width &amp; Height</h3></div>
    <div class="card-b">
      <div class="grid2 field">
        <div>
          <label class="f-label" for="r-w">Width</label>
          <div class="suffix"><input class="inp" type="number" id="r-w" min="1" max="12000" inputmode="numeric"><i>px</i></div>
        </div>
        <div>
          <label class="f-label" for="r-h">Height</label>
          <div class="suffix"><input class="inp" type="number" id="r-h" min="1" max="12000" inputmode="numeric"><i>px</i></div>
        </div>
      </div>

      <label class="tgl field">
        <input type="checkbox" id="r-lock" checked>
        <span class="track"></span>
        <span><span class="tl">Lock Aspect Ratio</span><br><span class="td">Changing width adjusts height automatically</span></span>
      </label>

      <div class="field">
        <button type="button" class="btn btn-s" id="r-orig-dims">Keep Original Dimensions</button>
      </div>

      <div class="field">
        <span class="f-label">Quick Size Presets</span>
        <div class="chips" id="r-presets">
          <?php foreach ($IMG_PRESETS as $p): ?>
            <button type="button" class="chip" data-w="<?= (int) $p[0] ?>" data-h="<?= (int) $p[1] ?>" aria-pressed="false"><?= (int) $p[0] ?> &times; <?= (int) $p[1] ?></button>
          <?php endforeach; ?>
          <button type="button" class="chip" data-custom="1" aria-pressed="true">Custom Size</button>
        </div>
      </div>

      <div class="field">
        <span class="f-label">Resize Method</span>
        <div class="seg" id="r-method">
          <button type="button" data-v="fit" aria-pressed="true">Fit</button>
          <button type="button" data-v="fill" aria-pressed="false">Fill</button>
          <button type="button" data-v="stretch" aria-pressed="false">Stretch</button>
        </div>
        <p class="f-help" id="r-method-help"></p>
      </div>

      <div class="field">
        <span class="f-label">Background</span>
        <div class="seg" id="r-bg">
          <button type="button" data-v="original" aria-pressed="true">Original</button>
          <button type="button" data-v="white" aria-pressed="false">White</button>
          <button type="button" data-v="black" aria-pressed="false">Black</button>
          <button type="button" data-v="custom" aria-pressed="false">Custom</button>
        </div>
        <div class="grid2" style="margin-top:10px">
          <div><input type="color" class="inp" id="r-bg-color" value="#ffffff" style="padding:5px;height:44px"></div>
          <div></div>
        </div>
        <p class="f-help">Used for transparent areas and for any empty space left by <b>Fit</b>. JPG has no transparency, so transparent areas become the background colour.</p>
      </div>
    </div>
  </div>

  <!-- STEP 5-7 : SIZE / QUALITY / FORMAT -->
  <div class="card hidden" id="r-out-card">
    <div class="card-h"><span class="n">4</span><h3>File Size, Quality &amp; Format</h3></div>
    <div class="card-b">

      <div class="field">
        <label class="f-label" for="r-target">Target File Size</label>
        <select class="inp" id="r-target">
          <option value="0" selected>No limit — use the quality slider</option>
          <?php foreach ($SIZE_PRESETS as $kb): ?>
            <option value="<?= (int) $kb ?>"><?= $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.') . ' MB' : $kb . ' KB' ?></option>
          <?php endforeach; ?>
          <option value="-1">Custom…</option>
        </select>

        <div id="r-target-slider" class="hidden" style="margin-top:12px">
          <div class="rng-head">
            <span class="f-label" style="margin:0">Target Size</span>
            <span class="rng-val" id="r-target-val">100 KB</span>
          </div>
          <input type="range" id="r-target-range" min="10" max="2048" step="5" value="100">
          <p class="f-help">Range: 10 KB &rarr; 2 MB</p>
        </div>

        <p class="f-help">
          The tool compresses towards your target and gets as close as it can while keeping the image valid.
          Very small targets at large dimensions will visibly reduce quality — you will be told if the target
          cannot be reached.
        </p>
      </div>

      <div class="field">
        <div class="rng-head">
          <label class="f-label" for="r-quality" style="margin:0">Image Quality</label>
          <span class="rng-val" id="r-quality-val">Quality: 85%</span>
        </div>
        <input type="range" id="r-quality" min="10" max="100" value="85">
        <p class="f-help" id="r-quality-help">Applies to JPG/JPEG. When a target file size is set, quality is adjusted automatically to reach it.</p>
      </div>

      <div class="field">
        <span class="f-label">Download Format</span>
        <div class="seg" id="r-format">
          <button type="button" data-v="jpg" aria-pressed="true">JPG</button>
          <button type="button" data-v="jpeg" aria-pressed="false">JPEG</button>
          <button type="button" data-v="png" aria-pressed="false">PNG</button>
        </div>
        <p class="f-help" id="r-format-help"></p>
      </div>

      <div class="btn-row" style="margin-top:6px">
        <button type="button" class="btn btn-p btn-lg" id="r-run">Resize Image</button>
      </div>
      <div class="btn-row" style="margin-top:9px">
        <button type="button" class="btn btn-d" id="r-reset">Reset</button>
      </div>

      <div id="r-busy" class="busy hidden"><span class="spin"></span> Processing your image…</div>
      <div id="r-msg"></div>

      <div class="result hidden" id="r-result">
        <h4>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
          Output ready
        </h4>
        <div class="meta" id="r-result-meta"></div>
        <div class="btn-row" style="margin-top:13px">
          <button type="button" class="btn btn-p" id="r-dl">Download Image</button>
          <button type="button" class="btn" id="r-share">Share</button>
          <button type="button" class="btn" id="r-copy">Copy Image</button>
        </div>
        <div class="btn-row" style="margin-top:9px">
          <button type="button" class="btn btn-s" data-dl="jpg">Download JPG</button>
          <button type="button" class="btn btn-s" data-dl="jpeg">Download JPEG</button>
          <button type="button" class="btn btn-s" data-dl="png">Download PNG</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ======================================================================= -->
<!--  PANEL 2 : SIGNATURE SCANNER                                            -->
<!-- ======================================================================= -->
<section class="panel" id="panel-signature" role="tabpanel" aria-labelledby="tab-signature">

  <div class="card">
    <div class="card-h"><span class="n">1</span><h3>Upload Your Signature</h3>
      <p class="sub">Sign on plain white paper, photograph or scan it, then upload here.</p>
    </div>
    <div class="card-b">
      <div class="drop" id="s-drop" tabindex="0" role="button" aria-label="Upload your signature">
        <span class="di" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 17c3.5 0 3.5-9 7-9s3.5 9 7 9c1.7 0 2.7-1 3.5-2"/><path d="M3 21h18"/>
          </svg>
        </span>
        <b>Drag &amp; Drop your signature here</b>
        <span class="dsub">Tap or click to browse your device</span>
        <div class="or">or</div>
        <span class="btn btn-p">Choose Signature</span>
        <div class="fmts">JPG &middot; JPEG &middot; PNG &middot; WEBP &nbsp;|&nbsp; Maximum recommended file size: <?= (int) $MAX_MB ?> MB</div>
      </div>
      <input type="file" id="s-file" class="sr" accept="image/jpeg,image/png,image/webp">
      <div id="s-src-meta" class="meta hidden"></div>
      <div id="s-upload-note"></div>
    </div>
  </div>

  <div class="card hidden" id="s-work">
    <div class="card-h"><span class="n">2</span><h3>Preview</h3></div>
    <div class="card-b">
      <div class="previews">
        <div class="pv">
          <div class="pv-h"><span class="dot"></span> Original Signature</div>
          <div class="pv-stage" id="s-pv-src"></div>
          <div class="pv-f" id="s-pv-src-f">&nbsp;</div>
        </div>
        <div class="pv pv-out">
          <div class="pv-h"><span class="dot"></span> Clean Signature</div>
          <div class="pv-stage" id="s-pv-out"><div class="pv-empty">Adjust the settings below</div></div>
          <div class="pv-f" id="s-pv-out-f">&nbsp;</div>
        </div>
      </div>
      <div class="note note-i">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <span>A checkerboard pattern behind the result means those areas are <b>transparent</b> (PNG only).</span>
      </div>
    </div>
  </div>

  <!-- CLEANING -->
  <div class="card hidden" id="s-clean-card">
    <div class="card-h"><span class="n">3</span><h3>Clean Signature</h3></div>
    <div class="card-b">

      <div class="field">
        <span class="f-label">Clean Signature Background</span>
        <div class="seg" id="s-mode">
          <button type="button" data-v="original" aria-pressed="false">Original</button>
          <button type="button" data-v="white" aria-pressed="false">White Background</button>
          <button type="button" data-v="scan" aria-pressed="true">Machine Scan</button>
        </div>
        <p class="f-help" id="s-mode-help"></p>
      </div>

      <div class="field" id="s-thr-wrap">
        <div class="rng-head">
          <label class="f-label" for="s-thr" style="margin:0">Signature Detection</label>
          <span class="rng-val" id="s-thr-val">Balanced</span>
        </div>
        <input type="range" id="s-thr" min="0" max="100" value="50">
        <p class="f-help">Low picks up only the darkest strokes. High picks up lighter strokes but may keep more background. Adjust until your signature looks complete and the paper looks clean.</p>
      </div>

      <label class="tgl field" id="s-noise-wrap">
        <input type="checkbox" id="s-noise" checked>
        <span class="track"></span>
        <span><span class="tl">Remove Shadows / Noise</span><br><span class="td">Deletes small specks and scanner dust, keeps the signature</span></span>
      </label>

      <label class="tgl field" id="s-rembg-wrap">
        <input type="checkbox" id="s-rembg">
        <span class="track"></span>
        <span><span class="tl">Remove Background (transparent)</span><br><span class="td">PNG only — JPG cannot store transparency and will use white</span></span>
      </label>

      <label class="tgl field" id="s-white-wrap">
        <input type="checkbox" id="s-white" checked>
        <span class="track"></span>
        <span><span class="tl">Pure White Background</span><br><span class="td">Forces the paper to exact RGB 255,255,255</span></span>
      </label>
    </div>
  </div>

  <!-- SIZE -->
  <div class="card hidden" id="s-dims-card">
    <div class="card-h"><span class="n">4</span><h3>Size, Quality &amp; Format</h3></div>
    <div class="card-b">
      <div class="grid2 field">
        <div>
          <label class="f-label" for="s-w">Width</label>
          <div class="suffix"><input class="inp" type="number" id="s-w" min="1" max="12000" inputmode="numeric"><i>px</i></div>
        </div>
        <div>
          <label class="f-label" for="s-h">Height</label>
          <div class="suffix"><input class="inp" type="number" id="s-h" min="1" max="12000" inputmode="numeric"><i>px</i></div>
        </div>
      </div>

      <label class="tgl field">
        <input type="checkbox" id="s-lock" checked>
        <span class="track"></span>
        <span><span class="tl">Lock Aspect Ratio</span></span>
      </label>

      <div class="field">
        <span class="f-label">Size Presets</span>
        <div class="chips" id="s-presets">
          <?php foreach ($SIGN_PRESETS as $p): ?>
            <button type="button" class="chip" data-w="<?= (int) $p[0] ?>" data-h="<?= (int) $p[1] ?>" aria-pressed="false"><?= (int) $p[0] ?> &times; <?= (int) $p[1] ?></button>
          <?php endforeach; ?>
          <button type="button" class="chip" data-custom="1" aria-pressed="true">Custom</button>
        </div>
      </div>

      <div class="field">
        <label class="f-label" for="s-target">Target File Size</label>
        <select class="inp" id="s-target">
          <option value="0" selected>No limit — use the quality slider</option>
          <?php foreach ($SIZE_PRESETS as $kb): ?>
            <option value="<?= (int) $kb ?>"><?= $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.') . ' MB' : $kb . ' KB' ?></option>
          <?php endforeach; ?>
          <option value="-1">Custom…</option>
        </select>
        <div id="s-target-slider" class="hidden" style="margin-top:12px">
          <div class="rng-head">
            <span class="f-label" style="margin:0">Target Size</span>
            <span class="rng-val" id="s-target-val">50 KB</span>
          </div>
          <input type="range" id="s-target-range" min="10" max="2048" step="5" value="50">
          <p class="f-help">Range: 10 KB &rarr; 2 MB</p>
        </div>
      </div>

      <div class="field">
        <div class="rng-head">
          <label class="f-label" for="s-quality" style="margin:0">Signature Quality</label>
          <span class="rng-val" id="s-quality-val">Quality: 90%</span>
        </div>
        <input type="range" id="s-quality" min="10" max="100" value="90">
      </div>

      <div class="field">
        <span class="f-label">Signature Format</span>
        <div class="seg" id="s-format">
          <button type="button" data-v="jpg" aria-pressed="true">JPG</button>
          <button type="button" data-v="jpeg" aria-pressed="false">JPEG</button>
          <button type="button" data-v="png" aria-pressed="false">PNG</button>
        </div>
        <p class="f-help" id="s-format-help"></p>
      </div>

      <div class="btn-row" style="margin-top:6px">
        <button type="button" class="btn btn-p btn-lg" id="s-run">Scan &amp; Prepare Signature</button>
      </div>
      <div class="btn-row" style="margin-top:9px">
        <button type="button" class="btn btn-d" id="s-reset">Clear Signature</button>
      </div>

      <div id="s-busy" class="busy hidden"><span class="spin"></span> Scanning your signature…</div>
      <div id="s-msg"></div>

      <div class="result hidden" id="s-result">
        <h4>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
          Clean Signature ready
        </h4>
        <div class="meta" id="s-result-meta"></div>
        <div class="btn-row" style="margin-top:13px">
          <button type="button" class="btn btn-p" id="s-dl">Download Signature</button>
          <button type="button" class="btn" id="s-share">Share Signature</button>
          <button type="button" class="btn" id="s-copy">Copy Image</button>
        </div>
        <div class="btn-row" style="margin-top:9px">
          <button type="button" class="btn btn-s" data-sdl="jpg">Download JPG</button>
          <button type="button" class="btn btn-s" data-sdl="jpeg">Download JPEG</button>
          <button type="button" class="btn btn-s" data-sdl="png">Download PNG</button>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<footer class="site-foot">
  <div class="wrap">
    &copy; <?= e($YEAR) ?> <?= e($SITE_NAME) ?> Image Tools — all processing happens in your browser.<br>
    <code><?= e($gdSummary) ?></code> &nbsp;·&nbsp; server-side image processing is not used by this tool.
  </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
/* ===========================================================================
   NaukriPatra Image Tools — all logic, vanilla JS, no libraries.

   Everything runs on <canvas> in the visitor's browser. No network requests
   are made at any point (the CSP above blocks them anyway).
   =========================================================================== */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  var MAX_BYTES = <?= (int) $MAX_MB ?> * 1024 * 1024;
  var OK_TYPES  = ['image/jpeg', 'image/png', 'image/webp'];
  var MAX_DIM   = 12000;
  // Above this the live preview is skipped: re-composing e.g. 9000x9000 on
  // every keystroke locks the page up for many seconds.
  var PREVIEW_MAX_PX = 4000000;
  // iOS Safari refuses to produce a bitmap beyond roughly 16.7 million pixels,
  // so anything past this may fail on a phone even though desktop copes.
  var SAFE_MAX_PX = 16000000;

  /* ------------------------------------------------------------------ misc */
  function toast(msg, kind) {
    var t = $('#toast');
    t.textContent = msg;
    t.className = 'toast show' + (kind ? ' ' + kind : '');
    clearTimeout(toast._t);
    toast._t = setTimeout(function () { t.className = 'toast'; }, 3200);
  }
  function fmtBytes(b) {
    if (b == null) { return '—'; }
    if (b < 1024) { return b + ' B'; }
    if (b < 1024 * 1024) { return (b / 1024).toFixed(b < 10240 ? 1 : 0) + ' KB'; }
    return (b / 1048576).toFixed(2) + ' MB';
  }
  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function debounce(fn, ms) {
    var t; return function () { var a = arguments, c = this;
      clearTimeout(t); t = setTimeout(function () { fn.apply(c, a); }, ms); };
  }
  function note(el, kind, html) {
    if (!html) { el.innerHTML = ''; return; }
    var icons = {
      i: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
      w: '<path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L14.7 3.9a2 2 0 00-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
      e: '<circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>',
      o: '<path d="M20 6L9 17l-5-5"/>'
    };
    el.innerHTML = '<div class="note note-' + kind + '">' +
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
      icons[kind] + '</svg><span>' + html + '</span></div>';
  }
  /** Toggle a group of aria-pressed buttons and return the chosen value. */
  function segBind(root, onPick) {
    root.addEventListener('click', function (ev) {
      var b = ev.target.closest('button[data-v]');
      if (!b) { return; }
      $$('button[data-v]', root).forEach(function (x) {
        x.setAttribute('aria-pressed', String(x === b));
      });
      onPick(b.getAttribute('data-v'));
    });
  }
  function segValue(root) {
    var b = $('button[aria-pressed="true"]', root);
    return b ? b.getAttribute('data-v') : null;
  }

  /* ------------------------------------------------------- file validation */
  /**
   * Detect the real format from the file's magic bytes.
   *
   * file.type is deliberately NOT trusted. It is unreliable in both
   * directions: Android file managers, Downloads folders and chat apps often
   * hand over a perfectly good JPEG labelled "application/octet-stream" or the
   * non-standard "image/jpg" (which would wrongly reject a real photo), while
   * a renamed script can simply claim "image/jpeg". The bytes cannot lie.
   */
  function sniffType(file) {
    return new Promise(function (resolve) {
      var slice = file.slice(0, 16);
      function read(buf) {
        var b = new Uint8Array(buf);
        if (b.length >= 3 && b[0] === 0xFF && b[1] === 0xD8 && b[2] === 0xFF) {
          resolve('image/jpeg'); return;
        }
        if (b.length >= 8 && b[0] === 0x89 && b[1] === 0x50 && b[2] === 0x4E && b[3] === 0x47 &&
            b[4] === 0x0D && b[5] === 0x0A && b[6] === 0x1A && b[7] === 0x0A) {
          resolve('image/png'); return;
        }
        if (b.length >= 12 && b[0] === 0x52 && b[1] === 0x49 && b[2] === 0x46 && b[3] === 0x46 &&
            b[8] === 0x57 && b[9] === 0x45 && b[10] === 0x42 && b[11] === 0x50) {
          resolve('image/webp'); return;
        }
        resolve(null);
      }
      if (slice.arrayBuffer) {
        slice.arrayBuffer().then(read).catch(function () { resolve(null); });
      } else {
        var fr = new FileReader();
        fr.onload = function () { read(fr.result); };
        fr.onerror = function () { resolve(null); };
        fr.readAsArrayBuffer(slice);
      }
    });
  }

  /**
   * Validates by signature and then by decoding — never by file name or by the
   * type the browser reports. Resolves with {img, kind}.
   */
  function loadImageFile(file) {
    return new Promise(function (resolve, reject) {
      if (!file) { reject(new Error('No file selected.')); return; }
      if (file.size === 0) { reject(new Error('That file is empty.')); return; }
      if (file.size > MAX_BYTES) {
        reject(new Error('That file is ' + fmtBytes(file.size) + '. Please use an image under <?= (int) $MAX_MB ?> MB.'));
        return;
      }

      sniffType(file).then(function (kind) {
        if (OK_TYPES.indexOf(kind) === -1) {
          reject(new Error('Only JPG, JPEG, PNG and WEBP images are supported. That file does not look like one of them.'));
          return;
        }

        // createImageBitmap honours EXIF rotation, which matters for phone photos.
        if (window.createImageBitmap) {
          createImageBitmap(file, { imageOrientation: 'from-image' })
            .then(function (bmp) {
              if (!bmp.width || !bmp.height) { throw new Error('bad'); }
              resolve({ img: bmp, kind: kind });
            })
            .catch(function () { fallback(kind); });
        } else {
          fallback(kind);
        }
      });

      function fallback(kind) {
        var url = URL.createObjectURL(file);
        var img = new Image();
        img.onload = function () {
          URL.revokeObjectURL(url);
          if (!img.naturalWidth) { reject(new Error('That file is not a valid image.')); return; }
          resolve({ img: img, kind: kind });
        };
        img.onerror = function () {
          URL.revokeObjectURL(url);
          reject(new Error('That file could not be read as an image.'));
        };
        img.src = url;
      }
    });
  }

  /* ------------------------------------------------------------- canvas ops */
  function newCanvas(w, h) {
    var c = document.createElement('canvas');
    c.width = Math.max(1, Math.round(w));
    c.height = Math.max(1, Math.round(h));
    return c;
  }
  /**
   * Draw with stepped halving. Browsers' one-shot downscale gets blocky below
   * ~50%, so large reductions are done in halves first.
   */
  function drawScaled(ctx, src, sx, sy, sw, sh, dx, dy, dw, dh) {
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    if (dw >= sw / 2 && dh >= sh / 2) {
      ctx.drawImage(src, sx, sy, sw, sh, dx, dy, dw, dh);
      return;
    }
    var cw = Math.max(1, Math.round(sw)), ch = Math.max(1, Math.round(sh));
    var tmp = newCanvas(cw, ch), tctx = tmp.getContext('2d');
    tctx.imageSmoothingEnabled = true; tctx.imageSmoothingQuality = 'high';
    tctx.drawImage(src, sx, sy, sw, sh, 0, 0, cw, ch);
    while (cw / 2 > dw && ch / 2 > dh) {
      var nw = Math.max(1, Math.floor(cw / 2)), nh = Math.max(1, Math.floor(ch / 2));
      var nx = newCanvas(nw, nh), nctx = nx.getContext('2d');
      nctx.imageSmoothingEnabled = true; nctx.imageSmoothingQuality = 'high';
      nctx.drawImage(tmp, 0, 0, cw, ch, 0, 0, nw, nh);
      tmp = nx; cw = nw; ch = nh;
    }
    ctx.drawImage(tmp, 0, 0, cw, ch, dx, dy, dw, dh);
  }
  function canvasToBlob(canvas, mime, quality) {
    return new Promise(function (resolve, reject) {
      canvas.toBlob(function (b) {
        if (b) { resolve(b); } else { reject(new Error('Could not encode the image.')); }
      }, mime, quality);
    });
  }
  /** Reduce colour depth — the only lever PNG gives us for file size. */
  function posterize(canvas, levels) {
    var ctx = canvas.getContext('2d');
    var d = ctx.getImageData(0, 0, canvas.width, canvas.height);
    var p = d.data, step = 255 / (levels - 1), i;
    for (i = 0; i < p.length; i += 4) {
      p[i]     = Math.round(Math.round(p[i] / step) * step);
      p[i + 1] = Math.round(Math.round(p[i + 1] / step) * step);
      p[i + 2] = Math.round(Math.round(p[i + 2] / step) * step);
    }
    ctx.putImageData(d, 0, 0);
  }

  /**
   * Encode towards a target byte size.
   *  - JPEG: binary search on the quality parameter (8 probes is plenty).
   *  - PNG : lossless, so quality is ignored by browsers. We try progressively
   *          coarser colour quantisation instead, and say so honestly if the
   *          target is still out of reach.
   * Returns {blob, quality, reached, note}.
   */
  function encodeTarget(canvas, mime, targetBytes, fallbackQuality) {
    if (!targetBytes) {
      return canvasToBlob(canvas, mime, fallbackQuality).then(function (b) {
        // fallbackQuality is a 0-1 fraction; callers display a percentage.
        return {
          blob: b,
          quality: mime === 'image/png' ? null : Math.round(fallbackQuality * 100),
          reached: true, note: ''
        };
      });
    }

    if (mime === 'image/png') {
      var tries = [0, 64, 32, 16, 8, 4];
      var idx = 0, best = null;
      var step = function () {
        var work = newCanvas(canvas.width, canvas.height);
        work.getContext('2d').drawImage(canvas, 0, 0);
        if (tries[idx] > 0) { posterize(work, tries[idx]); }
        return canvasToBlob(work, 'image/png').then(function (b) {
          if (!best || b.size < best.size) { best = b; }
          if (b.size <= targetBytes) {
            return { blob: b, quality: null, reached: true,
              note: tries[idx] > 0 ? 'PNG is lossless, so colours were reduced to ' + tries[idx] + ' levels to reach the target size.' : '' };
          }
          idx++;
          if (idx < tries.length) { return step(); }
          return { blob: best, quality: null, reached: false,
            note: 'PNG is a lossless format and cannot be compressed to an arbitrary size. Smallest achievable here is ' +
                  fmtBytes(best.size) + '. Choose JPG, or reduce the width and height, to go smaller.' };
        });
      };
      return step();
    }

    // JPEG binary search: keep the highest quality that still fits.
    var lo = 0.05, hi = 1.0, best = null, bestQ = null, smallest = null;
    var i = 0;
    var probe = function () {
      var q = (lo + hi) / 2;
      return canvasToBlob(canvas, mime, q).then(function (b) {
        if (!smallest || b.size < smallest.size) { smallest = b; }
        if (b.size <= targetBytes) { best = b; bestQ = q; lo = q; } else { hi = q; }
        i++;
        if (i < 8) { return probe(); }
        if (best) {
          return { blob: best, quality: Math.round(bestQ * 100), reached: true, note: '' };
        }
        return canvasToBlob(canvas, mime, 0.05).then(function (min) {
          return { blob: min, quality: 5, reached: false,
            note: 'To reach this file size, image quality may need to be reduced further than is usable. ' +
                  'The smallest valid result at these dimensions is ' + fmtBytes(min.size) +
                  '. Reduce the width and height to go smaller.' };
        });
      });
    };
    return probe();
  }

  function mimeFor(fmt) { return fmt === 'png' ? 'image/png' : 'image/jpeg'; }
  function download(blob, filename) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url; a.download = filename;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(url); }, 1500);
  }

  /* ------------------------------------------------ share / clipboard */
  function canShareFiles() {
    return !!(navigator.canShare && navigator.share);
  }
  function shareBlob(blob, filename, title) {
    var file = new File([blob], filename, { type: blob.type });
    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      return navigator.share({ files: [file], title: title })
        .then(function () { return true; })
        .catch(function (err) {
          if (err && err.name === 'AbortError') { return true; }
          return false;
        });
    }
    return Promise.resolve(false);
  }
  function clipboardSupported() {
    return !!(navigator.clipboard && window.ClipboardItem && navigator.clipboard.write);
  }
  /** Clipboard image support is effectively PNG-only, so convert first. */
  function copyBlob(canvas) {
    if (!clipboardSupported()) { return Promise.resolve(false); }
    return canvasToBlob(canvas, 'image/png').then(function (png) {
      var item = new window.ClipboardItem({ 'image/png': png });
      return navigator.clipboard.write([item]).then(function () { return true; })
        .catch(function () { return false; });
    });
  }

  /* =======================================================================
     TAB SWITCHING
     ======================================================================= */
  $$('.tool-card').forEach(function (card) {
    card.addEventListener('click', function () {
      var tool = card.getAttribute('data-tool');
      $$('.tool-card').forEach(function (c) {
        c.setAttribute('aria-selected', String(c === card));
      });
      $('#panel-resizer').classList.toggle('active', tool === 'resizer');
      $('#panel-signature').classList.toggle('active', tool === 'signature');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  /* =======================================================================
     SHARED DROPZONE WIRING
     ======================================================================= */
  function bindDrop(dropEl, inputEl, onFile) {
    dropEl.addEventListener('click', function () { inputEl.click(); });
    dropEl.addEventListener('keydown', function (ev) {
      if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); inputEl.click(); }
    });
    ['dragenter', 'dragover'].forEach(function (t) {
      dropEl.addEventListener(t, function (ev) { ev.preventDefault(); dropEl.classList.add('over'); });
    });
    ['dragleave', 'drop'].forEach(function (t) {
      dropEl.addEventListener(t, function (ev) { ev.preventDefault(); dropEl.classList.remove('over'); });
    });
    dropEl.addEventListener('drop', function (ev) {
      var f = ev.dataTransfer && ev.dataTransfer.files && ev.dataTransfer.files[0];
      if (f) { onFile(f); }
    });
    inputEl.addEventListener('change', function () {
      var f = this.files && this.files[0];
      if (f) { onFile(f); }
      this.value = '';
    });
  }

  /* =======================================================================
     TOOL 1 — IMAGE RESIZER
     ======================================================================= */
  var R = {
    img: null, name: '', type: '', size: 0, w: 0, h: 0,
    outBlob: null, outCanvas: null, outW: 0, outH: 0, fmt: 'jpg'
  };

  var rMethodHelp = {
    fit: 'Fit — the whole image is kept and scaled to sit inside your width and height. Any leftover space uses the background colour.',
    fill: 'Fill — the image covers the full width and height, and the overflowing edges are cropped off. Nothing is squashed.',
    stretch: 'Stretch — the image is forced to exactly your width and height. Fastest way to hit an exact size, but it can distort the picture.'
  };
  var rFormatHelp = {
    jpg: 'JPG — smallest files, best for photos. No transparency: transparent areas become the background colour.',
    jpeg: 'JPEG — identical encoding to JPG; only the file extension differs. Some forms specifically ask for .jpeg.',
    png: 'PNG — lossless and keeps transparency. Files are larger and the quality slider does not apply.'
  };

  function rSetHelp() {
    $('#r-method-help').textContent = rMethodHelp[segValue($('#r-method'))] || '';
    $('#r-format-help').textContent = rFormatHelp[segValue($('#r-format'))] || '';
    var isPng = segValue($('#r-format')) === 'png';
    $('#r-quality').disabled = isPng;
    $('#r-quality-help').textContent = isPng
      ? 'PNG is lossless, so the quality slider does not apply. Use JPG if you need a specific file size.'
      : 'Applies to JPG/JPEG. When a target file size is set, quality is adjusted automatically to reach it.';
  }

  function rShowCards(on) {
    ['#r-work', '#r-dims-card', '#r-out-card'].forEach(function (s) {
      $(s).classList.toggle('hidden', !on);
    });
  }

  function rLoad(file) {
    note($('#r-upload-note'), '', '');
    loadImageFile(file).then(function (res) {
      var img = res.img;
      R.img = img; R.name = file.name; R.type = res.kind; R.size = file.size;
      R.w = img.width; R.h = img.height;

      var meta = $('#r-src-meta');
      meta.classList.remove('hidden');
      meta.innerHTML =
        '<span>File: <b>' + esc(file.name) + '</b></span>' +
        '<span>Format: <b>' + esc(res.kind.replace('image/', '').toUpperCase()) + '</b></span>' +
        '<span>Size: <b>' + R.w + ' &times; ' + R.h + ' px</b></span>' +
        '<span>File size: <b>' + fmtBytes(file.size) + '</b></span>';

      var stage = $('#r-pv-src');
      stage.innerHTML = '';
      var c = newCanvas(R.w, R.h);
      c.getContext('2d').drawImage(img, 0, 0);
      c.style.maxHeight = '280px'; c.style.width = 'auto'; c.style.maxWidth = '100%';
      c.style.borderRadius = '6px';
      stage.appendChild(c);
      $('#r-pv-src-f').innerHTML = '<b>' + R.w + ' &times; ' + R.h + ' px</b> · ' + fmtBytes(file.size) +
        ' · ' + esc(res.kind.replace('image/', '').toUpperCase());

      $('#r-w').value = R.w;
      $('#r-h').value = R.h;
      rShowCards(true);
      $('#r-result').classList.add('hidden');
      note($('#r-msg'), '', '');
      rPreview();
      toast('Image loaded — it stays on your device.', 'ok');
    }).catch(function (err) {
      note($('#r-upload-note'), 'e', esc(err.message));
      toast(err.message, 'err');
    });
  }

  bindDrop($('#r-drop'), $('#r-file'), rLoad);

  /* ---- dimension logic ---- */
  function rClamp(v) {
    v = parseInt(v, 10);
    if (!isFinite(v) || v < 1) { return 1; }
    return Math.min(MAX_DIM, v);
  }
  var rSyncing = false;
  $('#r-w').addEventListener('input', function () {
    if (rSyncing || !R.img) { return; }
    if ($('#r-lock').checked) {
      rSyncing = true;
      var w = rClamp(this.value);
      $('#r-h').value = Math.max(1, Math.round(w * R.h / R.w));
      rSyncing = false;
    }
    rMarkCustom($('#r-presets'));
    rPreview();
  });
  $('#r-h').addEventListener('input', function () {
    if (rSyncing || !R.img) { return; }
    if ($('#r-lock').checked) {
      rSyncing = true;
      var h = rClamp(this.value);
      $('#r-w').value = Math.max(1, Math.round(h * R.w / R.h));
      rSyncing = false;
    }
    rMarkCustom($('#r-presets'));
    rPreview();
  });
  $('#r-lock').addEventListener('change', function () {
    if (this.checked && R.img) {
      $('#r-h').value = Math.max(1, Math.round(rClamp($('#r-w').value) * R.h / R.w));
      rPreview();
    }
  });
  $('#r-orig-dims').addEventListener('click', function () {
    if (!R.img) { return; }
    $('#r-w').value = R.w; $('#r-h').value = R.h;
    rMarkCustom($('#r-presets'));
    rPreview();
  });
  function rMarkCustom(root) {
    $$('.chip', root).forEach(function (c) {
      c.setAttribute('aria-pressed', String(!!c.getAttribute('data-custom')));
    });
  }
  $('#r-presets').addEventListener('click', function (ev) {
    var c = ev.target.closest('.chip');
    if (!c) { return; }
    $$('.chip', this).forEach(function (x) { x.setAttribute('aria-pressed', String(x === c)); });
    if (c.getAttribute('data-custom')) { return; }
    $('#r-w').value = c.getAttribute('data-w');
    $('#r-h').value = c.getAttribute('data-h');
    rPreview();
  });

  segBind($('#r-method'), function () { rSetHelp(); rPreview(); });
  segBind($('#r-bg'), function () { rPreview(); });
  segBind($('#r-format'), function (v) { R.fmt = v; rSetHelp(); rPreview(); });
  $('#r-bg-color').addEventListener('input', function () {
    $$('#r-bg button[data-v]').forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.getAttribute('data-v') === 'custom'));
    });
    rPreview();
  });
  $('#r-quality').addEventListener('input', function () {
    $('#r-quality-val').textContent = 'Quality: ' + this.value + '%';
    rPreview();
  });
  $('#r-target').addEventListener('change', function () {
    $('#r-target-slider').classList.toggle('hidden', this.value !== '-1');
    rPreview();
  });
  $('#r-target-range').addEventListener('input', function () {
    var kb = parseInt(this.value, 10);
    $('#r-target-val').textContent = kb >= 1024 ? (kb / 1024).toFixed(kb % 1024 ? 1 : 0) + ' MB' : kb + ' KB';
    rPreview();
  });

  function rTargetBytes() {
    var sel = $('#r-target').value;
    if (sel === '0') { return 0; }
    var kb = sel === '-1' ? parseInt($('#r-target-range').value, 10) : parseInt(sel, 10);
    return kb * 1024;
  }
  function rBackground() {
    var v = segValue($('#r-bg'));
    if (v === 'white') { return '#ffffff'; }
    if (v === 'black') { return '#000000'; }
    if (v === 'custom') { return $('#r-bg-color').value; }
    return null;   // "Original" — keep transparency
  }

  /** Compose the resized image onto a canvas at the requested dimensions. */
  function rCompose() {
    var w = rClamp($('#r-w').value), h = rClamp($('#r-h').value);
    var method = segValue($('#r-method'));
    var fmt = segValue($('#r-format'));
    var bg = rBackground();
    // JPEG cannot store transparency, so it always needs a solid base.
    if (!bg && fmt !== 'png') { bg = '#ffffff'; }

    var c = newCanvas(w, h), ctx = c.getContext('2d');
    if (bg) { ctx.fillStyle = bg; ctx.fillRect(0, 0, w, h); }

    var iw = R.w, ih = R.h;
    if (method === 'stretch') {
      drawScaled(ctx, R.img, 0, 0, iw, ih, 0, 0, w, h);
    } else if (method === 'fill') {
      var s = Math.max(w / iw, h / ih);
      var sw = w / s, sh = h / s;
      drawScaled(ctx, R.img, (iw - sw) / 2, (ih - sh) / 2, sw, sh, 0, 0, w, h);
    } else {
      var f = Math.min(w / iw, h / ih);
      var dw = Math.max(1, Math.round(iw * f)), dh = Math.max(1, Math.round(ih * f));
      drawScaled(ctx, R.img, 0, 0, iw, ih, Math.round((w - dw) / 2), Math.round((h - dh) / 2), dw, dh);
    }
    return c;
  }

  var rPreview = debounce(function () {
    if (!R.img) { return; }
    var pw = rClamp($('#r-w').value), ph = rClamp($('#r-h').value);
    if (pw * ph > PREVIEW_MAX_PX) {
      $('#r-pv-out').classList.remove('checker');
      $('#r-pv-out').innerHTML = '<div class="pv-empty">Live preview is paused at this size ' +
        '(' + pw + ' &times; ' + ph + ' px).<br>Press <b>Resize Image</b> to generate it.</div>';
      $('#r-pv-out-f').innerHTML = '<b>' + pw + ' &times; ' + ph + ' px</b> · not previewed';
      return;
    }
    var c = rCompose();
    var fmt = segValue($('#r-format'));
    var q = parseInt($('#r-quality').value, 10) / 100;
    canvasToBlob(c, mimeFor(fmt), q).then(function (b) {
      rShowOut(c, b, fmt, false);
    }).catch(function () { /* preview only — ignore */ });
  }, 220);

  function rShowOut(canvas, blob, fmt, isFinal) {
    var stage = $('#r-pv-out');
    stage.innerHTML = '';
    stage.classList.toggle('checker', fmt === 'png' && segValue($('#r-bg')) === 'original');
    var el = newCanvas(canvas.width, canvas.height);
    el.getContext('2d').drawImage(canvas, 0, 0);
    el.style.maxHeight = '280px'; el.style.width = 'auto'; el.style.maxWidth = '100%';
    el.style.borderRadius = '6px';
    stage.appendChild(el);
    $('#r-pv-out-f').innerHTML = '<b>' + canvas.width + ' &times; ' + canvas.height + ' px</b> · ' +
      fmtBytes(blob.size) + ' · ' + fmt.toUpperCase() + (isFinal ? '' : ' <i>(live preview)</i>');
  }

  $('#r-run').addEventListener('click', function () {
    if (!R.img) { toast('Please upload an image first.', 'err'); return; }
    var busy = $('#r-busy'), msg = $('#r-msg');
    busy.classList.remove('hidden');
    note(msg, '', '');
    $('#r-result').classList.add('hidden');

    // Yield once so the spinner paints before the heavy work starts.
    setTimeout(function () {
      var canvas = rCompose();
      var fmt = segValue($('#r-format'));
      var q = parseInt($('#r-quality').value, 10) / 100;
      encodeTarget(canvas, mimeFor(fmt), rTargetBytes(), q).then(function (res) {
        busy.classList.add('hidden');
        R.outBlob = res.blob; R.outCanvas = canvas;
        R.outW = canvas.width; R.outH = canvas.height; R.fmt = fmt;

        rShowOut(canvas, res.blob, fmt, true);

        var pct = R.size ? Math.round((1 - res.blob.size / R.size) * 100) : 0;
        var meta = $('#r-result-meta');
        meta.innerHTML =
          '<span>Dimensions: <b>' + canvas.width + ' &times; ' + canvas.height + ' px</b></span>' +
          '<span>File size: <b>' + fmtBytes(res.blob.size) + '</b></span>' +
          '<span>Format: <b>' + fmt.toUpperCase() + '</b></span>' +
          (res.quality ? '<span>Quality used: <b>' + res.quality + '%</b></span>' : '') +
          '<span>' + (pct >= 0 ? 'Reduced by' : 'Increased by') + ': <b>' + Math.abs(pct) + '%</b></span>';
        $('#r-result').classList.remove('hidden');

        var big = canvas.width * canvas.height > SAFE_MAX_PX;
        if (res.note) { note(msg, 'w', esc(res.note)); }
        else if (big) {
          note(msg, 'w', 'This output is ' + canvas.width + ' &times; ' + canvas.height +
            ' px. Sizes this large can fail on iPhones and older Android phones, which cap ' +
            'how big an image the browser may build. Reduce the width and height if the ' +
            'result does not appear on a phone.');
        }
        else if (rTargetBytes()) { note(msg, 'o', 'Target file size reached.'); }

        $('#r-copy').classList.toggle('hidden', !clipboardSupported());
        $('#r-share').classList.toggle('hidden', !canShareFiles());
        toast('Image ready.', 'ok');
      }).catch(function (err) {
        busy.classList.add('hidden');
        note(msg, 'e', esc(err.message || 'Something went wrong while processing.'));
      });
    }, 40);
  });

  function rFilename(fmt) { return 'naukripatra-resized-image.' + fmt; }

  $('#r-dl').addEventListener('click', function () {
    if (!R.outBlob) { return; }
    download(R.outBlob, rFilename(R.fmt));
  });
  $$('[data-dl]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!R.outCanvas) { return; }
      var fmt = b.getAttribute('data-dl');
      var q = parseInt($('#r-quality').value, 10) / 100;
      encodeTarget(R.outCanvas, mimeFor(fmt), rTargetBytes(), q).then(function (res) {
        download(res.blob, rFilename(fmt));
        if (res.note) { note($('#r-msg'), 'w', esc(res.note)); }
      });
    });
  });
  $('#r-share').addEventListener('click', function () {
    if (!R.outBlob) { return; }
    shareBlob(R.outBlob, rFilename(R.fmt), 'Resized image').then(function (ok) {
      if (!ok) { toast('Sharing is not available here — use Download instead.', 'err'); }
    });
  });
  $('#r-copy').addEventListener('click', function () {
    if (!R.outCanvas) { return; }
    copyBlob(R.outCanvas).then(function (ok) {
      toast(ok ? 'Image copied to clipboard.' : 'Copying is not supported in this browser.', ok ? 'ok' : 'err');
    });
  });

  $('#r-reset').addEventListener('click', function () {
    R = { img: null, name: '', type: '', size: 0, w: 0, h: 0,
          outBlob: null, outCanvas: null, outW: 0, outH: 0, fmt: 'jpg' };
    $('#r-file').value = '';
    $('#r-src-meta').classList.add('hidden');
    $('#r-src-meta').innerHTML = '';
    $('#r-pv-src').innerHTML = '';
    $('#r-pv-out').innerHTML = '<div class="pv-empty">Adjust the settings below</div>';
    $('#r-pv-src-f').innerHTML = '&nbsp;'; $('#r-pv-out-f').innerHTML = '&nbsp;';
    $('#r-w').value = ''; $('#r-h').value = '';
    $('#r-lock').checked = true;
    $('#r-quality').value = 85; $('#r-quality-val').textContent = 'Quality: 85%';
    $('#r-target').value = '0'; $('#r-target-slider').classList.add('hidden');
    rMarkCustom($('#r-presets'));
    $$('#r-method button').forEach(function (b) { b.setAttribute('aria-pressed', String(b.getAttribute('data-v') === 'fit')); });
    $$('#r-bg button').forEach(function (b) { b.setAttribute('aria-pressed', String(b.getAttribute('data-v') === 'original')); });
    $$('#r-format button').forEach(function (b) { b.setAttribute('aria-pressed', String(b.getAttribute('data-v') === 'jpg')); });
    note($('#r-msg'), '', ''); note($('#r-upload-note'), '', '');
    $('#r-result').classList.add('hidden');
    rShowCards(false);
    rSetHelp();
    toast('Reset — you can upload another image.');
  });

  /* =======================================================================
     TOOL 2 — SIGNATURE SCANNER
     ======================================================================= */
  var S = {
    img: null, name: '', type: '', size: 0, w: 0, h: 0,
    outBlob: null, outCanvas: null, fmt: 'jpg', bgLabel: 'White Background'
  };

  var sModeHelp = {
    original: 'Original — no cleaning at all. The signature is only resized and re-encoded.',
    white: 'White Background — brightens the paper to pure white and lifts the contrast, while keeping the natural look of your pen strokes.',
    scan: 'Machine Scan — detects the ink itself and rebuilds the signature on a clean background, removing shadows, grey scanner tint and uneven lighting. Best for photos taken with a phone.'
  };
  var sFormatHelp = {
    jpg: 'JPG — accepted by almost every form. No transparency, so the background will be white.',
    jpeg: 'JPEG — identical to JPG, only the extension differs.',
    png: 'PNG — lossless, and the only format that can keep a transparent background.'
  };

  function sSetHelp() {
    $('#s-mode-help').textContent = sModeHelp[segValue($('#s-mode'))] || '';
    $('#s-format-help').textContent = sFormatHelp[segValue($('#s-format'))] || '';
    var mode = segValue($('#s-mode'));
    var isScan = mode === 'scan';
    var isOrig = mode === 'original';
    $('#s-thr-wrap').classList.toggle('hidden', !isScan);
    $('#s-noise-wrap').classList.toggle('hidden', !isScan);
    $('#s-rembg-wrap').classList.toggle('hidden', !isScan);
    $('#s-white-wrap').classList.toggle('hidden', isOrig);
    var png = segValue($('#s-format')) === 'png';
    $('#s-rembg').disabled = !png;
    $('#s-quality').disabled = png;
  }
  function sShowCards(on) {
    ['#s-work', '#s-clean-card', '#s-dims-card'].forEach(function (s) {
      $(s).classList.toggle('hidden', !on);
    });
  }

  function sLoad(file) {
    note($('#s-upload-note'), '', '');
    loadImageFile(file).then(function (res) {
      var img = res.img;
      S.img = img; S.name = file.name; S.type = res.kind; S.size = file.size;
      S.w = img.width; S.h = img.height;

      var meta = $('#s-src-meta');
      meta.classList.remove('hidden');
      meta.innerHTML =
        '<span>File: <b>' + esc(file.name) + '</b></span>' +
        '<span>Format: <b>' + esc(res.kind.replace('image/', '').toUpperCase()) + '</b></span>' +
        '<span>Size: <b>' + S.w + ' &times; ' + S.h + ' px</b></span>' +
        '<span>File size: <b>' + fmtBytes(file.size) + '</b></span>';

      var stage = $('#s-pv-src'); stage.innerHTML = '';
      var c = newCanvas(S.w, S.h);
      c.getContext('2d').drawImage(img, 0, 0);
      c.style.maxHeight = '280px'; c.style.width = 'auto'; c.style.maxWidth = '100%';
      c.style.borderRadius = '6px';
      stage.appendChild(c);
      $('#s-pv-src-f').innerHTML = '<b>' + S.w + ' &times; ' + S.h + ' px</b> · ' + fmtBytes(file.size) +
        ' · ' + esc(res.kind.replace('image/', '').toUpperCase());

      // Default to a typical signature box, keeping the aspect ratio.
      var dw = Math.min(300, S.w);
      $('#s-w').value = Math.round(dw);
      $('#s-h').value = Math.max(1, Math.round(dw * S.h / S.w));

      sShowCards(true);
      $('#s-result').classList.add('hidden');
      note($('#s-msg'), '', '');
      sPreview();
      toast('Signature loaded — it stays on your device.', 'ok');
    }).catch(function (err) {
      note($('#s-upload-note'), 'e', esc(err.message));
      toast(err.message, 'err');
    });
  }
  bindDrop($('#s-drop'), $('#s-file'), sLoad);

  var sSyncing = false;
  $('#s-w').addEventListener('input', function () {
    if (sSyncing || !S.img) { return; }
    if ($('#s-lock').checked) {
      sSyncing = true;
      $('#s-h').value = Math.max(1, Math.round(rClamp(this.value) * S.h / S.w));
      sSyncing = false;
    }
    rMarkCustom($('#s-presets'));
    sPreview();
  });
  $('#s-h').addEventListener('input', function () {
    if (sSyncing || !S.img) { return; }
    if ($('#s-lock').checked) {
      sSyncing = true;
      $('#s-w').value = Math.max(1, Math.round(rClamp(this.value) * S.w / S.h));
      sSyncing = false;
    }
    rMarkCustom($('#s-presets'));
    sPreview();
  });
  $('#s-presets').addEventListener('click', function (ev) {
    var c = ev.target.closest('.chip');
    if (!c) { return; }
    $$('.chip', this).forEach(function (x) { x.setAttribute('aria-pressed', String(x === c)); });
    if (c.getAttribute('data-custom')) { return; }
    $('#s-w').value = c.getAttribute('data-w');
    $('#s-h').value = c.getAttribute('data-h');
    sPreview();
  });

  segBind($('#s-mode'), function () { sSetHelp(); sPreview(); });
  segBind($('#s-format'), function () { sSetHelp(); sPreview(); });
  // Wrapped, not passed directly: sPreview is a `var` defined further down, so
  // binding it by value here would attach `undefined` and the toggles would
  // silently do nothing.
  ['#s-noise', '#s-rembg', '#s-white', '#s-lock'].forEach(function (sel) {
    $(sel).addEventListener('change', function () { sPreview(); });
  });
  $('#s-thr').addEventListener('input', function () {
    var v = parseInt(this.value, 10);
    $('#s-thr-val').textContent = v < 25 ? 'Low' : (v < 45 ? 'Light' : (v <= 60 ? 'Balanced' : (v < 80 ? 'Strong' : 'High')));
    sPreview();
  });
  $('#s-quality').addEventListener('input', function () {
    $('#s-quality-val').textContent = 'Quality: ' + this.value + '%';
    sPreview();
  });
  $('#s-target').addEventListener('change', function () {
    $('#s-target-slider').classList.toggle('hidden', this.value !== '-1');
    sPreview();
  });
  $('#s-target-range').addEventListener('input', function () {
    var kb = parseInt(this.value, 10);
    $('#s-target-val').textContent = kb >= 1024 ? (kb / 1024).toFixed(kb % 1024 ? 1 : 0) + ' MB' : kb + ' KB';
    sPreview();
  });
  function sTargetBytes() {
    var sel = $('#s-target').value;
    if (sel === '0') { return 0; }
    var kb = sel === '-1' ? parseInt($('#s-target-range').value, 10) : parseInt(sel, 10);
    return kb * 1024;
  }

  /* ------------------------------------------------------------------------
     Signature cleaning.

     Uses an adaptive (local-mean) threshold rather than one global cut-off,
     because photos of signatures almost always have uneven lighting — one
     global value either eats the faint strokes or keeps half the shadow.

     A summed-area table gives the local mean in O(1) per pixel, so this stays
     fast even on a full-resolution phone photo.
     ------------------------------------------------------------------------ */
  var WORK_MAX = 1600;   // cap the processing resolution for speed

  function sWorkCanvas() {
    var scale = Math.min(1, WORK_MAX / Math.max(S.w, S.h));
    var w = Math.max(1, Math.round(S.w * scale)), h = Math.max(1, Math.round(S.h * scale));
    var c = newCanvas(w, h), ctx = c.getContext('2d');
    ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, w, h);
    drawScaled(ctx, S.img, 0, 0, S.w, S.h, 0, 0, w, h);
    return c;
  }

  /** Remove connected blobs smaller than minArea from an alpha mask. */
  function despeckle(alpha, w, h, minArea) {
    var seen = new Uint8Array(w * h);
    var stack = new Int32Array(w * h);
    var i;
    for (i = 0; i < w * h; i++) {
      if (seen[i] || alpha[i] < 128) { continue; }
      var sp = 0, n = 0;
      stack[sp++] = i; seen[i] = 1;
      var members = [];
      while (sp > 0) {
        var p = stack[--sp];
        // Only small blobs get erased, so stop recording once we know this one
        // is a keeper. Without this a mostly-dark image would build a members
        // array holding every pixel.
        if (members.length < minArea) { members.push(p); }
        n++;
        var px = p % w, py = (p / w) | 0;
        for (var dy = -1; dy <= 1; dy++) {
          for (var dx = -1; dx <= 1; dx++) {
            if (!dx && !dy) { continue; }
            var nx = px + dx, ny = py + dy;
            if (nx < 0 || ny < 0 || nx >= w || ny >= h) { continue; }
            var q = ny * w + nx;
            if (seen[q] || alpha[q] < 128) { continue; }
            seen[q] = 1; stack[sp++] = q;
          }
        }
      }
      if (n < minArea) {
        for (var k = 0; k < members.length; k++) { alpha[members[k]] = 0; }
      }
    }
    return alpha;
  }

  /**
   * Produce the cleaned signature on its own canvas.
   * transparent=true leaves the paper fully transparent (PNG only).
   */
  function sClean(work, mode, detect, denoise, transparent) {
    var w = work.width, h = work.height;
    var ctx = work.getContext('2d');
    var img = ctx.getImageData(0, 0, w, h);
    var p = img.data;
    var n = w * h;
    var i, j;

    // 1. luminance
    var lum = new Float32Array(n);
    for (i = 0, j = 0; i < n; i++, j += 4) {
      lum[i] = 0.299 * p[j] + 0.587 * p[j + 1] + 0.114 * p[j + 2];
    }

    if (mode === 'original') { return work; }

    // 2. summed-area table for fast local means
    var sat = new Float64Array((w + 1) * (h + 1));
    for (var y = 0; y < h; y++) {
      var rowSum = 0;
      for (var x = 0; x < w; x++) {
        rowSum += lum[y * w + x];
        sat[(y + 1) * (w + 1) + (x + 1)] = sat[y * (w + 1) + (x + 1)] + rowSum;
      }
    }
    var rad = Math.max(8, Math.round(Math.min(w, h) / 12));
    function localMean(x, y) {
      var x0 = Math.max(0, x - rad), y0 = Math.max(0, y - rad);
      var x1 = Math.min(w - 1, x + rad), y1 = Math.min(h - 1, y + rad);
      var area = (x1 - x0 + 1) * (y1 - y0 + 1);
      var s = sat[(y1 + 1) * (w + 1) + (x1 + 1)]
            - sat[y0 * (w + 1) + (x1 + 1)]
            - sat[(y1 + 1) * (w + 1) + x0]
            + sat[y0 * (w + 1) + x0];
      return s / area;
    }

    // detect 0..100 -> k 0.32..0.02 (higher detection = lower cut-off = more ink)
    var k = 0.32 - (detect / 100) * 0.30;
    var soft = 14;   // grey levels of anti-aliasing at stroke edges

    var alpha = new Uint8ClampedArray(n);
    var ink = new Float32Array(n);
    for (var yy = 0; yy < h; yy++) {
      for (var xx = 0; xx < w; xx++) {
        var idx = yy * w + xx;
        var m = localMean(xx, yy);
        var thr = m * (1 - k);
        var a = (thr - lum[idx]) / soft;
        a = a < 0 ? 0 : (a > 1 ? 1 : a);
        alpha[idx] = Math.round(a * 255);
        // keep some pen-pressure variation instead of flat black
        var dark = thr > 0 ? lum[idx] / thr : 1;
        ink[idx] = Math.max(0, Math.min(90, dark * 70));
      }
    }

    if (denoise) {
      // ~0.004% of the image. Comfortably below a dot on an "i" or a full
      // stop, so real marks survive while dust and JPEG speckle do not.
      var minArea = Math.max(6, Math.round(n * 0.00004));
      despeckle(alpha, w, h, minArea);
    }

    // 3. rebuild pixels
    var out = ctx.createImageData(w, h);
    var o = out.data;
    for (i = 0, j = 0; i < n; i++, j += 4) {
      var a = alpha[i] / 255;
      if (mode === 'white') {
        // Keep the original ink colour, force the paper to pure white.
        if (a <= 0.02) {
          o[j] = 255; o[j + 1] = 255; o[j + 2] = 255; o[j + 3] = 255;
        } else {
          o[j] = Math.round(p[j] * a + 255 * (1 - a));
          o[j + 1] = Math.round(p[j + 1] * a + 255 * (1 - a));
          o[j + 2] = Math.round(p[j + 2] * a + 255 * (1 - a));
          o[j + 3] = 255;
        }
      } else {
        var g = Math.round(ink[i]);
        if (transparent) {
          o[j] = g; o[j + 1] = g; o[j + 2] = g; o[j + 3] = alpha[i];
        } else {
          var v = Math.round(g * a + 255 * (1 - a));
          o[j] = v; o[j + 1] = v; o[j + 2] = v; o[j + 3] = 255;
        }
      }
    }
    var res = newCanvas(w, h);
    res.getContext('2d').putImageData(out, 0, 0);
    return res;
  }

  function sCompose() {
    var mode = segValue($('#s-mode'));
    var fmt = segValue($('#s-format'));
    var png = fmt === 'png';
    var transparent = png && $('#s-rembg').checked && mode === 'scan';
    var detect = parseInt($('#s-thr').value, 10);
    var denoise = $('#s-noise').checked;

    var work = sWorkCanvas();
    var cleaned = sClean(work, mode, detect, denoise, transparent);

    var w = rClamp($('#s-w').value), h = rClamp($('#s-h').value);
    var c = newCanvas(w, h), ctx = c.getContext('2d');

    var wantWhite = (mode !== 'original') && $('#s-white').checked;
    if (!transparent && (wantWhite || !png)) {
      ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, w, h);
    }
    // Fit keeps the whole signature visible — never crop someone's signature.
    var f = Math.min(w / cleaned.width, h / cleaned.height);
    var dw = Math.max(1, Math.round(cleaned.width * f));
    var dh = Math.max(1, Math.round(cleaned.height * f));
    drawScaled(ctx, cleaned, 0, 0, cleaned.width, cleaned.height,
               Math.round((w - dw) / 2), Math.round((h - dh) / 2), dw, dh);

    S.bgLabel = transparent ? 'Transparent' : (wantWhite || !png ? 'White Background' : 'Original');
    return { canvas: c, transparent: transparent };
  }

  var sPreview = debounce(function () {
    if (!S.img) { return; }
    var pw = rClamp($('#s-w').value), ph = rClamp($('#s-h').value);
    if (pw * ph > PREVIEW_MAX_PX) {
      $('#s-pv-out').classList.remove('checker');
      $('#s-pv-out').innerHTML = '<div class="pv-empty">Live preview is paused at this size ' +
        '(' + pw + ' &times; ' + ph + ' px).<br>Press <b>Scan &amp; Prepare Signature</b> to generate it.</div>';
      $('#s-pv-out-f').innerHTML = '<b>' + pw + ' &times; ' + ph + ' px</b> · not previewed';
      return;
    }
    var r;
    try { r = sCompose(); } catch (e) { return; }
    var fmt = segValue($('#s-format'));
    var q = parseInt($('#s-quality').value, 10) / 100;
    canvasToBlob(r.canvas, mimeFor(fmt), q).then(function (b) {
      sShowOut(r.canvas, b, fmt, r.transparent, false);
    }).catch(function () {});
  }, 260);

  function sShowOut(canvas, blob, fmt, transparent, isFinal) {
    var stage = $('#s-pv-out');
    stage.innerHTML = '';
    stage.classList.toggle('checker', !!transparent);
    var el = newCanvas(canvas.width, canvas.height);
    el.getContext('2d').drawImage(canvas, 0, 0);
    el.style.maxHeight = '280px'; el.style.width = 'auto'; el.style.maxWidth = '100%';
    el.style.borderRadius = '6px';
    stage.appendChild(el);
    $('#s-pv-out-f').innerHTML = '<b>' + canvas.width + ' &times; ' + canvas.height + ' px</b> · ' +
      fmtBytes(blob.size) + ' · ' + fmt.toUpperCase() + (isFinal ? '' : ' <i>(live preview)</i>');
  }

  $('#s-run').addEventListener('click', function () {
    if (!S.img) { toast('Please upload your signature first.', 'err'); return; }
    var busy = $('#s-busy'), msg = $('#s-msg');
    busy.classList.remove('hidden');
    note(msg, '', '');
    $('#s-result').classList.add('hidden');

    setTimeout(function () {
      var r;
      try { r = sCompose(); } catch (err) {
        busy.classList.add('hidden');
        note(msg, 'e', 'Could not process that signature.');
        return;
      }
      var fmt = segValue($('#s-format'));
      var q = parseInt($('#s-quality').value, 10) / 100;
      encodeTarget(r.canvas, mimeFor(fmt), sTargetBytes(), q).then(function (res) {
        busy.classList.add('hidden');
        S.outBlob = res.blob; S.outCanvas = r.canvas; S.fmt = fmt;

        sShowOut(r.canvas, res.blob, fmt, r.transparent, true);
        $('#s-result-meta').innerHTML =
          '<span>Dimensions: <b>' + r.canvas.width + ' &times; ' + r.canvas.height + ' px</b></span>' +
          '<span>File size: <b>' + fmtBytes(res.blob.size) + '</b></span>' +
          '<span>Format: <b>' + fmt.toUpperCase() + '</b></span>' +
          '<span>Background: <b>' + esc(S.bgLabel) + '</b></span>' +
          (res.quality ? '<span>Quality used: <b>' + res.quality + '%</b></span>' : '');
        $('#s-result').classList.remove('hidden');

        if (res.note) { note(msg, 'w', esc(res.note)); }
        else if (sTargetBytes()) { note(msg, 'o', 'Target file size reached.'); }

        $('#s-copy').classList.toggle('hidden', !clipboardSupported());
        $('#s-share').classList.toggle('hidden', !canShareFiles());
        toast('Signature ready.', 'ok');
      }).catch(function (err) {
        busy.classList.add('hidden');
        note(msg, 'e', esc(err.message || 'Something went wrong.'));
      });
    }, 40);
  });

  $('#s-dl').addEventListener('click', function () {
    if (S.outBlob) { download(S.outBlob, 'signature.' + S.fmt); }
  });
  $$('[data-sdl]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!S.img) { return; }
      var fmt = b.getAttribute('data-sdl');
      var prev = segValue($('#s-format'));
      $$('#s-format button[data-v]').forEach(function (x) {
        x.setAttribute('aria-pressed', String(x.getAttribute('data-v') === fmt));
      });
      var r = sCompose();
      $$('#s-format button[data-v]').forEach(function (x) {
        x.setAttribute('aria-pressed', String(x.getAttribute('data-v') === prev));
      });
      var q = parseInt($('#s-quality').value, 10) / 100;
      encodeTarget(r.canvas, mimeFor(fmt), sTargetBytes(), q).then(function (res) {
        download(res.blob, 'signature.' + fmt);
        if (res.note) { note($('#s-msg'), 'w', esc(res.note)); }
      });
    });
  });
  $('#s-share').addEventListener('click', function () {
    if (!S.outBlob) { return; }
    shareBlob(S.outBlob, 'signature.' + S.fmt, 'Signature').then(function (ok) {
      if (!ok) { toast('Sharing is not available here — use Download instead.', 'err'); }
    });
  });
  $('#s-copy').addEventListener('click', function () {
    if (!S.outCanvas) { return; }
    copyBlob(S.outCanvas).then(function (ok) {
      toast(ok ? 'Signature copied to clipboard.' : 'Copying is not supported in this browser.', ok ? 'ok' : 'err');
    });
  });

  $('#s-reset').addEventListener('click', function () {
    S = { img: null, name: '', type: '', size: 0, w: 0, h: 0,
          outBlob: null, outCanvas: null, fmt: 'jpg', bgLabel: 'White Background' };
    $('#s-file').value = '';
    $('#s-src-meta').classList.add('hidden'); $('#s-src-meta').innerHTML = '';
    $('#s-pv-src').innerHTML = '';
    $('#s-pv-out').innerHTML = '<div class="pv-empty">Adjust the settings below</div>';
    $('#s-pv-out').classList.remove('checker');
    $('#s-pv-src-f').innerHTML = '&nbsp;'; $('#s-pv-out-f').innerHTML = '&nbsp;';
    $('#s-w').value = ''; $('#s-h').value = '';
    $('#s-lock').checked = true; $('#s-noise').checked = true;
    $('#s-rembg').checked = false; $('#s-white').checked = true;
    $('#s-thr').value = 50; $('#s-thr-val').textContent = 'Balanced';
    $('#s-quality').value = 90; $('#s-quality-val').textContent = 'Quality: 90%';
    $('#s-target').value = '0'; $('#s-target-slider').classList.add('hidden');
    rMarkCustom($('#s-presets'));
    $$('#s-mode button').forEach(function (b) { b.setAttribute('aria-pressed', String(b.getAttribute('data-v') === 'scan')); });
    $$('#s-format button').forEach(function (b) { b.setAttribute('aria-pressed', String(b.getAttribute('data-v') === 'jpg')); });
    note($('#s-msg'), '', ''); note($('#s-upload-note'), '', '');
    $('#s-result').classList.add('hidden');
    sShowCards(false);
    sSetHelp();
    toast('Signature cleared.');
  });

  /* =======================================================================
     BOOT
     ======================================================================= */
  rSetHelp();
  sSetHelp();
  $('#r-copy').classList.toggle('hidden', !clipboardSupported());
  $('#s-copy').classList.toggle('hidden', !clipboardSupported());
  $('#r-share').classList.toggle('hidden', !canShareFiles());
  $('#s-share').classList.toggle('hidden', !canShareFiles());
})();
</script>
</body>
</html>
