import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import Sortable from 'sortablejs';

export default class extends Controller {
    static targets = ['list'];

    async initialize() {
        this.component = await getComponent(this.element);
    }

    connect() {
        this.initializeSortable();
    }

    initializeSortable() {
        new Sortable(this.listTarget, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            handle: '[data-sortable-handle]',
            onEnd: this.handleDrop.bind(this),
        });
    }

    handleDrop(event) {
        const itemEl = event.item;
        const statusId = itemEl.dataset.statusId;
        const prevItem = itemEl.previousElementSibling;
        const precedingStatusId = prevItem ? prevItem.dataset.statusId : null;

        this.component.action('reorder', {
            statusId: parseInt(statusId),
            precedingStatusId: precedingStatusId ? parseInt(precedingStatusId) : null,
        });
    }
}
