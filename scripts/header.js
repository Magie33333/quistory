class DropdownMenu {
    constructor() {
        this.userMenu = document.querySelector('.user-menu');
        this.dropdownMenu = document.querySelector('.dropdown-menu');
        this.init();
    }

    toggleMenu(event) {
        event.stopPropagation();
        this.dropdownMenu.classList.toggle('show');
    }

    closeMenu(event) {
        if (!this.userMenu.contains(event.target)) {
            this.dropdownMenu.classList.remove('show');
        }
    }

    init() {
        this.userMenu.addEventListener('click', (event) => this.toggleMenu(event));
        window.addEventListener('click', (event) => this.closeMenu(event));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    new DropdownMenu();
});