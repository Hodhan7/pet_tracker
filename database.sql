-- Pet Health Tracker Database Schema
-- Created: July 23, 2025
-- Description: Comprehensive pet health management system

-- Users table - stores all user accounts (pet owners, veterinarians, admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('pet_owner', 'veterinarian', 'admin') DEFAULT 'pet_owner',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(50),
    zip_code VARCHAR(10),
    emergency_contact VARCHAR(255),
    emergency_phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Veterinarian profiles - additional information for veterinarians
CREATE TABLE veterinarian_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    license_number VARCHAR(100) UNIQUE,
    clinic_name VARCHAR(255),
    clinic_address TEXT,
    clinic_phone VARCHAR(20),
    specializations TEXT, -- JSON array of specializations
    years_experience INT,
    education TEXT,
    certifications TEXT,
    bio TEXT,
    consultation_fee DECIMAL(10,2),
    is_accepting_patients BOOLEAN DEFAULT TRUE,
    working_hours JSON, -- Store weekly schedule
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Pets table - stores pet information
CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    species VARCHAR(50) NOT NULL,
    breed VARCHAR(100),
    gender ENUM('male', 'female', 'unknown') DEFAULT 'unknown',
    dob DATE,
    weight DECIMAL(5,2), -- in kg
    color VARCHAR(100),
    microchip_id VARCHAR(50) UNIQUE,
    is_spayed_neutered BOOLEAN DEFAULT FALSE,
    allergies TEXT,
    medications TEXT,
    special_needs TEXT,
    emergency_contact VARCHAR(255),
    emergency_phone VARCHAR(20),
    photo_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Health records - comprehensive medical history
CREATE TABLE health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    veterinarian_id INT,
    record_type ENUM('vaccination', 'checkup', 'illness', 'surgery', 'medication', 'lab_result', 'note', 'emergency') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    diagnosis TEXT,
    treatment TEXT,
    medications_prescribed TEXT,
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE,
    record_date DATE NOT NULL,
    weight_at_visit DECIMAL(5,2),
    temperature DECIMAL(4,1),
    attachments JSON, -- Array of file URLs
    cost DECIMAL(10,2),
    is_urgent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (veterinarian_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Vaccinations tracking
CREATE TABLE vaccinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    vaccine_name VARCHAR(255) NOT NULL,
    vaccine_type VARCHAR(100),
    administered_date DATE NOT NULL,
    expiry_date DATE,
    next_due_date DATE,
    veterinarian_id INT,
    batch_number VARCHAR(100),
    manufacturer VARCHAR(255),
    site_administered VARCHAR(100),
    reaction TEXT,
    cost DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (veterinarian_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Appointments system
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    veterinarian_id INT NOT NULL,
    owner_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    duration_minutes INT DEFAULT 30,
    appointment_type ENUM('checkup', 'vaccination', 'emergency', 'surgery', 'consultation', 'follow_up') DEFAULT 'checkup',
    purpose TEXT,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    cancellation_reason TEXT,
    notes TEXT,
    reminder_sent BOOLEAN DEFAULT FALSE,
    cost DECIMAL(10,2),
    payment_status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (veterinarian_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Veterinarian applications
CREATE TABLE veterinarian_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_number VARCHAR(100) NOT NULL,
    clinic_name VARCHAR(255),
    qualifications TEXT NOT NULL,
    specializations TEXT,
    years_experience INT,
    education TEXT,
    certifications TEXT,
    documents JSON, -- Array of uploaded document URLs
    status ENUM('pending', 'under_review', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Medications tracking
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE,
    prescribed_by INT,
    purpose TEXT,
    instructions TEXT,
    side_effects TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (prescribed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Reminders system
CREATE TABLE reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT,
    type ENUM('vaccination', 'medication', 'appointment', 'checkup', 'custom') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reminder_date DATE NOT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    recurrence_pattern VARCHAR(50), -- 'daily', 'weekly', 'monthly', 'yearly'
    is_completed BOOLEAN DEFAULT FALSE,
    is_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- Weight tracking
CREATE TABLE weight_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    unit ENUM('kg', 'lbs') DEFAULT 'kg',
    recorded_date DATE NOT NULL,
    notes TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- System notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('appointment', 'reminder', 'system', 'application_status') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT, -- ID of related record (appointment_id, reminder_id, etc.)
    related_type VARCHAR(50), -- Type of related record
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- System settings
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_pets_owner ON pets(owner_id);
CREATE INDEX idx_pets_species ON pets(species);
CREATE INDEX idx_health_records_pet ON health_records(pet_id);
CREATE INDEX idx_health_records_date ON health_records(record_date);
CREATE INDEX idx_health_records_type ON health_records(record_type);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_vet ON appointments(veterinarian_id);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_vaccinations_pet ON vaccinations(pet_id);
CREATE INDEX idx_vaccinations_due ON vaccinations(next_due_date);
CREATE INDEX idx_medications_pet ON medications(pet_id);
CREATE INDEX idx_medications_active ON medications(is_active);
CREATE INDEX idx_reminders_date ON reminders(reminder_date);
CREATE INDEX idx_reminders_user ON reminders(user_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);

-- Insert sample data for testing

-- Sample users
INSERT INTO users (email, password, role, first_name, last_name, phone, address, city, state, zip_code) VALUES
('admin@pethealthtracker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User', '555-0100', '123 Admin St', 'AdminCity', 'AC', '12345'),
('dr.smith@vetclinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'veterinarian', 'John', 'Smith', '555-0200', '456 Vet Ave', 'VetCity', 'VC', '23456'),
('dr.johnson@animalcare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'veterinarian', 'Sarah', 'Johnson', '555-0201', '789 Care Blvd', 'CareCity', 'CC', '34567'),
('owner1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pet_owner', 'Alice', 'Williams', '555-0300', '321 Pet Lane', 'PetCity', 'PC', '45678'),
('owner2@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pet_owner', 'Bob', 'Brown', '555-0301', '654 Animal Dr', 'AnimalTown', 'AT', '56789');

-- Veterinarian profiles for the vets
INSERT INTO veterinarian_profiles (user_id, license_number, clinic_name, clinic_address, clinic_phone, specializations, years_experience, education, consultation_fee, working_hours) VALUES
(2, 'VET123456', 'Smith Veterinary Clinic', '456 Vet Ave, VetCity, VC 23456', '555-0200', '["General Practice", "Surgery", "Internal Medicine"]', 15, 'DVM from State University, Board Certified in Internal Medicine', 75.00, '{"monday": "9:00-17:00", "tuesday": "9:00-17:00", "wednesday": "9:00-17:00", "thursday": "9:00-17:00", "friday": "9:00-17:00", "saturday": "9:00-13:00", "sunday": "closed"}'),
(3, 'VET789012', 'Johnson Animal Care Center', '789 Care Blvd, CareCity, CC 34567', '555-0201', '["Emergency Medicine", "Surgery", "Dermatology"]', 12, 'DVM from Animal Medical College, Emergency Medicine Residency', 85.00, '{"monday": "8:00-18:00", "tuesday": "8:00-18:00", "wednesday": "8:00-18:00", "thursday": "8:00-18:00", "friday": "8:00-18:00", "saturday": "10:00-16:00", "sunday": "10:00-14:00"}');

-- Sample pets
INSERT INTO pets (owner_id, name, species, breed, gender, dob, weight, color, is_spayed_neutered, allergies) VALUES
(4, 'Buddy', 'Dog', 'Golden Retriever', 'male', '2020-05-15', 28.5, 'Golden', TRUE, 'None known'),
(4, 'Whiskers', 'Cat', 'Siamese', 'female', '2019-08-22', 4.2, 'Seal Point', TRUE, 'Fish protein'),
(5, 'Max', 'Dog', 'German Shepherd', 'male', '2018-12-10', 35.8, 'Black and Tan', FALSE, 'Chicken'),
(5, 'Luna', 'Cat', 'Persian', 'female', '2021-03-08', 3.8, 'White', TRUE, 'None known');

-- Sample health records
INSERT INTO health_records (pet_id, veterinarian_id, record_type, title, description, diagnosis, treatment, record_date, weight_at_visit, temperature) VALUES
(1, 2, 'checkup', 'Annual Wellness Exam', 'Routine annual checkup', 'Healthy', 'Continue current diet and exercise routine', '2024-05-15', 28.5, 38.5),
(1, 2, 'vaccination', 'DHPP Vaccination', 'Annual DHPP booster vaccination', NULL, 'DHPP vaccine administered', '2024-05-15', 28.5, 38.5),
(2, 3, 'checkup', 'Senior Cat Checkup', 'Routine senior cat wellness exam', 'Mild dental tartar', 'Dental cleaning recommended', '2024-06-20', 4.2, 38.8),
(3, 2, 'illness', 'Skin Allergy Flare-up', 'Presenting with itching and redness', 'Allergic dermatitis', 'Antihistamine prescribed, hypoallergenic diet recommended', '2024-07-10', 35.8, 39.2),
(4, 3, 'checkup', 'Kitten Wellness Check', 'First wellness exam for new kitten', 'Healthy kitten', 'Continue kitten food, schedule spay surgery', '2024-04-12', 3.8, 38.6);

-- Sample vaccinations
INSERT INTO vaccinations (pet_id, vaccine_name, vaccine_type, administered_date, expiry_date, next_due_date, veterinarian_id, manufacturer) VALUES
(1, 'DHPP', 'Core', '2024-05-15', '2025-05-15', '2025-05-15', 2, 'Zoetis'),
(1, 'Rabies', 'Core', '2024-05-15', '2027-05-15', '2027-05-15', 2, 'Merial'),
(2, 'FVRCP', 'Core', '2024-06-20', '2025-06-20', '2025-06-20', 3, 'Zoetis'),
(3, 'DHPP', 'Core', '2024-01-15', '2025-01-15', '2025-01-15', 2, 'Zoetis'),
(4, 'FVRCP', 'Core', '2024-04-12', '2025-04-12', '2025-04-12', 3, 'Zoetis');

-- Sample appointments
INSERT INTO appointments (pet_id, veterinarian_id, owner_id, appointment_date, duration_minutes, appointment_type, purpose, status) VALUES
(1, 2, 4, '2024-08-15 10:00:00', 30, 'checkup', 'Annual wellness exam', 'confirmed'),
(2, 3, 4, '2024-08-20 14:30:00', 45, 'consultation', 'Follow-up for dental cleaning', 'confirmed'),
(3, 2, 5, '2024-08-25 09:15:00', 30, 'follow_up', 'Check on skin allergy treatment', 'pending'),
(4, 3, 5, '2024-09-05 16:00:00', 60, 'surgery', 'Spay surgery', 'confirmed');

-- Sample medications
INSERT INTO medications (pet_id, name, dosage, frequency, start_date, end_date, prescribed_by, purpose, instructions) VALUES
(3, 'Benadryl', '25mg', 'Twice daily', '2024-07-10', '2024-07-24', 2, 'Allergic reaction', 'Give with food, monitor for drowsiness'),
(2, 'Dental Chews', '1 chew', 'Daily', '2024-06-20', NULL, 3, 'Dental health', 'Give as treat, supervise while chewing');

-- Sample reminders
INSERT INTO reminders (user_id, pet_id, type, title, description, reminder_date, is_recurring, recurrence_pattern) VALUES
(4, 1, 'vaccination', 'Buddy DHPP Due', 'Annual DHPP vaccination is due', '2025-05-15', TRUE, 'yearly'),
(4, 2, 'vaccination', 'Whiskers FVRCP Due', 'Annual FVRCP vaccination is due', '2025-06-20', TRUE, 'yearly'),
(5, 3, 'checkup', 'Max Annual Checkup', 'Annual wellness examination', '2025-01-15', TRUE, 'yearly'),
(5, 4, 'appointment', 'Luna Spay Surgery', 'Scheduled spay surgery', '2024-09-05', FALSE, NULL);

-- Sample system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('appointment_reminder_days', '3', 'Number of days before appointment to send reminder'),
('vaccination_reminder_days', '30', 'Number of days before vaccination due date to send reminder'),
('clinic_hours', '{"open": "08:00", "close": "18:00"}', 'Default clinic operating hours'),
('emergency_contact', '555-EMERGENCY', 'Emergency contact number for after-hours'),
('max_appointment_duration', '120', 'Maximum appointment duration in minutes');