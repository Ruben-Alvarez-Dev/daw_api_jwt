-- Usuarios iniciales (admin, supervisor, customer)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Supervisor', 'supervisor@supervisor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor'),
('Customer', 'customer@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('María García', 'maria.garcia@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Juan Martínez', 'juan.martinez@hotmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Ana López', 'ana.lopez@yahoo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Carlos Rodríguez', 'carlos.rodriguez@outlook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Laura Fernández', 'laura.fernandez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('David Sánchez', 'david.sanchez@yahoo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Elena Pérez', 'elena.perez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Restaurantes con sus zonas
INSERT INTO restaurants (name, description, address, phone, email, created_at, updated_at) VALUES
('La Parrilla Mediterránea', 'Restaurante especializado en carnes a la brasa y pescados frescos', 'Calle Mayor 123, Valencia', '961234567', 'info@parrillamediterranea.com', NOW(), NOW()),
('El Rincón del Mar', 'Marisquería y arroces con las mejores vistas al mar', 'Paseo Marítimo 45, Valencia', '962345678', 'reservas@rincondelmar.com', NOW(), NOW()),
('Pasta & Love', 'Auténtica cocina italiana con pasta fresca casera', 'Calle Colón 67, Valencia', '963456789', 'info@pastaandlove.com', NOW(), NOW()),
('El Huerto Vegano', 'Cocina vegetariana y vegana creativa', 'Calle Ruzafa 89, Valencia', '964567890', 'reservas@huertovegano.com', NOW(), NOW()),
('Sushi Fusion', 'Fusión de cocina japonesa tradicional y moderna', 'Avenida del Puerto 234, Valencia', '965678901', 'info@sushifusion.com', NOW(), NOW()),
('Tapas & Vinos', 'Bar de tapas tradicionales y vinos selectos', 'Plaza del Ayuntamiento 12, Valencia', '966789012', 'reservas@tapasyvinos.com', NOW(), NOW()),
('La Brasería', 'Carnes premium y platos a la brasa', 'Calle Sagunto 45, Valencia', '967890123', 'info@labraseria.com', NOW(), NOW()),
('Mar y Montaña', 'Lo mejor del mar y la montaña en un solo lugar', 'Avenida del Cid 78, Valencia', '968901234', 'reservas@marymontana.com', NOW(), NOW()),
('El Asador', 'Asador tradicional con horno de leña', 'Calle San Vicente 90, Valencia', '969012345', 'info@elasador.com', NOW(), NOW()),
('La Terraza Garden', 'Cocina mediterránea en un entorno ajardinado', 'Avenida de Francia 123, Valencia', '960123456', 'reservas@terrazagarden.com', NOW(), NOW());

-- Zonas para cada restaurante
INSERT INTO zones (restaurant_id, name, description) VALUES
-- La Parrilla Mediterránea
(1, 'Main', 'Sala principal con vista a la parrilla'),
(1, 'Terraza', 'Terraza exterior con pérgola'),
(1, 'Privado', 'Sala privada para eventos'),

-- El Rincón del Mar
(2, 'Main', 'Comedor principal con vistas al mar'),
(2, 'Terraza', 'Terraza sobre la playa'),

-- Pasta & Love
(3, 'Main', 'Sala principal estilo toscano'),
(3, 'Cantina', 'Zona informal con barra'),
(3, 'Giardino', 'Jardín interior'),

-- El Huerto Vegano
(4, 'Main', 'Sala principal con jardín vertical'),
(4, 'Terraza', 'Terraza con huerto urbano'),

-- Sushi Fusion
(5, 'Main', 'Sala principal con barra de sushi'),
(5, 'Tatami', 'Zona de mesas bajas tradicionales'),
(5, 'Lounge', 'Zona de cocktails y tapas'),

-- Tapas & Vinos
(6, 'Main', 'Barra y mesas altas'),
(6, 'Bodega', 'Comedor en la bodega'),

-- La Brasería
(7, 'Main', 'Sala principal junto a la brasería'),
(7, 'Terraza', 'Terraza climatizada'),

-- Mar y Montaña
(8, 'Main', 'Comedor principal'),
(8, 'Mirador', 'Zona elevada con vistas'),

-- El Asador
(9, 'Main', 'Sala principal con horno de leña'),
(9, 'Patio', 'Patio interior tradicional'),

-- La Terraza Garden
(10, 'Main', 'Sala interior principal'),
(10, 'Jardín', 'Jardín con pérgola'),
(10, 'Chill Out', 'Zona lounge exterior');

-- Mesas para cada zona (5 por zona)
INSERT INTO tables (zone_id, name, capacity) 
SELECT 
    z.id,
    CONCAT('Mesa ', ROW_NUMBER() OVER (PARTITION BY z.id ORDER BY (SELECT NULL))),
    CASE 
        WHEN ROW_NUMBER() OVER (PARTITION BY z.id ORDER BY (SELECT NULL)) <= 2 THEN 2
        WHEN ROW_NUMBER() OVER (PARTITION BY z.id ORDER BY (SELECT NULL)) <= 4 THEN 4
        ELSE 6
    END
FROM zones z
CROSS JOIN (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) numbers;

-- Reservas (40 en total, distribuidas en el futuro)
INSERT INTO reservations (user_id, table_id, date, time, diners, status, created_at, updated_at)
SELECT 
    u.id as user_id,
    t.id as table_id,
    DATE_ADD(CURDATE(), INTERVAL (1 + FLOOR(RAND() * 30)) DAY) as date,
    CASE 
        WHEN RAND() < 0.5 THEN '13:30:00'
        ELSE '20:30:00'
    END as time,
    LEAST(t.capacity, 2 + FLOOR(RAND() * 4)) as diners,
    'pending' as status,
    NOW() as created_at,
    NOW() as updated_at
FROM 
    users u
    CROSS JOIN tables t
WHERE 
    u.role = 'customer'
    AND RAND() < 0.1
LIMIT 40;
