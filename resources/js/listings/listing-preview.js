// resources/js/listings/listing-preview.js
//
// "Clean" versioon:
// - ei kirjuta enam otse preview DOM elementidesse (previewTitle jne)
// - saadab kogu preview andmestiku ühe CustomEventiga detail-komponendile
// - detail-komponent (mode="preview") kuulab evente ja renderdab Alpine kaudu
//
// Eeldus: <x-listings.detail mode="preview" /> kuulab eventi "listing-preview-update"
// ja pildigalerii kuulab eventi "listing-preview-images" (nagu sul juba olemas).

function initListingPreview() {
  const openBtn = document.getElementById('openListingPreview');
  const modal = document.getElementById('listingPreviewModal');
  const closeBtn = document.getElementById('closeListingPreview');
  const editBtn = document.getElementById('editListing');

  if (!openBtn || !modal || !closeBtn || !editBtn) return;

  // Ära init'i mitu korda (wire:navigate / livewire:navigated)
  if (openBtn.dataset.previewInit === '1') return;
  openBtn.dataset.previewInit = '1';

  // Form field refs
  const titleEl = document.getElementById('title');
  const descEl = document.getElementById('description');
  const catEl = document.getElementById('category_id');
  const priceEl = document.getElementById('price');

  // -------------------------
  // Helpers
  // -------------------------

  const show = () => {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  };

  const hide = () => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  };

  const textOrDash = (s) => {
    const v = String(s ?? '').trim();
    return v !== '' ? v : '—';
  };

  const getCategoryLabel = () => {
    const opt = catEl?.selectedOptions?.[0];
    return textOrDash(opt?.textContent);
  };

  const getLocationLabel = () => {
    // Livewire autocomplete: hoiad labeli hidden inputis
    const locLabel = document.querySelector('input[name="location_label"]')?.value?.trim();
    if (locLabel) return locLabel;

    // fallback: kui ID olemas, aga labeli pole, näita üldist teksti
    const locId = document.querySelector('input[name="location_id"]')?.value?.trim();
    return locId ? 'Asukoht valitud' : '—';
  };

  const normalizeNumber = (raw) => {
    if (raw == null) return null;
    const s = String(raw).trim();
    if (s === '') return null;

    const n = Number(s.replace(',', '.'));
    return Number.isFinite(n) ? n : null;
  };

  // Arvestab price_mode (deal/free/price)
  const getPriceLabel = () => {
    const mode =
      document.querySelector('input[name="price_mode"]:checked')?.value ||
      document.querySelector('input[name="price_normalized"]')?.value ||
      'deal';

    if (mode === 'free') return 'Tasuta';
    if (mode === 'deal') return 'Kokkuleppel';

    const n = normalizeNumber(priceEl?.value);
    if (n === null) return 'Kokkuleppel';
    if (n === 0) return 'Tasuta';

    return n % 1 === 0 ? n.toFixed(0) : n.toFixed(2); // ilma € märgita
  };

  const getConditionLabel = () => {
    const v = document.querySelector('input[name="condition"]:checked')?.value;
    if (v === 'new') return 'Uus';
    if (v === 'used') return 'Kasutatud';
    if (v === 'leftover') return 'Jääk';
    return '—';
  };

  const getDeliveryLabels = () => {
    const map = {
      pickup: 'Järeletulemine',
      seller_delivery: 'Transpordi võimalus',
      courier: 'Saadan kulleriga või pakiautomaati',
      agreement: 'Lepime kokku',
    };

    const checked = Array.from(
      document.querySelectorAll('input[name="delivery_options[]"]:checked')
    )
      .map((el) => el?.value)
      .filter(Boolean);

    // unique säilitades järjekorra
    const uniq = Array.from(new Set(checked));
    return uniq.map((v) => map[v] || v);
  };

  const collectImagesFromFormPreview = () => {
    // "imagePreview" on see grid, kuhu sinu edit-images.js lisab <img> preview’d
    const formPreview = document.getElementById('imagePreview');
    const imgs = formPreview ? Array.from(formPreview.querySelectorAll('img')) : [];
    return imgs.map((img) => img?.src).filter(Boolean);
  };

  // Saada kogu preview andmestik detail-komponendile (Alpine kuulab)
  const dispatchPreviewData = () => {
    const payload = {
      title: textOrDash(titleEl?.value),
      description: textOrDash(descEl?.value),
      category: getCategoryLabel(),
      location: getLocationLabel(),
      price: getPriceLabel(),
      condition: getConditionLabel(),
      delivery: getDeliveryLabels(),
    };

    window.dispatchEvent(new CustomEvent('listing-preview-update', { detail: payload }));
  };

  // Saada pildid galerii Alpine state'ile (sul juba olemas listener detail blade'is)
  const dispatchPreviewImages = () => {
    const sources = collectImagesFromFormPreview();
    window.dispatchEvent(
      new CustomEvent('listing-preview-images', { detail: { images: sources } })
    );
  };

  const openPreview = () => {
    dispatchPreviewData();
    dispatchPreviewImages();
    show();
  };

  // -------------------------
  // Event listeners
  // -------------------------

  openBtn.addEventListener('click', openPreview);
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