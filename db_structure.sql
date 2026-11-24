-- Lagoon Breeze Villa - Negombo : database structure + sample data
-- Import this file in phpMyAdmin (Import tab) or run it in the MySQL console.

CREATE DATABASE IF NOT EXISTS lagoon_breeze_villa CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE lagoon_breeze_villa;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('guest','admin') NOT NULL DEFAULT 'guest',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  room_type VARCHAR(30) NOT NULL,            -- Standard, Deluxe, Family, Suite
  description TEXT NOT NULL,
  facilities VARCHAR(255) NOT NULL,          -- comma separated list
  max_guests INT NOT NULL,
  price_per_night DECIMAL(10,2) NOT NULL,    -- LKR
  image VARCHAR(100) NOT NULL DEFAULT 'Garden_View_Standard_room_descri…_cfeef5c1.jpg',
  status ENUM('Available','Unavailable') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  guests INT NOT NULL,
  nights INT NOT NULL,
  total_cost DECIMAL(10,2) NOT NULL,
  special_requests VARCHAR(500) DEFAULT '',
  status ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  booking_id INT NOT NULL UNIQUE,            -- one review per booking
  rating TINYINT NOT NULL,                   -- 1 to 5
  comment VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample users (password for all = Password123)
INSERT INTO users (full_name, email, phone, password_hash, role) VALUES
('Villa Admin', 'admin@lagoonbreeze.lk', '0312222333', '$2b$10$MhvSy6YxO0PrL1Jk9IYRee5NCIxfl42X3JVsWKGmShOkxkRZFK2zW', 'admin'),
('Nimali Perera', 'nimali@example.com', '0771234567', '$2b$10$MhvSy6YxO0PrL1Jk9IYRee5NCIxfl42X3JVsWKGmShOkxkRZFK2zW', 'guest'),
('Kasun Fernando', 'kasun@example.com', '0712345678', '$2b$10$MhvSy6YxO0PrL1Jk9IYRee5NCIxfl42X3JVsWKGmShOkxkRZFK2zW', 'guest'),
('Emma Wilson', 'emma@example.com', '0759876543', '$2b$10$MhvSy6YxO0PrL1Jk9IYRee5NCIxfl42X3JVsWKGmShOkxkRZFK2zW', 'guest');

-- Sample rooms (prices in LKR)
INSERT INTO rooms (name, room_type, description, facilities, max_guests, price_per_night, image, status) VALUES
('Garden View Standard', 'Standard', 'A cosy room with a garden view, a short walk from the lagoon path. Good for solo travellers and couples.', 'Air conditioning, Wi-Fi, Hot water, Breakfast', 2, 12500.00, 'Garden_View_Standard_room_descri…_cfeef5c1.jpg', 'Available'),
('Palm Courtyard Standard', 'Standard', 'Quiet ground floor room opening to a palm courtyard with a small sit-out area.', 'Fan, Wi-Fi, Hot water, Breakfast', 2, 9800.00, 'Palm_Courtyard_Standard_Quiet_ground_daf25b3a.jpg', 'Available'),
('Sea Breeze Deluxe', 'Deluxe', 'Spacious deluxe room with a private balcony facing the sea breeze and sunsets.', 'Air conditioning, Wi-Fi, Balcony, Mini fridge, Breakfast', 3, 18500.00, 'Sea_Breeze_Deluxe_Spacious_deluxe_c3f3b912.jpg', 'Available'),
('Lagoon Deluxe Twin', 'Deluxe', 'Two single beds, a work desk and a view over the Negombo lagoon.', 'Air conditioning, Wi-Fi, Work desk, Tea/coffee maker', 2, 16500.00, 'Lagoon_Deluxe_Twin_room_overview_414c257b.jpg', 'Available'),
('Coconut Family Room', 'Family', 'Large family room with one double bed and two single beds, ideal for families visiting from abroad.', 'Air conditioning, Wi-Fi, Extra beds, Mini fridge, Breakfast', 5, 24000.00, 'Coconut_family_room_accommodation_bb6bcc0c.jpg', 'Available'),
('Ocean Suite', 'Suite', 'Our best room: separate living area, king bed, bathtub and a wide terrace facing Negombo beach.', 'Air conditioning, Wi-Fi, Bathtub, Terrace, Mini bar, Breakfast', 4, 35000.00, 'Sea_Breeze_Deluxe_room_description_e6ea2dc1.jpg', 'Available');

-- Sample bookings
INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, nights, total_cost, special_requests, status) VALUES
(2, 3, '2026-10-05', '2026-10-08', 2, 3, 55500.00, 'Late check-in please', 'Confirmed'),
(3, 5, '2026-10-12', '2026-10-15', 4, 3, 72000.00, 'Need one baby cot', 'Confirmed'),
(4, 6, '2026-12-20', '2026-12-24', 2, 4, 140000.00, 'Airport pickup from BIA', 'Confirmed'),
(2, 1, '2026-12-28', '2026-12-30', 2, 2, 25000.00, '', 'Pending'),
(3, 2, '2026-11-30', '2026-12-02', 1, 2, 19600.00, '', 'Cancelled'),
(4, 4, '2027-01-10', '2027-01-13', 2, 3, 49500.00, 'Quiet room if possible', 'Pending');

-- Sample reviews (only for past confirmed bookings)
INSERT INTO reviews (user_id, booking_id, rating, comment) VALUES
(2, 1, 5, 'Lovely stay! The balcony view at sunset was the highlight and the staff were very friendly.'),
(3, 2, 4, 'Great for our family. Rooms were clean and Browns Beach was only a short tuk-tuk ride away.');
