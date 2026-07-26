/**
 * Wires up every [data-upload-target] file input on the page: on file
 * selection, uploads to admin/upload.php and fills the paired URL text
 * input with the resulting URL. Lets admins either paste a URL or upload
 * an actual file for the same field.
 */
(function () {
  function csrfToken() {
    var el = document.querySelector('input[name="_csrf"]');
    return el ? el.value : '';
  }

  function setStatus(input, text, isError) {
    var status = input.parentElement.querySelector('.pq-upload-status');
    if (!status) return;
    status.textContent = text;
    status.style.color = isError ? '#c0392b' : '#1a5c3a';
  }

  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.dataset.uploadTarget) return;

    var file = input.files && input.files[0];
    if (!file) return;

    var targetField = document.getElementById(input.dataset.uploadTarget);
    var kind = input.dataset.uploadKind || 'image';
    if (!targetField) return;

    setStatus(input, 'Uploading…', false);

    var formData = new FormData();
    formData.append('file', file);
    formData.append('kind', kind);
    formData.append('_csrf', csrfToken());

    fetch('upload.php', { method: 'POST', body: formData })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.status === 'success') {
          targetField.value = res.url;
          setStatus(input, 'Uploaded: ' + res.url, false);
        } else {
          setStatus(input, res.message || 'Upload failed.', true);
        }
      })
      .catch(function () {
        setStatus(input, 'Upload failed. Check your connection.', true);
      });
  });
})();
