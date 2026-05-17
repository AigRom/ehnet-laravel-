export default function supportModal(config = {}) {
    return {
        open: Boolean(config.initialOpen),
        category: config.initialCategory || 'problem',

        scrollY: 0,
        scrollLocked: false,
        previousBodyStyle: {},
        previousHtmlStyle: {},

        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.$nextTick(() => this.lockScroll());
                    return;
                }

                this.unlockScroll();
            });

            if (this.open) {
                this.$nextTick(() => this.lockScroll());
            }
        },

        destroy() {
            this.unlockScroll();
        },

        openModal() {
            this.open = true;
        },

        closeModal() {
            this.open = false;
        },

        lockScroll() {
            if (this.scrollLocked) {
                return;
            }

            this.scrollLocked = true;
            this.scrollY = window.scrollY || document.documentElement.scrollTop || 0;

            this.previousBodyStyle = {
                position: document.body.style.position,
                top: document.body.style.top,
                left: document.body.style.left,
                right: document.body.style.right,
                width: document.body.style.width,
                overflow: document.body.style.overflow,
                touchAction: document.body.style.touchAction,
                overscrollBehavior: document.body.style.overscrollBehavior,
            };

            this.previousHtmlStyle = {
                overflow: document.documentElement.style.overflow,
                overscrollBehavior: document.documentElement.style.overscrollBehavior,
            };

            document.documentElement.style.overflow = 'hidden';
            document.documentElement.style.overscrollBehavior = 'none';

            document.body.style.position = 'fixed';
            document.body.style.top = `-${this.scrollY}px`;
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
            document.body.style.touchAction = 'none';
            document.body.style.overscrollBehavior = 'none';
        },

        unlockScroll() {
            if (!this.scrollLocked) {
                return;
            }

            document.documentElement.style.overflow = this.previousHtmlStyle.overflow || '';
            document.documentElement.style.overscrollBehavior = this.previousHtmlStyle.overscrollBehavior || '';

            document.body.style.position = this.previousBodyStyle.position || '';
            document.body.style.top = this.previousBodyStyle.top || '';
            document.body.style.left = this.previousBodyStyle.left || '';
            document.body.style.right = this.previousBodyStyle.right || '';
            document.body.style.width = this.previousBodyStyle.width || '';
            document.body.style.overflow = this.previousBodyStyle.overflow || '';
            document.body.style.touchAction = this.previousBodyStyle.touchAction || '';
            document.body.style.overscrollBehavior = this.previousBodyStyle.overscrollBehavior || '';

            window.scrollTo(0, this.scrollY);

            this.scrollLocked = false;
        },
    };
}