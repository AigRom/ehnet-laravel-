export default function messageCompose() {
    return {
        files: [],

        onFilesSelected(event) {
            const selectedFiles = Array.from(event.target.files || []);

            this.files.forEach(item => {
                if (item.previewUrl) {
                    URL.revokeObjectURL(item.previewUrl);
                }
            });

            this.files = selectedFiles.slice(0, 5).map((file, index) => {
                const isImage = file.type.startsWith("image/");

                return {
                    id: `${file.name}-${file.size}-${index}`,
                    file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    isImage,
                    previewUrl: isImage ? URL.createObjectURL(file) : null,
                };
            });

            this.syncInputFiles(event.target);
        },

        removeFile(index, inputId) {
            const removed = this.files[index];

            if (removed?.previewUrl) {
                URL.revokeObjectURL(removed.previewUrl);
            }

            this.files.splice(index, 1);

            const input = document.getElementById(inputId);
            if (!input) return;

            const dataTransfer = new DataTransfer();

            this.files.forEach(item => {
                dataTransfer.items.add(item.file);
            });

            input.files = dataTransfer.files;
        },

        syncInputFiles(input) {
            const dataTransfer = new DataTransfer();

            this.files.forEach(item => {
                dataTransfer.items.add(item.file);
            });

            input.files = dataTransfer.files;
        },

        autosizeTextarea(el) {
            if (!el) return;

            el.style.height = "auto";
            el.style.height = `${Math.min(el.scrollHeight, 180)}px`;
        },

        formatSize(bytes) {
            if (!bytes || bytes < 1024) return `${bytes || 0} B`;
            if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        }
    };
}