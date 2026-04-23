@push('styles')
<style>
    [data-exam-guard],
    [data-exam-guard] * {
        -webkit-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
    }

    [data-exam-guard] input,
    [data-exam-guard] textarea,
    [data-exam-guard] [contenteditable="true"] {
        -webkit-user-select: text;
        user-select: text;
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const root = document.querySelector('[data-exam-guard]');
        let allowUnload = false;

        if (!root) {
            return;
        }

        document.documentElement.classList.add('notranslate');
        document.documentElement.setAttribute('translate', 'no');
        document.body.classList.add('notranslate');
        document.body.setAttribute('translate', 'no');

        const isEditableTarget = (target) => !!target?.closest('input, textarea, [contenteditable="true"]');
        const blockEvent = (event) => {
            if (isEditableTarget(event.target)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
        };

        ['copy', 'cut', 'paste', 'contextmenu', 'selectstart', 'dragstart'].forEach((eventName) => {
            document.addEventListener(eventName, blockEvent, true);
        });

        document.addEventListener('keydown', (event) => {
            const ctrlOrMeta = event.ctrlKey || event.metaKey;
            const key = (event.key || '').toLowerCase();

            if (
                event.key === 'F5' ||
                event.key === 'F12' ||
                (ctrlOrMeta && ['r', 'u', 'p', 's', 'a', 'c', 'x', 'v'].includes(key)) ||
                (ctrlOrMeta && event.shiftKey && ['i', 'j', 'c', 'r'].includes(key))
            ) {
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);

        document.addEventListener('click', (event) => {
            const allowTarget = event.target.closest('[data-allow-unload="1"]');

            if (!allowTarget) {
                return;
            }

            allowUnload = true;
        }, true);

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form');

            if (!form || form.dataset.allowUnload !== '1') {
                return;
            }

            allowUnload = true;
        }, true);

        window.addEventListener('beforeunload', (event) => {
            if (allowUnload) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });
    })();
</script>
@endpush
