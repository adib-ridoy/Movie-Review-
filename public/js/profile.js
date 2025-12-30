const API_URL = 'http://localhost:3000/api';

// Check authentication and load profile
document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');
    
    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    await loadProfile(token);
    setupEventListeners();
});

// Load user profile
async function loadProfile(token) {
    try {
        const response = await fetch(`${API_URL}/auth/profile`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            if (response.status === 401) {
                localStorage.removeItem('token');
                window.location.href = 'login.html';
                return;
            }
            throw new Error('Failed to load profile');
        }

        const user = await response.json();
        document.getElementById('username').value = user.username;
        document.getElementById('email').value = user.email;
        
        document.getElementById('loadingDiv').style.display = 'none';
        document.getElementById('profileForm').style.display = 'block';
    } catch (error) {
        console.error('Error loading profile:', error);
        showMessage('Failed to load profile. Please try again.', 'error');
    }
}

// Setup event listeners
function setupEventListeners() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordSection = document.getElementById('passwordSection');
    const form = document.getElementById('profileForm');
    const logoutBtn = document.getElementById('logoutBtn');

    // Toggle password section
    togglePassword.addEventListener('change', (e) => {
        if (e.target.checked) {
            passwordSection.style.display = 'block';
            document.getElementById('password').required = true;
            document.getElementById('newPassword').required = true;
            document.getElementById('confirmPassword').required = true;
        } else {
            passwordSection.style.display = 'none';
            document.getElementById('password').required = false;
            document.getElementById('newPassword').required = false;
            document.getElementById('confirmPassword').required = false;
            document.getElementById('password').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        }
    });

    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await updateProfile();
    });

    // Handle logout
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('token');
        window.location.href = 'login.html';
    });
}

// Update user profile
async function updateProfile() {
    const token = localStorage.getItem('token');
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validation
    if (!username || username.length < 3) {
        showMessage('Username must be at least 3 characters', 'error');
        return;
    }

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showMessage('Invalid email format', 'error');
        return;
    }

    if (document.getElementById('togglePassword').checked) {
        if (!password) {
            showMessage('Current password is required to change password', 'error');
            return;
        }
        if (newPassword !== confirmPassword) {
            showMessage('New passwords do not match', 'error');
            return;
        }
        if (newPassword.length < 6) {
            showMessage('New password must be at least 6 characters', 'error');
            return;
        }
    }

    try {
        const payload = {
            username,
            email
        };

        if (document.getElementById('togglePassword').checked) {
            payload.password = password;
            payload.newPassword = newPassword;
            payload.confirmPassword = confirmPassword;
        }

        const response = await fetch(`${API_URL}/auth/profile`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            showMessage(data.error || 'Failed to update profile', 'error');
            return;
        }

        showMessage('Profile updated successfully!', 'success');
        
        // Reset password fields and toggle
        document.getElementById('togglePassword').checked = false;
        document.getElementById('passwordSection').style.display = 'none';
        document.getElementById('password').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';

        // If username changed, reload profile to ensure consistency
        setTimeout(() => {
            window.location.href = 'profile.html';
        }, 1500);
    } catch (error) {
        console.error('Error updating profile:', error);
        showMessage('An error occurred while updating profile', 'error');
    }
}

// Show message
function showMessage(message, type) {
    const container = document.getElementById('messageContainer');
    container.innerHTML = `<div class="message ${type}">${message}</div>`;
    
    if (type === 'success') {
        setTimeout(() => {
            container.innerHTML = '';
        }, 4000);
    }
}
