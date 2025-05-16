# Car Rental Database Setup

This folder contains the SQL files needed to set up the database for the car rental website.

## Setup Instructions

### 1. Using phpMyAdmin

1. Open phpMyAdmin (usually at http://localhost/phpmyadmin/ if using XAMPP)
2. Click on "Import" in the top menu
3. Click "Browse" and select the `car_rental.sql` file
4. Click "Go" to import the database

### 2. Using MySQL Command Line

1. Open your terminal or command prompt
2. Navigate to this folder
3. Connect to MySQL server:
   ```
   mysql -u root -p
   ```
4. Import the SQL file:
   ```
   source car_rental.sql
   ```

## Database Structure

The database is named `rental` and consists of three main tables:

1. **cars**: Contains information about both regular cars and business vehicles
2. **users**: Stores user information for authentication
3. **bookings**: Records of car rentals

## Default Admin Access

An admin user is created by default with these credentials:
- Email: admin@carrental.com
- Password: admin123

## Customizing the Database

- You can modify the `car_rental.sql` file to add more cars or change existing ones
- Make sure the image paths in the SQL file match your actual image paths in the website

## Connection to the Website

The website connects to this database through the `includes/db_connect.php` file. The current settings are:
- Database name: rental
- Username: root
- Password: (empty)

If you need to change these settings, please update the db_connect.php file.

## Troubleshooting

If you encounter any issues:
1. Make sure your MySQL server is running
2. Verify your database username and password
3. Check that the XAMPP environment is properly configured
4. Ensure the paths to car images are correct 