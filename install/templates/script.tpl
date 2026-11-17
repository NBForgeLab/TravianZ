<?php
/**
 * Installer helpers and assets.
 *
 * Everything the wizard needs lives in PHP templates: no separate .css/.js
 * files. index.php calls tz_install_head_assets() inside <head> and
 * tz_install_body_assets() before </body>, so the doctype stays the very
 * first byte of the response (the old version printed markup before it).
 */

function tz_install_checks() {
    $root = dirname(__DIR__, 2);
    $paths = [
        'GameEngine/' => $root . '/GameEngine',
        'GameEngine/Admin/Mods/' => $root . '/GameEngine/Admin/Mods',
        'var/' => $root . '/var',
    ];

    $checks = [];
    $checks[] = [
        'label' => 'PHP ' . PHP_VERSION,
        'ok' => version_compare(PHP_VERSION, '8.1', '>='),
        'detail' => '8.1+',
    ];
    $checks[] = [
        'label' => 'mysqli extension',
        'ok' => extension_loaded('mysqli'),
        'detail' => extension_loaded('mysqli') ? 'loaded' : 'missing',
    ];
    foreach ($paths as $name => $path) {
        $checks[] = [
            'label' => $name . ' writable',
            'ok' => is_dir($path) && is_writable($path),
            'detail' => is_dir($path) ? (is_writable($path) ? 'ok' : 'read-only') : 'missing',
        ];
    }
    $checks[] = [
        'label' => 'Fresh installation',
        'ok' => !file_exists($root . '/var/installed'),
        'detail' => file_exists($root . '/var/installed') ? 'already installed' : 'ok',
    ];

    return $checks;
}

function tz_step_is_revisitable($index, $cur) {
    return $index < $cur && $index <= 1 && $cur < 5;
}

function tz_install_icon($name) {
    $icons = [
        'check' => '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 8 3.5 3.5L13 4.5"/></svg>',
        'info' => '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="8" cy="8" r="6.5"/><path d="M8 7v4M8 4.5v.5"/></svg>',
        'lock' => '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><rect x="3" y="7" width="10" height="7" rx="1.5"/><path d="M5.5 7V5a2.5 2.5 0 0 1 5 0v2"/></svg>',
        'eye' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
        'eye-slash' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>',
    ];
    return isset($icons[$name]) ? $icons[$name] : '';
}

function tz_install_head_assets() {
    echo "<style>\n" . tz_install_css() . "\n</style>\n";
    echo "<script>(function(){try{var t=localStorage.getItem('tz-install-theme');document.documentElement.setAttribute('data-theme',t||(window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'));}catch(e){}})();</script>\n";
}

function tz_install_body_assets() {
    echo "<script>\n" . tz_install_js() . "\n</script>\n";
}

function tz_install_css() {
    return <<<'TZCSS'
:root {
  color-scheme: light;
  --tz-bg: #f1f5f9;
  --tz-surface: #ffffff;
  --tz-surface-2: #f8fafc;
  --tz-text: #0f172a;
  --tz-muted: #475569;
  --tz-subtle: #64748b;
  --tz-border: #dbe2ec;
  --tz-border-strong: #c3cddd;
  --tz-primary: #15803d;
  --tz-primary-hover: #166534;
  --tz-on-primary: #ffffff;
  --tz-header: #0f172a;
  --tz-header-text: #ffffff;
  --tz-header-muted: #cbd5e1;
  --tz-success: #15803d;
  --tz-success-bg: #ecfdf5;
  --tz-warning: #b45309;
  --tz-warning-bg: #fffbeb;
  --tz-danger: #b91c1c;
  --tz-danger-bg: #fef2f2;
  --tz-focus: #2563eb;
  --tz-code-bg: #0f172a;
  --tz-code-text: #e2e8f0;
  --tz-radius: 14px;
  --tz-radius-sm: 10px;
  --tz-font: system-ui, -apple-system, "Segoe UI", Roboto, Tahoma, Arial, sans-serif;
  --tz-mono: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
}

:root[data-theme="dark"] {
  color-scheme: dark;
  --tz-bg: #0b1220;
  --tz-surface: #111c2e;
  --tz-surface-2: #17253b;
  --tz-text: #e8eef8;
  --tz-muted: #b6c4d8;
  --tz-subtle: #93a4bd;
  --tz-border: #273951;
  --tz-border-strong: #3a506c;
  --tz-primary: #34d399;
  --tz-primary-hover: #6ee7b7;
  --tz-on-primary: #052e1a;
  --tz-header: #16233a;
  --tz-header-text: #f1f5f9;
  --tz-header-muted: #b6c4d8;
  --tz-success: #6ee7b7;
  --tz-success-bg: #10372a;
  --tz-warning: #fbbf24;
  --tz-warning-bg: #3a2a0c;
  --tz-danger: #fca5a5;
  --tz-danger-bg: #3b1616;
  --tz-focus: #7dd3fc;
  --tz-code-bg: #081020;
  --tz-code-text: #dbeafe;
}

*, *::before, *::after { box-sizing: border-box; }
html { background: var(--tz-bg); }
body { margin: 0; background: var(--tz-bg); color: var(--tz-text); font: 14px/1.6 var(--tz-font); -webkit-font-smoothing: antialiased; }
a { color: var(--tz-primary); text-decoration: none; }
a:hover { text-decoration: underline; }
:focus-visible { outline: 2px solid var(--tz-focus); outline-offset: 2px; }
button, input, select, textarea { font: inherit; }

.tz-app { max-width: 1100px; margin: 0 auto; padding: 20px 16px 40px; }

.tz-header {
  display: flex; align-items: center; gap: 16px;
  padding: 20px 24px;
  border: 1px solid var(--tz-border); border-top: 3px solid var(--tz-primary);
  border-radius: 18px; background: var(--tz-header); color: var(--tz-header-text);
}
.tz-logo { width: 44px; height: 44px; flex: none; display: grid; place-items: center; border-radius: 12px; background: rgba(255, 255, 255, .09); }
.tz-logo svg { width: 28px; height: 28px; }
.tz-header-text { flex: 1 1 auto; min-width: 0; }
.tz-header h1 { margin: 0; font-size: 20px; font-weight: 650; letter-spacing: .2px; }
.tz-header p { margin: 2px 0 0; font-size: 13px; color: var(--tz-header-muted); }
.tz-header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.tz-icon-btn {
  min-width: 36px; height: 36px; padding: 0 10px;
  border: 1px solid rgba(255, 255, 255, .24); border-radius: var(--tz-radius-sm);
  background: transparent; color: var(--tz-header-text); cursor: pointer; font-size: 13px;
}
.tz-icon-btn:hover { background: rgba(255, 255, 255, .1); }
.tz-icon-btn svg { width: 16px; height: 16px; vertical-align: middle; }
.tz-help { font-size: 13px; color: var(--tz-header-muted); padding: 6px 8px; white-space: nowrap; }
.tz-help:hover { color: var(--tz-header-text); }

.tz-steps { margin: 18px 0 0; }
.tz-steps-list { list-style: none; margin: 0; padding: 0; display: flex; gap: 8px; align-items: stretch; overflow-x: auto; scrollbar-width: thin; }
.tz-step { flex: 1 1 0; min-width: 138px; }
.tz-step-inner {
  display: flex; align-items: center; gap: 10px; height: 100%; padding: 10px 12px;
  border: 1px solid var(--tz-border); border-radius: var(--tz-radius-sm);
  background: var(--tz-surface); color: var(--tz-muted); font-size: 13px;
}
a.tz-step-inner:hover { border-color: var(--tz-primary); text-decoration: none; }
.tz-step-num {
  width: 28px; height: 28px; flex: none; display: grid; place-items: center;
  border: 1px solid var(--tz-border-strong); border-radius: 50%;
  background: var(--tz-surface-2); color: var(--tz-muted); font-size: 12px; font-weight: 700;
}
.tz-step-num svg { width: 14px; height: 14px; }
.tz-step-label { white-space: nowrap; }
.tz-step.is-current .tz-step-inner { background: var(--tz-success-bg); border-color: var(--tz-primary); color: var(--tz-text); font-weight: 600; }
.tz-step.is-current .tz-step-num { background: var(--tz-primary); border-color: var(--tz-primary); color: var(--tz-on-primary); }
.tz-step.is-done .tz-step-inner { color: var(--tz-text); }
.tz-step.is-done .tz-step-num { background: var(--tz-success-bg); border-color: var(--tz-success); color: var(--tz-success); }
.tz-progress { height: 6px; margin: 10px 0 18px; border-radius: 999px; background: var(--tz-border); overflow: hidden; }
.tz-progress span { display: block; height: 100%; border-radius: 999px; background: var(--tz-primary); }

.tz-main { min-width: 0; }
.tz-step-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin: 0 0 14px; }
.tz-eyebrow { margin: 0; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: var(--tz-subtle); }
.tz-step-head h2 { margin: 0; font-size: 20px; font-weight: 650; }

.card, .tz-card { background: var(--tz-surface); border: 1px solid var(--tz-border); border-radius: var(--tz-radius); padding: 18px; margin: 0 0 14px; }
.tz-card h3 { margin: 0 0 10px; font-size: 15px; font-weight: 650; }
.tz-card-title { display: block; margin: 0 0 12px; font-size: 12px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--tz-muted); }
.grid-2, .tz-grid-2 { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.tz-grid-2 { grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); align-items: start; }
.grid-2 > .card, .grid-2 > .tz-card { margin-bottom: 0; }
.tz-stack { display: grid; gap: 10px; }
.tz-note { font-size: 12px; color: var(--tz-subtle); }
.tz-muted { color: var(--tz-muted); }

label { display: block; margin: 0 0 5px; font-size: 12px; font-weight: 600; color: var(--tz-muted); }
input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="submit"]):not([type="button"]),
select, textarea {
  width: 100%; padding: 9px 12px;
  border: 1px solid var(--tz-border-strong); border-radius: var(--tz-radius-sm);
  background: var(--tz-surface); color: var(--tz-text); font-size: 14px;
}
.input { width: 100%; }
input:focus-visible, select:focus-visible, textarea:focus-visible { outline: 2px solid var(--tz-focus); outline-offset: 1px; border-color: var(--tz-focus); }
input:disabled, select:disabled, textarea:disabled { background: var(--tz-surface-2); color: var(--tz-subtle); cursor: not-allowed; opacity: .8; }
input[aria-invalid="true"], select[aria-invalid="true"] { border-color: var(--tz-danger); }

.tz-switch { display: inline-flex; align-items: center; gap: 10px; margin: 0 0 4px; cursor: pointer; font-size: 13px; font-weight: 400; color: var(--tz-text); }
.tz-switch input { position: absolute; width: 1px; height: 1px; opacity: 0; }
.tz-switch-track { position: relative; width: 38px; height: 22px; flex: none; border-radius: 999px; background: var(--tz-border-strong); transition: background .15s; }
.tz-switch-track::after { content: ""; position: absolute; inset-block-start: 3px; inset-inline-start: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: transform .15s; }
.tz-switch input:checked + .tz-switch-track { background: var(--tz-primary); }
.tz-switch input:checked + .tz-switch-track::after { transform: translateX(16px); }
html[dir="rtl"] .tz-switch input:checked + .tz-switch-track::after { transform: translateX(-16px); }
.tz-switch input:focus-visible + .tz-switch-track { outline: 2px solid var(--tz-focus); outline-offset: 2px; }
.tz-switch-text { color: var(--tz-subtle); font-size: 12px; }
select.tz-select-native { position: absolute; width: 1px; height: 1px; padding: 0; opacity: 0; pointer-events: none; border: 0; }

.btn, .tz-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 10px 18px; border: 1px solid transparent; border-radius: var(--tz-radius-sm);
  background: var(--tz-primary); color: var(--tz-on-primary);
  font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none;
}
.btn:hover, .tz-btn:hover { background: var(--tz-primary-hover); color: var(--tz-on-primary); text-decoration: none; }
.btn:disabled, .tz-btn:disabled { opacity: .6; cursor: not-allowed; }
.tz-btn-secondary { background: transparent; color: var(--tz-muted); border-color: var(--tz-border-strong); }
.tz-btn-secondary:hover { background: var(--tz-surface-2); color: var(--tz-text); }
.tz-actions {
  position: sticky; bottom: 0;
  display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
  margin: 18px 0 0; padding: 12px 16px;
  border: 1px solid var(--tz-border); border-radius: var(--tz-radius); background: var(--tz-surface);
}
.tz-actions-row { display: flex; gap: 10px; flex-wrap: wrap; }
.tz-actions-note { font-size: 12px; color: var(--tz-subtle); }

.tz-tabs { display: flex; gap: 4px; flex-wrap: wrap; margin: 0 0 14px; border-bottom: 1px solid var(--tz-border); }
.tz-tab {
  padding: 9px 14px; border: 0; border-bottom: 2px solid transparent; border-radius: 8px 8px 0 0;
  background: transparent; color: var(--tz-muted); font-size: 13px; font-weight: 600; cursor: pointer;
}
.tz-tab:hover { background: var(--tz-surface-2); }
.tz-tab.is-active { border-bottom-color: var(--tz-primary); color: var(--tz-primary); }
.tz-tabs-ready .card[data-group]:not(.is-active) { display: none; }

.tz-alert { padding: 14px 16px; margin: 0 0 14px; border: 1px solid var(--tz-border); border-radius: var(--tz-radius-sm); background: var(--tz-surface-2); color: var(--tz-text); }
.tz-alert-error { background: var(--tz-danger-bg); border-color: var(--tz-danger); color: var(--tz-danger); }
.tz-alert-warning { background: var(--tz-warning-bg); border-color: var(--tz-warning); color: var(--tz-warning); }
.tz-alert-success { background: var(--tz-success-bg); border-color: var(--tz-success); color: var(--tz-success); }

/* Spacing and layout utilities */
.tz-lead { margin: 0 0 10px; }
.tz-mt-1 { margin-top: 4px; }
.tz-mt-2 { margin-top: 8px; }
.tz-mt-3 { margin-top: 12px; }
.tz-mt-4 { margin-top: 14px; }
.tz-mb-2 { margin-bottom: 8px; }
.tz-mb-3 { margin-bottom: 12px; }
.tz-mb-4 { margin-bottom: 16px; }
.tz-disclaimer-list { margin: 0; padding-inline-start: 18px; line-height: 1.7; font-size: 13px; }
.tz-field-group { display: grid; gap: 10px; margin-top: 12px; }
.tz-field-col { display: flex; flex-direction: column; gap: 4px; }
.tz-progress-wdata { max-width: 600px; }
.tz-status-text { font-weight: 600; margin: 10px 0; }
.tz-countdown { display: none; margin-top: 10px; color: var(--tz-success); font-weight: 600; }
.tz-countdown.is-visible { display: block; }
.is-hidden { display: none; }

/* Password show/hide toggle */
.tz-password-wrap { position: relative; display: flex; align-items: center; width: 100%; }
.tz-password-wrap input { padding-inline-end: 38px; width: 100%; }
.tz-pw-toggle {
  position: absolute; inset-inline-end: 6px; top: 50%; transform: translateY(-50%);
  width: 28px; height: 28px; padding: 0;
  display: inline-flex; align-items: center; justify-content: center;
  border: 0; border-radius: 6px; background: transparent;
  color: var(--tz-subtle); cursor: pointer;
}
.tz-pw-toggle:hover { color: var(--tz-text); background: var(--tz-surface-2); }
.tz-pw-toggle:focus-visible { outline: 2px solid var(--tz-focus); outline-offset: 2px; color: var(--tz-text); }
.tz-pw-toggle .tz-pw-hide { display: none; }
.tz-pw-toggle.is-visible .tz-pw-show { display: none; }
.tz-pw-toggle.is-visible .tz-pw-hide { display: block; }
.tz-pw-icon { width: 16px; height: 16px; }
.tz-code { font-family: var(--tz-mono); font-size: 13px; line-height: 1.7; padding: 12px 14px; border-radius: var(--tz-radius-sm); background: var(--tz-code-bg); color: var(--tz-code-text); white-space: pre-wrap; word-break: break-word; overflow-x: auto; }
.tz-checks { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
.tz-check { display: flex; align-items: center; gap: 10px; padding: 9px 11px; border-radius: var(--tz-radius-sm); background: var(--tz-surface-2); font-size: 13px; }
.tz-dot { width: 10px; height: 10px; flex: none; border-radius: 50%; background: var(--tz-success); }
.tz-check.is-bad .tz-dot { background: var(--tz-warning); }
.tz-check-detail { margin-inline-start: auto; color: var(--tz-subtle); font-size: 12px; }
.tz-progress-track { height: 12px; border-radius: 999px; background: var(--tz-border); overflow: hidden; }
.tz-progress-track > div { height: 100%; width: 0; background: var(--tz-primary); transition: width .2s; }
.tz-log { max-height: 200px; overflow: auto; margin-top: 10px; }

.tz-footer {
  display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;
  margin-top: 28px; padding: 14px 18px;
  border: 1px solid var(--tz-border); border-radius: var(--tz-radius);
  background: var(--tz-surface); color: var(--tz-subtle); font-size: 12px;
}
.tz-footer b { color: var(--tz-text); }

@media (max-width: 820px) { .grid-2, .tz-grid-2 { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 640px) {
  .tz-app { padding: 12px 12px 32px; }
  .tz-header { padding: 16px; gap: 12px; flex-wrap: wrap; }
  .tz-header h1 { font-size: 17px; }
  .tz-header-actions { width: 100%; }
  .tz-steps-list { gap: 6px; }
  .tz-step { flex: 0 0 auto; min-width: 124px; }
  .tz-step-inner { padding: 8px 10px; }
  .card, .tz-card { padding: 14px; }
  .tz-actions { position: static; }
}
@media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition-duration: .01ms; animation-duration: .01ms; } }
TZCSS;
}

function tz_install_js() {
    return <<<'TZJS'
(function () {
  'use strict';

  var root = document.documentElement;
  var TAB_LABELS = {
    server: 'Server', database: 'Database', gameplay: 'Gameplay',
    features: 'Features', advanced: 'Advanced'
  };

  var themeBtn = document.getElementById('tzThemeToggle');
  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('tz-install-theme', next); } catch (e) {}
      themeBtn.setAttribute('aria-pressed', String(next === 'dark'));
    });
    themeBtn.setAttribute('aria-pressed', String(root.getAttribute('data-theme') === 'dark'));
  }

  function wireLabels() {
    var used = Object.create(null);
    Array.prototype.forEach.call(document.querySelectorAll('input, select, textarea'), function (el, i) {
      if (el.type === 'hidden') return;
      var id = el.id;
      if (!id || used[id]) { id = 'tz-field-' + i; el.id = id; }
      used[id] = true;
      var container = el.closest('.tz-password-wrap') ? el.closest('.tz-password-wrap').parentNode : el.parentNode;
      if (!container) return;
      var label = null;
      Array.prototype.forEach.call(container.children, function (child) {
        if (!label && child.tagName === 'LABEL' && !child.classList.contains('tz-switch')) label = child;
      });
      if (label && !label.hasAttribute('for')) label.setAttribute('for', id);
    });
  }

  function buildSwitches() {
    Array.prototype.forEach.call(document.querySelectorAll('select'), function (sel) {
      if (sel.options.length !== 2) return;
      var values = [sel.options[0].value, sel.options[1].value].sort().join('|');
      if (values !== 'false|true') return;

      var checked = sel.value === 'true';
      var wrap = document.createElement('label');
      wrap.className = 'tz-switch';
      var input = document.createElement('input');
      input.type = 'checkbox';
      input.checked = checked;
      var track = document.createElement('span');
      track.className = 'tz-switch-track';
      var text = document.createElement('span');
      text.className = 'tz-switch-text';
      text.textContent = checked ? 'On' : 'Off';
      wrap.appendChild(input);
      wrap.appendChild(track);
      wrap.appendChild(text);

      input.addEventListener('change', function () {
        sel.value = input.checked ? 'true' : 'false';
        text.textContent = input.checked ? 'On' : 'Off';
      });

      sel.classList.add('tz-select-native');
      sel.parentNode.insertBefore(wrap, sel);
    });
  }

  function buildTabs() {
    var cards = Array.prototype.slice.call(document.querySelectorAll('.card[data-group]'));
    if (!cards.length) return;
    var groups = [];
    cards.forEach(function (card) {
      if (groups.indexOf(card.dataset.group) === -1) groups.push(card.dataset.group);
    });

    var bar = document.createElement('div');
    bar.className = 'tz-tabs';
    bar.setAttribute('role', 'tablist');
    groups.forEach(function (group, i) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'tz-tab' + (i === 0 ? ' is-active' : '');
      button.setAttribute('role', 'tab');
      button.setAttribute('data-group', group);
      button.textContent = TAB_LABELS[group] || group;
      button.addEventListener('click', function () { showGroup(group, true); });
      bar.appendChild(button);
    });

    var form = document.getElementById('dataform');
    var host = form ? form.parentNode : cards[0].parentNode;
    host.insertBefore(bar, form || cards[0]);
    document.body.classList.add('tz-tabs-ready');

    function showGroup(group, scroll) {
      Array.prototype.forEach.call(bar.children, function (button) {
        var active = button.getAttribute('data-group') === group;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-selected', String(active));
      });
      cards.forEach(function (card) { card.classList.toggle('is-active', card.dataset.group === group); });
      if (scroll) bar.scrollIntoView({ block: 'nearest' });
    }

    showGroup(groups[0], false);
  }

  window.refresh = function (tz) {
    var parts = String(tz).split(',');
    window.location.href = '?s=1&t=' + encodeURIComponent(parts[0]);
  };
  window.proceed = function () {
    var button = document.getElementById('Submit');
    if (!button) return true;
    setTimeout(function () { button.disabled = true; }, 200);
    button.textContent = button.getAttribute('data-processing') || 'Processing...';
    return true;
  };

  function buildPasswordToggles() {
    Array.prototype.forEach.call(document.querySelectorAll('.tz-password-wrap'), function (wrap) {
      var input = wrap.querySelector('input');
      var btn = wrap.querySelector('.tz-pw-toggle');
      if (!input || !btn) return;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var isPw = input.type === 'password';
        input.type = isPw ? 'text' : 'password';
        btn.classList.toggle('is-visible', isPw);
        var label = isPw ? 'Hide password' : 'Show password';
        btn.setAttribute('aria-label', label);
        btn.setAttribute('title', label);
        btn.setAttribute('aria-pressed', String(isPw));
        if (e.detail !== 0) { input.focus(); }
      });
    });
  }

  function init() {
    buildSwitches();
    wireLabels();
    buildTabs();
    buildPasswordToggles();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
TZJS;
}
