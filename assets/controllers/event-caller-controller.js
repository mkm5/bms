import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    call(event) {
        const { type, detail } = event.params
        window.dispatchEvent(new CustomEvent(type, { detail }));
    }
}
