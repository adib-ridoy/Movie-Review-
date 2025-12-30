async function loadMovieDetail() {
  const params = new URLSearchParams(window.location.search);
  const movieId = params.get('id');

  if (!movieId) {
    window.location.href = 'index.html';
    return;
  }

  try {
    const response = await getMovieById(movieId);
    const movie = await response.json();

    if (!response.ok) {
      window.location.href = 'index.html';
      return;
    }

    const user = getUser();
    const isLoggedIn = !!user;

    const detailContainer = document.getElementById('movieDetail');
    const avgRating = movie.avg_rating ? parseFloat(movie.avg_rating).toFixed(1) : 'N/A';

    let reviewForm = '';
    if (isLoggedIn) {
      reviewForm = `
        <div class="review-form">
          <h3>Share Your Review</h3>
          <form id="reviewForm">
            <label for="rating">Rating (1-5 stars):</label>
            <div class="star-rating">
              <input type="radio" name="rating" value="5" id="star5" required>
              <label for="star5">★</label>
              <input type="radio" name="rating" value="4" id="star4">
              <label for="star4">★</label>
              <input type="radio" name="rating" value="3" id="star3">
              <label for="star3">★</label>
              <input type="radio" name="rating" value="2" id="star2">
              <label for="star2">★</label>
              <input type="radio" name="rating" value="1" id="star1">
              <label for="star1">★</label>
            </div>

            <label for="comment">Your Comment:</label>
            <textarea name="comment" id="comment" rows="4" placeholder="Share your thoughts about this movie..."></textarea>

            <button type="submit" class="btn">Submit Review</button>
          </form>
          <div id="reviewMessage"></div>
        </div>
      `;
    } else {
      reviewForm = `
        <div class="login-prompt">
          <p><a href="login.html">Login</a> or <a href="register.html">Register</a> to leave a review!</p>
        </div>
      `;
    }

    const reviewsHtml = movie.reviews.map(review => `
      <div class="review-card">
        <div class="review-header">
          <strong>${escapeHtml(review.username)}</strong>
          <span class="rating">⭐ ${review.rating}/5</span>
        </div>
        <p class="review-date">${new Date(review.created_at).toLocaleDateString()}</p>
        <p class="review-comment">${escapeHtml(review.comment)}</p>
      </div>
    `).join('');

    detailContainer.innerHTML = `
      <h2>${escapeHtml(movie.title)}</h2>
      ${movie.image_path ? `<div class="movie-detail-image"><img src="${movie.image_path}" alt="${escapeHtml(movie.title)}"></div>` : ''}
      <div class="movie-meta">
        <p><strong>Genre:</strong> ${escapeHtml(movie.genre)}</p>
        <p><strong>Year:</strong> ${movie.release_year}</p>
        <p><strong>Average Rating:</strong> ⭐ ${avgRating} (${movie.review_count || 0} reviews)</p>
      </div>
      <div class="movie-description">
        <p>${escapeHtml(movie.description)}</p>
      </div>

      ${reviewForm}

      <div class="reviews-section">
        <h3>Reviews</h3>
        ${reviewsHtml.length > 0 ? reviewsHtml : '<p>No reviews yet. Be the first to review this movie!</p>'}
      </div>
    `;

    if (isLoggedIn) {
      const reviewForm = document.getElementById('reviewForm');
      reviewForm.addEventListener('submit', submitReview);
    }

  } catch (error) {
    console.error('Error loading movie:', error);
    document.getElementById('movieDetail').innerHTML = '<p style="color: #ff6b6b;">Error loading movie details</p>';
  }
}

async function submitReview(e) {
  e.preventDefault();
  const params = new URLSearchParams(window.location.search);
  const movieId = parseInt(params.get('id'));

  const rating = parseInt(document.querySelector('input[name="rating"]:checked').value);
  const comment = document.getElementById('comment').value;

  try {
    const response = await submitReview(movieId, rating, comment);
    const data = await response.json();
    const messageDiv = document.getElementById('reviewMessage');

    if (response.ok) {
      messageDiv.className = 'success';
      messageDiv.textContent = 'Review submitted successfully!';
      messageDiv.style.display = 'block';
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      messageDiv.className = 'error';
      messageDiv.textContent = data.error;
      messageDiv.style.display = 'block';
    }
  } catch (error) {
    console.error('Error submitting review:', error);
    document.getElementById('reviewMessage').textContent = 'Error submitting review';
    document.getElementById('reviewMessage').style.display = 'block';
  }
}

function performSearch() {
  const search = document.getElementById('searchInput').value.trim();
  
  if (search) {
    window.location.href = 'index.html?search=' + encodeURIComponent(search);
  } else {
    window.location.href = 'index.html';
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
  updateNavigation();
  loadMovieDetail();
  
  // Allow Enter key to search
  const searchInput = document.getElementById('searchInput');
  
  if (searchInput) {
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') performSearch();
    });
  }
});
