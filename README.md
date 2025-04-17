# University Notice Board Web Application

A comprehensive web-based notice board system for universities with admin management, notice posting, and student viewing capabilities.

## Features

### 1. Authentication System
- Secure admin login with email/username and password
- Role-based access control (Super Admin and Admin roles)
- Session management and secure logout
- Password reset functionality via email

### 2. Admin Management
- Super Admin can manage other administrators
- Add, edit, and delete admin accounts
- Role assignment and modification
- Secure password handling with hashing

### 3. Notice Management
- Create, edit, and delete notices
- File attachments support (PDF, Images)
- Categorization of notices
- Rich text description
- Search and filter functionality

### 4. Student Interface
- Public notice board view
- Search notices by keywords
- Filter by category and date
- View and download attachments
- Mobile-responsive design

### 5. Security Features
- Password hashing using PHP's password_hash()
- SQL injection protection with prepared statements
- XSS prevention with input sanitization
- Secure file upload handling
- Session security measures

## Technical Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP (or similar stack)
- Modern web browser
- Email server configuration (for password reset)

## Installation Guide

1. **Set Up XAMPP**
   - Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Start Apache and MySQL services

2. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named 'university_notice_board'
   - Import the database schema from `database/schema.sql`

3. **Application Setup**
   - Clone/copy the project files to `C:/xampp/htdocs/university_notice_board/`
   - Configure database connection in `config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'university_notice_board');
     ```

4. **Email Configuration**
   - Update email settings in `config.php`:
     ```php
     define('SMTP_HOST', 'smtp.gmail.com');
     define('SMTP_PORT', 587);
     define('SMTP_USER', 'your-email@gmail.com');
     define('SMTP_PASS', 'your-app-specific-password');
     ```

5. **File Permissions**
   - Ensure the `uploads` directory has write permissions
   - Set appropriate permissions for config files

6. **First-Time Setup**
   - Access http://localhost/university_notice_board/
   - Default super admin credentials:
     - Username: admin
     - Password: admin123
   - Change the default password immediately after first login

## Directory Structure

```
university_notice_board/
├── admin/                 # Admin panel files
├── auth/                  # Authentication files
├── includes/             # Common includes
├── uploads/              # File uploads
├── database/             # Database schema
├── config.php            # Configuration
└── index.php            # Public notice board
```

## Feature Verification Checklist

### Authentication System
- [ ] Admin login functionality
- [ ] Session management
- [ ] Logout functionality
- [ ] Password reset via email

### Admin Management
- [ ] Super admin can add new admins
- [ ] Edit admin details
- [ ] Delete admin accounts
- [ ] Role assignment

### Notice Management
- [ ] Create new notices
- [ ] Edit existing notices
- [ ] Delete notices
- [ ] File attachment handling
- [ ] Search and filter notices

### Security
- [ ] Password hashing
- [ ] SQL injection protection
- [ ] XSS prevention
- [ ] Secure file uploads
- [ ] Session security

## Troubleshooting

1. **Database Connection Issues**
   - Verify MySQL service is running
   - Check database credentials in config.php
   - Ensure database exists

2. **File Upload Problems**
   - Check upload directory permissions
   - Verify PHP file upload settings in php.ini
   - Ensure proper file types are allowed

3. **Email Not Working**
   - Verify SMTP settings
   - Check email credentials
   - Enable less secure app access for Gmail

4. **Session Issues**
   - Check PHP session configuration
   - Clear browser cookies
   - Verify session directory permissions

## Support

For issues and support:
1. Check the troubleshooting guide
2. Review error logs in XAMPP
3. Contact system administrator

## Security Notes

1. Always change default credentials
2. Regularly update passwords
3. Keep XAMPP and PHP updated
4. Monitor error logs
5. Regular security audits recommended
