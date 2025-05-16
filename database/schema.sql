

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'client') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    available BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- Insert admin user (password: admin123)
INSERT INTO users (email, password, name, role) VALUES 
('admin@luxurycars.com', '$2y$10$8KQYqFZ5KZ5KQYqFZ5KZ5O5KQYqFZ5KZ5KQYqFZ5KZ5KQYqFZ5K', 'Admin', 'admin');

-- Insert sample vehicles
INSERT INTO vehicles (brand, model, year, price_per_day, description, image) VALUES
('Ferrari', '488 GTB', 2023, 1500.00, 'Supercar emblématique avec moteur V8 bi-turbo', 'ferrari-488.jpg'),
('Lamborghini', 'Huracán', 2023, 1800.00, 'Puissance et design italien', 'huracan.jpg'),
('Rolls-Royce', 'Phantom', 2023, 2500.00, 'Le summum du luxe automobile', 'phantom.jpg'),
('Porsche', '911 GT3', 2023, 1200.00, 'Performance et précision allemande', '911-gt3.jpg'),
('Bentley', 'Continental GT', 2023, 1600.00, 'Grand tourisme de luxe', 'continental-gt.jpg'),
('Mercedes', 'AMG GT', 2023, 1300.00, 'Sport et élégance', 'amg-gt.jpg');
