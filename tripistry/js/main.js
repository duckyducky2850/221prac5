/*Client-side helpers:
 - Form validation (client side layer; server side is the real protection)
 - Tab switcher
 - Star rating widget
 - Package compare
 - AJAX package filter*/

'use strict';

/* ── DOM ready ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initStarRatings();
    initFormValidation();
    initFilterForm();
    initCompare();
    initConfirmDelete();
    autoCloseFlash();
    initPasswordStrength(); //added to show password strength on client side
});

/* ── Tabs ───────────────────────────────────────────────────── */
function initTabs() {
    document.querySelectorAll('.tabs').forEach(tabGroup => {
        tabGroup.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tab;
                tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // panels sit as siblings after the .tabs element
                const container = tabGroup.parentElement;
                container.querySelectorAll('.tab-panel').forEach(p => {
                    p.classList.toggle('active', p.dataset.panel === target);
                });
            });
        });
    });
}

/* ── Star rating widget ─────────────────────────────────────── */
function initStarRatings() {
    document.querySelectorAll('.stars-input').forEach(widget => {
        const labels = widget.querySelectorAll('label');
        labels.forEach((lbl, i) => {
            lbl.addEventListener('mouseenter', () => {
                labels.forEach((l, j) => {
                    l.style.color = j >= labels.length - 1 - i
                        ? 'var(--clr-accent)' : 'var(--clr-border)';
                });
            });
            lbl.addEventListener('mouseleave', () => {
                const checked = widget.querySelector('input:checked');
                const val = checked ? parseInt(checked.value) : 0;
                labels.forEach((l, j) => {
                    l.style.color = j >= labels.length - val
                        ? 'var(--clr-accent)' : 'var(--clr-border)';
                });
            });
        });
    });
}

/* ── Form validation ────────────────────────────────────────── */
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', e => {
            let ok = true;
            form.querySelectorAll('[data-required]').forEach(field => {
                const errEl = field.parentElement.querySelector('.form-error');
                if (!field.value.trim()) {
                    field.classList.add('error');
                    if (errEl) errEl.classList.add('visible');
                    ok = false;
                } else {
                    field.classList.remove('error');
                    if (errEl) errEl.classList.remove('visible');
                }
            });
            // Email format check
            form.querySelectorAll('input[type=email]').forEach(field => {
                const errEl = field.parentElement.querySelector('.form-error');
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (field.value && !re.test(field.value)) {
                    field.classList.add('error');
                    if (errEl) { errEl.textContent = 'Enter a valid email.'; errEl.classList.add('visible'); }
                    ok = false;
                }
            });
            // Password confirm check
            const pw  = form.querySelector('#password');
            const pw2 = form.querySelector('#password_confirm');
            if (pw && pw2 && pw.value !== pw2.value) {
                pw2.classList.add('error');
                const errEl = pw2.parentElement.querySelector('.form-error');
                if (errEl) { errEl.textContent = 'Passwords do not match.'; errEl.classList.add('visible'); }
                ok = false;
            }
            if (!ok) e.preventDefault();
        });
    });
}

/* ── AJAX filter form ───────────────────────────────────────── */
function initFilterForm() {
    const form = document.getElementById('package-filter-form');
    if (!form) return;
    const resultsDiv = document.getElementById('package-results');

    const doFilter = () => {
        const params = new URLSearchParams(new FormData(form));
        // update URL without reload
        const url = window.location.pathname + '?' + params.toString();
        history.replaceState(null, '', url);
        // if JS fetch fails, form still submits normally
        if (!resultsDiv) return;
        resultsDiv.style.opacity = '0.5';
        fetch('/api/ajax.php?' + params.toString())
            .then(r => r.text())
            .then(html => {
                resultsDiv.innerHTML = html;
                resultsDiv.style.opacity = '1';
                initCompare(); // re init compare checkboxes on new content
            })
            .catch(() => { resultsDiv.style.opacity = '1'; });
    };

    // Live filter on change for selects/inputs
    form.querySelectorAll('select').forEach(s => s.addEventListener('change', doFilter));
    // Debounce text inputs
    form.querySelectorAll('input[type=text], input[type=number]').forEach(inp => {
        let t;
        inp.addEventListener('input', () => { clearTimeout(t); t = setTimeout(doFilter, 350); });
    });
    form.addEventListener('submit', e => { e.preventDefault(); doFilter(); });
}

/* ── Package comparison ─────────────────────────────────────── */
const compareList = new Set();

function initCompare() {
    document.querySelectorAll('.compare-check').forEach(cb => {
        if (compareList.has(cb.value)) cb.checked = true;

        cb.addEventListener('change', () => {
            const id = cb.value;
            if (cb.checked) {
                if (compareList.size >= 3) {
                    cb.checked = false;
                    showToast('You can compare at most 3 packages at a time.', 'warning');
                    return;
                }
                compareList.add(id);
            } else {
                compareList.delete(id);
            }
            updateCompareBar();
        });
    });
}

function updateCompareBar() {
    let bar = document.getElementById('compare-bar');
    if (compareList.size === 0) {
        if (bar) bar.remove();
        return;
    }
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'compare-bar';
        bar.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:var(--clr-primary);'
            + 'color:#fff;padding:.75rem 1.5rem;display:flex;align-items:center;'
            + 'justify-content:space-between;z-index:200;box-shadow:0 -4px 16px rgba(0,0,0,.15)';
        document.body.appendChild(bar);
    }
    bar.innerHTML = `<span>Comparing ${compareList.size} package(s)</span>
        <div style="display:flex;gap:.5rem">
            <a href="/traveller/packages.php?compare=${[...compareList].join(',')}"
               class="btn btn-accent btn-sm">Compare Now</a>
            <button onclick="clearCompare()" class="btn btn-outline btn-sm"
                    style="color:#fff;border-color:#fff">Clear</button>
        </div>`;
}

function clearCompare() {
    compareList.clear();
    document.querySelectorAll('.compare-check').forEach(cb => cb.checked = false);
    const bar = document.getElementById('compare-bar');
    if (bar) bar.remove();
}

/* ── Confirm delete ─────────────────────────────────────────── */
function initConfirmDelete() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
}

/* ── Toast notification ─────────────────────────────────────── */
function showToast(msg, type = 'info') {
    const t = document.createElement('div');
    t.className = `flash flash--${type}`;
    t.style.cssText = 'position:fixed;top:80px;right:1rem;z-index:300;'
        + 'max-width:320px;box-shadow:var(--shadow-md);animation:fadeIn .2s';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

/* ── Auto-dismiss flash messages ────────────────────────────── */
function autoCloseFlash() {
    document.querySelectorAll('.flash').forEach(f => {
        setTimeout(() => { f.style.transition = 'opacity .5s'; f.style.opacity = '0'; setTimeout(() => f.remove(), 500); }, 4000);
    });
}

/* ── Display password strength to user ────────────────────────────── */
function initPasswordStrength()
{
    const pw = document.getElementById('password');
    if (!pw) return;

    const wrapper = pw.parentElement;
    const bar = document.createElement('div');
    bar.id = 'pw-strength-bar';
    bar.style.cssText = 'height:4px;border-radius:2px;margin-top:6px;transition:width .3s,background .3s;width:0';
    const label = document.createElement('div');
    label.id = 'pw-strength-label';
    label.style.cssText = 'font-size:.75rem;margin-top:3px;color:var(--clr-text-muted)';
    wrapper.appendChild(bar);
    wrapper.appendChild(label);

    pw.addEventListener('input', () => {
        const val = pw.value;
        let score = 0;
        if (val.length >= 8) score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val))  score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { label: '', color: 'transparent', width: '0%'   },
            { label: 'Weak', color: '#e74c3c', width: '25%'  },
            { label: 'Fair', color: '#e67e22', width: '50%'  },
            { label: 'Good', color: '#f1c40f', width: '75%'  },
            { label: 'Strong', color: '#2ecc71', width: '90%'  },
            { label: 'Very strong', color: '#27ae60', width: '100%' },
        ];

        const lvl = levels[Math.min(score, 5)];
        bar.style.width = val.length ? lvl.width : '0%';
        bar.style.background = lvl.color;
        label.textContent = val.length ? lvl.label : '';
        label.style.color = lvl.color;
    });
}