-- Table des paiements
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL, -- 'stripe', 'paypal', etc.
    transaction_id VARCHAR(255) NOT NULL, -- ID de la transaction côté passerelle de paiement
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

-- Modification de la table reservations pour ajouter un champ payment_status
ALTER TABLE reservations 
ADD COLUMN payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid' AFTER status;
