document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openListingPreview');
  const modal = document.getElementById('listingPreviewModal');
  const closeBtn = document.getElementById('closeListingPreview');
  const editBtn = document.getElementById('editListing');

  if (!openBtn || !modal || !closeBtn || !editBtn) return;

  // Form fields (need ids in Blade)
  const titleEl = document.getElementById('title');
  const descEl = document.getElementById('description');
  const catEl = document.getElementById('category_id');
  const priceEl = document.getElementById('price');

  // Preview targets (modal elements)
  const pTitle = document.getElementById('previewTitle');
  const pDesc = document.getElementById('previewDescription');
  const pCat = document.getElementById('previewCategory');
  const pLoc = document.getElementById('previewLocation');
  const pPrice = document.getElementById('previewPrice');
  const pImages = document.getElementById('previewImages');

  const thumbs = document.getElementById('imagePreview'); // existing thumbs on form

  function show() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function hide() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  function getCategoryLabel() {
    const opt = catEl?.selectedOptions?.[0];
    return opt?.textContent?.trim() || '—';
  }

  function getPriceLabel() {
    const raw = (priceEl?.value || '').trim();
    if (raw === '') return 'Kokkuleppel';
    return raw;
  }

  function getLocationLabel() {
    // If you add a hidden input 'location_label', preview shows actual label
    const locLabel = document.querySelector('input[name="location_label"]')?.value?.trim();
    if (locLabel) return locLabel;

    // Otherwise fallback:
    const locId = document.querySelector('input[name="location_id"]')?.value?.trim();
    return locId ? 'Asukoht valitud' : '—';
  }

  function cloneThumbs() {
    if (!pImages) return;
    pImages.innerHTML = '';

    if (!thumbs || thumbs.children.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'text-sm text-zinc-500';
      empty.textContent = 'Pilte pole lisatud';
      pImages.appendChild(empty);
      return;
    }

    [...thumbs.children].forEach((node) => {
      const clone = node.cloneNode(true);

      // remove any buttons (X / Rotate) from the preview clone
      clone.querySelectorAll('button').forEach((b) => b.remove());

      // remove drag behaviors/attributes
      clone.removeAttribute('draggable');
      clone.classList.remove('ring-2', 'ring-zinc-400');

      // make it non-interactive in preview
      clone.style.pointerEvents = 'none';

      pImages.appendChild(clone);
    });
  }

  function fillPreview() {
    if (pTitle) pTitle.textContent = (titleEl?.value || '').trim() || '—';
    if (pDesc) pDesc.textContent = (descEl?.value || '').trim() || '—';
    if (pCat) pCat.textContent = getCategoryLabel();
    if (pLoc) pLoc.textContent = getLocationLabel();
    if (pPrice) pPrice.textContent = getPriceLabel();

    cloneThumbs();
  }

  openBtn.addEventListener('click', () => {
    fillPreview();
    show();
  });

  closeBtn.addEventListener('click', hide);
  editBtn.addEventListener('click', hide);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) hide();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') hide();
  });
});
