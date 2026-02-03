import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['action'];

    static values = {
        message: {
            type: String,
            default: 'Are you sure?',
        },
    };

    connect() {
        this.confirmBeforeExecution = this.confirmBeforeExecution.bind(this);
        this.elementTargets.forEach(target => {
            console.log(target);
            target.addEventListener('click', this.confirmBeforeExecution);
        });
    }

    disconnect() {
        this.requireConfirmationTargets.forEach(target => {
            target.removeEventListener('click', this.confirmBeforeExecution);
        });
    }

    confirmBeforeExecution(event) {
        const target = event.target;
        const enabled = target.dataset?.confirmationEnabled;
        const message = target.dataset?.confirmationMessage ?? this.messageValue;

        if (enabled === undefined || enabled === 'true') {
            if (!confirm(message)) {
                event?.stopImmediatePropagation();
                event?.preventDefault();
                return false;
            }
        }

        return true;
    }
}
