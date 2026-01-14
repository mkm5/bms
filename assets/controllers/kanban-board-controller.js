import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import Sortable from 'sortablejs';

export default class extends Controller {
    /** @type { object|null } */
    previousState = null;

    static targets = ['column'];

    async initialize() {
        this.component = await getComponent(this.element);
    }

    connect() {
        this.columnTargets.forEach(this.initializeSortable.bind(this));
    }

    initializeSortable(column) {
        new Sortable(column, {
            group: 'shared',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            delay: 100,
            delayOnTouchOnly: true,
            onStart: this.handlePickUp.bind(this),
            onEnd: this.handleDrop.bind(this),
        });
    }

    handlePickUp(event) {
        const itemEl = event.item;
        const prevItem = itemEl.previousElementSibling;
        const nextItem = itemEl.nextElementSibling;
        this.previousState = {
            prevItemId: prevItem ? prevItem.dataset.ticketId : null,
            nextItemId:  nextItem ? nextItem.dataset.ticketId : null,
        };
    }

    handleDrop(event) {
        const itemEl = event.item;
        const ticket = itemEl.dataset.ticketId;
        const fromColumn = event.from.dataset.columnId;
        const toColumn = event.to.dataset.columnId;

        const prevItem = itemEl.previousElementSibling;
        const prevItemId = prevItem ? prevItem.dataset.ticketId : null;

        const nextItem = itemEl.nextElementSibling;
        const nextItemId = nextItem ? nextItem.dataset.ticketId : null;

        const prev_prevItemId = this.previousState?.prevItemId;
        const prev_nextItemId = this.previousState?.nextItemId;
        this.previousState = null;

        if (fromColumn === toColumn && prevItemId === prev_prevItemId && nextItemId === prev_nextItemId) {
            return;
        }

        console.log('--- Move Complete ---');
        console.log(`Task: ${ticket}`);
        console.log(`Target Column: from ${fromColumn} to ${toColumn}`);
        console.log(`Preceding Task ID: ${prevItemId || 'None (Start of Column)'}`);
        console.log(`Following Task ID: ${nextItemId || 'None (End of Column)'}`);

        this.component.action('ticketMove', {
            ticket,
            targetStatus: toColumn,
            precedingTicket: prevItemId,
        });
    }
}
