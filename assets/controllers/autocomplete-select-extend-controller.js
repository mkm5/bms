import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);
        this.syncAllAutocompletes = this.syncAllAutocompletes.bind(this);
        this.component.on('render:finished', this.syncAllAutocompletes);
    }

    syncAllAutocompletes() {
        const elements = this.element.querySelectorAll('.tomselected');
        elements.forEach(selectElement => {
            if (selectElement.tomselect) {
                this.syncTomSelectWithSelect(selectElement.tomselect, selectElement);
            }
        });
    }

    syncTomSelectWithSelect(tomSelect, selectElement) {
        const selectedValues = new Set(
            Array.from(selectElement.options)
                .filter(opt => opt.selected)
                .map(opt => opt.value)
        );

        const tomSelectValues = new Set(tomSelect.items);

        tomSelectValues.forEach(value => {
            if (!selectedValues.has(value)) {
                tomSelect.removeItem(value, true);
            }
        });

        selectedValues.forEach(value => {
            if (!tomSelectValues.has(value)) {
                const option = selectElement.querySelector(`option[value="${value}"]`);
                if (option) {
                    if (!tomSelect.options[value]) {
                        tomSelect.addOption({ value, text: option.textContent });
                    }
                    tomSelect.addItem(value, true);
                }
            }
        });
    }
}
