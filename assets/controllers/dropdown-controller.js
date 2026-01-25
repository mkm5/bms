import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['menu', 'chevron', 'button'];

    connect() {
        this.clickOutsideHandler = this.clickOutside.bind(this);
        document.addEventListener('click', this.clickOutsideHandler);
    }

    disconnect() {
        document.removeEventListener('click', this.clickOutsideHandler);
    }

    toggle(event) {
        const isOpening = this.menuTarget.classList.contains('hidden');

        if (isOpening) {
            this.positionMenu(event.currentTarget);
        }

        this.menuTarget.classList.toggle('hidden');

        if (this.hasChevronTarget) {
            this.chevronTarget.classList.toggle('rotate-180');
        }
    }

    positionMenu(trigger) {
        const menu = this.menuTarget;
        const rect = trigger.getBoundingClientRect();

        // Menu must be shown off-screen to measure its width
        menu.style.visibility = 'hidden';
        menu.classList.remove('hidden');
        const menuWidth = menu.offsetWidth;
        menu.classList.add('hidden');
        menu.style.visibility = '';

        menu.style.top = `${rect.bottom + 4}px`;
        menu.style.left = `${rect.right - menuWidth}px`;
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
