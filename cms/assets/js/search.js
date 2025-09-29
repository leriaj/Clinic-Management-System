document.getElementById('search_item').addEventListener('input', function () {
    const searchValue = this.value.trim();

    if (searchValue !== '') {
        fetch(`../assets/php/fetch_item.php?search=${encodeURIComponent(searchValue)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = data.data;
                    document.getElementById('item_name').value = item.item_name;
                    document.getElementById('unit_price').value = item.unit_price;
                    document.getElementById('category').value = item.category;
                    document.getElementById('type_of_medicine').value = item.type_of_medicine;
                    document.getElementById('storage_location').value = item.storage_location;
                    document.getElementById('expiration_date').value = item.oldest_expiration_date;
                } else {
                    alert(data.message);
                    clearFormFields();
                }
            })
            .catch(error => console.error('Error fetching item:', error));
    } else {
        clearFormFields();
    }
});

function clearFormFields() {
    document.getElementById('item_name').value = '';
    document.getElementById('quantity').value = '';
    document.getElementById('unit_price').value = '';
    document.getElementById('category').value = '';
    document.getElementById('type_of_medicine').value = '';
    document.getElementById('storage_location').value = '';
    document.getElementById('expiration_date').value = '';
}