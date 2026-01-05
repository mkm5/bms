import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['menu', 'chevron'];

    connect() {
        this.clickOutsideHandler = this.clickOutside.bind(this);
        document.addEventListener('click', this.clickOutsideHandler);
    }

    disconnect() {
        document.removeEventListener('click', this.clickOutsideHandler);
    }

    toggle(event) {
        event.stopPropagation();

        this.menuTarget.classList.toggle('hidden');

        if (this.hasChevronTarget) {
            this.chevronTarget.classList.toggle('rotate-180');
        }
    }

    clickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    close() {
        if (!this.menuTarget.classList.contains('hidden')) {
            this.menuTarget.classList.add('hidden');
            if (this.hasChevronTarget) {
                this.chevronTarget.classList.remove('rotate-180');
            }
        }
    }
}
