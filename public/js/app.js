async function loadMovies(search = '') {
  try {
    let url = '/api/movies';
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (params.toString()) url += '?' + params.toString();
    
    const response = await fetch(url);
    const movies = await response.json();

    const container = document.getElementById('moviesContainer');

    if (!response.ok) {
      container.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #ff6b6b;">Error loading movies</p>';
      return;
    }

    if (movies.length === 0) {
      container.innerHTML = '<p style="grid-column: 1/-1; text-align: center;">No movies found matching your search.</p>';
      return;
    }

    container.innerHTML = movies.map(movie => `
      <div class="movie-card">
        ${movie.image_path ? `<div class="movie-image"><img src="${movie.image_path}" alt="${escapeHtml(movie.title)}"></div>` : `<div class="movie-image placeholder">📽️</div>`}
        <div class="movie-header">
          <h3>${escapeHtml(movie.title)}</h3>
          <p class="year">${movie.release_year}</p>
        </div>
        <p class="genre">${escapeHtml(movie.genre)}</p>
        <p class="description">${escapeHtml(movie.description.substring(0, 100))}...</p>
        <div class="rating-info">
          <span class="avg-rating">⭐ ${movie.avg_rating ? parseFloat(movie.avg_rating).toFixed(1) : 'N/A'}</span>
          <span class="review-count">(${movie.review_count || 0} reviews)</span>
        </div>
        <a href="movie-detail.html?id=${movie.id}" class="btn-view">View Details</a>
      </div>
    `).join('');
  } catch (error) {
    console.error('Error loading movies:', error);
    document.getElementById('moviesContainer').innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #ff6b6b;">Error loading movies</p>';
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
  loadMovies();
  
  // Search functionality
  const searchBtn = document.getElementById('searchBtn');
  const clearBtn = document.getElementById('clearBtn');
  const searchInput = document.getElementById('searchInput');
  
  if (searchBtn) {
    searchBtn.addEventListener('click', () => {
      const search = searchInput.value.trim();
      loadMovies(search);
    });
  }
  
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      loadMovies();
    });
  }
  
  // Allow Enter key to search
  if (searchInput) {
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        const search = searchInput.value.trim();
        loadMovies(search);
      }
    });
  }
});
