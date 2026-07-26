/**
 * Pentagon Quest – wire Elementor/contact forms to PHP API
 */
(function () {
  // Resolve /api whether the site is at domain root or under Pentagon Quest UI.
  const scriptEl = document.currentScript
    || document.querySelector('script[src*="forms.js"]');
  const scriptSrc = scriptEl && scriptEl.src;
  let API = '/api';
  if (scriptSrc) {
    API = scriptSrc.replace(/js\/forms\.js.*$/i, 'api');
  } else {
    const path = window.location.pathname || '/';
    const m = path.match(/^(\/Pentagon(?:%20| )Quest(?:%20| )UI)/i);
    if (m) API = decodeURIComponent(m[1]) + '/api';
  }

  function getFields(form) {
    const data = {};
    const fields = form.querySelectorAll('[name^="form_fields"]');
    fields.forEach((el) => {
      const match = el.name.match(/form_fields\[([^\]]+)\]/);
      if (!match) return;
      const key = match[1];
      if (key === 'Honeypot') data.honeypot = el.value;
      else if (key === 'field_c4ab847') data.subject = el.value;
      else data[key] = el.value;
    });
    // Also plain named fields
    ['name', 'email', 'subject', 'message', 'phone'].forEach((k) => {
      const el = form.querySelector(`[name="${k}"]`);
      if (el && el.value) data[k] = el.value;
    });
    return data;
  }

  function showMessage(form, text, ok) {
    let box = form.querySelector('.pq-form-message');
    if (!box) {
      box = document.createElement('div');
      box.className = 'pq-form-message';
      box.style.cssText = 'margin-top:12px;padding:12px 14px;border-radius:8px;font-size:14px';
      form.appendChild(box);
    }
    box.style.background = ok ? '#e7f5ec' : '#fbeceb';
    box.style.color = ok ? '#1a5c3a' : '#8a2f26';
    box.textContent = text;
  }

  async function postJson(url, payload) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    return res.json();
  }

  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    const isContact = form.querySelector('[name="form_fields[message]"]')
      || form.getAttribute('name') === 'New Form' && form.querySelector('[name="form_fields[email]"]') && form.querySelector('textarea');
    const isNewsletter = form.querySelector('[name="form_fields[email]"]')
      && !form.querySelector('[name="form_fields[message]"]')
      && form.querySelector('[type="submit"]');

    // Contact form (has message)
    if (form.querySelector('[name="form_fields[message]"]')) {
      e.preventDefault();
      e.stopPropagation();
      const data = getFields(form);
      try {
        const result = await postJson(`${API}/contact.php`, data);
        showMessage(form, result.message || (result.status === 'success' ? 'Sent!' : 'Failed'), result.status === 'success');
        if (result.status === 'success') form.reset();
      } catch (err) {
        showMessage(form, 'Could not send. Please try again or WhatsApp us.', false);
      }
      return;
    }

    // Newsletter (email only / name+email, no message)
    if (form.querySelector('[name="form_fields[email]"]') && !form.querySelector('textarea')) {
      e.preventDefault();
      e.stopPropagation();
      const data = getFields(form);
      try {
        const result = await postJson(`${API}/newsletter.php`, data);
        showMessage(form, result.message || 'Subscribed!', result.status === 'success');
        if (result.status === 'success') form.reset();
      } catch (err) {
        showMessage(form, 'Subscription failed. Please try again.', false);
      }
    }
  }, true);
})();
