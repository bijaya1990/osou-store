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
   1. Design tokens
   =========================================================================== */
:root{
  --indigo:#6366f1;  --indigo-d:#4f46e5;
  --violet:#8b5cf6;
  --pink:#ec4899;
  --sky:#0ea5e9;
  --emerald:#10b981; --emerald-d:#059669;
  --teal:#14b8a6;
  --amber:#f59e0b;
  --rose:#f43f5e;

  --ink:#0f1729;
  --ink-2:#334155;
  --muted:#64748b;
  --faint:#94a3b8;
  --line:#e6ebf3;
  --line-2:#eff3f9;
  --card:#fff;

  --ok:#059669; --ok-bg:#ecfdf5; --ok-line:#a7f3d0;
  --warn:#b45309; --warn-bg:#fffbeb; --warn-line:#fde68a;
  --err:#e11d48; --err-bg:#fff1f2; --err-line:#fecdd3;

  --r-sm:12px; --r:18px; --r-lg:24px;
  --sh-1:0 1px 2px rgba(15,23,41,.05), 0 4px 12px rgba(15,23,41,.05);
  --sh-2:0 4px 10px rgba(15,23,41,.06), 0 16px 40px rgba(15,23,41,.09);
  --sh-glow:0 8px 24px rgba(99,102,241,.35);

  /* Per-tool accent, swapped when a tab is chosen */
  --accent:var(--indigo);
  --accent-d:var(--indigo-d);
  --accent-grad:linear-gradient(135deg,#6366f1,#8b5cf6 55%,#a855f7);
  --accent-soft:#eef2ff;
  --accent-line:#c7d2fe;
}
body[data-tool="signature"]{
  --accent:var(--emerald);
  --accent-d:var(--emerald-d);
  --accent-grad:linear-gradient(135deg,#10b981,#14b8a6 55%,#06b6d4);
  --accent-soft:#ecfdf5;
  --accent-line:#a7f3d0;
}

*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%;scroll-behavior:smooth}
body{
  margin:0;color:var(--ink);
  font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
  font-size:15.5px;line-height:1.55;-webkit-font-smoothing:antialiased;
  overflow-x:hidden;
  background:
    radial-gradient(900px 500px at 8% -8%, #e0e7ff 0%, transparent 60%),
    radial-gradient(800px 460px at 96% 2%, #fce7f3 0%, transparent 58%),
    radial-gradient(700px 500px at 50% 108%, #cffafe 0%, transparent 60%),
    #f7f9fd;
  background-attachment:fixed;
}
img{max-width:100%;display:block}
button{font:inherit;color:inherit}
:focus-visible{outline:3px solid color-mix(in srgb, var(--accent) 45%, transparent);outline-offset:2px}
.sr{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0}
.hidden{display:none!important}
.wrap{max-width:1200px;margin:0 auto;padding:0 16px}

/* ===========================================================================
   2. Header
   =========================================================================== */
.site-head{
  position:sticky;top:0;z-index:60;
  background:rgba(255,255,255,.82);backdrop-filter:saturate(180%) blur(14px);
  border-bottom:1px solid rgba(226,232,240,.9);
}
.site-head-in{display:flex;align-items:center;gap:12px;padding:12px 0}
.logo{
  width:42px;height:42px;border-radius:13px;flex:0 0 42px;display:grid;place-items:center;
  background:linear-gradient(135deg,#6366f1,#8b5cf6 50%,#ec4899);color:#fff;
  box-shadow:0 6px 18px rgba(139,92,246,.4);
}
.logo svg{width:23px;height:23px}
.brand b{
  display:block;font-size:17px;font-weight:800;letter-spacing:-.35px;line-height:1.15;
  background:linear-gradient(90deg,#4f46e5,#8b5cf6 55%,#ec4899);
  -webkit-background-clip:text;background-clip:text;color:transparent;
}
.brand span{display:block;font-size:11.5px;color:var(--muted);font-weight:600;letter-spacing:.02em}
.site-head-in .home{
  margin-left:auto;font-size:14px;font-weight:700;color:var(--ink-2);text-decoration:none;
  padding:9px 14px;border-radius:99px;background:#fff;border:1px solid var(--line);
}
.site-head-in .home:hover{border-color:var(--accent-line);color:var(--accent-d)}

/* ===========================================================================
   3. Hero
   =========================================================================== */
.hero{text-align:center;padding:40px 0 8px}
.hero .pill{
  display:inline-flex;align-items:center;gap:7px;
  background:#fff;border:1px solid var(--line);border-radius:99px;
  padding:6px 15px;font-size:12.5px;font-weight:700;color:var(--ink-2);
  box-shadow:var(--sh-1);margin-bottom:16px;
}
.hero .pill i{width:7px;height:7px;border-radius:50%;background:var(--emerald);animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.45;transform:scale(.82)}}
.hero h1{
  margin:0 0 12px;font-size:clamp(28px,5.4vw,46px);font-weight:900;letter-spacing:-1.4px;line-height:1.08;
}
.hero h1 em{
  font-style:normal;
  background:linear-gradient(100deg,#4f46e5,#8b5cf6 40%,#ec4899 75%,#f59e0b);
  -webkit-background-clip:text;background-clip:text;color:transparent;
}
.hero p{margin:0 auto;max-width:620px;color:var(--muted);font-size:16px}

/* 3-step ribbon */
.howto{
  display:flex;justify-content:center;gap:8px;flex-wrap:wrap;
  margin:22px auto 0;max-width:660px;
}
.howto li{
  list-style:none;display:flex;align-items:center;gap:8px;
  background:rgba(255,255,255,.8);border:1px solid var(--line);border-radius:99px;
  padding:7px 14px 7px 8px;font-size:13px;font-weight:700;color:var(--ink-2);
}
.howto li b{
  width:22px;height:22px;border-radius:50%;display:grid;place-items:center;
  font-size:11.5px;color:#fff;background:var(--accent-grad);
}
.howto .arw{display:grid;place-items:center;color:var(--faint);font-weight:800}
@media (max-width:520px){.howto .arw{display:none}.howto li{flex:1 1 100%;justify-content:center}}

/* ===========================================================================
   4. Tool chooser
   =========================================================================== */
.chooser{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:26px 0 22px}
.tool-card{
  position:relative;overflow:hidden;
  display:flex;gap:15px;align-items:flex-start;text-align:left;width:100%;
  background:#fff;border:2px solid var(--line);border-radius:var(--r-lg);
  padding:20px;cursor:pointer;transition:transform .2s,box-shadow .2s,border-color .2s;
  box-shadow:var(--sh-1);
}
.tool-card::after{
  content:"";position:absolute;inset:0;opacity:0;transition:.25s;pointer-events:none;
  background:radial-gradient(420px 160px at 88% 8%, rgba(255,255,255,.6), transparent 70%);
}
.tool-card:hover{transform:translateY(-3px);box-shadow:var(--sh-2)}
.tool-card .ic{
  width:52px;height:52px;flex:0 0 52px;border-radius:16px;display:grid;place-items:center;color:#fff;
  box-shadow:0 8px 20px rgba(15,23,41,.16);
}
.tool-card .ic svg{width:26px;height:26px}
#tab-resizer .ic{background:linear-gradient(135deg,#6366f1,#8b5cf6 60%,#a855f7)}
#tab-signature .ic{background:linear-gradient(135deg,#10b981,#14b8a6 60%,#06b6d4)}
.tool-card h2{margin:0 0 4px;font-size:18px;font-weight:800;letter-spacing:-.3px}
.tool-card p{margin:0;font-size:13.8px;color:var(--muted);line-height:1.45}
.tool-card .tick{
  position:absolute;top:14px;right:14px;width:24px;height:24px;border-radius:50%;
  display:grid;place-items:center;background:var(--line-2);color:transparent;transition:.2s;
}
.tool-card .tick svg{width:14px;height:14px}
#tab-resizer[aria-selected="true"]{border-color:var(--indigo);background:linear-gradient(180deg,#f5f3ff,#fff 60%)}
#tab-signature[aria-selected="true"]{border-color:var(--emerald);background:linear-gradient(180deg,#ecfdf5,#fff 60%)}
.tool-card[aria-selected="true"]{box-shadow:var(--sh-2)}
.tool-card[aria-selected="true"] .tick{background:var(--accent);color:#fff}
.tool-card[aria-selected="true"]::after{opacity:1}

/* ===========================================================================
   5. Privacy banner
   =========================================================================== */
.privacy{
  display:flex;gap:13px;align-items:flex-start;
  background:linear-gradient(100deg,#ecfdf5,#f0fdfa 60%,#fff);
  border:1px solid var(--ok-line);border-radius:var(--r);
  padding:15px 18px;margin:0 0 22px;box-shadow:var(--sh-1);
}
.privacy .pi{
  width:38px;height:38px;flex:0 0 38px;border-radius:12px;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 6px 16px rgba(16,185,129,.35);
}
.privacy .pi svg{width:20px;height:20px}
.privacy b{display:block;font-size:14.5px;margin-bottom:2px;color:#065f46}
.privacy p{margin:0;font-size:13.2px;color:#047857;line-height:1.5}

/* ===========================================================================
   6. Panels + cards
   =========================================================================== */
.panel{display:none}
.panel.active{display:block;animation:rise .28s cubic-bezier(.2,.7,.3,1)}
@keyframes rise{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}

.card{
  background:var(--card);border:1px solid var(--line);border-radius:var(--r-lg);
  box-shadow:var(--sh-1);margin-bottom:18px;overflow:hidden;
}
.card-h{display:flex;align-items:center;gap:13px;padding:17px 20px;background:linear-gradient(180deg,#fcfdff,#fff);border-bottom:1px solid var(--line-2);flex-wrap:wrap}
.card-h .n{
  width:32px;height:32px;flex:0 0 32px;border-radius:11px;color:#fff;
  display:grid;place-items:center;font-size:14px;font-weight:800;
  background:var(--accent-grad);box-shadow:0 5px 14px rgba(99,102,241,.3);
}
body[data-tool="signature"] .card-h .n{box-shadow:0 5px 14px rgba(16,185,129,.32)}
.card-h h3{margin:0;font-size:17px;font-weight:800;letter-spacing:-.25px}
.card-h .sub{margin:0;font-size:13px;color:var(--muted);flex:1 1 100%}
.card-b{padding:20px}

/* ===========================================================================
   7. Dropzone
   =========================================================================== */
.drop{
  position:relative;overflow:hidden;
  border:2.5px dashed var(--accent-line);border-radius:var(--r);
  background:linear-gradient(180deg,var(--accent-soft),#fff 70%);
  padding:34px 18px;text-align:center;cursor:pointer;transition:.2s;
}
.drop:hover{border-color:var(--accent);transform:translateY(-2px)}
.drop.over{border-color:var(--accent);background:var(--accent-soft);transform:scale(1.01)}
.drop .di{
  width:66px;height:66px;margin:0 auto 14px;border-radius:20px;display:grid;place-items:center;
  color:#fff;background:var(--accent-grad);box-shadow:0 10px 26px rgba(99,102,241,.34);
}
body[data-tool="signature"] .drop .di{box-shadow:0 10px 26px rgba(16,185,129,.34)}
.drop .di svg{width:32px;height:32px}
.drop b{display:block;font-size:18px;font-weight:800;margin-bottom:4px;letter-spacing:-.3px}
.drop .dsub{display:block;font-size:14px;color:var(--muted)}
.drop .or{
  margin:14px auto 12px;font-size:11px;color:var(--faint);font-weight:800;letter-spacing:.14em;
  display:flex;align-items:center;gap:10px;max-width:220px;
}
.drop .or::before,.drop .or::after{content:"";flex:1;height:1px;background:var(--line)}
/* display:flex, not inline-flex: as an inline box it would sit on the same
   line as the Choose button instead of below it. */
.drop .fmts{
  margin-top:16px;font-size:12.2px;color:var(--muted);font-weight:600;
  display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:7px 10px;
}
.drop .fmts kbd{
  font:inherit;background:#fff;border:1px solid var(--line);border-radius:7px;padding:2px 8px;
  font-size:11.5px;font-weight:700;color:var(--ink-2);
}

/* ===========================================================================
   8. Buttons
   =========================================================================== */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:9px;
  border:1.5px solid var(--line);background:#fff;color:var(--ink-2);
  border-radius:13px;padding:12px 18px;font-size:15px;font-weight:700;
  cursor:pointer;transition:.16s;text-decoration:none;min-height:48px;
}
.btn:hover{background:#fbfcfe;border-color:#cdd7e5;transform:translateY(-1px)}
.btn:active{transform:translateY(0)}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.btn svg{width:18px;height:18px;flex:0 0 18px}
.btn-p{
  background:var(--accent-grad);border-color:transparent;color:#fff;
  box-shadow:var(--sh-glow);
}
body[data-tool="signature"] .btn-p{box-shadow:0 8px 24px rgba(16,185,129,.35)}
.btn-p:hover{filter:brightness(1.07);color:#fff;border-color:transparent}
.btn-s{background:var(--accent-soft);border-color:transparent;color:var(--accent-d)}
.btn-s:hover{background:color-mix(in srgb,var(--accent) 14%, #fff);color:var(--accent-d)}
.btn-d{color:var(--err);border-color:var(--err-line);background:var(--err-bg)}
.btn-d:hover{background:#ffe4e6;border-color:#fda4af}
.btn-lg{width:100%;padding:17px 22px;font-size:17px;border-radius:15px;min-height:58px;letter-spacing:-.2px}
.btn-row{display:flex;gap:10px;flex-wrap:wrap}
.btn-row .btn{flex:1 1 auto}

/* Sticky primary action on phones so the CTA is always reachable */
.cta{position:sticky;bottom:12px;z-index:20}
@media (min-width:641px){.cta{position:static}}

/* ===========================================================================
   9. Quick presets ("one tap" setup)
   =========================================================================== */
.qp{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:11px}
.qp button{
  position:relative;text-align:left;cursor:pointer;
  border:1.5px solid var(--line);border-radius:var(--r);background:#fff;
  padding:14px 14px 13px;transition:.17s;box-shadow:var(--sh-1);
}
.qp button:hover{transform:translateY(-3px);border-color:var(--accent-line);box-shadow:var(--sh-2)}
.qp button[aria-pressed="true"]{border-color:var(--accent);background:var(--accent-soft)}
.qp .qi{
  width:38px;height:38px;border-radius:12px;display:grid;place-items:center;
  color:#fff;margin-bottom:10px;
}
.qp .qi svg{width:19px;height:19px}
.qp .q1 .qi{background:linear-gradient(135deg,#6366f1,#818cf8)}
.qp .q2 .qi{background:linear-gradient(135deg,#0ea5e9,#38bdf8)}
.qp .q3 .qi{background:linear-gradient(135deg,#f59e0b,#fbbf24)}
.qp .q4 .qi{background:linear-gradient(135deg,#ec4899,#f472b6)}
.qp .q5 .qi{background:linear-gradient(135deg,#10b981,#34d399)}
.qp strong{display:block;font-size:14.5px;font-weight:800;letter-spacing:-.2px;margin-bottom:2px}
.qp em{display:block;font-style:normal;font-size:12.2px;color:var(--muted);font-weight:600}

/* ===========================================================================
   10. Option cards (signature cleaning modes)
   =========================================================================== */
.opts{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:11px}
.opts button{
  text-align:left;cursor:pointer;background:#fff;
  border:1.5px solid var(--line);border-radius:var(--r);padding:15px;transition:.17s;
  box-shadow:var(--sh-1);position:relative;
}
.opts button:hover{transform:translateY(-2px);border-color:var(--accent-line)}
.opts button[aria-pressed="true"]{border-color:var(--accent);background:var(--accent-soft)}
.opts .oi{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;color:#fff;margin-bottom:9px;background:var(--accent-grad)}
.opts .oi svg{width:18px;height:18px}
.opts strong{display:block;font-size:14.5px;font-weight:800;margin-bottom:3px}
.opts em{display:block;font-style:normal;font-size:12.3px;color:var(--muted);line-height:1.42;font-weight:500}
.opts .badge{
  position:absolute;top:12px;right:12px;font-size:9.5px;font-weight:800;letter-spacing:.06em;
  background:var(--amber);color:#fff;padding:3px 8px;border-radius:99px;
}

/* ===========================================================================
   11. Form controls
   =========================================================================== */
.f-label{display:block;font-size:13.2px;font-weight:800;color:var(--ink-2);margin-bottom:7px}
.inp,select.inp{
  width:100%;border:1.5px solid #dde4ee;border-radius:13px;padding:13px 14px;
  font-size:16px;font-weight:600;background:#fcfdff;color:var(--ink);min-height:50px;
  transition:.15s;
}
.inp:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 4px color-mix(in srgb,var(--accent) 16%,transparent);background:#fff}
select.inp{
  appearance:none;cursor:pointer;
  background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4 6l4 4 4-4z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 12px center;background-size:17px;padding-right:38px;
}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field{margin-bottom:18px}
.field:last-child{margin-bottom:0}
.f-help{font-size:12.6px;color:var(--muted);margin-top:7px;line-height:1.5}
.suffix{position:relative}
.suffix .inp{padding-right:44px}
.suffix i{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-style:normal;font-size:13px;color:var(--faint);font-weight:700;pointer-events:none}

/* toggle */
.tgl{display:flex;align-items:center;gap:13px;cursor:pointer;user-select:none;min-height:48px;
  background:#fcfdff;border:1.5px solid var(--line);border-radius:14px;padding:11px 14px;transition:.15s}
.tgl:hover{border-color:var(--accent-line)}
.tgl input{position:absolute;opacity:0;width:0;height:0}
.tgl .track{width:48px;height:28px;flex:0 0 48px;border-radius:99px;background:#cbd5e1;position:relative;transition:.22s}
.tgl .track::after{content:"";position:absolute;top:3px;left:3px;width:22px;height:22px;border-radius:50%;background:#fff;transition:.22s;box-shadow:0 2px 5px rgba(0,0,0,.22)}
.tgl input:checked+.track{background:var(--accent)}
.tgl input:checked+.track::after{transform:translateX(20px)}
.tgl input:focus-visible+.track{box-shadow:0 0 0 4px color-mix(in srgb,var(--accent) 30%,transparent)}
.tgl .tl{font-size:14.5px;font-weight:700}
.tgl .td{font-size:12.4px;color:var(--muted);font-weight:500}
.tgl input:checked~span .tl{color:var(--accent-d)}

/* range */
input[type=range]{
  -webkit-appearance:none;appearance:none;width:100%;height:8px;border-radius:99px;
  background:linear-gradient(90deg,var(--accent) 0%,#dbe3ee 0%);outline:none;margin:14px 0;cursor:pointer;
}
input[type=range]::-webkit-slider-thumb{
  -webkit-appearance:none;appearance:none;width:28px;height:28px;border-radius:50%;
  background:#fff;cursor:pointer;border:4px solid var(--accent);box-shadow:0 3px 10px rgba(15,23,41,.28);
}
input[type=range]::-moz-range-thumb{
  width:28px;height:28px;border-radius:50%;background:#fff;cursor:pointer;
  border:4px solid var(--accent);box-shadow:0 3px 10px rgba(15,23,41,.28);
}
input[type=range]:disabled{opacity:.45;cursor:not-allowed}
.rng-head{display:flex;justify-content:space-between;align-items:center;gap:10px}
.rng-val{
  font-size:13.5px;font-weight:800;color:#fff;background:var(--accent);
  padding:4px 12px;border-radius:99px;white-space:nowrap;
}

/* chips */
.chips{display:flex;flex-wrap:wrap;gap:9px}
.chip{
  border:1.5px solid var(--line);background:#fff;color:var(--ink-2);
  border-radius:99px;padding:11px 17px;font-size:14px;font-weight:700;cursor:pointer;
  transition:.15s;min-height:44px;box-shadow:var(--sh-1);
}
.chip:hover{border-color:var(--accent-line);color:var(--accent-d);transform:translateY(-2px)}
.chip[aria-pressed="true"]{background:var(--accent-grad);border-color:transparent;color:#fff;box-shadow:var(--sh-glow)}
body[data-tool="signature"] .chip[aria-pressed="true"]{box-shadow:0 6px 18px rgba(16,185,129,.32)}

/* segmented */
.seg{display:flex;gap:6px;background:#f1f5f9;padding:6px;border-radius:15px;flex-wrap:wrap}
.seg button{
  flex:1 1 92px;border:0;background:transparent;border-radius:11px;padding:12px 10px;
  font-size:14.5px;font-weight:700;color:var(--ink-2);cursor:pointer;transition:.15s;min-height:46px;
}
.seg button:hover{background:#e6ecf5}
.seg button[aria-pressed="true"]{background:#fff;color:var(--accent-d);box-shadow:0 2px 8px rgba(15,23,41,.14)}

/* Advanced disclosure */
.adv{border:1.5px solid var(--line);border-radius:var(--r);overflow:hidden;background:#fcfdff}
.adv-t{
  width:100%;display:flex;align-items:center;gap:11px;background:none;border:0;cursor:pointer;
  padding:15px 17px;font-size:15px;font-weight:800;color:var(--ink-2);text-align:left;
}
.adv-t:hover{background:#f6f9ff}
.adv-t .ai{
  width:30px;height:30px;flex:0 0 30px;border-radius:10px;display:grid;place-items:center;
  background:var(--accent-soft);color:var(--accent-d);
}
.adv-t .ai svg{width:16px;height:16px}
.adv-t .chev{margin-left:auto;transition:transform .22s;color:var(--faint)}
.adv-t .chev svg{width:18px;height:18px}
.adv-t[aria-expanded="true"] .chev{transform:rotate(180deg)}
.adv-t small{display:block;font-size:12.3px;color:var(--muted);font-weight:500}
.adv-b{padding:0 17px 18px;border-top:1px solid var(--line-2)}
.adv-b.closed{display:none}

/* ===========================================================================
   12. Previews
   =========================================================================== */
.previews{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.pv{border:1.5px solid var(--line);border-radius:var(--r);overflow:hidden;background:#fff;display:flex;flex-direction:column;box-shadow:var(--sh-1)}
.pv-h{
  padding:11px 15px;font-size:11.5px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;
  color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--line-2);
  display:flex;align-items:center;gap:8px;
}
.pv-h .dot{width:8px;height:8px;border-radius:50%;background:var(--faint)}
.pv-out .pv-h{background:var(--accent-soft);color:var(--accent-d)}
.pv-out .pv-h .dot{background:var(--accent);box-shadow:0 0 0 4px color-mix(in srgb,var(--accent) 22%,transparent)}
.pv-stage{flex:1;min-height:200px;display:grid;place-items:center;padding:16px;background:#f8fafc}
.pv-stage img,.pv-stage canvas{max-height:290px;width:auto;max-width:100%;border-radius:8px;box-shadow:0 3px 12px rgba(15,23,41,.14)}
.pv-stage.checker{
  background-color:#fff;
  background-image:linear-gradient(45deg,#e6ebf3 25%,transparent 25%),linear-gradient(-45deg,#e6ebf3 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#e6ebf3 75%),linear-gradient(-45deg,transparent 75%,#e6ebf3 75%);
  background-size:18px 18px;background-position:0 0,0 9px,9px -9px,-9px 0;
}
.pv-empty{color:var(--faint);font-size:13.8px;text-align:center;padding:24px 14px;line-height:1.6}
.pv-f{padding:11px 15px;border-top:1px solid var(--line-2);font-size:12.8px;color:var(--muted);background:#fff;font-weight:600}
.pv-f b{color:var(--ink);font-weight:800}

/* meta chips */
.meta{display:flex;flex-wrap:wrap;gap:9px;margin-top:14px}
.meta span{
  background:#fff;border:1px solid var(--line);border-radius:11px;padding:8px 13px;
  font-size:12.8px;color:var(--muted);font-weight:700;box-shadow:var(--sh-1);
}
.meta span b{color:var(--ink);font-weight:800}

/* notes */
.note{display:flex;gap:11px;align-items:flex-start;border-radius:14px;padding:13px 15px;font-size:13.4px;line-height:1.55;margin-top:14px;border:1px solid transparent}
.note svg{width:18px;height:18px;flex:0 0 18px;margin-top:1px}
.note-i{background:#eef2ff;border-color:#c7d2fe;color:#3730a3}
.note-w{background:var(--warn-bg);border-color:var(--warn-line);color:#92400e}
.note-e{background:var(--err-bg);border-color:var(--err-line);color:#9f1239}
.note-o{background:var(--ok-bg);border-color:var(--ok-line);color:#065f46}

/* result */
.result{
  border:2px solid var(--ok-line);
  background:linear-gradient(160deg,#ecfdf5,#f0fdfa 55%,#fff);
  border-radius:var(--r);padding:18px;margin-top:16px;
}
.result h4{margin:0 0 11px;font-size:16px;font-weight:800;color:#065f46;display:flex;align-items:center;gap:10px}
.result h4 .rk{
  width:30px;height:30px;border-radius:50%;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 12px rgba(16,185,129,.4);
}
.result h4 svg{width:17px;height:17px}

/* busy */
.busy{display:flex;align-items:center;justify-content:center;gap:13px;padding:20px;color:var(--accent-d);font-size:15px;font-weight:700}
.spin{width:24px;height:24px;border:3.5px solid var(--accent-soft);border-top-color:var(--accent);border-radius:50%;animation:sp .7s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){
  *{animation-duration:.01ms!important;transition-duration:.01ms!important}
  .spin{animation-duration:1.2s!important}
}

/* footer */
.site-foot{text-align:center;padding:26px 0 40px;color:var(--muted);font-size:12.8px}
.site-foot code{background:#eef2f8;padding:3px 8px;border-radius:7px;font-size:11.8px}

/* toast */
.toast{
  position:fixed;left:50%;bottom:24px;transform:translate(-50%,22px);
  background:var(--ink);color:#fff;padding:14px 20px;border-radius:15px;font-size:14.5px;font-weight:700;
  box-shadow:0 18px 44px rgba(15,23,41,.35);opacity:0;pointer-events:none;transition:.26s;
  z-index:200;max-width:92vw;text-align:center;
}
.toast.show{opacity:1;transform:translate(-50%,0)}
.toast.err{background:linear-gradient(135deg,#e11d48,#be123c)}
.toast.ok{background:linear-gradient(135deg,#059669,#047857)}

/* ===========================================================================
   13. Responsive
   =========================================================================== */
@media (max-width:900px){
  .previews{grid-template-columns:1fr}
}
@media (max-width:700px){
  .chooser{grid-template-columns:1fr;gap:12px;margin:18px 0}
  .tool-card{padding:16px;gap:13px;border-radius:var(--r)}
  .tool-card .ic{width:46px;height:46px;flex:0 0 46px;border-radius:14px}
  .tool-card h2{font-size:16.5px}
}
@media (max-width:640px){
  .wrap{padding:0 13px}
  .hero{padding:26px 0 4px}
  .hero p{font-size:15px}
  .card-b{padding:16px}
  .card-h{padding:15px 16px}
  .card-h h3{font-size:16px}
  .grid2{grid-template-columns:1fr;gap:13px}
  .drop{padding:26px 15px}
  .drop .di{width:58px;height:58px;border-radius:17px}
  .drop b{font-size:16.5px}
  .btn-row .btn{flex:1 1 100%}
  .qp{grid-template-columns:1fr 1fr;gap:9px}
  .qp button{padding:12px}
  .qp .qi{width:34px;height:34px;margin-bottom:8px}
  .qp strong{font-size:13.5px}
  .qp em{font-size:11.5px}
  .opts{grid-template-columns:1fr}
  .pv-stage img,.pv-stage canvas{max-height:230px}
  .privacy{padding:13px 14px;gap:11px}
  .privacy .pi{width:34px;height:34px;flex:0 0 34px}
}
@media (max-width:380px){
  .qp{grid-template-columns:1fr}
}

</style>
</head>
<body data-tool="resizer">

<!-- ======================================================================= -->
<header class="site-head">
  <div class="wrap site-head-in">
    <span class="logo" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.6"/><path d="M21 15l-5-5L5 21"/>
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
    <span class="pill"><i></i> 100% free &middot; No sign-up &middot; Nothing is uploaded</span>
    <h1>Resize photos &amp; clean your <em>signature</em> in seconds</h1>
    <p><?= e($TOOL_SUB) ?></p>
    <ul class="howto">
      <li><b>1</b> Upload</li>
      <span class="arw">&rarr;</span>
      <li><b>2</b> Pick a size</li>
      <span class="arw">&rarr;</span>
      <li><b>3</b> Download</li>
    </ul>
  </div>

  <!-- ============================ TOOL CHOOSER ============================ -->
  <div class="chooser" role="tablist" aria-label="Choose a tool">
    <button type="button" class="tool-card" role="tab" id="tab-resizer"
            aria-selected="true" aria-controls="panel-resizer" data-tool="resizer">
      <span class="ic" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.6"/><path d="M21 15l-5-5L5 21"/>
        </svg>
      </span>
      <span>
        <h2>Image Resizer</h2>
        <p>Resize your image to the exact width, height and file size you need.</p>
      </span>
      <span class="tick" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
      </span>
    </button>

    <button type="button" class="tool-card" role="tab" id="tab-signature"
            aria-selected="false" aria-controls="panel-signature" data-tool="signature">
      <span class="ic" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 17c3.5 0 3.5-9 7-9s3.5 9 7 9c1.7 0 2.7-1 3.5-2"/><path d="M3 21h18"/>
        </svg>
      </span>
      <span>
        <h2>Signature Scanner</h2>
        <p>Clean, resize and prepare your signature for online applications.</p>
      </span>
      <span class="tick" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
      </span>
    </button>
  </div>

  <div class="privacy">
    <span class="pi" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>
      </svg>
    </span>
    <div>
      <b>Your files never leave your phone or computer.</b>
      <p>
        Everything is done inside your own browser. Nothing is uploaded to our server, nothing is
        stored, and nothing is sent to any other website or AI service.
      </p>
    </div>
  </div>

<!-- ======================================================================= -->
<!--  PANEL 1 : IMAGE RESIZER                                                -->
<!-- ======================================================================= -->
<section class="panel active" id="panel-resizer" role="tabpanel" aria-labelledby="tab-resizer">

  <!-- STEP 1 : UPLOAD -->
  <div class="card">
    <div class="card-h"><span class="n">1</span><h3>Choose your image</h3></div>
    <div class="card-b">
      <div class="drop" id="r-drop" tabindex="0" role="button" aria-label="Upload your image">
        <span class="di" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v13"/>
          </svg>
        </span>
        <b>Tap to choose a photo</b>
        <span class="dsub">or drag &amp; drop it here</span>
        <div class="or">OR</div>
        <span class="btn btn-p" id="r-choose-fake">Choose Image</span>
        <div class="fmts">
          <kbd>JPG</kbd><kbd>JPEG</kbd><kbd>PNG</kbd><kbd>WEBP</kbd>
          <span>up to <?= (int) $MAX_MB ?> MB</span>
        </div>
      </div>
      <div id="r-src-meta" class="meta hidden"></div>
      <div id="r-upload-note"></div>
    </div>
  </div>
  <input type="file" id="r-file" class="sr" accept="image/jpeg,image/png,image/webp">

  <!-- STEP 2 : PREVIEW -->
  <div class="card hidden" id="r-work">
    <div class="card-h"><span class="n">2</span><h3>Before &amp; after</h3>
      <p class="sub">The result updates by itself as you change anything below.</p>
    </div>
    <div class="card-b">
      <div class="previews">
        <div class="pv">
          <div class="pv-h"><span class="dot"></span> Original</div>
          <div class="pv-stage" id="r-pv-src"></div>
          <div class="pv-f" id="r-pv-src-f">&nbsp;</div>
        </div>
        <div class="pv pv-out">
          <div class="pv-h"><span class="dot"></span> Your new image</div>
          <div class="pv-stage" id="r-pv-out"><div class="pv-empty">Pick a size below</div></div>
          <div class="pv-f" id="r-pv-out-f">&nbsp;</div>
        </div>
      </div>
    </div>
  </div>

  <!-- STEP 3 : SIZE (simple) -->
  <div class="card hidden" id="r-dims-card">
    <div class="card-h"><span class="n">3</span><h3>What do you need?</h3>
      <p class="sub">Tap one option — it sets the size, the file size and the format for you.</p>
    </div>
    <div class="card-b">

      <div class="field">
        <div class="qp" id="r-quick">
          <button type="button" class="q1" aria-pressed="false" data-w="350" data-h="450" data-kb="100" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            <strong>Passport Photo</strong><em>350 &times; 450 px &middot; 100 KB</em>
          </button>
          <button type="button" class="q2" aria-pressed="false" data-w="200" data-h="230" data-kb="50" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span>
            <strong>Form Photo</strong><em>200 &times; 230 px &middot; 50 KB</em>
          </button>
          <button type="button" class="q3" aria-pressed="false" data-w="200" data-h="200" data-kb="20" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18M3 12h18"/></svg></span>
            <strong>Very Small</strong><em>200 &times; 200 px &middot; 20 KB</em>
          </button>
          <button type="button" class="q4" aria-pressed="false" data-w="400" data-h="400" data-kb="100" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="10" r="3"/><path d="M6.5 19a6 6 0 0111 0"/></svg></span>
            <strong>Profile Picture</strong><em>400 &times; 400 px &middot; 100 KB</em>
          </button>
          <button type="button" class="q5" aria-pressed="false" data-orig="1" data-kb="200" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 001 1h4"/><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg></span>
            <strong>Just Compress</strong><em>Same size &middot; 200 KB</em>
          </button>
        </div>
        <div class="note note-i">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          <span>These are common sizes. <b>Always check what your own form asks for</b> — you can set any size you like below.</span>
        </div>
      </div>

      <div class="field">
        <span class="f-label">Or set the size yourself</span>
        <div class="grid2">
          <div>
            <label class="f-label" for="r-w" style="font-weight:600;color:var(--muted)">Width</label>
            <div class="suffix"><input class="inp" type="number" id="r-w" min="1" max="12000" inputmode="numeric"><i>px</i></div>
          </div>
          <div>
            <label class="f-label" for="r-h" style="font-weight:600;color:var(--muted)">Height</label>
            <div class="suffix"><input class="inp" type="number" id="r-h" min="1" max="12000" inputmode="numeric"><i>px</i></div>
          </div>
        </div>
      </div>

      <div class="field">
        <span class="f-label">Common sizes</span>
        <div class="chips" id="r-presets">
          <?php foreach ($IMG_PRESETS as $p): ?>
            <button type="button" class="chip" data-w="<?= (int) $p[0] ?>" data-h="<?= (int) $p[1] ?>" aria-pressed="false"><?= (int) $p[0] ?> &times; <?= (int) $p[1] ?></button>
          <?php endforeach; ?>
          <button type="button" class="chip" data-custom="1" aria-pressed="true">My own size</button>
        </div>
      </div>

      <div class="field">
        <label class="tgl">
          <input type="checkbox" id="r-lock" checked>
          <span class="track"></span>
          <span><span class="tl">Keep the photo's shape</span><br><span class="td">Stops the picture looking stretched</span></span>
        </label>
      </div>

      <div class="field">
        <label class="f-label" for="r-target">How big should the file be?</label>
        <select class="inp" id="r-target">
          <option value="0" selected>Best quality (no size limit)</option>
          <?php foreach ($SIZE_PRESETS as $kb): ?>
            <option value="<?= (int) $kb ?>"><?= $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.') . ' MB' : $kb . ' KB' ?> or less</option>
          <?php endforeach; ?>
          <option value="-1">Choose exactly…</option>
        </select>

        <div id="r-target-slider" class="hidden" style="margin-top:14px">
          <div class="rng-head">
            <span class="f-label" style="margin:0">Target size</span>
            <span class="rng-val" id="r-target-val">100 KB</span>
          </div>
          <input type="range" id="r-target-range" min="10" max="2048" step="5" value="100">
        </div>
        <p class="f-help">We compress as close to your target as possible. If a size is impossible without wrecking the picture, we tell you instead of giving you a ruined file.</p>
      </div>

      <div class="field">
        <span class="f-label">File type</span>
        <div class="seg" id="r-format">
          <button type="button" data-v="jpg" aria-pressed="true">JPG</button>
          <button type="button" data-v="jpeg" aria-pressed="false">JPEG</button>
          <button type="button" data-v="png" aria-pressed="false">PNG</button>
        </div>
        <p class="f-help" id="r-format-help"></p>
      </div>

      <!-- ADVANCED -->
      <div class="field">
        <div class="adv">
          <button type="button" class="adv-t" data-adv="r-adv" aria-expanded="false">
            <span class="ai" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg></span>
            <span>Advanced settings<small>Quality, cropping and background — most people can skip this</small></span>
            <span class="chev" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span>
          </button>
          <div class="adv-b closed" id="r-adv">
            <div class="field" style="padding-top:16px">
              <div class="rng-head">
                <label class="f-label" for="r-quality" style="margin:0">Picture quality</label>
                <span class="rng-val" id="r-quality-val">Quality: 85%</span>
              </div>
              <input type="range" id="r-quality" min="10" max="100" value="85">
              <p class="f-help" id="r-quality-help"></p>
            </div>

            <div class="field">
              <span class="f-label">If the shape doesn't match</span>
              <div class="seg" id="r-method">
                <button type="button" data-v="fit" aria-pressed="true">Fit inside</button>
                <button type="button" data-v="fill" aria-pressed="false">Fill &amp; crop</button>
                <button type="button" data-v="stretch" aria-pressed="false">Stretch</button>
              </div>
              <p class="f-help" id="r-method-help"></p>
            </div>

            <div class="field">
              <span class="f-label">Background colour</span>
              <div class="seg" id="r-bg">
                <button type="button" data-v="original" aria-pressed="true">Original</button>
                <button type="button" data-v="white" aria-pressed="false">White</button>
                <button type="button" data-v="black" aria-pressed="false">Black</button>
                <button type="button" data-v="custom" aria-pressed="false">Pick</button>
              </div>
              <div style="margin-top:11px;max-width:180px">
                <input type="color" class="inp" id="r-bg-color" value="#ffffff" style="padding:6px;height:50px;cursor:pointer">
              </div>
              <p class="f-help">Used for see-through areas and for any empty space left by <b>Fit inside</b>.</p>
            </div>

            <div class="field">
              <button type="button" class="btn btn-s" id="r-orig-dims">Back to original size</button>
            </div>
          </div>
        </div>
      </div>

      <div class="cta">
        <button type="button" class="btn btn-p btn-lg" id="r-run">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
          Resize My Image
        </button>
      </div>
      <div class="btn-row" style="margin-top:10px">
        <button type="button" class="btn btn-d" id="r-reset">Start over</button>
      </div>

      <div id="r-busy" class="busy hidden"><span class="spin"></span> Working on your image…</div>
      <div id="r-msg"></div>

      <div class="result hidden" id="r-result">
        <h4>
          <span class="rk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
          Done! Your image is ready
        </h4>
        <div class="meta" id="r-result-meta"></div>
        <div class="btn-row" style="margin-top:15px">
          <button type="button" class="btn btn-p" id="r-dl">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            Download
          </button>
          <button type="button" class="btn" id="r-share">Share</button>
          <button type="button" class="btn" id="r-copy">Copy</button>
        </div>
        <div class="btn-row" style="margin-top:10px">
          <button type="button" class="btn btn-s" data-dl="jpg">Save as JPG</button>
          <button type="button" class="btn btn-s" data-dl="jpeg">Save as JPEG</button>
          <button type="button" class="btn btn-s" data-dl="png">Save as PNG</button>
        </div>
      </div>
    </div>
  </div>

  <!-- kept for JS compatibility: the settings now live in card 3 -->
  <div class="hidden" id="r-out-card"></div>
</section>

<!-- ======================================================================= -->
<!--  PANEL 2 : SIGNATURE SCANNER                                            -->
<!-- ======================================================================= -->
<section class="panel" id="panel-signature" role="tabpanel" aria-labelledby="tab-signature">

  <div class="card">
    <div class="card-h"><span class="n">1</span><h3>Choose your signature</h3>
      <p class="sub">Sign on plain white paper, take a photo of it, and upload that photo.</p>
    </div>
    <div class="card-b">
      <div class="drop" id="s-drop" tabindex="0" role="button" aria-label="Upload your signature">
        <span class="di" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 17c3.5 0 3.5-9 7-9s3.5 9 7 9c1.7 0 2.7-1 3.5-2"/><path d="M3 21h18"/>
          </svg>
        </span>
        <b>Tap to choose your signature</b>
        <span class="dsub">or drag &amp; drop it here</span>
        <div class="or">OR</div>
        <span class="btn btn-p">Choose Signature</span>
        <div class="fmts">
          <kbd>JPG</kbd><kbd>JPEG</kbd><kbd>PNG</kbd><kbd>WEBP</kbd>
          <span>up to <?= (int) $MAX_MB ?> MB</span>
        </div>
      </div>
      <div id="s-src-meta" class="meta hidden"></div>
      <div id="s-upload-note"></div>
    </div>
  </div>
  <input type="file" id="s-file" class="sr" accept="image/jpeg,image/png,image/webp">

  <div class="card hidden" id="s-work">
    <div class="card-h"><span class="n">2</span><h3>Before &amp; after</h3></div>
    <div class="card-b">
      <div class="previews">
        <div class="pv">
          <div class="pv-h"><span class="dot"></span> Your photo</div>
          <div class="pv-stage" id="s-pv-src"></div>
          <div class="pv-f" id="s-pv-src-f">&nbsp;</div>
        </div>
        <div class="pv pv-out">
          <div class="pv-h"><span class="dot"></span> Cleaned signature</div>
          <div class="pv-stage" id="s-pv-out"><div class="pv-empty">Choose a cleaning style below</div></div>
          <div class="pv-f" id="s-pv-out-f">&nbsp;</div>
        </div>
      </div>
    </div>
  </div>

  <!-- CLEANING -->
  <div class="card hidden" id="s-clean-card">
    <div class="card-h"><span class="n">3</span><h3>Clean it up</h3>
      <p class="sub">Machine Scan is the best choice for a photo taken with a phone.</p>
    </div>
    <div class="card-b">
      <div class="field">
        <div class="opts" id="s-mode">
          <button type="button" data-v="scan" aria-pressed="true">
            <span class="badge">BEST</span>
            <span class="oi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2"/><path d="M3 12h18"/></svg></span>
            <strong>Machine Scan</strong>
            <em>Removes shadows, grey paper and dust. Looks like a proper scanner copy.</em>
          </button>
          <button type="button" data-v="white" aria-pressed="false">
            <span class="oi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v3M12 20v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M1 12h3M20 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg></span>
            <strong>White Background</strong>
            <em>Makes the paper pure white but keeps your natural pen strokes.</em>
          </button>
          <button type="button" data-v="original" aria-pressed="false">
            <span class="oi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15l5-5 4 4 3-3 6 6"/></svg></span>
            <strong>No Cleaning</strong>
            <em>Leaves the photo exactly as it is, only resized.</em>
          </button>
        </div>
        <p class="f-help" id="s-mode-help"></p>
      </div>

      <div class="field" id="s-thr-wrap">
        <div class="rng-head">
          <label class="f-label" for="s-thr" style="margin:0">How much to pick up</label>
          <span class="rng-val" id="s-thr-val">Balanced</span>
        </div>
        <input type="range" id="s-thr" min="0" max="100" value="50">
        <p class="f-help">Slide right if parts of your signature are missing. Slide left if grey patches from the paper are still showing.</p>
      </div>

      <div class="field" id="s-noise-wrap">
        <label class="tgl">
          <input type="checkbox" id="s-noise" checked>
          <span class="track"></span>
          <span><span class="tl">Remove dust &amp; shadows</span><br><span class="td">Deletes tiny specks, keeps your signature</span></span>
        </label>
      </div>

      <div class="field" id="s-white-wrap">
        <label class="tgl">
          <input type="checkbox" id="s-white" checked>
          <span class="track"></span>
          <span><span class="tl">Pure white background</span><br><span class="td">Exactly white — what most forms expect</span></span>
        </label>
      </div>

      <div class="field" id="s-rembg-wrap">
        <label class="tgl">
          <input type="checkbox" id="s-rembg">
          <span class="track"></span>
          <span><span class="tl">See-through background</span><br><span class="td">Needs PNG. A checkerboard means see-through.</span></span>
        </label>
      </div>
    </div>
  </div>

  <!-- SIZE -->
  <div class="card hidden" id="s-dims-card">
    <div class="card-h"><span class="n">4</span><h3>Size &amp; file type</h3>
      <p class="sub">Tap an option — it fills everything in for you.</p>
    </div>
    <div class="card-b">

      <div class="field">
        <div class="qp" id="s-quick">
          <button type="button" class="q5" aria-pressed="false" data-w="300" data-h="80" data-kb="20" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17c3.5 0 3.5-9 7-9s3.5 9 7 9c1.7 0 2.7-1 3.5-2"/></svg></span>
            <strong>Form Signature</strong><em>300 &times; 80 px &middot; 20 KB</em>
          </button>
          <button type="button" class="q2" aria-pressed="false" data-w="400" data-h="100" data-kb="30" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20M6 8v8M18 8v8"/></svg></span>
            <strong>Wide Signature</strong><em>400 &times; 100 px &middot; 30 KB</em>
          </button>
          <button type="button" class="q3" aria-pressed="false" data-w="200" data-h="60" data-kb="10" data-fmt="jpg">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg></span>
            <strong>Very Small</strong><em>200 &times; 60 px &middot; 10 KB</em>
          </button>
          <button type="button" class="q4" aria-pressed="false" data-w="400" data-h="150" data-kb="0" data-fmt="png" data-transparent="1">
            <span class="qi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 12h18M12 3v18"/></svg></span>
            <strong>See-through PNG</strong><em>400 &times; 150 px &middot; no background</em>
          </button>
        </div>
      </div>

      <div class="field">
        <span class="f-label">Or set the size yourself</span>
        <div class="grid2">
          <div>
            <label class="f-label" for="s-w" style="font-weight:600;color:var(--muted)">Width</label>
            <div class="suffix"><input class="inp" type="number" id="s-w" min="1" max="12000" inputmode="numeric"><i>px</i></div>
          </div>
          <div>
            <label class="f-label" for="s-h" style="font-weight:600;color:var(--muted)">Height</label>
            <div class="suffix"><input class="inp" type="number" id="s-h" min="1" max="12000" inputmode="numeric"><i>px</i></div>
          </div>
        </div>
      </div>

      <div class="field">
        <span class="f-label">Common sizes</span>
        <div class="chips" id="s-presets">
          <?php foreach ($SIGN_PRESETS as $p): ?>
            <button type="button" class="chip" data-w="<?= (int) $p[0] ?>" data-h="<?= (int) $p[1] ?>" aria-pressed="false"><?= (int) $p[0] ?> &times; <?= (int) $p[1] ?></button>
          <?php endforeach; ?>
          <button type="button" class="chip" data-custom="1" aria-pressed="true">My own size</button>
        </div>
      </div>

      <div class="field">
        <label class="tgl">
          <input type="checkbox" id="s-lock" checked>
          <span class="track"></span>
          <span><span class="tl">Keep the shape</span><br><span class="td">Stops the signature looking squashed</span></span>
        </label>
      </div>

      <div class="field">
        <label class="f-label" for="s-target">How big should the file be?</label>
        <select class="inp" id="s-target">
          <option value="0" selected>Best quality (no size limit)</option>
          <?php foreach ($SIZE_PRESETS as $kb): ?>
            <option value="<?= (int) $kb ?>"><?= $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.') . ' MB' : $kb . ' KB' ?> or less</option>
          <?php endforeach; ?>
          <option value="-1">Choose exactly…</option>
        </select>
        <div id="s-target-slider" class="hidden" style="margin-top:14px">
          <div class="rng-head">
            <span class="f-label" style="margin:0">Target size</span>
            <span class="rng-val" id="s-target-val">50 KB</span>
          </div>
          <input type="range" id="s-target-range" min="10" max="2048" step="5" value="50">
        </div>
      </div>

      <div class="field">
        <span class="f-label">File type</span>
        <div class="seg" id="s-format">
          <button type="button" data-v="jpg" aria-pressed="true">JPG</button>
          <button type="button" data-v="jpeg" aria-pressed="false">JPEG</button>
          <button type="button" data-v="png" aria-pressed="false">PNG</button>
        </div>
        <p class="f-help" id="s-format-help"></p>
      </div>

      <div class="field">
        <div class="adv">
          <button type="button" class="adv-t" data-adv="s-adv" aria-expanded="false">
            <span class="ai" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg></span>
            <span>Advanced settings<small>Quality — most people can skip this</small></span>
            <span class="chev" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span>
          </button>
          <div class="adv-b closed" id="s-adv">
            <div class="field" style="padding-top:16px">
              <div class="rng-head">
                <label class="f-label" for="s-quality" style="margin:0">Picture quality</label>
                <span class="rng-val" id="s-quality-val">Quality: 90%</span>
              </div>
              <input type="range" id="s-quality" min="10" max="100" value="90">
            </div>
          </div>
        </div>
      </div>

      <div class="cta">
        <button type="button" class="btn btn-p btn-lg" id="s-run">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2"/><path d="M3 12h18"/></svg>
          Clean My Signature
        </button>
      </div>
      <div class="btn-row" style="margin-top:10px">
        <button type="button" class="btn btn-d" id="s-reset">Start over</button>
      </div>

      <div id="s-busy" class="busy hidden"><span class="spin"></span> Scanning your signature…</div>
      <div id="s-msg"></div>

      <div class="result hidden" id="s-result">
        <h4>
          <span class="rk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
          Done! Your signature is ready
        </h4>
        <div class="meta" id="s-result-meta"></div>
        <div class="btn-row" style="margin-top:15px">
          <button type="button" class="btn btn-p" id="s-dl">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            Download
          </button>
          <button type="button" class="btn" id="s-share">Share</button>
          <button type="button" class="btn" id="s-copy">Copy</button>
        </div>
        <div class="btn-row" style="margin-top:10px">
          <button type="button" class="btn btn-s" data-sdl="jpg">Save as JPG</button>
          <button type="button" class="btn btn-s" data-sdl="jpeg">Save as JPEG</button>
          <button type="button" class="btn btn-s" data-sdl="png">Save as PNG</button>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<footer class="site-foot">
  <div class="wrap">
    &copy; <?= e($YEAR) ?> <?= e($SITE_NAME) ?> Image Tools — everything happens in your browser.<br>
    <code><?= e($gdSummary) ?></code>
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
        // ISO-BMFF container: "....ftyp<brand>". iPhones shoot HEIC by default,
        // so this is a common upload. Safari can decode it, most other browsers
        // cannot — we detect it here so the user gets useful advice instead of
        // a blank "unsupported" message.
        if (b.length >= 12 && b[4] === 0x66 && b[5] === 0x74 && b[6] === 0x79 && b[7] === 0x70) {
          var brand = String.fromCharCode(b[8], b[9], b[10], b[11]).toLowerCase();
          if (['heic', 'heix', 'heim', 'heis', 'hevc', 'hevm', 'hevs', 'mif1', 'msf1'].indexOf(brand) !== -1) {
            resolve('image/heic'); return;
          }
          if (brand === 'avif' || brand === 'avis') { resolve('image/avif'); return; }
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
        // HEIC/AVIF are not in the advertised list, but if the browser happens
        // to decode them (Safari does) there is no reason to refuse the user.
        // Anything that decodes is re-encoded to JPG/PNG on the way out.
        var tryAnyway = (kind === 'image/heic' || kind === 'image/avif');
        if (OK_TYPES.indexOf(kind) === -1 && !tryAnyway) {
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
          if (kind === 'image/heic') {
            reject(new Error('This looks like an iPhone HEIC photo, which this browser cannot open. ' +
              'Easiest fix: on your iPhone go to Settings \u2192 Camera \u2192 Formats and choose ' +
              '"Most Compatible", then take the photo again. For a photo you already have, open it in ' +
              'Photos, tap Share \u2192 Copy Photo, paste it into a new note or message, and save that ' +
              'copy as a JPG.'));
            return;
          }
          if (kind === 'image/avif') {
            reject(new Error('This is an AVIF image, which this browser cannot open. Please save or ' +
              'export it as a JPG or PNG and try again.'));
            return;
          }
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
    $('#r-pv-out').innerHTML = '<div class="pv-empty">Pick a size below</div>';
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
    $('#s-pv-out').innerHTML = '<div class="pv-empty">Choose a cleaning style below</div>';
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
     ONE-TAP PRESETS + ADVANCED DISCLOSURE

     These exist so somebody who just needs "a photo for a form" never has to
     understand resize methods or quality curves: one tap fills in the size,
     the target file size and the format.
     ======================================================================= */

  /** Pick the <option> matching a KB value, else drive the custom slider. */
  function setTargetKb(selectEl, sliderWrap, rangeEl, valEl, kb) {
    if (!kb) { selectEl.value = '0'; sliderWrap.classList.add('hidden'); return; }
    var found = Array.prototype.some.call(selectEl.options, function (o) {
      return parseInt(o.value, 10) === kb;
    });
    if (found) {
      selectEl.value = String(kb);
      sliderWrap.classList.add('hidden');
    } else {
      selectEl.value = '-1';
      sliderWrap.classList.remove('hidden');
      rangeEl.value = String(kb);
      valEl.textContent = kb >= 1024 ? (kb / 1024).toFixed(kb % 1024 ? 1 : 0) + ' MB' : kb + ' KB';
    }
  }
  function pressOnly(root, btn) {
    $$('button', root).forEach(function (b) { b.setAttribute('aria-pressed', String(b === btn)); });
  }
  function setSeg(root, value) {
    $$('button[data-v]', root).forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.getAttribute('data-v') === value));
    });
  }

  $('#r-quick').addEventListener('click', function (ev) {
    var b = ev.target.closest('button');
    if (!b || !R.img) {
      if (!R.img) { toast('Please choose an image first.', 'err'); }
      return;
    }
    pressOnly(this, b);
    if (b.getAttribute('data-orig')) {
      $('#r-w').value = R.w; $('#r-h').value = R.h;
    } else {
      $('#r-w').value = b.getAttribute('data-w');
      $('#r-h').value = b.getAttribute('data-h');
    }
    rMarkCustom($('#r-presets'));
    setSeg($('#r-format'), b.getAttribute('data-fmt') || 'jpg');
    setTargetKb($('#r-target'), $('#r-target-slider'), $('#r-target-range'),
                $('#r-target-val'), parseInt(b.getAttribute('data-kb'), 10) || 0);
    rSetHelp();
    rPreview();
    toast('Settings applied — now press Resize My Image.', 'ok');
  });

  $('#s-quick').addEventListener('click', function (ev) {
    var b = ev.target.closest('button');
    if (!b || !S.img) {
      if (!S.img) { toast('Please choose your signature first.', 'err'); }
      return;
    }
    pressOnly(this, b);
    $('#s-w').value = b.getAttribute('data-w');
    $('#s-h').value = b.getAttribute('data-h');
    rMarkCustom($('#s-presets'));
    var fmt = b.getAttribute('data-fmt') || 'jpg';
    setSeg($('#s-format'), fmt);
    var wantsTransparent = !!b.getAttribute('data-transparent');
    if (wantsTransparent) { setSeg($('#s-mode'), 'scan'); }
    $('#s-rembg').checked = wantsTransparent;
    $('#s-white').checked = !wantsTransparent;
    setTargetKb($('#s-target'), $('#s-target-slider'), $('#s-target-range'),
                $('#s-target-val'), parseInt(b.getAttribute('data-kb'), 10) || 0);
    sSetHelp();
    sPreview();
    toast('Settings applied — now press Clean My Signature.', 'ok');
  });

  $$('.adv-t').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var body = $('#' + btn.getAttribute('data-adv'));
      var open = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', String(!open));
      body.classList.toggle('closed', open);
    });
  });

  /* Colour the filled part of every slider as it moves. */
  function paintRange(el) {
    var min = parseFloat(el.min || 0), max = parseFloat(el.max || 100);
    var pct = ((parseFloat(el.value) - min) / (max - min)) * 100;
    el.style.background = 'linear-gradient(90deg, var(--accent) ' + pct + '%, #dbe3ee ' + pct + '%)';
  }
  $$('input[type=range]').forEach(function (el) {
    paintRange(el);
    el.addEventListener('input', function () { paintRange(el); });
  });

  /* Swap the page accent when the tool changes. */
  $$('.tool-card').forEach(function (card) {
    card.addEventListener('click', function () {
      document.body.setAttribute('data-tool', card.getAttribute('data-tool'));
      $$('input[type=range]').forEach(paintRange);
    });
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
