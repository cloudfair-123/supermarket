// Small helpers shared across pages: live table filter + delete confirmations.
document.querySelectorAll('input[data-filter]').forEach(function (box) {
  var rows = document.querySelectorAll(box.dataset.filter + ' tbody tr, ' + box.dataset.filter + ' tr:not(:first-child)');
  box.addEventListener('input', function () {
    var q = box.value.toLowerCase();
    rows.forEach(function (r) { r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none'; });
  });
});
document.querySelectorAll('form[data-confirm]').forEach(function (f) {
  f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
});
