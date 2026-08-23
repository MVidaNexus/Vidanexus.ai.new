/**
 * VidaNexus — Admin AJAX Form Save
 * ---------------------------------------------------------------
 * Lets configuration forms inside the Horizon admin save in place:
 * no full-page reload, no scroll jump, just a toast + green flash on
 * the inputs that were updated.
 *
 * Forms opt-in by declaring the `data-ajax-save` attribute, e.g.
 *   <form data-ajax-save action="..." method="POST">
 * Coupon / user / roadmap CRUD forms intentionally keep the standard
 * redirect flow so newly created rows appear on reload.
 *
 * Behaviour:
 * - The button that triggered the submit shows a spinner while saving.
 * - On 2xx, a SweetAlert toast says "Saved" and inputs get a brief
 *   green pulse.
 * - On any non-success (validation, 4xx, 5xx) the server's flash
 *   message (extracted from the returned HTML) is shown via SweetAlert,
 *   and the form is left untouched.
 * - If credit-cost inputs (`name="credit_cost"`, `name^="tool_credit_cost_"`,
 *   `name="sync_credits"`, `name="ai_analysis_credits"`) were part of the
 *   payload, the global credit chip is refreshed too.
 */
(function () {
    if (window.VidaAdminAjaxSave) return;
    window.VidaAdminAjaxSave = true;

    var CREDIT_INPUT_NAMES = [
        'credit_cost',
        'sync_credits',
        'ai_analysis_credits',
        'unlock_price',
        'bonus_credits',
    ];

    function isAdminTarget(form) {
        if (! form || form.tagName !== 'FORM') return false;
        // Opt-in: form must declare data-ajax-save.
        return form.hasAttribute('data-ajax-save');
    }

    function showSwalToast(opts) {
        if (!window.Swal) {
            console.log('[admin-ajax-save]', opts.title || '', opts.text || '');
            return;
        }
        window.Swal.fire(Object.assign({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            background: '#0f172a',
            color: '#fff',
        }, opts));
    }

    function flashInputs(form) {
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (el.type === 'hidden' || el.disabled) return;
            el.classList.add('admin-ajax-saved');
            setTimeout(function () {
                el.classList.remove('admin-ajax-saved');
            }, 1200);
        });
    }

    function touchesCredits(form) {
        var fd = new FormData(form);
        for (var pair of fd.entries()) {
            var name = pair[0];
            if (CREDIT_INPUT_NAMES.indexOf(name) !== -1) return true;
            if (name.indexOf('tool_credit_cost_') === 0) return true;
            if (name.indexOf('plan_credits_') === 0) return true;
        }
        return false;
    }

    function extractFlash(html, type) {
        try {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            // Laravel session flash is rendered inside the layout — pick the
            // first colored alert containing `success` or `error`.
            var selector = type === 'success'
                ? 'div[style*="rgba(16, 185, 129"]'
                : 'div[style*="rgba(239, 68, 68"]';
            var hit = doc.querySelector(selector);
            return hit ? hit.textContent.trim() : null;
        } catch (e) {
            return null;
        }
    }

    function findSubmitter(form, evt) {
        if (evt && evt.submitter) return evt.submitter;
        return form.querySelector('button[type="submit"], input[type="submit"]');
    }

    document.addEventListener('submit', function (evt) {
        var form = evt.target;
        if (! isAdminTarget(form)) return;

        evt.preventDefault();

        var btn = findSubmitter(form, evt);
        var originalLabel = btn ? btn.innerHTML : null;
        if (btn) {
            btn.disabled = true;
            btn.dataset.origLabel = originalLabel;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
        }

        var activePanel = form.querySelector('.tab-panel.active');
        var formData = new FormData();
        var token = form.querySelector('input[name="_token"]');
        if (token) formData.append('_token', token.value);
        var methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) formData.append('_method', methodInput.value);

        var scope = activePanel || form;
        scope.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name || el.disabled || el.name === '_token' || el.name === '_method') return;
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
            if (el.type === 'file') {
                if (el.files) {
                    for (var i = 0; i < el.files.length; i++) {
                        formData.append(el.name, el.files[i]);
                    }
                }
            } else if (el.tagName === 'TEXTAREA' || el.hasAttribute('data-b64-safe')) {
                // Base64 encode textareas to bypass ModSecurity / WAF keyword rules on shared hosting
                try {
                    var b64Val = btoa(unescape(encodeURIComponent(el.value || '')));
                    formData.append('_b64_' + el.name, b64Val);
                } catch (err) {
                    formData.append(el.name, el.value);
                }
            } else {
                formData.append(el.name, el.value);
            }
        });

        var targetUrl = form.action;
        if (activePanel && activePanel.id && activePanel.id.indexOf('content-') === 0) {
            var tabName = activePanel.id.replace('content-', '');
            // Update URL to match active tab if updating settings
            if (targetUrl.indexOf('/horizon-admin/settings') !== -1) {
                targetUrl = targetUrl.replace(/\/settings(\/[^\/\?]*)?/, '/settings/' + tabName);
            }
        }

        var creditsTouched = touchesCredits(form);
        var method = (form.getAttribute('method') || 'POST').toUpperCase();

        fetch(targetUrl, {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html',
            },
            credentials: 'same-origin',
            body: formData,
            redirect: 'follow',
        })
            .then(function (res) {
                return res.text().then(function (body) {
                    return { ok: res.ok, status: res.status, body: body, contentType: res.headers.get('content-type') || '' };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    var errorMsg = null;
                    if (result.contentType.indexOf('application/json') !== -1) {
                        try {
                            var jsonErr = JSON.parse(result.body);
                            errorMsg = jsonErr.message || jsonErr.error;
                        } catch (e) {}
                    }
                    var msg = errorMsg || extractFlash(result.body, 'error') || ('Save failed (HTTP ' + result.status + ').');
                    if (window.Swal) {
                        window.Swal.fire({ icon: 'error', title: 'Could not save', text: msg, background: '#0f172a', color: '#fff' });
                    } else {
                        alert(msg);
                    }
                    return;
                }

                var successMsg = null;
                if (result.contentType.indexOf('application/json') !== -1) {
                    try {
                        var jsonOk = JSON.parse(result.body);
                        if (jsonOk && jsonOk.message) successMsg = jsonOk.message;
                    } catch (e) {}
                }
                successMsg = successMsg || extractFlash(result.body, 'success') || 'Saved successfully.';
                showSwalToast({ icon: 'success', title: successMsg });
                flashInputs(form);

                if (creditsTouched && window.VidaCredits) {
                    window.VidaCredits.refresh();
                }
            })
            .catch(function (err) {
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Network error', text: err.message || String(err), background: '#0f172a', color: '#fff' });
                } else {
                    alert('Network error: ' + (err.message || err));
                }
            })
            .then(function () {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.origLabel || originalLabel || 'Save';
                    delete btn.dataset.origLabel;
                }
            });
    });
})();
