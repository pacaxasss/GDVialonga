document.addEventListener('change', function (event) {
  const status = event.target.closest('[data-toggle-score]');
  if (!status) return;

  const visible = status.value === 'finished';
  for (const id of ['score-group', 'scorers-group']) {
    const group = document.getElementById(id);
    if (group) group.style.display = visible ? '' : 'none';
  }
});

document.addEventListener('click', function (event) {
  const button = event.target.closest('[data-delete-id]');
  if (!button) return;

  const deleteField = button.form?.elements.namedItem('delete_id');
  if (deleteField) deleteField.value = button.dataset.deleteId;
  if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) {
    event.preventDefault();
  }
});

document.addEventListener('submit', function (event) {
  const form = event.target.closest('form[data-confirm]');
  if (form && !window.confirm(form.dataset.confirm)) event.preventDefault();
});