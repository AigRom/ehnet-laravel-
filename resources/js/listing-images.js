document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('images');
  if (!input) return;

  const preview = document.getElementById('imagePreview');
  const orderField = document.getElementById('images_order');
  if (!preview || !orderField) return;

  const form = input.closest('form');
  const description = form?.querySelector('[name="description"]');

  // Modal elemendid (olemas create.blade.php-s)
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('imageModalImg');
  const modalClose = document.getElementById('imageModalClose');
  const modalRotate = document.getElementById('imageModalRotate');

  // items: { file: File, rotation: 0|90|180|270 }
  let items = [];
  let activeModalIndex = null;

  function rebuildInputFilesFromItems() {
    const dt = new DataTransfer();
    items.forEach(it => dt.items.add(it.file));
    input.files = dt.files;
  }

  function syncOrderField() {
    // Kuna me ehitame input.files alati ümber täpselt samas järjekorras nagu items,
    // on kõige kindlam saata lihtsalt [0..n-1].
    orderField.value = JSON.stringify(items.map((_, i) => i));
  }

  function openModalFromItem(index) {
    if (!modal || !modalImg) return;
    if (!items[index]) return;

    activeModalIndex = index;

    const item = items[index];
    const reader = new FileReader();

    reader.onload = (ev) => {
      modalImg.src = ev.target.result;
      modalImg.alt = item.file.name;
      modalImg.style.transform = `rotate(${item.rotation}deg)`;

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    };

    reader.readAsDataURL(item.file);
  }

  function closeModal() {
    if (!modal || !modalImg) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    modalImg.src = '';
    modalImg.style.transform = 'rotate(0deg)';
    activeModalIndex = null;
  }

  if (modalClose) modalClose.addEventListener('click', (e) => {
    e.preventDefault();
    closeModal();
  });

  if (modal) {
    modal.addEventListener('click', (e) => {
      // sulge ainult taustal klikates, mitte pildil
      if (e.target === modal) closeModal();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });

  if (modalRotate) {
    modalRotate.addEventListener('click', (e) => {
      e.preventDefault();
      if (activeModalIndex === null) return;
      if (!items[activeModalIndex]) return;

      items[activeModalIndex].rotation = (items[activeModalIndex].rotation + 90) % 360;

      // uuenda modal
      if (modalImg) {
        modalImg.style.transform = `rotate(${items[activeModalIndex].rotation}deg)`;
      }

      // uuenda eelvaade
      render();
    });
  }

  function render() {
    preview.innerHTML = '';

    items.forEach((item, index) => {
      const wrap = document.createElement('div');
      wrap.className =
        'relative rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900';
      wrap.draggable = true;
      wrap.dataset.index = String(index);

      const img = document.createElement('img');
      img.className = 'w-full h-24 object-cover cursor-zoom-in';
      img.alt = item.file.name;
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

        // kui sama pilt on modalis avatud, uuenda ka modal
        if (activeModalIndex === index && modalImg) {
          modalImg.style.transform = `rotate(${item.rotation}deg)`;
        }

        render();
      });

      // klikiga suur vaade
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

        // kui modal oli lahti, kohenda activeModalIndex
        if (activeModalIndex !== null) {
          if (activeModalIndex === from) {
            activeModalIndex = to;
          } else if (from < activeModalIndex && to >= activeModalIndex) {
            activeModalIndex -= 1;
          } else if (from > activeModalIndex && to <= activeModalIndex) {
            activeModalIndex += 1;
          }
        }

        rebuildInputFilesFromItems();
        syncOrderField();
        render();
      });

      // Eelvaate pildi laadimine
      const reader = new FileReader();
      reader.onload = (e) => { img.src = e.target.result; };
      reader.readAsDataURL(item.file);

      wrap.appendChild(img);
      wrap.appendChild(badge);
      wrap.appendChild(removeBtn);
      wrap.appendChild(rotateBtn);
      preview.appendChild(wrap);
    });
  }

  function isDuplicate(file) {
    return items.some(it =>
      it.file.name === file.name &&
      it.file.size === file.size &&
      it.file.lastModified === file.lastModified
    );
  }

  // ⬇️ SIIN on “add more images” lahendus: ei kirjuta üle, vaid lisab juurde
  input.addEventListener('change', () => {
    const newlySelected = Array.from(input.files || []);
    if (newlySelected.length === 0) return;

    for (const file of newlySelected) {
      if (!isDuplicate(file)) {
        items.push({ file, rotation: 0 });
      }
    }

    rebuildInputFilesFromItems();
    syncOrderField();
    render();

    // oluline: võimaldab valida sama faili uuesti (muidu change ei pruugi käivituda)
    //input.value = '';
  });

  // Klientpoolne guard: väldi submit'i, kui description liiga lühike (pildid ei kao)
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
});
