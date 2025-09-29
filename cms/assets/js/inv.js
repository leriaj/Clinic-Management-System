const openAddItem = document.getElementById('open-add-item');
const closeAddItem = document.getElementById('close-add-item');
const addItemOverlay = document.getElementById('add-item-overlay');

openAddItem.addEventListener('click', () => {

    addItemOverlay.style.display = 'flex';

    const expirationDateField = document.getElementById('expiration_date');
    const today = new Date();
    const nextFiveYears = new Date(today.setFullYear(today.getFullYear() + 5));
    const formattedDate = nextFiveYears.toISOString().split('T')[0];
    expirationDateField.value = formattedDate;
});

closeAddItem.addEventListener('click', () => {
    addItemOverlay.style.display = 'none';
});

const openRemoveItem = document.getElementById('open-remove-item');
const closeRemoveItem = document.getElementById('close-remove-item');
const removeItemOverlay = document.getElementById('remove-item-overlay');

openRemoveItem.addEventListener('click', () => {
    removeItemOverlay.style.display = 'flex';
});

closeRemoveItem.addEventListener('click', () => {
    removeItemOverlay.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === addItemOverlay) {
        addItemOverlay.style.display = 'none';
    }
    if (e.target === removeItemOverlay) {
        removeItemOverlay.style.display = 'none';
    }
});