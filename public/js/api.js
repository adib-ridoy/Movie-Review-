// API base URL
const API_BASE = '/api';

// Helper function to make API requests
async function apiCall(endpoint, method = 'GET', body = null) {
  const options = {
    method,
    headers: {
      'Content-Type': 'application/json'
    }
  };

  const token = localStorage.getItem('token');
  if (token) {
    options.headers['Authorization'] = `Bearer ${token}`;
  }

  if (body) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(`${API_BASE}${endpoint}`, options);
  return response;
}

// Auth functions
async function registerUser(username, email, password, confirmPassword) {
  return apiCall('/auth/register', 'POST', { username, email, password, confirmPassword });
}

async function loginUser(username, password) {
  return apiCall('/auth/login', 'POST', { username, password });
}

// Movie functions
async function getMovies() {
  return apiCall('/movies', 'GET');
}

async function getMovieById(id) {
  return apiCall(`/movies/${id}`, 'GET');
}

async function addMovie(title, genre, release_year, description) {
  return apiCall('/movies', 'POST', { title, genre, release_year, description });
}

// Review functions
async function submitReview(movie_id, rating, comment) {
  return apiCall('/reviews', 'POST', { movie_id, rating, comment });
}

async function getMovieReviews(movie_id) {
  return apiCall(`/reviews/movie/${movie_id}`, 'GET');
}

async function getUserReview(movie_id) {
  return apiCall(`/reviews/user/${movie_id}`, 'GET');
}

// Auth state management
function isLoggedIn() {
  return localStorage.getItem('token') !== null;
}

function getUser() {
  return JSON.parse(localStorage.getItem('user') || 'null');
}

function logout() {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  window.location.href = 'index.html';
}

// Update navigation based on auth state
function updateNavigation() {
  const user = getUser();
  const isLoggedIn = !!user;

  const loginLink = document.getElementById('loginLink');
  const registerLink = document.getElementById('registerLink');
  const logoutLink = document.getElementById('logoutLink');
  const adminLink = document.getElementById('adminLink');
  const profileLink = document.getElementById('profileLink');

  if (loginLink) loginLink.style.display = isLoggedIn ? 'none' : '';
  if (registerLink) registerLink.style.display = isLoggedIn ? 'none' : '';
  if (logoutLink) logoutLink.style.display = isLoggedIn ? '' : 'none';
  if (profileLink) profileLink.style.display = isLoggedIn ? '' : 'none';
  if (adminLink) adminLink.style.display = isLoggedIn && user.is_admin ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', updateNavigation);
