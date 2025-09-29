function showOverlay() {
    document.getElementById('overlay').style.display = 'block';
}

document.getElementById('close-overlay').addEventListener('click', function() {
    document.getElementById('overlay').style.display = 'none';
});
window.addEventListener('scroll', function() {
    if (window.scrollY > 200) {  
        document.getElementById('overlay').style.display = 'none';
    }
});

document.getElementById('filter-form').addEventListener('submit', function(event) {
    event.preventDefault(); 


    let search = document.querySelector('input[name="search"]').value;
    let startDate = document.querySelector('input[name="start_date"]').value;
    let endDate = document.querySelector('input[name="end_date"]').value;

    let filters = {
        search: search,
        start_date: startDate,
        end_date: endDate
    };


    fetch('path-to-your-php-file', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(filters) 
    })
    .then(response => response.json()) 
    .then(data => {
        let resultsContainer = document.querySelector('.filtered-results');
        resultsContainer.innerHTML = ''; 
        data.forEach(item => {
            let resultItem = document.createElement('div');
            resultItem.classList.add('result-item');
            resultItem.innerHTML = `<p>${item.title}</p><p>${item.description}</p>`; 
            resultsContainer.appendChild(resultItem);
        });

        showOverlay();
    })
    .catch(error => {
        console.error('Error fetching data:', error);
    });
});

document.querySelectorAll('.announcement a').forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const href = this.getAttribute('href');
      const form = document.querySelector('form[action=""]');
      const currentAction = form.getAttribute('action');
      form.setAttribute('action', `${currentAction}${href}`);
      form.submit();
    });
});
