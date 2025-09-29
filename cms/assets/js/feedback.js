function setRating(value) {
  document.getElementById('rating').value = value;
  const stars = document.querySelectorAll('.rate-stars span');
  stars.forEach((star, index) => {
    star.style.color = index < value ? 'orange' : '#ccc';
  });
}
function filterFeedback(value) {
  const url = new URL(window.location.href);
  url.searchParams.set('rating', value);
  window.location.href = url;
}
window.onload = function () {
  if (window.location.search.includes('rating=')) {
    document.getElementById('feedbacks').scrollIntoView({ behavior: 'smooth' });
  }
}

document.querySelectorAll('.feedback-form .stars span').forEach((star, index) => {
  star.addEventListener('mouseover', () => {
    document.documentElement.style.setProperty('--hovered-index', index + 1);
  });
});

function filterFeedbackByDate() {
  const startDate = document.getElementById('start-date').value;
  const endDate = document.getElementById('end-date').value;

  const url = new URL(window.location.href);
  if (startDate) {
      url.searchParams.set('start_date', startDate);
  } else {
      url.searchParams.delete('start_date');
  }
  if (endDate) {
      url.searchParams.set('end_date', endDate);
  } else {
      url.searchParams.delete('end_date');
  }

  window.location.href = url.toString();
}