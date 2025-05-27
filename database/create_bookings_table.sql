-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES account(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Add an index to improve query performance
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_car ON bookings(car_id);
CREATE INDEX idx_bookings_dates ON bookings(start_date, end_date);

-- Add a comment explaining the table
-- The bookings table stores all car reservations
-- It references the account table for users (not the users table)
-- and the cars table for the reserved vehicles 