# Pet Health Tracker - Database Schema Documentation

## Overview
The Pet Health Tracker uses a comprehensive MySQL database schema designed to manage pet health records, appointments, vaccinations, and user management for a veterinary practice management system.

## Database Setup

### Quick Setup
1. Ensure your database connection is configured in `db.php`
2. Run the setup script:
   ```bash
   php setup_database.php
   ```
   Or visit `http://your-domain/setup_database.php` in your browser

### Manual Setup
```bash
mysql -u your_username -p your_database < database.sql
```

## Database Schema

### Core Tables

#### `users`
Stores all user accounts (pet owners, veterinarians, admins)
- **Primary Key**: `id`
- **Unique Fields**: `email`
- **Roles**: `pet_owner`, `veterinarian`, `admin`
- **Features**: Email verification, last login tracking, soft delete

#### `veterinarian_profiles`
Extended profiles for veterinarian users
- **Linked to**: `users.id`
- **Features**: License tracking, clinic information, specializations, working hours
- **JSON Fields**: `specializations`, `working_hours`

#### `pets`
Pet information and basic health data
- **Linked to**: `users.id` (owner)
- **Features**: Microchip tracking, photos, emergency contacts
- **Tracking**: Weight, allergies, medications, special needs

#### `health_records`
Comprehensive medical history for pets
- **Linked to**: `pets.id`, `users.id` (veterinarian)
- **Record Types**: vaccination, checkup, illness, surgery, medication, lab_result, note, emergency
- **Features**: Attachments, follow-up tracking, cost tracking

### Specialized Tables

#### `vaccinations`
Detailed vaccination tracking
- **Features**: Batch numbers, expiry dates, next due dates
- **Tracking**: Manufacturer, administration site, reactions

#### `appointments`
Appointment scheduling and management
- **Statuses**: pending, confirmed, in_progress, completed, cancelled, no_show
- **Types**: checkup, vaccination, emergency, surgery, consultation, follow_up
- **Features**: Duration tracking, cost tracking, payment status

#### `medications`
Current and historical medication tracking
- **Features**: Dosage, frequency, active status
- **Tracking**: Purpose, instructions, side effects

#### `reminders`
Automated reminder system
- **Types**: vaccination, medication, appointment, checkup, custom
- **Features**: Recurring reminders, completion tracking

#### `weight_records`
Weight tracking over time
- **Features**: Multiple units (kg/lbs), notes, recorder tracking

#### `notifications`
In-app notification system
- **Types**: appointment, reminder, system, application_status
- **Features**: Read status, related record linking

### Administrative Tables

#### `veterinarian_applications`
Vet license application workflow
- **Statuses**: pending, under_review, approved, rejected
- **Features**: Document uploads, review tracking

#### `system_settings`
Configurable system parameters
- **Features**: Key-value storage, update tracking

## Sample Data

The schema includes comprehensive sample data:
- **Users**: 5 users (1 admin, 2 vets, 2 pet owners)
- **Pets**: 4 pets with varied species and breeds
- **Health Records**: Complete medical histories
- **Appointments**: Scheduled and completed appointments
- **Vaccinations**: Up-to-date vaccination records

### Default Login Credentials
- **Admin**: `admin@pethealthtracker.com` / `password`
- **Veterinarian**: `dr.smith@vetclinic.com` / `password`
- **Pet Owner**: `owner1@email.com` / `password`

## Performance Optimizations

### Indexes Created
- User email and role lookups
- Pet owner relationships
- Health record dates and types
- Appointment scheduling queries
- Vaccination due date tracking
- Medication active status
- Reminder date queries
- Notification management

### Query Optimization Tips
1. Use date range indexes for appointment queries
2. Filter by `is_active` fields for current data
3. Use status indexes for workflow queries
4. Leverage foreign key indexes for joins

## Data Relationships

```
users (1) → (many) pets
users (1) → (many) appointments (as vet)
users (1) → (many) appointments (as owner)
users (1) → (1) veterinarian_profiles
pets (1) → (many) health_records
pets (1) → (many) vaccinations
pets (1) → (many) medications
pets (1) → (many) weight_records
pets (1) → (many) appointments
```

## Security Considerations

1. **Password Hashing**: All passwords use PHP's `password_hash()` function
2. **Foreign Key Constraints**: Ensure data integrity
3. **Soft Deletes**: `is_active` flags instead of hard deletes
4. **Input Validation**: Required fields and data types enforced
5. **Role-Based Access**: User roles control feature access

## Backup and Maintenance

### Regular Backups
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Maintenance Queries
```sql
-- Clean old notifications (older than 30 days)
DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_read = TRUE;

-- Archive completed appointments (older than 1 year)
UPDATE appointments SET status = 'archived' WHERE appointment_date < DATE_SUB(NOW(), INTERVAL 1 YEAR) AND status = 'completed';

-- Update overdue reminders
UPDATE reminders SET is_completed = FALSE WHERE reminder_date < CURDATE() AND is_completed = FALSE;
```

## Future Enhancements

1. **Audit Logging**: Track all data changes
2. **Document Storage**: File attachment system
3. **Billing Integration**: Invoice and payment tracking
4. **Telemedicine**: Virtual appointment support
5. **Mobile API**: REST API for mobile applications
6. **Analytics**: Reporting and dashboard metrics

## Troubleshooting

### Common Issues

1. **Foreign Key Errors**: Ensure parent records exist before creating child records
2. **Date Format Issues**: Use `YYYY-MM-DD` format for dates
3. **JSON Field Errors**: Validate JSON syntax before insertion
4. **Index Conflicts**: Check for duplicate entries on unique fields

### Database Connection Testing
```php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=pet_health_tracker", $username, $password);
    echo "Database connection successful!";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

## Support

For database-related issues:
1. Check error logs in your web server
2. Verify database connection settings in `db.php`
3. Ensure MySQL version compatibility (5.7+ recommended)
4. Check user permissions for database operations
