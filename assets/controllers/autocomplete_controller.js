import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];
    static values = {
        url: String
    };

    callsigns = [];
    dropdown = null;
    selectedIndex = -1;

    connect() {
        this.loadCallsigns();
        this.createDropdown();
        this.bindEvents();
    }

    disconnect() {
        if (this.dropdown) {
            this.dropdown.remove();
        }
        document.removeEventListener('click', this.handleDocumentClick);
    }

    async loadCallsigns() {
        try {
            const response = await fetch(this.urlValue);
            this.callsigns = await response.json();
        } catch (error) {
            console.error('Failed to load callsigns:', error);
        }
    }

    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'autocomplete-dropdown';
        this.dropdown.style.display = 'none';
        this.inputTarget.parentNode.style.position = 'relative';
        this.inputTarget.parentNode.appendChild(this.dropdown);
    }

    bindEvents() {
        this.inputTarget.addEventListener('input', this.onInput.bind(this));
        this.inputTarget.addEventListener('keydown', this.onKeyDown.bind(this));
        this.inputTarget.addEventListener('focus', this.onFocus.bind(this));
        this.handleDocumentClick = this.onDocumentClick.bind(this);
        document.addEventListener('click', this.handleDocumentClick);
    }

    onInput(event) {
        const query = event.target.value.toUpperCase();
        this.showSuggestions(query);
    }

    onFocus(event) {
        const query = event.target.value.toUpperCase();
        if (query.length > 0) {
            this.showSuggestions(query);
        }
    }

    onKeyDown(event) {
        const items = this.dropdown.querySelectorAll('.autocomplete-item');

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                this.updateSelection(items);
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                this.updateSelection(items);
                break;
            case 'Enter':
                if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                    event.preventDefault();
                    this.selectItem(items[this.selectedIndex].textContent);
                }
                break;
            case 'Escape':
                this.hideDropdown();
                break;
        }
    }

    onDocumentClick(event) {
        if (!this.element.contains(event.target)) {
            this.hideDropdown();
        }
    }

    showSuggestions(query) {
        if (query.length === 0) {
            this.hideDropdown();
            return;
        }

        const filtered = this.callsigns
            .filter(cs => cs.toUpperCase().includes(query))
            .slice(0, 10);

        if (filtered.length === 0) {
            this.hideDropdown();
            return;
        }

        this.dropdown.innerHTML = filtered
            .map(cs => `<div class="autocomplete-item">${cs}</div>`)
            .join('');

        this.dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', () => this.selectItem(item.textContent));
            item.addEventListener('mouseenter', () => {
                this.selectedIndex = Array.from(this.dropdown.children).indexOf(item);
                this.updateSelection(this.dropdown.querySelectorAll('.autocomplete-item'));
            });
        });

        this.selectedIndex = -1;
        this.dropdown.style.display = 'block';
    }

    updateSelection(items) {
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === this.selectedIndex);
        });

        if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
            items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    selectItem(value) {
        this.inputTarget.value = value;
        this.hideDropdown();
        this.inputTarget.focus();
    }

    hideDropdown() {
        this.dropdown.style.display = 'none';
        this.selectedIndex = -1;
    }
}
