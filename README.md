# CineRate - Movie Review Site

A simple movie review website where users can log in, rate movies, and leave comments. Admins can add new movies to the database.

## Features

- **User Registration & Login**: Secure user authentication with password hashing
- **Movie Browsing**: View all movies with average ratings and review counts
- **Rating System**: Users can rate movies from 1-5 stars
- **Comments**: Users can leave detailed comments on movies
- **Admin Panel**: Admins can add new movies to the system
- **Responsive Design**: Works on desktop and mobile devices
- **Dark Theme**: Modern dark UI with orange accent color

## Project Structure

```
Movie Review/
├── config.php              # Database configuration and schema
├── index.php               # Home page with movie listings
├── movie_detail.php        # Movie detail page with reviews
├── css/
│   └── style.css          # Main stylesheet
├── admin/
│   └── add_movie.php      # Admin panel to add movies
├── users/
│   ├── login.php          # User login page
│   ├── register.php       # User registration page
│   └── logout.php         # Logout handler
└── components/
    └── submit_review.php  # Review submission handler
```

## Database Setup

The application uses MySQL with the following tables:

1. **users**: Stores user information
   - id, username, password, email, is_admin, created_at

2. **movies**: Stores movie information
   - id, title, description, genre, release_year, created_by, created_at

3. **reviews**: Stores ratings and comments
   - id, movie_id, user_id, rating (1-5), comment, created_at

## Installation & Setup

### Prerequisites
- XAMPP (or any Apache + PHP + MySQL server)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Steps

1. **Extract the project** to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\Movie Review
   ```

2. **Start XAMPP Services**:
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Create Admin User** (Optional):
   - Access phpMyAdmin: `http://localhost/phpmyadmin`
   - Navigate to the `movie_review` database
   - In the `users` table, add an admin user with `is_admin` set to 1
   - Or register a user normally, then manually set `is_admin = 1` in the database

4. **Access the Application**:
   - Open your browser and go to: `http://localhost/Movie%20Review`
   - The database will be created automatically on first access

## Usage

### User Features
1. **Register**: Create a new account with username and email
2. **Login**: Log in with your credentials
3. **Browse Movies**: View all available movies on the home page
4. **View Details**: Click on a movie to see its full details
5. **Rate & Comment**: Leave a rating (1-5 stars) and comment on movies
6. **Update Review**: You can edit your review by submitting a new one

### Admin Features
1. **Login**: Use your admin account to log in
2. **Add Movie**: Click "Add Movie" in the navigation bar
3. **Fill Details**: Enter movie title, genre, release year, and description
4. **Submit**: Click "Add Movie" to add it to the database

## Key Technologies

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3
- **Security**: 
  - Password hashing with bcrypt
  - SQL injection prevention with prepared statements
  - Session-based authentication
  - CSRF protection through session validation

## Security Features

- Passwords are hashed using PHP's `password_hash()` with BCRYPT algorithm
- SQL queries use prepared statements to prevent SQL injection
- Session-based authentication for user management
- Input validation and sanitization
- HTML escaping for output to prevent XSS attacks

## Features Breakdown

### Home Page
- Display all movies in a responsive grid
- Show average rating and review count for each movie
- Quick access to movie details
- Navigation bar with login/register or logout options

### Movie Detail Page
- Full movie information (title, genre, year, description)
- Average rating and total review count
- Review form for authenticated users
- Star rating selector
- Comment textarea
- List of all user reviews sorted by newest first
- Login prompt for non-authenticated users

### Authentication
- Secure login/registration system
- Email validation
- Password confirmation on registration
- Minimum password length requirement
- Duplicate username/email detection

### Admin Panel
- Add new movies to the database
- Input validation for all fields
- Success/error feedback

## Default Admin Setup

To create an admin user manually:

1. Register a normal user account
2. Open phpMyAdmin
3. Go to `movie_review` database → `users` table
4. Edit the user and change `is_admin` from 0 to 1

## Future Enhancements

- User profile pages
- Movie search and filtering
- Category/genre filtering
- User review moderation
- Movie ratings aggregation
- Email notifications
- Movie images/posters
- User ratings history
- Pagination for reviews

## Troubleshooting

**Database not creating?**
- Ensure MySQL is running
- Check if user has permissions to create databases
- Verify `config.php` has correct credentials

**Can't log in?**
- Ensure the user was registered successfully
- Check if the user exists in the database
- Verify password was entered correctly

**Can't add movies as admin?**
- Ensure you're logged in with an admin account
- Check database user permissions
- Verify `is_admin` is set to 1 in the database

## License

This project is open source and available under the MIT License.

## Support

For issues or questions, please check the code comments or review the implementation in the respective PHP files.
