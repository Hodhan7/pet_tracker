-- Database migration to add user settings and preferences fields
-- Run this to add support for the new profile and settings features

-- Add missing fields to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS bio TEXT AFTER zip_code,
ADD COLUMN IF NOT EXISTS notification_preferences JSON AFTER bio,
ADD COLUMN IF NOT EXISTS privacy_settings JSON AFTER notification_preferences,
ADD COLUMN IF NOT EXISTS deletion_requested BOOLEAN DEFAULT FALSE AFTER privacy_settings,
ADD COLUMN IF NOT EXISTS deletion_reason TEXT AFTER deletion_requested,
ADD COLUMN IF NOT EXISTS deletion_requested_at TIMESTAMP NULL AFTER deletion_reason;

-- Update existing users with default settings
UPDATE users 
SET 
    notification_preferences = JSON_OBJECT(
        'email_notifications', TRUE,
        'sms_notifications', FALSE,
        'appointment_reminders', TRUE,
        'vaccination_reminders', TRUE,
        'health_updates', TRUE,
        'newsletter', FALSE
    ),
    privacy_settings = JSON_OBJECT(
        'profile_visibility', 'private',
        'data_sharing', FALSE,
        'analytics', TRUE
    )
WHERE notification_preferences IS NULL OR privacy_settings IS NULL;

-- Indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_deletion_requested ON users(deletion_requested);
CREATE INDEX IF NOT EXISTS idx_users_last_login ON users(last_login);
CREATE INDEX IF NOT EXISTS idx_users_role_active ON users(role, is_active);

-- Show final structure
DESCRIBE users;
