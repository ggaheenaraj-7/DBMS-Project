CREATE DATABASE IF NOT EXISTS smart_logistics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_logistics;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS feedback, returns, shipment_events, shipments, vehicles, drivers, warehouses, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(180) UNIQUE NOT NULL,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','customer','warehouse_staff','delivery_agent') NOT NULL DEFAULT 'customer',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE warehouses(
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 city VARCHAR(100) NOT NULL,
 capacity INT NOT NULL,
 used_capacity INT NOT NULL DEFAULT 0,
 address VARCHAR(255)
);

CREATE TABLE vehicles(
 id INT AUTO_INCREMENT PRIMARY KEY,
 vehicle_no VARCHAR(40) UNIQUE NOT NULL,
 vehicle_type VARCHAR(60),
 capacity_kg DECIMAL(10,2),
 status ENUM('Available','Assigned','Maintenance') DEFAULT 'Available'
);

CREATE TABLE drivers(
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 name VARCHAR(120) NOT NULL,
 phone VARCHAR(30),
 vehicle_id INT NULL,
 status ENUM('Available','Assigned','Offline') DEFAULT 'Available',
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

CREATE TABLE shipments(
 id INT AUTO_INCREMENT PRIMARY KEY,
 customer_id INT NULL,
 driver_id INT NULL,
 warehouse_id INT NULL,
 tracking_id VARCHAR(80) UNIQUE NOT NULL,
 sender_name VARCHAR(120),
 receiver_name VARCHAR(120),
 origin VARCHAR(180),
 destination VARCHAR(180),
 package_type VARCHAR(60),
 weight DECIMAL(10,2),
 priority ENUM('Normal','Express','Urgent') DEFAULT 'Normal',
 status VARCHAR(60) DEFAULT 'Booked',
 eta_hours DECIMAL(8,2),
 carbon_kg DECIMAL(10,2) DEFAULT 0,
 booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 delivered_at DATETIME NULL,
 FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
 FOREIGN KEY(warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL
);

CREATE TABLE shipment_events(
 id INT AUTO_INCREMENT PRIMARY KEY,
 shipment_id INT NOT NULL,
 status VARCHAR(60) NOT NULL,
 location VARCHAR(180),
 message VARCHAR(255),
 event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
);

CREATE TABLE returns(
 id INT AUTO_INCREMENT PRIMARY KEY,
 shipment_id INT NOT NULL,
 reason VARCHAR(255),
 status ENUM('Requested','Approved','Picked Up','Completed','Rejected') DEFAULT 'Requested',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
);

CREATE TABLE feedback(
 id INT AUTO_INCREMENT PRIMARY KEY,
 shipment_id INT NOT NULL,
 rating INT NOT NULL CHECK(rating BETWEEN 1 AND 5),
 comment VARCHAR(500),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
);

INSERT INTO users(name,email,password_hash,role) VALUES
('Admin Demo','admin@demo.com',SHA2('admin123',256),'admin'),
('Customer Demo','customer@demo.com',SHA2('customer123',256),'customer');

INSERT INTO warehouses(name,city,capacity,used_capacity,address) VALUES
('Central Hyderabad Hub','Hyderabad',1000,620,'Central Hub'),
('Destination Hub','Warangal',700,590,'Destination Hub'),
('South Hub','Bengaluru',1200,420,'South Hub');

INSERT INTO vehicles(vehicle_no,vehicle_type,capacity_kg,status) VALUES
('TS09AB1234','Van',500,'Available'),
('TS10CD5678','Mini Truck',1000,'Available'),
('TS11EF9012','Bike',30,'Available');

INSERT INTO drivers(name,phone,vehicle_id,status) VALUES
('Ravi Kumar','9000000001',1,'Available'),
('Priya Sharma','9000000002',2,'Available'),
('Arun Das','9000000003',3,'Available');

INSERT INTO shipments(customer_id,tracking_id,sender_name,receiver_name,origin,destination,package_type,weight,priority,status,eta_hours,carbon_kg) VALUES
(2,'TRK-DEMO-001','Demo Sender','Demo Receiver','Hyderabad','Warangal','Parcel',4.5,'Express','Out for Delivery',1.1,1.35),
(2,'TRK-DEMO-002','Demo Sender','Receiver 2','Hyderabad','Bengaluru','Document',0.5,'Normal','In Transit',3.2,0.15),
(2,'TRK-DEMO-003','Demo Sender','Receiver 3','Bengaluru','Hyderabad','Electronics',7.0,'Urgent','Delivered',1.0,2.1);

INSERT INTO shipment_events(shipment_id,status,location,message) VALUES
(1,'Booked','Hyderabad','Booking created'),
(1,'Registered','Hyderabad','Package registered'),
(1,'Picked Up','Hyderabad','Picked up by delivery agent'),
(1,'Warehouse Processing','Hyderabad','Processed at warehouse'),
(1,'In Transit','Highway','Shipment in transit'),
(1,'Destination Warehouse','Warangal','Arrived at destination warehouse'),
(1,'Out for Delivery','Warangal','Out for delivery'),
(2,'Booked','Hyderabad','Booking created'),
(2,'Registered','Hyderabad','Package registered'),
(2,'In Transit','Highway','Shipment in transit'),
(3,'Booked','Bengaluru','Booking created'),
(3,'Delivered','Hyderabad','Delivery confirmed');
