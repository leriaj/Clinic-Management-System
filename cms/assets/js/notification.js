document.querySelectorAll('.notification-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('notification-dropdown');
    const toggle = document.getElementById('notification-toggle');
    if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
        dropdown.classList.remove('open');
    }
});

document.getElementById('filter-button').addEventListener('click', function () {
    const overlay = document.getElementById('filter-overlay');
    overlay.style.display = 'flex';
});

document.getElementById('close-overlay').addEventListener('click', function () {
    const overlay = document.getElementById('filter-overlay');
    overlay.style.display = 'none';
});
