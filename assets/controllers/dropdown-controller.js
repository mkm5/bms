import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'chevron'];

    static values = {
        side: {
            type: String,
            default: 'left', /** left|right */
        },
    };

    connect() {
        this.orignalParent = this.menuTarget.parentElement;
        this.originalMenu = this.menuTarget;
        this.clickOutsideHandler = this.clickOutside.bind(this);
        document.addEventListener('click', this.clickOutsideHandler);
    }

    disconnect() {
        document.removeEventListener('click', this.clickOutsideHandler);
    }

    toggle(event) {
        const isOpening = this.originalMenu.classList.contains('hidden');

        if (isOpening) this.show(event, this.originalMenu);
        else this.close(event, this.originalMenu);

        if (this.hasChevronTarget) {
            this.chevronTarget.classList.toggle('rotate-180');
        }
    }

    show(event, menu) {
        document.body.appendChild(menu);
        menu.classList.remove('hidden');
        menu.classList.add('fixed');

        const buttonRect = event.currentTarget.getBoundingClientRect();
        const menuWidth = menu.offsetWidth;
        const positionX = this.sideValue === 'right'
            ? buttonRect.left
            : buttonRect.right - menuWidth;
        menu.style.left = `${positionX}px`;
        menu.style.top = `${buttonRect.bottom}px`;

        if (positionX + menuWidth > window.innerWidth) {
            menu.style.left = `${window.innerWidth - menuWidth}px`;
        }
    }

    clickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.close(event, this.originalMenu);
        }
    }

    close(event, menu) {
        if (menu.classList.contains('hidden')) {
            return;
        }

        menu.classList.add('hidden');

        if (this.orignalParent) {
            this.orignalParent.appendChild(menu);
            menu.style.top = null;
            menu.style.left = null;
        }
    }
}
