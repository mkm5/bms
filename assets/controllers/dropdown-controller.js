import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'chevron'];

    static values = {
        triggerAsZero: { type: Boolean, default: false },
        side: { type: String, default: 'left' /** left|right */ },
    };

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
        const { left, right, top, bottom } = trigger.getBoundingClientRect();

        // Menu must be shown off-screen to measure its width
        menu.style.visibility = 'hidden';
        menu.classList.remove('hidden');
        const menuWidth = menu.offsetWidth;
        const menuHeight = menu.offsetHeight;
        menu.classList.add('hidden');
        menu.style.visibility = '';

        let positionX = this.sideValue === 'right' ? left : right - menuWidth;
        positionX -= (this.triggerAsZeroValue ? menuWidth : 0);
        positionX = Math.max(0, Math.min(positionX, window.innerWidth - menuWidth));

        const spaceBelow = window.innerHeight - bottom;
        const positionY = spaceBelow >= menuHeight ? bottom + 4 : top - menuHeight - 4;

        menu.style.left = `${positionX}px`;
        menu.style.top = `${positionY}px`;
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
