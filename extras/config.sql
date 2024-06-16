CREATE TABLE config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,
    value FLOAT
);
INSERT INTO config (name, value) VALUES
('target', 2.5),
('checkInput_high', 90),
('checkInput_medium', 75),
('checkInput_low', 50),
('checkInputuni_high', 95),
('checkInputuni_medium', 85),
('checkInputuni_low', 60),
('attainment_percentage_threshold', 85);

