import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static openModals = new Set();

    static targets = ['container'];

    static values = {
        open: {
            type: Boolean,
            default: false,
        },
        id: String,
    };

    connect() {
        this.handleExternalOpen = this.handleExternalOpen.bind(this);
        window.addEventListener('modal:open', this.handleExternalOpen);

        this.handleExternalClose = this.handleExternalClose.bind(this);
        window.addEventListener('modal:close', this.handleExternalClose);
    }

    disconnect() {
        window.removeEventListener('modal:open', this.handleExternalOpen);
        window.removeEventListener('modal:close', this.handleExternalClose);
    }

    open() {
        if (this.constructor.openModals.has(this.idValue)) {
            return;
        }

        this.constructor.openModals.add(this.idValue);
        this.containerTarget.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    close() {
        if (!this.constructor.openModals.has(this.idValue)) {
            return;
        }

        this.constructor.openModals.delete(this.idValue);
        this.containerTarget.classList.add('hidden');
        if (this.constructor.openModals.size === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    handleExternalOpen(event) {
        console.log(event);
        if (event.detail.id === this.idValue) {
            this.open();
        }
    }

    handleExternalClose(event) {
        if (event.detail.id === this.idValue) {
            this.close();
        }
    }
}
