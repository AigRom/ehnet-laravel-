function initListingPreview() {
    const openBtn = document.getElementById('openListingPreview');
    const modal = document.getElementById('listingPreviewModal');
    const closeBtn = document.getElementById('closeListingPreview');

    if (!openBtn || !modal || !closeBtn) return;
    if (openBtn.dataset.previewInit === '1') return;

    openBtn.dataset.previewInit = '1';

    const titleEl = document.getElementById('title');
    const descEl = document.getElementById('description');
    const catEl = document.getElementById('category_id');
    const priceEl = document.getElementById('price');

    const show = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const hide = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    const textOrDash = (value) => {
        const v = String(value ?? '').trim();
        return v !== '' ? v : '—';
    };

    const getPriceMode = () => {
        return (
            document.querySelector('input[name="price_mode"]:checked')?.value ||
            document.querySelector('input[name="price_normalized"]')?.value ||
            'deal'
        );
    };

    const getCategoryLabel = () => {
        const opt = catEl?.selectedOptions?.[0];
        return textOrDash(opt?.textContent);
    };

    const getLocationLabel = () => {
        const locLabel = document
            .querySelector('input[name="location_label"]')
            ?.value?.trim();

        if (locLabel) return locLabel;

        const locId = document
            .querySelector('input[name="location_id"]')
            ?.value?.trim();

        return locId ? 'Asukoht valitud' : '—';
    };

    const normalizeNumber = (raw) => {
        if (raw == null) return null;

        const s = String(raw).trim();
        if (s === '') return null;

        const n = Number(s.replace(',', '.'));
        return Number.isFinite(n) ? n : null;
    };

    const getPriceLabel = () => {
        const mode = getPriceMode();

        if (mode === 'free') return 'Tasuta';
        if (mode === 'deal') return 'Kokkuleppel';

        const n = normalizeNumber(priceEl?.value);

        if (n === null) return 'Kokkuleppel';
        if (n === 0) return 'Tasuta';

        return n % 1 === 0 ? n.toFixed(0) : n.toFixed(2);
    };

    const getVatText = () => {
        const mode = getPriceMode();
        const vatCheckbox = document.querySelector('input[name="vat_included"]');

        if (mode !== 'price' || !vatCheckbox || !vatCheckbox.checked) {
            return '';
        }

        return 'Hind sisaldab käibemaksu';
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

        return Array.from(new Set(checked)).map((v) => map[v] || v);
    };

    const collectImagesFromGrid = () => {
        const grid = document.querySelector('[data-listing-images-grid]');
        if (!grid) return [];

        return Array.from(grid.querySelectorAll('img'))
            .map((img) => img.getAttribute('src'))
            .filter(Boolean);
    };

    const dispatchPreviewData = () => {
        window.dispatchEvent(
            new CustomEvent('listing-preview-update', {
                detail: {
                    title: textOrDash(titleEl?.value),
                    description: textOrDash(descEl?.value),
                    category: getCategoryLabel(),
                    location: getLocationLabel(),
                    price: getPriceLabel(),
                    vatText: getVatText(),
                    condition: getConditionLabel(),
                    delivery: getDeliveryLabels(),
                },
            })
        );
    };

    const dispatchPreviewImages = () => {
        window.dispatchEvent(
            new CustomEvent('listing-preview-images', {
                detail: {
                    images: collectImagesFromGrid(),
                },
            })
        );
    };

    const openPreview = () => {
        dispatchPreviewData();
        dispatchPreviewImages();
        show();
    };

    openBtn.addEventListener('click', openPreview);
    closeBtn.addEventListener('click', hide);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hide();
        }
    });
}

document.addEventListener('DOMContentLoaded', initListingPreview);
document.addEventListener('livewire:navigated', initListingPreview);

if (!document.body.dataset.listingPreviewEscapeBound) {
    document.body.dataset.listingPreviewEscapeBound = '1';

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;

        const modal = document.getElementById('listingPreviewModal');
        if (!modal) return;

        const isOpen =
            modal.classList.contains('flex') && !modal.classList.contains('hidden');

        if (!isOpen) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
}