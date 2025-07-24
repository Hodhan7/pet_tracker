# Pet Health Tracker - Quick Start Guide

## 🎉 Database Setup Complete!

Your Pet Health Tracker database has been successfully created with a comprehensive schema and sample data.

## 🔐 Test Accounts

You can now log in with these pre-created accounts:

### Admin Account
- **Email**: `admin@pethealthtracker.com`
- **Password**: `password`
- **Role**: Administrator
- **Access**: Full system management, vet applications, user management

### Veterinarian Account
- **Email**: `dr.smith@vetclinic.com`
- **Password**: `password`
- **Role**: Veterinarian
- **Access**: Manage appointments, view pet records, create health records

### Pet Owner Account
- **Email**: `owner1@email.com`
- **Password**: `password`
- **Role**: Pet Owner
- **Access**: Manage pets, schedule appointments, view health records

## 📋 What's Included

### Sample Data
- **5 Users**: 1 admin, 2 veterinarians, 2 pet owners
- **4 Pets**: Dogs and cats with complete profiles
- **Health Records**: Vaccination records, checkups, treatments
- **Appointments**: Scheduled and completed appointments
- **Medications**: Current and historical medication tracking

### Database Tables
- `users` - User accounts and profiles
- `veterinarian_profiles` - Extended vet information
- `pets` - Pet information and basic health data
- `health_records` - Comprehensive medical history
- `vaccinations` - Vaccination tracking
- `appointments` - Appointment scheduling
- `medications` - Medication management
- `reminders` - Automated reminder system
- `weight_records` - Weight tracking over time
- `notifications` - In-app notifications
- `veterinarian_applications` - Vet license applications
- `system_settings` - System configuration

## 🚀 Getting Started

1. **Access the Application**
   ```
   http://your-domain/index.php
   ```

2. **Login with Test Account**
   - Choose any of the test accounts above
   - Explore different user roles and features

3. **Test Key Features**
   - **Pet Owners**: Add pets, schedule appointments, view health records
   - **Veterinarians**: Manage appointments, create health records
   - **Admins**: Approve vet applications, manage users

## 🛠️ Database Management

### Reset Database (if needed)
```bash
php reset_database.php
```
This will drop all tables and recreate them with fresh sample data.

### Backup Database
```bash
mysqldump -u root -p pet_health_tracker > backup_$(date +%Y%m%d).sql
```

### View Database Structure
```bash
php -r "
require 'db.php';
\$result = \$conn->query('SHOW TABLES');
while(\$row = \$result->fetch_array()) echo \$row[0] . PHP_EOL;
"
```

## 📊 Database Features

### Performance Optimized
- **Indexes**: Created for all frequently queried fields
- **Foreign Keys**: Ensure data integrity
- **Soft Deletes**: Uses `is_active` flags instead of hard deletes

### Security Features
- **Password Hashing**: Uses PHP's `password_hash()` function
- **Role-Based Access**: Different permissions for each user type
- **Input Validation**: Required fields and data types enforced

### Advanced Features
- **JSON Storage**: For complex data like working hours, attachments
- **Enum Fields**: For status tracking and categorization
- **Timestamp Tracking**: Created and updated timestamps on all tables
- **Reminder System**: Automated notifications for important events

## 🔧 Customization

### Add New Users
Users can register through the registration page, or you can add them directly:

```sql
INSERT INTO users (email, password, role, first_name, last_name) 
VALUES ('new@email.com', '$2y$10$hash', 'pet_owner', 'First', 'Last');
```

### Modify System Settings
```sql
UPDATE system_settings 
SET setting_value = 'new_value' 
WHERE setting_key = 'appointment_reminder_days';
```

### View Sample Data
```sql
-- View all pets with their owners
SELECT p.name, p.species, p.breed, u.first_name, u.last_name 
FROM pets p 
JOIN users u ON p.owner_id = u.id;

-- View upcoming appointments
SELECT a.appointment_date, p.name as pet_name, 
       CONCAT(u.first_name, ' ', u.last_name) as owner_name
FROM appointments a 
JOIN pets p ON a.pet_id = p.id 
JOIN users u ON a.owner_id = u.id 
WHERE a.appointment_date > NOW();
```

## 📚 Documentation

- **Full Database Schema**: See `DATABASE_README.md`
- **Table Structure**: Detailed in `database.sql`
- **API Documentation**: Available in individual PHP files

## 🆘 Troubleshooting

### Common Issues
1. **Login Issues**: Ensure you're using the correct test credentials
2. **Database Connection**: Check `db.php` for correct database settings
3. **Permission Errors**: Ensure your MySQL user has appropriate permissions

### Reset Everything
If something goes wrong, run:
```bash
php reset_database.php
```

This will give you a fresh start with all sample data restored.

---

## 🎯 Next Steps

1. **Explore the Interface**: Login with different accounts to see role-based features
2. **Test Functionality**: Schedule appointments, add health records, manage pets
3. **Customize**: Modify the schema or add new features as needed
4. **Deploy**: Move to production when ready

Your Pet Health Tracker is now ready to use! 🐕🐱
