# Think Fast Quiz - MySQL Only Setup Guide

## Prerequisites

1. **MySQL Server** (standalone installation)
2. **PHP** (for running the backend APIs)
3. **Web Server** (Apache, Nginx, or PHP built-in server)

## Option 1: Using PHP Built-in Server (Easiest)

### Step 1: Install MySQL

1. Download MySQL from [https://dev.mysql.com/downloads/mysql/](https://dev.mysql.com/downloads/mysql/)
2. Install MySQL Server
3. Set up root password during installation
4. Start MySQL service

### Step 2: Install PHP

1. Download PHP from [https://www.php.net/downloads.php](https://www.php.net/downloads.php)
2. Install PHP with MySQL extension
3. Add PHP to your system PATH

### Step 3: Create Database

1. Open MySQL Command Line Client or MySQL Workbench
2. Connect to MySQL server
3. Run the following commands:

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS quiz_app;
USE quiz_app;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    total_quizzes INT DEFAULT 0,
    best_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create quiz_results table
CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_quiz_results_user_id ON quiz_results(user_id);
CREATE INDEX idx_quiz_results_category ON quiz_results(category);
CREATE INDEX idx_quiz_results_created_at ON quiz_results(created_at);
```

### Step 4: Configure Database Connection

1. Open `config/database.php`
2. Update the database credentials:
   ```php
   $host = 'localhost';
   $port = '3306';
   $dbname = 'quiz_app';
   $username = 'root'; // Your MySQL username
   $password = 'your_password'; // Your MySQL password
   ```

### Step 5: Start the Application

1. Open Command Prompt/Terminal
2. Navigate to your project folder
3. Start PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
4. Open browser and go to `http://localhost:8000`

## Option 2: Using Apache/Nginx

### Step 1: Install Web Server + PHP + MySQL

**For Windows:**
- Use XAMPP, WAMP, or MAMP (but only use MySQL, not the bundled Apache)

**For Linux:**
```bash
sudo apt update
sudo apt install apache2 php php-mysql mysql-server
```

**For macOS:**
```bash
brew install apache2 php mysql
```

### Step 2: Configure MySQL

1. Start MySQL service
2. Create database and tables (same as Option 1, Step 3)
3. Update `config/database.php` with your credentials

### Step 3: Deploy Application

1. Copy project files to web server directory:
   - **Apache:** `/var/www/html/quiz-app/` (Linux) or `C:\xampp\htdocs\quiz-app\` (Windows)
   - **Nginx:** `/usr/share/nginx/html/quiz-app/` (Linux)

2. Set proper permissions:
   ```bash
   chmod -R 755 /path/to/quiz-app/
   chmod -R 644 /path/to/quiz-app/*.php
   ```

3. Access via `http://localhost/quiz-app/`

## Option 3: Using Docker (Advanced)

### Step 1: Create Docker Compose File

Create `docker-compose.yml`:

```yaml
version: '3.8'
services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: quiz_app
      MYSQL_USER: quiz_user
      MYSQL_PASSWORD: quiz_password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/setup.sql:/docker-entrypoint-initdb.d/setup.sql

  web:
    image: php:8.0-apache
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
    environment:
      - MYSQL_HOST=mysql
      - MYSQL_DATABASE=quiz_app
      - MYSQL_USER=quiz_user
      - MYSQL_PASSWORD=quiz_password

volumes:
  mysql_data:
```

### Step 2: Update Database Config

Update `config/database.php`:

```php
$host = $_ENV['MYSQL_HOST'] ?? 'mysql';
$dbname = $_ENV['MYSQL_DATABASE'] ?? 'quiz_app';
$username = $_ENV['MYSQL_USER'] ?? 'quiz_user';
$password = $_ENV['MYSQL_PASSWORD'] ?? 'quiz_password';
```

### Step 3: Run with Docker

```bash
docker-compose up -d
```

Access via `http://localhost:8080`

## Testing the Setup

1. **Test Database Connection:**
   ```bash
   php -r "require 'config/database.php'; echo 'Database connected successfully!';"
   ```

2. **Test APIs:**
   - Visit `http://localhost:8000/test-api.html`
   - Check if all API endpoints are working

3. **Test Complete Flow:**
   - Sign up for a new account
   - Login with the account
   - Select a quiz category
   - Take a quiz
   - Check if results are saved

## Troubleshooting

### Common Issues

1. **"Connection refused" error:**
   - Check if MySQL service is running
   - Verify MySQL port (default: 3306)
   - Check firewall settings

2. **"Access denied" error:**
   - Verify username and password
   - Check if user has privileges on the database
   - Run: `GRANT ALL PRIVILEGES ON quiz_app.* TO 'your_user'@'localhost';`

3. **"Database doesn't exist" error:**
   - Create the database manually
   - Run the SQL commands from Step 3

4. **PHP errors:**
   - Check if PHP MySQL extension is installed
   - Verify PHP version (7.4+ recommended)

### Database Management

**View all users:**
```sql
USE quiz_app;
SELECT * FROM users;
```

**View quiz results:**
```sql
USE quiz_app;
SELECT u.first_name, u.last_name, qr.category, qr.score, qr.percentage, qr.created_at 
FROM quiz_results qr 
JOIN users u ON qr.user_id = u.id 
ORDER BY qr.created_at DESC;
```

**Reset database:**
```sql
USE quiz_app;
DROP TABLE IF EXISTS quiz_results;
DROP TABLE IF EXISTS users;
-- Then run the CREATE TABLE commands again
```

## Security Notes

- Change default MySQL root password
- Use strong passwords for database users
- Consider using environment variables for sensitive data
- Enable SSL for production deployments
- Regular database backups

## File Structure

```
quiz-app/
├── index.html              # Main quiz page
├── fpage.html              # Homepage
├── login.html              # Login page
├── signin.html             # Signup page
├── quiz-app.js             # Main JavaScript logic
├── test-api.html           # API testing page
├── config/
│   └── database.php        # Database configuration
├── api/
│   ├── auth.php            # Authentication API
│   └── quiz.php            # Quiz API
├── database/
│   └── setup.sql           # Database schema
└── MYSQL_SETUP.md          # This guide
```

## Quick Start (Recommended)

1. Install MySQL and PHP
2. Create database using the SQL commands
3. Update `config/database.php` with your credentials
4. Run: `php -S localhost:8000`
5. Visit: `http://localhost:8000`

This setup gives you a complete quiz application using only MySQL for data storage!

