# CineRate - Movie Review Platform

A full-stack movie review website with both PHP/MySQL and Node.js/Express backends. Users can browse movies, leave ratings and comments, while admins manage the movie catalog.

## Overview

CineRate is a comprehensive movie review platform that allows users to discover films, rate them, and share their opinions. The project supports dual backend architectures—a traditional PHP/MySQL setup and a modern Node.js/Express API.

## Features

- **User Authentication**: Secure registration and login with password hashing (bcryptjs)
- **Movie Browsing**: Browse all movies with average ratings and review statistics
- **Rating System**: Rate movies on a 1-5 star scale
- **Comments**: Leave detailed reviews and comments on movies
- **Admin Dashboard**: Admin panel for managing the movie catalog
  - Add new movies
  - Edit existing movies
  - Delete movies
  - View offensive content reports
- **User Profiles**: View user profile information
- **Offensive Content Reporting**: Flag inappropriate reviews
- **Responsive Design**: Mobile-friendly UI with dark theme and orange accent colors
- **File Uploads**: Support for movie images and user uploads

## Project Structure

```
Movie Review/
├── index.php                    # Home page - movie listings
├── movie_detail.php             # Movie detail page with reviews
├── config.php                   # Database configuration (PHP)
├── db.js                        # Database configuration (Node.js)
├── server.js                    # Node.js Express server entry point
├── package.json                 # Node.js dependencies
│
├── admin/                       # Admin management pages
│   ├── manage_movies.php        # Main admin dashboard
│   ├── add_movie.php            # Add new movie
│   ├── edit_movie.php           # Edit existing movie
│   ├── delete_movie.php         # Delete movie
│   └── offensive_list.php       # View offensive reports
│
├── users/                       # User authentication pages
│   ├── login.php                # Login page
│   ├── register.php             # Registration page
│   ├── logout.php               # Logout handler
│   └── profile.php              # User profile
│
├── components/                  # Reusable components
│   ├── submit_review.php        # Review submission handler
│   ├── delete_review.php        # Review deletion
│   └── increase_offense.php     # Flag offensive content
│
├── routes/                      # Node.js API routes
│   ├── auth.js                  # Authentication routes
│   ├── movies.js                # Movie routes
│   └── reviews.js               # Review routes
│
├── middleware/                  # Node.js middleware
│   ├── auth.js                  # Authentication middleware
│   └── upload.js                # File upload middleware
│
├── public/                      # Frontend assets
│   ├── index.html               # Home page (SPA version)
│   ├── login.html               # Login page (SPA)
│   ├── register.html            # Registration page (SPA)
│   ├── movie-detail.html        # Movie detail (SPA)
│   ├── admin.html               # Admin dashboard (SPA)
│   ├── profile.html             # User profile (SPA)
│   ├── admin-dashboard.html     # Admin dashboard UI
│   ├── css/
│   │   ├── style.css            # Main stylesheet
│   │   └── admin.css            # Admin panel styles
│   ├── js/
│   │   ├── app.js               # Main application logic
│   │   ├── api.js               # API client utilities
│   │   ├── admin.js             # Admin functionality
│   │   ├── movie-detail.js      # Movie detail page logic
│   │   └── profile.js           # Profile page logic
│   └── uploads/                 # User uploaded files
│
├── img/                         # Project images
│
├── Database Files/              # Database setup scripts
│   ├── complete_database_setup.sql
│   ├── movie_review.sql
│   ├── movie_review_data.sql
│   ├── migration_add_offense_count.sql
│   ├── migration_add_user_offense_count.sql
│   └── migration_drop_review_offense_count.sql
│
├── README.md                    # Original project README
├── ADMIN_PANEL_README.md        # Admin panel documentation
└── README_NODEJS.md             # Node.js setup guide
```

## Database Schema

### Users Table
```sql
- id (INT, Primary Key)
- username (VARCHAR)
- password (VARCHAR - hashed)
- email (VARCHAR)
- is_admin (BOOLEAN)
- created_at (TIMESTAMP)
```

### Movies Table
```sql
- id (INT, Primary Key)
- title (VARCHAR)
- description (TEXT)
- genre (VARCHAR)
- release_year (INT)
- created_by (INT, Foreign Key to users)
- created_at (TIMESTAMP)
- image_url (VARCHAR) - Optional
```

### Reviews Table
```sql
- id (INT, Primary Key)
- movie_id (INT, Foreign Key to movies)
- user_id (INT, Foreign Key to users)
- rating (INT, 1-5)
- comment (TEXT)
- created_at (TIMESTAMP)
```

### Offense Reports Table
```sql
- id (INT, Primary Key)
- review_id (INT, Foreign Key to reviews)
- reported_by (INT, Foreign Key to users)
- reason (TEXT)
- created_at (TIMESTAMP)
```

## Setup & Installation

### Prerequisites
- **For PHP Backend**: XAMPP or any Apache + PHP + MySQL stack
- **For Node.js Backend**: Node.js (v14+) and npm/yarn
- MySQL 5.7+ or MariaDB

### Installing Dependencies

#### PHP Dependencies
PHP dependencies are minimal as the project uses built-in extensions:

1. **Required PHP Extensions** (Usually pre-installed)
   - `mysqli` - MySQL database extension
   - `json` - JSON encoding/decoding
   - `sessions` - User session management
   - `filter` - Input validation

2. **Verify PHP Installation**
   ```bash
   # Windows - Check PHP version and extensions
   php -v
   php -m    # List all loaded extensions
   ```

3. **Enable Extensions if Needed**
   - Edit `php.ini` in your XAMPP installation
   - Uncomment required extensions:
   ```
   extension=mysqli
   extension=json
   ```

#### Node.js Dependencies

1. **Install Node.js and npm**
   - Download from: https://nodejs.org/
   - Recommended: LTS version (v18+)
   - Verify installation:
   ```bash
   node --version
   npm --version
   ```

2. **Install Project Dependencies**
   ```bash
   cd "c:\xampp\htdocs\Movie Review"
   npm install
   ```

3. **Project Dependencies Installed**
   - `express` (^4.18.2) - Web framework
   - `mysql2` (^3.6.5) - Database driver
   - `bcryptjs` (^2.4.3) - Password hashing
   - `jsonwebtoken` (^9.0.2) - JWT authentication
   - `cors` (^2.8.5) - Cross-origin requests
   - `dotenv` (^16.3.1) - Environment variables
   - `body-parser` (^1.20.2) - Request body parsing
   - `multer` (^1.4.5-lts.1) - File uploads
   - `nodemon` (^3.0.2) - Development auto-reload

4. **Verify Packages**
   ```bash
   npm list
   ```

### PHP Backend Setup

1. **Clone/Download Project**
   ```bash
   cd c:\xampp\htdocs
   # Copy project files here
   ```

2. **Create Database**
   ```bash
   # Import the database schema
   mysql -u root < complete_database_setup.sql
   ```

3. **Configure Database**
   - Edit `config.php` with your database credentials:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $database = "movie_review";
   ```

4. **Access Application**
   - Open `http://localhost/Movie%20Review/index.php` in your browser

5. **Default Admin Account** (if created during setup)
   - Username: admin
   - Password: (check database setup script)

### Node.js Backend Setup

1. **Install Dependencies**
   ```bash
   npm install
   ```

2. **Configure Environment**
   - Create a `.env` file:
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASSWORD=
   DB_NAME=movie_review
   JWT_SECRET=your_jwt_secret_key
   PORT=3000
   ```

3. **Create Database**
   ```bash
   mysql -u root < complete_database_setup.sql
   ```

4. **Start Server**
   ```bash
   npm start        # Production
   npm run dev      # Development with nodemon
   ```

5. **Access API**
   - Server runs on `http://localhost:3000`

## API Endpoints (Node.js)

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout

### Movies
- `GET /api/movies` - Get all movies
- `GET /api/movies/:id` - Get movie details
- `POST /api/movies` - Add new movie (Admin only)
- `PUT /api/movies/:id` - Update movie (Admin only)
- `DELETE /api/movies/:id` - Delete movie (Admin only)

### Reviews
- `GET /api/reviews/movie/:movieId` - Get movie reviews
- `POST /api/reviews` - Submit new review
- `DELETE /api/reviews/:id` - Delete review
- `POST /api/reviews/:id/flag` - Flag as offensive

### Users
- `GET /api/users/:id` - Get user profile
- `PUT /api/users/:id` - Update user profile

## Frontend Pages

### Public Pages
- **Home** (`index.html` / `index.php`) - Browse all movies
- **Movie Detail** (`movie-detail.html` / `movie_detail.php`) - View movie and reviews
- **Login** (`login.html` / `users/login.php`) - User authentication
- **Register** (`register.html` / `users/register.php`) - New user signup

### User Pages
- **Profile** (`profile.html` / `users/profile.php`) - View and edit profile

### Admin Pages
- **Admin Dashboard** (`admin.html` / `admin/manage_movies.php`) - Manage movies
- **Add Movie** (`admin/add_movie.php`) - Add new movie
- **Edit Movie** (`admin/edit_movie.php`) - Modify existing movie
- **Offensive Reports** (`admin/offensive_list.php`) - View flagged content

## Technology Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **Node.js & Express.js** - Alternative REST API backend
- **MySQL/MariaDB** - Database

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling with responsive design
- **JavaScript (Vanilla)** - Client-side logic
- **Multer** - File upload handling (Node.js)

### Security
- **bcryptjs** - Password hashing
- **JWT (JSON Web Tokens)** - API authentication (Node.js)
- **CORS** - Cross-origin resource sharing
- **Input validation** - Server-side validation

## Key Features Explained

### User Registration & Login
- Secure password hashing with bcryptjs
- Email verification (optional enhancement)
- Session management

### Movie Reviews System
- 1-5 star rating system
- Comment/review text field
- Average rating calculation
- Review count tracking

### Admin Functions
- Movie CRUD operations (Create, Read, Update, Delete)
- Offensive content moderation
- User management capabilities

### Responsive Design
- Mobile-first approach
- Dark theme UI
- Orange accent colors
- Fluid layouts

## File Upload

User and movie images are stored in:
- `public/uploads/` - Profile pictures and movie posters
- Maximum file size: 5MB (configurable in `upload.js`)
- Allowed formats: JPG, PNG, GIF

## Security Considerations

- ✅ Password hashing with bcryptjs
- ✅ SQL injection protection (parameterized queries)
- ✅ CORS enabled for API endpoints
- ✅ JWT token-based authentication
- ✅ File upload validation
- ✅ Input sanitization

## Database Migrations

The project includes migration scripts for evolving the schema:
- `migration_add_offense_count.sql` - Add offense counting
- `migration_add_user_offense_count.sql` - Track user offense history
- `migration_drop_review_offense_count.sql` - Schema cleanup

Run migrations:
```bash
mysql -u root movie_review < migration_*.sql
```

## Environment Configuration

### PHP Configuration
Edit `config.php`:
```php
$servername = "localhost";
$username = "root";
$password = "";
$database = "movie_review";
```

### Node.js Configuration
Create `.env` file:
```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=movie_review
JWT_SECRET=your_secret_key
PORT=3000
CORS_ORIGIN=http://localhost:3000
```

## Common Issues & Solutions

### Database Connection Error
- Ensure MySQL is running
- Check credentials in `config.php` or `.env`
- Verify database `movie_review` exists

### Port Already in Use (Node.js)
- Change PORT in `.env`
- Or kill process: `netstat -ano | findstr :3000`

### File Upload Failures
- Check `public/uploads/` directory permissions
- Verify `upload.js` middleware configuration
- Check file size limits

### Authentication Issues
- Clear browser cookies
- Check JWT token expiration
- Verify user exists in database

## Contributing

When adding features:
1. Update database schema if needed
2. Test with both PHP and Node.js versions
3. Update API documentation
4. Follow existing code style

## License

ISC License

## Additional Documentation

See also:
- [ADMIN_PANEL_README.md](ADMIN_PANEL_README.md) - Admin panel detailed guide
- [README_NODEJS.md](README_NODEJS.md) - Node.js setup guide
- [README.md](README.md) - Original README

## Support

For issues or questions:
1. Check existing documentation
2. Review database setup scripts
3. Verify credentials and connections
4. Check browser console for frontend errors
5. Check server logs for backend errors

---

**Project**: CineRate Movie Review Platform
**Last Updated**: January 2026
**Status**: Active Development
