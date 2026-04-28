document.addEventListener('alpine:init', () => {
    Alpine.data('listingImagesEdit', ({ existing = [], maxImages = 10 } = {}) => ({
        maxImages,
        items: [],
        deletedExistingIds: [],
        modalOpen: false,
        activeModalIndex: null,

        init() {
            this.items = (Array.isArray(existing) ? existing : [])
                .filter((img) => img && img.id && img.src)
                .map((img) => ({
                    uid: `e-${img.id}`,
                    kind: 'existing',
                    id: Number(img.id),
                    src: String(img.src),
                    preview: img.thumb ? String(img.thumb) : String(img.src),
                    name: img.name ? String(img.name) : '',
                }));

            this.rebuildInputFiles();
        },

        visibleItems() {
            return this.items.filter((item) => {
                return item.kind !== 'existing' || !this.deletedExistingIds.includes(item.id);
            });
        },

        get deletedImageIdsJson() {
            return JSON.stringify(this.deletedExistingIds);
        },

        get imagesOrderJson() {
            const visible = this.visibleItems();
            let newIndex = 0;

            return JSON.stringify(
                visible.map((item) => {
                    if (item.kind === 'existing') {
                        return `e:${item.id}`;
                    }

                    return `n:${newIndex++}`;
                })
            );
        },

        handleFiles(event) {
            const selected = Array.from(event.target.files || []);

            if (!selected.length) return;

            let freeSlots = this.maxImages - this.visibleItems().length;

            if (freeSlots <= 0) {
                alert(`Maksimaalselt ${this.maxImages} pilti (olemasolevad + uued kokku).`);
                this.rebuildInputFiles();
                return;
            }

            let added = 0;

            for (const file of selected) {
                if (freeSlots <= 0) break;
                if (this.isDuplicateNew(file)) continue;

                const preview = URL.createObjectURL(file);

                this.items.push({
                    uid: this.makeUid(file),
                    kind: 'new',
                    file,
                    src: preview,
                    preview,
                    name: file.name,
                });

                freeSlots--;
                added++;
            }

            if (added < selected.length) {
                alert(`Lisati ${added} pilti. Ülejäänud jäeti välja (max piirang või duplikaat).`);
            }

            this.rebuildInputFiles();
        },

        makeUid(file) {
            const random =
                typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function'
                    ? crypto.randomUUID()
                    : `${Date.now()}-${Math.random().toString(36).slice(2)}`;

            return `n-${file.name}-${file.size}-${file.lastModified}-${random}`;
        },

        isDuplicateNew(file) {
            return this.items.some((item) =>
                item.kind === 'new' &&
                item.file &&
                item.file.name === file.name &&
                item.file.size === file.size &&
                item.file.lastModified === file.lastModified
            );
        },

        rebuildInputFiles() {
            if (!this.$refs.input) return;

            const dt = new DataTransfer();

            this.items.forEach((item) => {
                if (item.kind === 'new' && item.file) {
                    dt.items.add(item.file);
                }
            });

            this.$refs.input.files = dt.files;
        },

        visibleIndexToRealIndex(visibleIndex) {
            const visible = this.visibleItems();
            const target = visible[visibleIndex];

            if (!target) return -1;

            return this.items.findIndex((item) => item.uid === target.uid);
        },

        swapReal(realA, realB) {
            if (
                realA < 0 ||
                realB < 0 ||
                realA >= this.items.length ||
                realB >= this.items.length
            ) {
                return;
            }

            const temp = this.items[realA];
            this.items[realA] = this.items[realB];
            this.items[realB] = temp;

            this.items = [...this.items];

            this.rebuildInputFiles();
        },

        moveUp(visibleIndex) {
            if (visibleIndex <= 0) return;

            const realA = this.visibleIndexToRealIndex(visibleIndex);
            const realB = this.visibleIndexToRealIndex(visibleIndex - 1);

            this.swapReal(realA, realB);

            if (this.modalOpen && this.activeModalIndex === visibleIndex) {
                this.activeModalIndex = visibleIndex - 1;
            } else if (this.modalOpen && this.activeModalIndex === visibleIndex - 1) {
                this.activeModalIndex = visibleIndex;
            }
        },

        moveDown(visibleIndex) {
            const visible = this.visibleItems();

            if (visibleIndex >= visible.length - 1) return;

            const realA = this.visibleIndexToRealIndex(visibleIndex);
            const realB = this.visibleIndexToRealIndex(visibleIndex + 1);

            this.swapReal(realA, realB);

            if (this.modalOpen && this.activeModalIndex === visibleIndex) {
                this.activeModalIndex = visibleIndex + 1;
            } else if (this.modalOpen && this.activeModalIndex === visibleIndex + 1) {
                this.activeModalIndex = visibleIndex;
            }
        },

        remove(visibleIndex) {
            const visible = this.visibleItems();
            const target = visible[visibleIndex];

            if (!target) return;

            if (target.kind === 'existing') {
                if (!this.deletedExistingIds.includes(target.id)) {
                    this.deletedExistingIds = [...this.deletedExistingIds, target.id];
                }
            } else {
                const realIndex = this.items.findIndex((item) => item.uid === target.uid);

                if (realIndex >= 0) {
                    const removed = this.items[realIndex];

                    if (removed?.preview) {
                        URL.revokeObjectURL(removed.preview);
                    }

                    this.items.splice(realIndex, 1);
                    this.items = [...this.items];
                }
            }

            this.rebuildInputFiles();

            const newVisible = this.visibleItems();

            if (!newVisible.length) {
                this.closeModal();
                return;
            }

            if (this.modalOpen) {
                if (this.activeModalIndex === visibleIndex) {
                    this.activeModalIndex = Math.min(visibleIndex, newVisible.length - 1);
                } else if (visibleIndex < this.activeModalIndex) {
                    this.activeModalIndex--;
                }
            }
        },

        openModal(visibleIndex) {
            const visible = this.visibleItems();

            if (!visible[visibleIndex]) return;

            this.activeModalIndex = visibleIndex;
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
            this.activeModalIndex = null;
        },

        prevModal() {
            const visible = this.visibleItems();

            if (!visible.length || this.activeModalIndex === null) return;

            this.activeModalIndex =
                this.activeModalIndex <= 0
                    ? visible.length - 1
                    : this.activeModalIndex - 1;
        },

        nextModal() {
            const visible = this.visibleItems();

            if (!visible.length || this.activeModalIndex === null) return;

            this.activeModalIndex =
                this.activeModalIndex >= visible.length - 1
                    ? 0
                    : this.activeModalIndex + 1;
        },

        modalImageSrc() {
            const visible = this.visibleItems();

            if (this.activeModalIndex === null || !visible[this.activeModalIndex]) {
                return '';
            }

            return visible[this.activeModalIndex].src || visible[this.activeModalIndex].preview;
        },

        modalCounterText() {
            const visible = this.visibleItems();

            if (this.activeModalIndex === null || !visible.length) {
                return '';
            }

            return `${this.activeModalIndex + 1} / ${visible.length}`;
        }
    }));
});