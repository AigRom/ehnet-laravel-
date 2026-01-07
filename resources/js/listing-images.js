function initListingImages() {
  const input = document.getElementById('images');
  if (!input) return;

  // ✅ ära init'i mitu korda sama elemendi peal (wire:navigate puhul)
  if (input.dataset.imagesInit === '1') return;
  input.dataset.imagesInit = '1';

  const preview = document.getElementById('imagePreview');
  const orderField = document.getElementById('images_order');
  if (!preview || !orderField) return;

  const form = input.closest('form');
  const description = form?.querySelector('[name="description"]');

  const MAX_IMAGES = 10;

  // Modal elemendid (uues markupis)
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('imageModalImg');
  const modalClose = document.getElementById('imageModalClose');
  const modalPrev = document.getElementById('imageModalPrev');
  const modalNext = document.getElementById('imageModalNext');
  const modalCounter = document.getElementById('imageModalCounter');

  // items: { file: File, rotation: number } (rotation ainult thumbs'i jaoks)
  let items = [];
  let activeModalIndex = null;

  function rebuildInputFilesFromItems() {
    const dt = new DataTransfer();
    items.forEach(it => dt.items.add(it.file));
    input.files = dt.files;
  }

  function syncOrderField() {
    orderField.value = JSON.stringify(items.map((_, i) => i));
  }

  function isDuplicate(file) {
    return items.some(it =>
      it.file.name === file.name &&
      it.file.size === file.size &&
      it.file.lastModified === file.lastModified
    );
  }

  function isModalOpen() {
    return !!(modal && modal.classList.contains('flex') && !modal.classList.contains('hidden'));
  }

  function showModalIndex(index) {
    if (!modal || !modalImg) return;
    if (!items.length) return;

    // wrap-around
    if (index < 0) index = items.length - 1;
    if (index >= items.length) index = 0;

    activeModalIndex = index;
    const item = items[activeModalIndex];

    const reader = new FileReader();
    reader.onload = (ev) => {
      modalImg.src = ev.target.result;
      modalImg.alt = item.file.name;

      // ✅ modalis rotate't EI ole
      modalImg.style.transform = 'rotate(0deg)';

      if (modalCounter) {
        modalCounter.textContent = `${activeModalIndex + 1} / ${items.length}`;
      }
    };
    reader.readAsDataURL(item.file);
  }

  function openModalFromItem(index) {
    if (!modal || !modalImg) return;
    if (!items[index]) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    showModalIndex(index);
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
      closeModal();
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

  function addPlusTile() {
    if (items.length >= MAX_IMAGES) return;

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
      input.value = null; // lubab valida sama faili uuesti
      input.click();
    });

    preview.appendChild(addTile);
  }

  function render() {
    preview.innerHTML = '';

    items.forEach((item, index) => {
      const wrap = document.createElement('div');
      wrap.className =
        'relative aspect-square rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900';

      wrap.draggable = true;
      wrap.dataset.index = String(index);

      const img = document.createElement('img');
      img.className = 'w-full h-full object-cover cursor-zoom-in';
      img.alt = item.file.name;

      // rotate ainult thumbil
      img.style.transform = `rotate(${item.rotation}deg)`;

      const badge = document.createElement('div');
      badge.className =
        'absolute top-1 left-1 text-[10px] px-2 py-1 rounded-lg bg-black/60 text-white';
      badge.textContent = index === 0 ? 'Cover' : `#${index + 1}`;

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className =
        'absolute top-1 right-1 text-[12px] w-7 h-7 rounded-lg bg-black/60 text-white flex items-center justify-center';
      removeBtn.textContent = '×';
      removeBtn.title = 'Remove';

      removeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        items.splice(index, 1);

        // modal indeks turvaliseks
        if (activeModalIndex !== null) {
          if (items.length === 0) {
            closeModal();
          } else if (activeModalIndex >= items.length) {
            activeModalIndex = items.length - 1;
            if (isModalOpen()) showModalIndex(activeModalIndex);
          } else if (index === activeModalIndex) {
            if (isModalOpen()) showModalIndex(activeModalIndex);
          }
        }

        rebuildInputFilesFromItems();
        syncOrderField();
        render();
      });

      const rotateBtn = document.createElement('button');
      rotateBtn.type = 'button';
      rotateBtn.className =
        'absolute bottom-1 right-1 text-[10px] px-2 py-1 rounded-lg bg-black/60 text-white';
      rotateBtn.textContent = 'Rotate';
      rotateBtn.title = 'Rotate 90°';

      rotateBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        item.rotation = (item.rotation + 90) % 360;
        render();
      });

      img.addEventListener('click', (e) => {
        e.preventDefault();
        openModalFromItem(index);
      });

      // Drag & drop reorder
      wrap.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', String(index));
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

        const from = Number(e.dataTransfer.getData('text/plain'));
        const to = index;
        if (Number.isNaN(from) || from === to) return;

        const moved = items.splice(from, 1)[0];
        items.splice(to, 0, moved);

        // modal index korrigeerimine
        if (activeModalIndex !== null) {
          if (activeModalIndex === from) activeModalIndex = to;
          else if (from < activeModalIndex && to >= activeModalIndex) activeModalIndex -= 1;
          else if (from > activeModalIndex && to <= activeModalIndex) activeModalIndex += 1;

          if (isModalOpen()) showModalIndex(activeModalIndex);
        }

        rebuildInputFilesFromItems();
        syncOrderField();
        render();
      });

      // Load thumb
      const reader = new FileReader();
      reader.onload = (e) => { img.src = e.target.result; };
      reader.readAsDataURL(item.file);

      wrap.appendChild(img);
      wrap.appendChild(badge);
      wrap.appendChild(removeBtn);
      wrap.appendChild(rotateBtn);
      preview.appendChild(wrap);
    });

    addPlusTile();
  }

  // Add more images (max 10)
  input.addEventListener('change', () => {
    const newlySelected = Array.from(input.files || []);
    if (newlySelected.length === 0) return;

    const freeSlots = MAX_IMAGES - items.length;
    if (freeSlots <= 0) {
      alert(`Maksimaalselt ${MAX_IMAGES} pilti.`);
      rebuildInputFilesFromItems();
      syncOrderField();
      render();
      return;
    }

    let added = 0;

    for (const file of newlySelected) {
      if (items.length >= MAX_IMAGES) break;
      if (!isDuplicate(file)) {
        items.push({ file, rotation: 0 });
        added += 1;
      }
    }

    if (added < newlySelected.length) {
      alert(`Lisati ${added} pilti. Maksimaalne lubatud on ${MAX_IMAGES}. Ülejäänud jäeti välja.`);
    }

    rebuildInputFilesFromItems();
    syncOrderField();
    render();
  });

  // Klientpoolne guard: väldi submit'i, kui description liiga lühike
  if (form && description) {
    form.addEventListener('submit', (e) => {
      const text = (description.value || '').trim();
      if (text.length < 20) {
        e.preventDefault();
        alert('Kirjeldus peab olema vähemalt 20 tähemärki.');
        description.focus();
      }
    });
  }

  // init
  syncOrderField();
  render();
}

document.addEventListener('DOMContentLoaded', initListingImages);
document.addEventListener('livewire:navigated', initListingImages);
