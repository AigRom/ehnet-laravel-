export default function chatScroll() {
    return {
        shouldAutoScroll: true,

        init() {
            this.$nextTick(() => {
                this.scrollToBottom(false);
            });

            const observer = new MutationObserver(() => {
                if (this.shouldAutoScroll) {
                    this.scrollToBottom(true);
                }
            });

            observer.observe(this.$el, {
                childList: true,
                subtree: true,
            });
        },

        onScroll() {
            const threshold = 120;

            const atBottom =
                this.$el.scrollHeight - this.$el.scrollTop - this.$el.clientHeight < threshold;

            this.shouldAutoScroll = atBottom;
        },

        scrollToBottom(smooth = true) {
            this.$refs.bottom.scrollIntoView({
                behavior: smooth ? 'smooth' : 'auto',
                block: 'end',
            });
        },
    };
}