# Booking System Fix

## Issue
The booking system has a critical issue where user reservations aren't appearing in the "My Reservations" page. This happens because:

1. The system uses the `account` table for user authentication (see `actions/login.php`)
2. BUT the `bookings` table has a foreign key constraint to the `users` table instead of the `account` table
3. This means bookings are technically being stored, but with a foreign key to a table that doesn't match the currently logged-in users

## Solution

### 1. Database Changes
Run the SQL script `update_bookings_fk.sql` to update the foreign key constraint:

```bash
mysql -u your_username -p your_database < database/update_bookings_fk.sql
```

### 2. Code Changes
The `create-booking.php` file has already been updated to use the `account` table instead of the `users` table for storing reservation information. Verify that the changes are in place.

### 3. Testing
After applying these changes:
1. Create a new user account
2. Log in with that account
3. Make a car reservation
4. Visit the "My Reservations" page
5. Verify that your reservation appears correctly

## Technical Details

The issue was caused by a mismatch between the authentication system and the database schema. The system authenticates users against the `account` table, but the `bookings` table had a foreign key to the `users` table. This meant that when a user made a reservation, the booking was saved with their account ID, but as a foreign key to the `users` table, which didn't have a corresponding record with that ID.

The SQL script removes the existing foreign key constraint and adds a new one referencing the `account` table instead, which aligns with how the authentication system works. 