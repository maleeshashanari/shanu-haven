"# shanu-haven" 
# Shanu Haven – Coastal Villa

## 1. Project Overview

Shanu Haven is a web-based villa reservation system developed for the IT1208 Web Technologies module. The system allows customers to explore rooms, check availability, register, log in and make online reservations. Administrators can manage rooms and customer bookings.

## 2. Main Features

### Customer Features
- Customer registration and login
- View rooms and room details
- Search available rooms by dates and guest count
- Online room reservations
- Automatic price calculation
- View personal bookings and booking status
- Cancel pending bookings
- Secure logout

### Admin Features
- Secure admin login
- Admin dashboard
- Manage customer bookings
- Confirm or cancel reservations
- Add, edit and delete rooms
- Manage room images and availability

## 3. Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- XAMPP
- Visual Studio Code
- Git and GitHub

## 4. Database

Database name: `shanu_haven`

Main database tables:

- users
- admins
- rooms
- bookings

The database export is provided in `db_structure.sql`.

## 5. Installation Instructions

1. Install XAMPP.
2. Copy the project folder into the XAMPP `htdocs` directory.
3. Start Apache and MySQL.
4. Open phpMyAdmin.
5. Create a database named `shanu_haven`.
6. Import the `db_structure.sql` file.
7. Check the database connection settings in `config/database.php`.
8. Open the website using the following URL:

http://localhost/shanu-haven/

## 6. Login Details

### Sample Customer Account

- Email: [Enter sample customer email]
- Password: [Enter sample customer password]

### Sample Admin Account

- Email: [Enter sample admin email]
- Password: [Enter sample admin password]

Admin login URL:

http://localhost/shanu-haven/admin/login.php

## 7. Security Features

- Password hashing
- PHP session management
- PDO prepared statements
- Server-side input validation
- CSRF protection
- Customer booking ownership verification
- Prevention of overlapping room reservations

## 8. AI Usage Declaration

ChatGPT was used to assist with code development, user interface design, debugging, technical explanations and documentation. The generated suggestions were reviewed and adapted during project development.

## 9. Developer Information

- Student Name: U.G.M Shanari
- Registration Number: 24IT0528
- Module:  Web Technologies
- Assessment: Continuous Assessment 02
- Year: 2026
