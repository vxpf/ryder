document.addEventListener('DOMContentLoaded', function() {
    // Account dropdown functionality
    const accountImage = document.querySelector('.account img');
    if (accountImage) {
        accountImage.addEventListener('click', function () {
            const account = this.closest('.account');
            account.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            const account = document.querySelector('.account');
            if (account && !account.contains(e.target)) {
                account.classList.remove('active');
            }
        });
    }

    // Modal functionality - ONLY for the specific register button in the header
    // Using a much more specific selector to ensure we only get the register button in the header
    const headerRegisterButton = document.querySelector('.topbar .auth-buttons a[href="/register-form"]');
    if (headerRegisterButton) {
        headerRegisterButton.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = document.getElementById('loginModal');
            if (modal) modal.classList.remove('hidden');
        });
    }

    // Modal close functionality
    const modalClose = document.querySelector('.modal-close');
    if (modalClose) {
        modalClose.addEventListener('click', function () {
            const modal = document.getElementById('loginModal');
            if (modal) modal.classList.add('hidden');
        });
    }

    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    }
});
