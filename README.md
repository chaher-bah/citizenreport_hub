# CitizenReport Hub

A web application for citizens to report municipal issues and for municipality workers to manage those reports.

## Features

### Sprint 0 - Foundation
- ✅ User authentication (CIN + password)
- ✅ Role-based access control (Citizen / Worker)
- ✅ MVC architecture
- ✅ MySQL database with PDO
- ✅ URL routing with .htaccess

### Sprint 1 - Citizen Reporting Core
- ✅ Report submission with category selection
- ✅ Photo/video upload (max 2 files)
- ✅ Interactive map with Leaflet.js for location selection
- ✅ Browser geolocation fallback
- ✅ Unique ticket ID generation (CIT-YYYYMMDD-XXXX)
- ✅ Citizen dashboard with report history
- ✅ Report detail view with media gallery
- ✅ Status history tracking

## Requirements

- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled
- Composer (optional, not required for this implementation)

## Installation

### 1. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE citizen_report_hub;
```

2. Import the schema:
```bash
mysql -u root -p citizen_report_hub < database/migrations/001_initial_schema.sql
```

Or run the SQL file directly in phpMyAdmin or MySQL Workbench.

### 2. Configuration

Edit `config/database.php` with your MySQL credentials:

```php
return [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'citizen_report_hub',
    'username' => 'root',
    'password' => 'your_password',
    // ...
];
```

### 3. Apache Configuration

Set the document root to the `public/` directory.

**Option A: Using .htaccess (recommended for development)**

The `.htaccess` file is already configured. Just ensure `mod_rewrite` is enabled:



# On Windows (XAMPP)
# Edit httpd.conf and ensure this line is uncommented:
# LoadModule rewrite_module modules/mod_rewrite.so
```

**Option B: Virtual Host Configuration**

```apache
<VirtualHost *:80>
    ServerName citizenhub.local
    DocumentRoot "W:/EPI_STUDY/modelisation/bladna/public"
    
    <Directory "W:/EPI_STUDY/modelisation/bladna/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 4. File Permissions

Ensure the `public/uploads/` directory is writable:

```bash
# On Linux/Mac
chmod -R 755 public/uploads/
chown -R www-data:www-data public/uploads/

# On Windows
# Right-click folder > Properties > Security > Edit
# Add IIS_IUSRS or Apache user with Modify permissions
```

### 5. Access the Application

Open your browser and navigate to:
- `http://localhost` (if using default Apache setup)
- `http://citizenhub.local` (if using virtual host)

## Default Test Accounts

After importing the database schema, you can use these test accounts:

### Citizen Account
- **CIN:** CITIZEN001
- **Password:** password123

### Worker Account
- **CIN:** WORKER001
- **Password:** password123

## Project Structure

```
bladna/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php      # Login, register, logout
│   │   ├── ReportController.php    # Report CRUD operations
│   │   └── DashboardController.php # Citizen & worker dashboards
│   ├── Core/
│   │   ├── Controller.php          # Base controller
│   │   ├── Model.php               # Base model
│   │   ├── Database.php            # Database connection (Singleton)
│   │   └── Router.php              # URL routing
│   ├── Middleware/
│   │   ├── AuthMiddleware.php      # Check if logged in
│   │   ├── WorkerMiddleware.php    # Check worker role
│   │   └── CitizenMiddleware.php   # Check citizen role
│   ├── Models/
│   │   ├── User.php                # User model
│   │   ├── Report.php              # Report model
│   │   ├── ReportMedia.php         # Media attachments model
│   │   └── StatusUpdate.php        # Status history model
│   ├── Services/
│   │   ├── FileUploadService.php   # File upload handling
│   │   └── GeoService.php          # Geolocation utilities
│   ├── Views/
│   │   ├── auth/                   # Login & register views
│   │   ├── dashboard/              # Citizen & worker dashboards
│   │   ├── report/                 # Report views
│   │   ├── layouts/                # Main layout template
│   │   └── errors/                 # Error pages
│   └── Helpers.php                 # Utility functions
├── config/
│   ├── database.php                # Database configuration
│   └── app.php                     # Application configuration
├── database/
│   └── migrations/
│       └── 001_initial_schema.sql  # Database schema
├── public/
│   ├── uploads/
│   │   └── reports/                # Uploaded media files
│   ├── .htaccess                   # URL rewriting rules
│   └── index.php                   # Application entry point
└── README.md
```

## API Endpoints (Web Routes)

### Authentication
| Method | Route | Description |
|--------|-------|-------------|
| GET | /auth/login | Show login page |
| POST | /auth/login | Process login |
| GET | /auth/register | Show registration page |
| POST | /auth/register | Process registration |
| GET | /auth/logout | Logout user |

### Citizen Routes
| Method | Route | Description |
|--------|-------|-------------|
| GET | /dashboard | Citizen dashboard |
| GET | /report/create | Show report form |
| POST | /report/create | Submit report |
| GET | /report/success | Report success page |
| GET | /report/view?id=X | View report details |

### Worker Routes
| Method | Route | Description |
|--------|-------|-------------|
| GET | /admin/dashboard | Worker dashboard |
| GET | /report/view?id=X | View report details |

## Database Schema

### Tables
- **users** - User accounts (citizens and workers)
- **reports** - Citizen reports
- **report_media** - Photo/video attachments
- **status_updates** - Status change history
- **broadcasts** - System announcements (for future sprints)
- **assignments** - Report assignments (for future sprints)

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with PDO prepared statements
- XSS prevention with htmlspecialchars
- Role-based access control
- Session-based authentication
- File upload validation (type, size)



## Future Enhancements (Upcoming Sprints)

- Worker report management (status updates, assignments)
- Broadcast system for announcements
- Email notifications
- Report statistics and analytics
- Mobile app API
- Advanced search and filtering
- User profile management

## License

This project is created for educational purposes.
