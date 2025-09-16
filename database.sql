CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    manufacturer VARCHAR(255) NOT NULL,
    batch_no VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    expiry_date DATE NOT NULL,
    photo VARCHAR(255) NULL, -- optional, store image file path
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
