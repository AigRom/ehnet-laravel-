function initListingPreview() {
  const openBtn = document.getElementById('openListingPreview');
  const modal = document.getElementById('listingPreviewModal');
  const closeBtn = document.getElementById('closeListingPreview');
  const editBtn = document.getElementById('editListing');

  if (!openBtn || !modal || !closeBtn || !editBtn) return;

  // ✅ ära init'i mitu korda sama elemendi peal (wire:navigate)
  if (openBtn.dataset.previewInit === '1') return;
  openBtn.dataset.previewInit = '1';

  const titleEl = document.getElementById('title');
  const descEl = document.getElementById('description');
  const catEl = document.getElementById('category_id');
  const priceEl = document.getElementById('price');

  const pTitle = document.getElementById('previewTitle');
  const pDesc = document.getElementById('previewDescription');
  const pCat = document.getElementById('previewCategory');
  const pLoc = document.getElementById('previewLocation');
  const pPrice = document.getElementById('previewPrice');

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
    const locLabel = document.querySelector('input[name="location_label"]')?.value?.trim();
    if (locLabel) return locLabel;

    const locId = document.querySelector('input[name="location_id"]')?.value?.trim();
    return locId ? 'Asukoht valitud' : '—';
  }

  function fillTextPreview() {
    if (pTitle) pTitle.textContent = (titleEl?.value || '').trim() || '—';
    if (pDesc) pDesc.textContent = (descEl?.value || '').trim() || '—';
    if (pCat) pCat.textContent = getCategoryLabel();
    if (pLoc) pLoc.textContent = getLocationLabel();
    if (pPrice) pPrice.textContent = getPriceLabel();
  }

  function sendImagesToAlpineGallery() {
    const formPreview = document.getElementById('imagePreview');
    const imgs = formPreview ? Array.from(formPreview.querySelectorAll('img')) : [];

    const sources = imgs
      .map(img => img?.src)
      .filter(Boolean);

    window.dispatchEvent(new CustomEvent('listing-preview-images', {
      detail: { images: sources }
    }));
  }

  openBtn.addEventListener('click', () => {
    fillTextPreview();
    sendImagesToAlpineGallery();
    show();
  });

  closeBtn.addEventListener('click', hide);
  editBtn.addEventListener('click', hide);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) hide();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('flex')) hide();
  });
}

document.addEventListener('DOMContentLoaded', initListingPreview);
document.addEventListener('livewire:navigated', initListingPreview);
