// Admin Dashboard Functions

// Check if user is logged in and is admin on page load
window.addEventListener('load', () => {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    if (!user || !user.is_admin) {
        alert('You do not have permission to access this page.');
        window.location.href = 'index.html';
        return;
    }

    // Initialize dashboard
    loadDashboardStats();
    loadMovies();
});

// Show/Hide sections
function showSection(sectionId) {
    event.preventDefault();
    
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));
    
    // Remove active class from all menu items
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => item.classList.remove('active'));
    
    // Show selected section
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.add('active');
    }
    
    // Add active class to clicked menu item
    event.target.classList.add('active');
    
    // Load data based on section
    if (sectionId === 'manage-movies') {
        loadMovies();
    } else if (sectionId === 'stats') {
        loadStatistics();
    }
    
    // Scroll to top
    document.querySelector('.admin-content').scrollTop = 0;
}

// Load Dashboard Statistics
async function loadDashboardStats() {
    try {
        const response = await fetch('/api/movies');
        const movies = await response.json();
        
        document.getElementById('totalMovies').textContent = movies.length;
        
        // Display recent movies
        const recentMovies = movies.slice(0, 5);
        const recentMoviesList = document.getElementById('recentMoviesList');
        
        if (recentMovies.length === 0) {
            recentMoviesList.innerHTML = '<p>No movies added yet</p>';
        } else {
            recentMoviesList.innerHTML = recentMovies.map(movie => `
                <div class="movie-item">
                    <div>
                        <h4>${escapeHtml(movie.title)}</h4>
                        <p>${movie.genre} • ${movie.release_year}</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="color: #ffb800;">⭐ ${movie.avg_rating ? movie.avg_rating.toFixed(1) : 'N/A'}</p>
                    </div>
                </div>
            `).join('');
        }
        
        // Calculate stats (would need API endpoints for these)
        document.getElementById('totalReviews').textContent = calculateTotalReviews(movies);
        document.getElementById('avgRating').textContent = calculateAverageRating(movies);
        document.getElementById('totalUsers').textContent = '...';
        
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

// Load and display all movies in manage section
async function loadMovies() {
    try {
        const response = await fetch('/api/movies');
        const movies = await response.json();
        
        const moviesTable = document.getElementById('moviesTable');
        
        if (movies.length === 0) {
            moviesTable.innerHTML = '<p style="padding: 2rem; text-align: center;">No movies found</p>';
            return;
        }
        
        const tableHTML = `
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Genre</th>
                        <th>Year</th>
                        <th>Rating</th>
                        <th>Reviews</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${movies.map(movie => `
                        <tr>
                            <td>${escapeHtml(movie.title)}</td>
                            <td>${escapeHtml(movie.genre)}</td>
                            <td>${movie.release_year}</td>
                            <td>${movie.avg_rating ? movie.avg_rating.toFixed(1) : 'N/A'}</td>
                            <td>${movie.review_count || 0}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit" onclick="openEditModal(${movie.id})">Edit</button>
                                    <button class="btn-delete" onclick="deleteMovie(${movie.id})">Delete</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        
        moviesTable.innerHTML = tableHTML;
        
    } catch (error) {
        console.error('Error loading movies:', error);
        document.getElementById('moviesTable').innerHTML = '<p style="color: red;">Error loading movies</p>';
    }
}

// Bulk assign posters by matching filenames to titles
async function assignPosters() {
    try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/movies/assign-posters', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();
        if (response.ok) {
            alert(`Linked ${data.updated} posters.`);
            loadMovies();
            loadDashboardStats();
        } else {
            alert(data.error || 'Failed to assign posters');
        }
    } catch (error) {
        console.error('Error assigning posters:', error);
        alert('An error occurred while assigning posters');
    }
}

// Load Statistics
async function loadStatistics() {
    try {
        const response = await fetch('/api/movies');
        const movies = await response.json();
        
        // Sort by rating
        const topRatedMovies = [...movies]
            .filter(m => m.avg_rating)
            .sort((a, b) => b.avg_rating - a.avg_rating)
            .slice(0, 5);
        
        // Sort by review count
        const mostReviewedMovies = [...movies]
            .filter(m => m.review_count > 0)
            .sort((a, b) => b.review_count - a.review_count)
            .slice(0, 5);
        
        // Display top rated
        const topRatedHtml = topRatedMovies.length === 0 
            ? '<p>No ratings yet</p>'
            : topRatedMovies.map(movie => `
                <div class="stat-list-item">
                    <div>
                        <h4>${escapeHtml(movie.title)}</h4>
                        <p class="count">${movie.review_count} reviews</p>
                    </div>
                    <div class="value">⭐ ${movie.avg_rating.toFixed(1)}</div>
                </div>
            `).join('');
        
        document.getElementById('topRatedMovies').innerHTML = topRatedHtml;
        
        // Display most reviewed
        const mostReviewedHtml = mostReviewedMovies.length === 0 
            ? '<p>No reviews yet</p>'
            : mostReviewedMovies.map(movie => `
                <div class="stat-list-item">
                    <div>
                        <h4>${escapeHtml(movie.title)}</h4>
                        <p class="count">${movie.release_year}</p>
                    </div>
                    <div class="value">${movie.review_count}</div>
                </div>
            `).join('');
        
        document.getElementById('mostReviewedMovies').innerHTML = mostReviewedHtml;
        
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

// Add Movie Form Handler
document.addEventListener('DOMContentLoaded', () => {
    const addMovieForm = document.getElementById('addMovieForm');
    if (addMovieForm) {
        addMovieForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await submitAddMovie();
        });
    }
    
    // Image preview for add form
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const preview = document.getElementById('imagePreview');
                    document.getElementById('previewImg').src = event.target.result;
                    preview.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Edit Movie Form Handler
    const editMovieForm = document.getElementById('editMovieForm');
    if (editMovieForm) {
        editMovieForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await submitEditMovie();
        });
    }
});

// Submit Add Movie
async function submitAddMovie() {
    const form = document.getElementById('addMovieForm');
    const token = localStorage.getItem('token');
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/movies', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showSuccessMessage('addMovieSuccess', 'Movie added successfully!');
            form.reset();
            document.getElementById('imagePreview').style.display = 'none';
            
            // Reload movies in background
            setTimeout(() => {
                loadMovies();
                loadDashboardStats();
            }, 1500);
        } else {
            showErrorMessage('addMovieError', data.error || 'Error adding movie');
        }
    } catch (error) {
        console.error('Error adding movie:', error);
        showErrorMessage('addMovieError', 'An error occurred while adding the movie');
    }
}

// Open Edit Modal
async function openEditModal(movieId) {
    try {
        const response = await fetch(`/api/movies/${movieId}`);
        const movie = await response.json();
        
        document.getElementById('editMovieId').value = movieId;
        document.getElementById('editTitle').value = movie.title;
        document.getElementById('editGenre').value = movie.genre;
        document.getElementById('editReleaseYear').value = movie.release_year;
        document.getElementById('editDescription').value = movie.description;
        
        document.getElementById('editModal').classList.add('active');
    } catch (error) {
        console.error('Error loading movie:', error);
        alert('Error loading movie details');
    }
}

// Close Edit Modal
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    document.getElementById('editMovieForm').reset();
}

// Submit Edit Movie
async function submitEditMovie() {
    const form = document.getElementById('editMovieForm');
    const movieId = document.getElementById('editMovieId').value;
    const token = localStorage.getItem('token');
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch(`/api/movies/${movieId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Movie updated successfully!');
            closeEditModal();
            loadMovies();
            loadDashboardStats();
        } else {
            alert(data.error || 'Error updating movie');
        }
    } catch (error) {
        console.error('Error updating movie:', error);
        alert('An error occurred while updating the movie');
    }
}

// Delete Movie
async function deleteMovie(movieId) {
    if (!confirm('Are you sure you want to delete this movie?')) {
        return;
    }
    
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch(`/api/movies/${movieId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        if (response.ok) {
            alert('Movie deleted successfully!');
            loadMovies();
            loadDashboardStats();
        } else {
            const data = await response.json();
            alert(data.error || 'Error deleting movie');
        }
    } catch (error) {
        console.error('Error deleting movie:', error);
        alert('An error occurred while deleting the movie');
    }
}

// Clear image preview
function clearImagePreview() {
    document.getElementById('image').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

// Helper functions
function showSuccessMessage(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
        setTimeout(() => {
            element.style.display = 'none';
        }, 4000);
    }
}

function showErrorMessage(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
    }
}

function calculateTotalReviews(movies) {
    return movies.reduce((total, movie) => total + (movie.review_count || 0), 0);
}

function calculateAverageRating(movies) {
    const ratedMovies = movies.filter(m => m.avg_rating);
    if (ratedMovies.length === 0) return '0';
    const total = ratedMovies.reduce((sum, m) => sum + m.avg_rating, 0);
    return (total / ratedMovies.length).toFixed(1);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    }
}

// Close modal when clicking outside
window.addEventListener('click', (event) => {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
});
