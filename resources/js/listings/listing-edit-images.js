// resources/js/listings/listing-edit-images.js

function initListingEditImages() {
  const root = document.querySelector('[data-listing-edit-images]');
  if (!root) return;

  // ära init’i mitu korda (wire:navigate / livewire:navigated)
  if (root.dataset.imagesInit === '1') return;
  root.dataset.imagesInit = '1';

  const input = root.querySelector('#images'); // name="new_images[]"
  const preview = root.querySelector('#imagePreview');

  const orderField = root.querySelector('#images_order');
  const deletedField = root.querySelector('#deleted_image_ids');

  if (!input || !preview || !orderField || !deletedField) return;

  const MAX_IMAGES = 10;

  /**
   * items:
   *  existing: { kind:'existing', id:number, src:string, name?:string }
   *  new:      { kind:'new', file:File }
   */
  let items = [];
  let deletedExistingIds = new Set(); // existing ids marked for delete
  let activeModalIndex = null;

  // --- Modal (kui sul on sama markup nagu create lehel)
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('imageModalImg');
  const modalClose = document.getElementById('imageModalClose');
  const modalPrev = document.getElementById('imageModalPrev');
  const modalNext = document.getElementById('imageModalNext');
  const modalCounter = document.getElementById('imageModalCounter');

  function isModalOpen() {
    return !!(modal && modal.classList.contains('flex') && !modal.classList.contains('hidden'));
  }

  function getVisibleItems() {
    return items.filter((it) => it.kind === 'new' || !deletedExistingIds.has(it.id));
  }

  function showModalIndex(index) {
    if (!modal || !modalImg) return;
    const visible = getVisibleItems();
    if (!visible.length) return;

    // wrap
    if (index < 0) index = visible.length - 1;
    if (index >= visible.length) index = 0;

    activeModalIndex = index;
    const it = visible[activeModalIndex];

    modalImg.style.transform = 'rotate(0deg)';

    if (it.kind === 'existing') {
      modalImg.src = it.src;
      modalImg.alt = it.name || '';
    } else {
      const reader = new FileReader();
      reader.onload = (ev) => {
        modalImg.src = ev.target.result;
        modalImg.alt = it.file?.name || '';
      };
      reader.readAsDataURL(it.file);
    }

    if (modalCounter) modalCounter.textContent = `${activeModalIndex + 1} / ${visible.length}`;
  }

  function openModalFromItemIndex(visibleIndex) {
    if (!modal || !modalImg) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    showModalIndex(visibleIndex);
  }

  function closeModal() {
    if (!modal || !modalImg) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modalImg.src = '';
    modalImg.style.transform = 'rotate(0deg)';
    activeModalIndex = null;
    if (modalCounter) modalCounter.textContent = '';
  }

  if (modalClose) {
    modalClose.addEventListener('click', (e) => {
      e.preventDefault();
      closeModal();
    });
  }

  if (modalPrev) {
    modalPrev.addEventListener('click', (e) => {
      e.preventDefault();
      if (activeModalIndex === null) return;
      showModalIndex(activeModalIndex - 1);
    });
  }

  if (modalNext) {
    modalNext.addEventListener('click', (e) => {
      e.preventDefault();
      if (activeModalIndex === null) return;
      showModalIndex(activeModalIndex + 1);
    });
  }

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (isModalOpen()) closeModal();
      return;
    }
    if (!isModalOpen()) return;
    if (e.key === 'ArrowLeft') {
      if (activeModalIndex !== null) showModalIndex(activeModalIndex - 1);
    }
    if (e.key === 'ArrowRight') {
      if (activeModalIndex !== null) showModalIndex(activeModalIndex + 1);
    }
  });

  // --- Load existing from data-existing JSON
  function loadExistingFromDataset() {
    const raw = root.getAttribute('data-existing');
    if (!raw) return;

    let arr = [];
    try {
      arr = JSON.parse(raw);
    } catch {
      arr = [];
    }

    arr.forEach((x) => {
      if (!x || !x.id || !x.src) return;

      items.push({
        kind: 'existing',
        id: Number(x.id),
        src: String(x.src),
        name: x.name ? String(x.name) : '',
      });
    });
  }

  function countTotalVisible() {
    return getVisibleItems().length;
  }

  function isDuplicateNew(file) {
    // ainult uute sees kontroll
    return items.some((it) =>
      it.kind === 'new' &&
      it.file.name === file.name &&
      it.file.size === file.size &&
      it.file.lastModified === file.lastModified
    );
  }

  function rebuildInputFilesFromItems() {
    // input.files sisaldab ainult NEW faile järjekorras nagu items-is (new itemid)
    const dt = new DataTransfer();
    items.forEach((it) => {
      if (it.kind === 'new') dt.items.add(it.file);
    });
    input.files = dt.files;
  }

  /**
   * ✅ OLULINE: edit backend ootab images_order JSON-i segajärjekorras:
   * ["e:12","n:0","e:15","n:1", ...]
   * kus n: indeks viitab uute piltide järjekorrale (0..)
   */
  function syncHiddenFields() {
    // deleted existing ids
    deletedField.value = JSON.stringify(Array.from(deletedExistingIds));

    const visible = getVisibleItems();

    // anna uutele nähtavatele piltidele n-indeksid nende nähtavas järjekorras
    let nIdx = 0;
    const orderTokens = visible.map((it) => {
      if (it.kind === 'existing') return `e:${it.id}`;
      return `n:${nIdx++}`;
    });

    orderField.value = JSON.stringify(orderTokens);
  }

  function addPlusTile() {
    if (countTotalVisible() >= MAX_IMAGES) return;

    const addTile = document.createElement('button');
    addTile.type = 'button';
    addTile.className =
      'flex aspect-square items-center justify-center rounded-xl border-2 border-dashed ' +
      'border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-500 ' +
      'hover:text-zinc-700 dark:hover:text-zinc-200';

    addTile.innerHTML = `
      <div class="flex flex-col items-center gap-1">
        <div class="text-3xl leading-none">+</div>
        <div class="text-xs">Lisa</div>
      </div>
    `;

    addTile.addEventListener('click', () => {
      input.value = null;
      input.click();
    });

    preview.appendChild(addTile);
  }

  function sameItem(a, b) {
    if (a.kind !== b.kind) return false;
    if (a.kind === 'existing') return a.id === b.id;
    return a.file === b.file;
  }

  function render() {
    preview.innerHTML = '';

    const visible = getVisibleItems();

    visible.forEach((item, visibleIndex) => {
      const wrap = document.createElement('div');
      wrap.className =
        'relative aspect-square rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900';
      wrap.draggable = true;
      wrap.dataset.visibleIndex = String(visibleIndex);

      const img = document.createElement('img');
      img.className = 'w-full h-full object-cover cursor-zoom-in';
      img.alt = item.kind === 'existing' ? (item.name || '') : (item.file?.name || '');
      img.style.transform = 'rotate(0deg)';

      if (item.kind === 'existing') {
        img.src = item.src;
      } else {
        const reader = new FileReader();
        reader.onload = (e) => { img.src = e.target.result; };
        reader.readAsDataURL(item.file);
      }

      img.addEventListener('click', (e) => {
        e.preventDefault();
        if (modal && modalImg) openModalFromItemIndex(visibleIndex);
      });

      const badge = document.createElement('div');
      badge.className =
        'absolute top-1 left-1 text-[10px] px-2 py-1 rounded-lg bg-black/60 text-white';
      badge.textContent = visibleIndex === 0 ? 'Cover' : `#${visibleIndex + 1}`;

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className =
        'absolute top-1 right-1 text-[12px] w-7 h-7 rounded-lg bg-black/60 text-white flex items-center justify-center';
      removeBtn.textContent = '×';
      removeBtn.title = 'Eemalda';

      removeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        const it = visible[visibleIndex];
        if (!it) return;

        if (it.kind === 'existing') {
          deletedExistingIds.add(it.id);
        } else {
          const realIdx = items.findIndex((x) => x.kind === 'new' && x.file === it.file);
          if (realIdx >= 0) items.splice(realIdx, 1);
        }

        if (activeModalIndex !== null && isModalOpen()) {
          const newVisible = getVisibleItems();
          if (newVisible.length === 0) closeModal();
          else if (activeModalIndex >= newVisible.length) showModalIndex(newVisible.length - 1);
          else showModalIndex(activeModalIndex);
        }

        rebuildInputFilesFromItems();
        syncHiddenFields();
        render();
      });

      // Drag & drop reorder
      wrap.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', String(visibleIndex));
        e.dataTransfer.effectAllowed = 'move';
      });

      wrap.addEventListener('dragover', (e) => {
        e.preventDefault();
        wrap.classList.add('ring-2', 'ring-zinc-400');
      });

      wrap.addEventListener('dragleave', () => {
        wrap.classList.remove('ring-2', 'ring-zinc-400');
      });

      wrap.addEventListener('drop', (e) => {
        e.preventDefault();
        wrap.classList.remove('ring-2', 'ring-zinc-400');

        const fromVisible = Number(e.dataTransfer.getData('text/plain'));
        const toVisible = visibleIndex;
        if (Number.isNaN(fromVisible) || fromVisible === toVisible) return;

        const visibleNow = getVisibleItems();
        const fromItem = visibleNow[fromVisible];
        const toItem = visibleNow[toVisible];
        if (!fromItem || !toItem) return;

        const fromReal = items.findIndex((x) => sameItem(x, fromItem));
        const toReal = items.findIndex((x) => sameItem(x, toItem));
        if (fromReal < 0 || toReal < 0) return;

        const moved = items.splice(fromReal, 1)[0];
        items.splice(toReal, 0, moved);

        if (activeModalIndex !== null && isModalOpen()) {
          const newVisible = getVisibleItems();
          if (newVisible.length) {
            activeModalIndex = Math.min(activeModalIndex, newVisible.length - 1);
            showModalIndex(activeModalIndex);
          }
        }

        rebuildInputFilesFromItems();
        syncHiddenFields();
        render();
      });

      wrap.appendChild(img);
      wrap.appendChild(badge);
      wrap.appendChild(removeBtn);

      preview.appendChild(wrap);
    });

    addPlusTile();
  }

  // --- NEW file add
  input.addEventListener('change', () => {
    const selected = Array.from(input.files || []);
    if (!selected.length) return;

    let freeSlots = MAX_IMAGES - countTotalVisible();

    if (freeSlots <= 0) {
      alert(`Maksimaalselt ${MAX_IMAGES} pilti (olemasolevad + uued kokku).`);
      input.value = null;
      rebuildInputFilesFromItems();
      syncHiddenFields();
      render();
      return;
    }

    let added = 0;

    for (const file of selected) {
      if (freeSlots <= 0) break;
      if (isDuplicateNew(file)) continue;

      items.push({ kind: 'new', file });
      freeSlots--;
      added++;
    }

    if (added < selected.length) {
      alert(`Lisati ${added} pilti. Ülejäänud jäeti välja (max piirang / duplikaat).`);
    }

    rebuildInputFilesFromItems();
    syncHiddenFields();
    render();

    // allow selecting same file again
    //input.value = null; //See praegu ei lase uutel piltidel salvestuda vist
  });

  // --- INIT
  loadExistingFromDataset();
  rebuildInputFilesFromItems();
  syncHiddenFields();
  render();
}

document.addEventListener('DOMContentLoaded', initListingEditImages);
document.addEventListener('livewire:navigated', initListingEditImages);
