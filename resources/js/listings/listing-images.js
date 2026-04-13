document.addEventListener('alpine:init', () => {
    Alpine.data('listingImagesCreate', () => ({
        maxImages: 10,
        items: [],
        modalOpen: false,
        activeModalIndex: null,

        get imagesOrderJson() {
            return JSON.stringify(this.items.map((_, index) => index));
        },

        init() {
            // create lehel algseid pilte ei ole
        },

        handleFiles(event) {
            const selected = Array.from(event.target.files || []);
            if (!selected.length) return;

            const freeSlots = this.maxImages - this.items.length;

            if (freeSlots <= 0) {
                alert(`Maksimaalselt ${this.maxImages} pilti.`);
                this.rebuildInputFiles();
                return;
            }

            let added = 0;

            for (const file of selected) {
                if (this.items.length >= this.maxImages) break;
                if (this.isDuplicate(file)) continue;

                this.items.push({
                    uid: this.makeUid(file),
                    file,
                    preview: URL.createObjectURL(file),
                });

                added++;
            }

            if (added < selected.length) {
                alert(`Lisati ${added} pilti. Maksimaalne lubatud on ${this.maxImages}. Ülejäänud jäeti välja.`);
            }

            this.rebuildInputFiles();
        },

        makeUid(file) {
            const random =
                typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function'
                    ? crypto.randomUUID()
                    : `${Date.now()}-${Math.random().toString(36).slice(2)}`;

            return `${file.name}-${file.size}-${file.lastModified}-${random}`;
        },

        isDuplicate(file) {
            return this.items.some((item) =>
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
                if (item.file) {
                    dt.items.add(item.file);
                }
            });

            this.$refs.input.files = dt.files;
        },

        swap(indexA, indexB) {
            if (
                indexA < 0 ||
                indexB < 0 ||
                indexA >= this.items.length ||
                indexB >= this.items.length
            ) {
                return;
            }

            const temp = this.items[indexA];
            this.items[indexA] = this.items[indexB];
            this.items[indexB] = temp;

            this.items = [...this.items];
            this.rebuildInputFiles();
        },

        moveUp(index) {
            if (index <= 0) return;

            this.swap(index, index - 1);

            if (this.modalOpen && this.activeModalIndex === index) {
                this.activeModalIndex = index - 1;
            } else if (this.modalOpen && this.activeModalIndex === index - 1) {
                this.activeModalIndex = index;
            }
        },

        moveDown(index) {
            if (index >= this.items.length - 1) return;

            this.swap(index, index + 1);

            if (this.modalOpen && this.activeModalIndex === index) {
                this.activeModalIndex = index + 1;
            } else if (this.modalOpen && this.activeModalIndex === index + 1) {
                this.activeModalIndex = index;
            }
        },

        remove(index) {
            if (index < 0 || index >= this.items.length) return;

            const removed = this.items[index];

            if (removed?.preview) {
                URL.revokeObjectURL(removed.preview);
            }

            this.items.splice(index, 1);
            this.items = [...this.items];
            this.rebuildInputFiles();

            if (!this.items.length) {
                this.closeModal();
                return;
            }

            if (this.modalOpen) {
                if (this.activeModalIndex === index) {
                    this.activeModalIndex = Math.min(index, this.items.length - 1);
                } else if (index < this.activeModalIndex) {
                    this.activeModalIndex--;
                }
            }
        },

        openModal(index) {
            if (!this.items[index]) return;

            this.activeModalIndex = index;
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
            this.activeModalIndex = null;
        },

        prevModal() {
            if (!this.items.length || this.activeModalIndex === null) return;

            this.activeModalIndex =
                this.activeModalIndex <= 0
                    ? this.items.length - 1
                    : this.activeModalIndex - 1;
        },

        nextModal() {
            if (!this.items.length || this.activeModalIndex === null) return;

            this.activeModalIndex =
                this.activeModalIndex >= this.items.length - 1
                    ? 0
                    : this.activeModalIndex + 1;
        },

        modalImageSrc() {
            if (this.activeModalIndex === null || !this.items[this.activeModalIndex]) {
                return '';
            }

            return this.items[this.activeModalIndex].preview;
        },

        modalCounterText() {
            if (this.activeModalIndex === null || !this.items.length) {
                return '';
            }

            return `${this.activeModalIndex + 1} / ${this.items.length}`;
        },

        destroy() {
            this.items.forEach((item) => {
                if (item.preview) {
                    URL.revokeObjectURL(item.preview);
                }
            });
        }
    }));
});