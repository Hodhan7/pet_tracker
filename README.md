# 🐾 Pet Health Tracker

A comprehensive web-based pet health management system built with PHP, MySQL, and Tailwind CSS. This application allows pet owners to track their pets' health records, schedule appointments with veterinarians, and enables veterinarians to manage patient records efficiently.

## ✨ Features

### 👥 Multi-Role System
- **Pet Owners**: Manage pets, view health records, schedule appointments
- **Veterinarians**: Manage patient records, add health entries, schedule appointments
- **Administrators**: Manage users, oversee system operations

### 🐕 Pet Management
- Complete pet profiles with photos, medical history, and basic information
- Track multiple pets per owner
- Microchip and vaccination tracking
- Spay/neuter status and allergy information

### 🏥 Health Records
- Comprehensive health record system with multiple record types:
  - Regular checkups
  - Vaccinations
  - Illness records
  - Surgery records
  - Medication tracking
  - Lab results
  - Emergency visits
- Weight and temperature tracking
- Follow-up scheduling
- Treatment notes and diagnosis

### 📅 Appointment System
- Online appointment scheduling
- Appointment management for veterinarians
- Multiple appointment types (checkup, emergency, surgery, etc.)
- Duration-based scheduling

### 💉 Vaccination Tracking
- Complete vaccination history
- Due date reminders
- Batch number and manufacturer tracking
- Reaction monitoring

### 👨‍⚕️ Veterinarian Features
- Patient search and filtering
- Quick access health record forms
- Multiple entry points for adding records
- Recent activity dashboard
- Patient management interface

## 🛠 Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, Tailwind CSS 4.1.11, JavaScript
- **Icons**: Heroicons
- **Session Management**: PHP Sessions
- **Security**: Password hashing, SQL injection prevention

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx) or PHP built-in server
- Modern web browser

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Hodhan7/pet_tracker.git
cd pet-health-tracker
```

### 2. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE pet_health_tracker;"

# Import database schema and sample data
mysql -u root -p pet_health_tracker < database.sql
```

### 3. Database Configuration
Update the database connection settings in `db.php`:
```php
$servername = "localhost";
$username = "your_db_username";
$password = "your_db_password";
$dbname = "pet_health_tracker";
```

### 4. Start the Server
```bash
# Using PHP built-in server
php -S localhost:8000

# Or configure your web server to point to the project directory
```

### 5. Access the Application
Open your browser and navigate to:
- Local development: `http://localhost:8000`
- Production: `http://yourdomain.com`

## 👤 Default User Accounts

The system comes with pre-configured test accounts:

### Administrator
- **Email**: `admin@pethealthtracker.com`
- **Password**: `password`
- **Role**: Admin

### Veterinarians
- **Dr. John Smith**: `dr.smith@vetclinic.com` / `password`
- **Dr. Sarah Johnson**: `dr.johnson@animalcare.com` / `password`

### Pet Owners
- **Alice Williams**: `owner1@email.com` / `password`
- **Bob Brown**: `owner2@email.com` / `password`

## 📁 Project Structure

```
pet-health-tracker/
├── admin_dashboard.php      # Admin dashboard
├── db.php                   # Database connection
├── index.php               # Landing page
├── login.php               # User authentication
├── register.php            # User registration
├── logout.php              # Session termination
├── manage_user.php         # User management (Admin)
├── manage_appointments.php # Appointment management
├── manage_vet_application.php # Vet application management
├── owner_dashboard.php     # Pet owner dashboard
├── vet_dashboard.php       # Veterinarian dashboard
├── vet_pet_details.php     # Pet details and health records
├── add_pet.php             # Add new pet
├── add_health_record.php   # Add health records
├── schedule_appointment.php # Appointment scheduling
├── database.sql            # Database schema and sample data
├── css/                    # Compiled CSS files
├── js/                     # JavaScript files
│   └── main.js            # Main JavaScript functionality
├── input.css              # Tailwind CSS input file
├── package.json           # Node.js dependencies
└── README.md              # This file
```

## 🎯 Core Functionality

### Pet Owner Features
- **Dashboard**: Overview of all pets and recent activities
- **Pet Management**: Add, edit, and view pet profiles
- **Health Records**: View complete health history
- **Appointments**: Schedule and manage veterinary appointments
- **Vaccinations**: Track vaccination schedules and due dates

### Veterinarian Features
- **Patient Dashboard**: Search and filter through patients
- **Health Records**: Add comprehensive health records with multiple access points:
  - Patient cards "Add Record" buttons
  - Appointment quick actions
  - Header dropdown menu
  - Floating action button
- **Appointment Management**: View and manage scheduled appointments
- **Patient Details**: Complete patient information and history

### Administrator Features
- **User Management**: Create, edit, delete users
- **Role Management**: Assign and modify user roles
- **System Overview**: Monitor system usage and activities
- **Veterinarian Applications**: Review and approve vet applications

## 🔒 Security Features

- **Password Hashing**: BCrypt password hashing
- **SQL Injection Prevention**: Prepared statements
- **Session Management**: Secure PHP sessions
- **Role-Based Access Control**: Multi-level authorization
- **Input Validation**: Server-side form validation
- **CSRF Protection**: Form token validation

## 🎨 User Interface

- **Responsive Design**: Mobile-first responsive layout
- **Modern UI**: Clean, professional interface using Tailwind CSS
- **Accessibility**: WCAG compliant design elements
- **Interactive Elements**: Modal forms, dropdown menus, search functionality
- **Visual Feedback**: Success/error messages, loading states

## 🧪 Testing

The system includes sample data for testing:
- 5 user accounts across all roles
- 4 sample pets with complete profiles
- 6 health records with various types
- Appointment scheduling examples

## 🚧 Development

### CSS Compilation
```bash
# Install dependencies
npm install

# Watch for changes (development)
npm run watch

# Build for production
npm run build
```

### Database Migrations
When making database changes, update the `database.sql` file and test with fresh installations.

## 📝 API Documentation

### Health Record Types
The system supports the following health record types:
- `vaccination` - Vaccination records
- `checkup` - Regular wellness exams
- `illness` - Illness and treatment records
- `surgery` - Surgical procedures
- `medication` - Medication prescriptions
- `lab_result` - Laboratory test results
- `note` - General notes
- `emergency` - Emergency visits

### User Roles
- `admin` - System administrators
- `veterinarian` - Licensed veterinarians
- `pet_owner` - Pet owners

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
1. Check the documentation in this README
2. Review the code comments in the source files
3. Create an issue in the repository
4. Contact the development team

## 🎉 Acknowledgments

- Built with PHP and MySQL
- Styled with Tailwind CSS
- Icons by Heroicons
- Inspired by modern veterinary practice management needs

---

**Pet Health Tracker** - Making pet healthcare management simple and efficient! 🐾
