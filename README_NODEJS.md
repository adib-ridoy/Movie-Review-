# CineRate - Movie Review Site (Node.js Version)

A modern movie review website built with Node.js backend and MySQL database. Users can register, log in, rate movies, and leave comments. Admins can add new movies to the database.

## Features

- **User Authentication**: Secure registration and login with JWT tokens
- **Movie Management**: Browse movies, view details, and see ratings
- **Rating System**: Users can rate movies 1-5 stars
- **Comments**: Leave detailed reviews and comments
- **Admin Panel**: Add new movies (admin-only)
- **Responsive Design**: Works on desktop and mobile
- **Dark Theme**: Modern dark UI with orange accent

## Tech Stack

### Backend
- **Node.js** with Express.js framework
- **MySQL/MariaDB** database
- **JWT** for authentication
- **bcryptjs** for password hashing
- **CORS** for cross-origin requests

### Frontend
- **HTML5** for structure
- **CSS3** for styling
- **Vanilla JavaScript** for interactivity

## Project Structure

```
Movie Review/
├── server.js              # Express server entry point
├── db.js                  # Database configuration and initialization
├── package.json           # Node.js dependencies
├── .env                   # Environment variables
├── middleware/
│   └── auth.js           # JWT authentication middleware
├── routes/
│   ├── auth.js           # Authentication routes (register, login)
│   ├── movies.js         # Movie management routes
│   └── reviews.js        # Review management routes
└── public/               # Frontend files
    ├── index.html        # Home page
    ├── login.html        # Login page
    ├── register.html     # Registration page
    ├── movie-detail.html # Movie details page
    ├── admin.html        # Admin panel
    ├── css/
    │   └── style.css     # Main stylesheet
    └── js/
        ├── api.js        # API client functions
        ├── app.js        # Home page logic
        └── movie-detail.js # Movie detail page logic
```

## Database Schema

### users table
```sql
- id (INT, PRIMARY KEY)
- username (VARCHAR, UNIQUE)
- password (VARCHAR)
- email (VARCHAR)
- is_admin (INT, DEFAULT 0)
- created_at (TIMESTAMP)
```

### movies table
```sql
- id (INT, PRIMARY KEY)
- title (VARCHAR)
- description (TEXT)
- genre (VARCHAR)
- release_year (INT)
- created_by (INT, FOREIGN KEY -> users.id)
- created_at (TIMESTAMP)
```

### reviews table
```sql
- id (INT, PRIMARY KEY)
- movie_id (INT, FOREIGN KEY -> movies.id)
- user_id (INT, FOREIGN KEY -> users.id)
- rating (INT, 1-5)
- comment (TEXT)
- created_at (TIMESTAMP)
- UNIQUE(movie_id, user_id) - One review per user per movie
```

## Installation

### Prerequisites
- Node.js (v14 or higher)
- npm or yarn
- MySQL/MariaDB server running
- XAMPP (optional, if running MySQL via XAMPP)

### Setup Steps

1. **Clone/Download the project**
   ```bash
   cd "C:\xampp\htdocs\Movie Review"
   ```

2. **Install Dependencies**
   ```bash
   npm install
   ```

3. **Configure Environment**
   Edit `.env` file with your database credentials:
   ```
   PORT=3000
   DB_HOST=localhost
   DB_USER=root
   DB_PASSWORD=
   DB_DATABASE=movie_review
   JWT_SECRET=your_jwt_secret_key_change_this_in_production
   NODE_ENV=development
   ```

4. **Start MySQL Service**
   - If using XAMPP, start MySQL from the control panel
   - Or ensure your MySQL server is running

5. **Start the Server**
   ```bash
   npm start
   ```
   
   For development with auto-reload:
   ```bash
   npm run dev
   ```

6. **Access the Application**
   - Open browser and go to: `http://localhost:3000`
   - Database will be created automatically on first access

## API Endpoints

### Authentication Routes (`/api/auth`)
- **POST /register** - Register new user
  - Body: `{ username, email, password, confirmPassword }`
  - Returns: `{ message: "User registered successfully" }`

- **POST /login** - User login
  - Body: `{ username, password }`
  - Returns: `{ token, user: { id, username, is_admin } }`

### Movie Routes (`/api/movies`)
- **GET /** - Get all movies with average ratings
  - Returns: Array of movies with ratings

- **GET /:id** - Get single movie with all reviews
  - Returns: Movie object with reviews array

- **POST /** - Add new movie (Admin only)
  - Headers: `Authorization: Bearer {token}`
  - Body: `{ title, genre, release_year, description }`
  - Returns: `{ message: "Movie added successfully" }`

### Review Routes (`/api/reviews`)
- **POST /** - Submit/update review
  - Headers: `Authorization: Bearer {token}`
  - Body: `{ movie_id, rating, comment }`
  - Returns: `{ message: "Review submitted successfully" }`

- **GET /movie/:movie_id** - Get all reviews for a movie
  - Returns: Array of reviews

- **GET /user/:movie_id** - Get user's review for a movie
  - Headers: `Authorization: Bearer {token}`
  - Returns: User's review object

## Usage Guide

### For Regular Users

1. **Register Account**
   - Go to Register page
   - Enter username, email, and password
   - Click Register

2. **Login**
   - Go to Login page
   - Enter credentials
   - Click Login

3. **Browse Movies**
   - View all movies on home page
   - See average ratings and review count
   - Click "View Details" for more info

4. **Rate & Review**
   - Go to movie details page
   - Select star rating (1-5)
   - Write your comment
   - Click Submit Review
   - Update review by submitting again

### For Admin Users

1. **Create Admin Account**
   - Register normally
   - Use phpMyAdmin to set `is_admin = 1` for the user
   - Or contact database administrator

2. **Add Movies**
   - Login with admin account
   - Click "Add Movie" in navigation
   - Fill in movie details
   - Click "Add Movie"

## Security Features

- **Password Hashing**: bcryptjs with salt rounds
- **JWT Authentication**: Secure token-based authentication
- **Input Validation**: Server-side validation on all inputs
- **SQL Injection Prevention**: Prepared statements for all queries
- **XSS Protection**: HTML escaping on output
- **CORS**: Configured to prevent unauthorized cross-origin requests

## Environment Variables

```
PORT              - Server port (default: 3000)
DB_HOST          - MySQL host (default: localhost)
DB_USER          - MySQL username (default: root)
DB_PASSWORD      - MySQL password
DB_DATABASE      - Database name (default: movie_review)
JWT_SECRET       - Secret key for JWT signing
NODE_ENV         - Environment (development/production)
```

## Troubleshooting

### Database Connection Issues
- Ensure MySQL is running
- Check database credentials in `.env`
- Verify database user has necessary permissions
- Try creating database manually via phpMyAdmin

### Port Already in Use
- Change PORT in `.env` to different port (e.g., 3001)
- Or kill the process using port 3000

### npm Install Issues
- Delete `node_modules` folder
- Delete `package-lock.json`
- Run `npm install` again
- Try `npm install --legacy-peer-deps` if issues persist

### Authentication Issues
- Clear browser localStorage: `localStorage.clear()`
- Re-login to get new token
- Check JWT_SECRET in `.env`

## Development Tips

### Add New Movie via API (for testing)
```bash
curl -X POST http://localhost:3000/api/movies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{"title":"Test Movie","genre":"Action","release_year":2024,"description":"A test movie"}'
```

### Get All Movies
```bash
curl http://localhost:3000/api/movies
```

### Health Check
```bash
curl http://localhost:3000/api/health
```

## Performance Optimization Ideas

- Add database indexing on frequently queried columns
- Implement pagination for movie listings
- Cache popular movies data
- Add database query optimization
- Implement rate limiting on API endpoints

## Future Enhancements

- User profile pages
- Movie search and filtering
- Category/genre filtering
- Advanced rating aggregation
- User review moderation
- Movie images/posters
- Email notifications
- User ratings history
- Pagination for large datasets
- Admin dashboard with statistics

## License

This project is open source and available under the MIT License.

## Support

For issues or questions:
1. Check the error messages in browser console
2. Review server logs in terminal
3. Verify database connection
4. Check `.env` configuration
5. Ensure all dependencies are installed

## Contact

For support or feature requests, please open an issue or contact the development team.
