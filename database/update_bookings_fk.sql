-- Update bookings table to use account table as foreign key
-- First drop the existing foreign key constraint
ALTER TABLE bookings
DROP FOREIGN KEY bookings_ibfk_1;

-- Then add the new foreign key constraint to the account table
ALTER TABLE bookings
ADD CONSTRAINT bookings_account_fk
FOREIGN KEY (user_id) REFERENCES account(id) ON DELETE CASCADE;

-- Update any existing bookings to associate with account users
-- This assumes there are matching IDs between users and account tables
-- If not, you'll need a more complex migration strategy

-- Finally, add a comment explaining the change
-- This foreign key was changed from users table to account table
-- because the system authenticates using the account table 