# Shanu Haven – Coastal Villa

## 1. Project Overview

Shanu Haven is a web-based coastal villa reservation
and management system designed for guests who wish
to book accommodation in Galle, Sri Lanka.

The system allows guests to explore rooms, check
availability, create accounts, make reservations,
manage bookings and find directions to the villa.

It also provides an administration panel to manage
rooms, bookings and reservation statuses.

This project was developed for the IT1208 Web
Technologies Continuous Assessment 02.

## 2. Project Objectives

- Develop a user-friendly online reservation system.
- Allow guests to search for available rooms.
- Provide secure registration and login facilities.
- Automate booking and price calculations.
- Simplify room and reservation management.
- Provide interactive maps and location services.
- Create a responsive and attractive user interface.

## 3. Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript
- Leaflet.js

### Backend

- PHP
- MySQL
- PHP Data Objects (PDO)

### Location Services

- OpenStreetMap
- Browser Geolocation API
- Nominatim Geocoding Service
- Google Maps Directions

### Development Tools

- Visual Studio Code
- XAMPP
- phpMyAdmin
- Git
- GitHub

## 4. Main System Features

### 4.1 Guest Features

- User registration and login.
- Secure password authentication.
- Browse available rooms.
- View room images, facilities and prices.
- Search rooms using check-in and check-out dates.
- Search rooms according to guest capacity.
- Make online reservations.
- Automatic total price calculation.
- Booking confirmation.
- View personal booking history.
- View booking status.
- Cancel pending bookings.
- Secure logout.

### 4.2 Administrator Features

- Secure administrator login.
- Administrative dashboard.
- View booking statistics.
- View and search reservations.
- Filter bookings by status.
- Confirm or cancel reservations.
- Add new rooms.
- Edit existing room details.
- Delete rooms when permitted.
- Upload and update room images.
- Manage room availability.
- Secure administrator logout.

### 4.3 Map and Location Features

- Interactive map using Leaflet.js.
- OpenStreetMap integration.
- Villa location marker in Galle.
- GPS-based current location detection.
- Search locations within Sri Lanka.
- Select a starting location directly on the map.
- Display the selected starting location.
- Automatic straight-line distance calculation.
- Google Maps driving directions.
- Map zoom and navigation controls.
- Location permission and error handling.

### 4.4 User Interface Features

- Responsive web design.
- Dark teal and gold colour theme.
- Transparent glass-effect cards.
- Interactive buttons and hover effects.
- User-friendly navigation.
- Mobile-friendly layouts.
- Form validation and feedback messages.

## 5. Database Design

Database Name: shanu_haven

The system uses four main database tables.

| Table | Description |
| --- | --- |
| users | Stores registered guest information |
| rooms | Stores room details and availability |
| bookings | Stores guest reservation details |
| admins | Stores administrator account information |

### Database Relationships

- One registered user can make multiple bookings.
- One room can have multiple bookings.
- Each new booking belongs to a registered user.
- Each booking is associated with a room.
- Administrators manage rooms and reservations.

Primary keys and foreign keys are used to maintain
database relationships and data integrity.

## 6. Main Project Structure

```text
shanu-haven/
|
|-- admin/
|
|-- assets/
|   |-- CSS/
|   |   |-- style.css
|   |   |-- admin.css
|   |   |-- customer-auth.css
|   |   |-- customer-nav.css
|   |   |-- location.css
|   |
|   |-- images/
|   |
|   |-- js/
|       |-- customer-auth.js
|       |-- location.js
|
|-- config/
|   |-- database.php
|   |-- customer-session.php
|
|-- index.php
|-- room-details.php
|-- booking.php
|-- booking-success.php
|-- register.php
|-- login.php
|-- logout.php
|-- my-booking.php
|-- db_structure.sql
|-- README.md
```

## 7. Installation and Setup

### Step 1 - Install XAMPP

Install XAMPP on your computer.

### Step 2 - Copy Project Files

Copy the shanu-haven project folder into:

C:\xampp\htdocs\

### Step 3 - Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### Step 4 - Create the Database

Open phpMyAdmin:

http://localhost/phpmyadmin/

Create a new database named:

shanu_haven

Import the db_structure.sql file into the database.

### Step 5 - Configure the Database

Open config/database.php and configure the database
connection according to your local environment.

Default XAMPP configuration:

- Host: localhost
- Database: shanu_haven
- Username: root
- Password: Empty by default

These settings are intended for local development.

### Step 6 - Run the Website

Open the following URL in your web browser:

http://localhost/shanu-haven/

### Step 7 - Access the Admin Panel

http://localhost/shanu-haven/admin/login.php

## 8. Sample Login Credentials

### Guest Account

- Email: [Enter sample guest email]
- Password: [Enter sample guest password]

### Administrator Account

- Email/Username: [Enter sample admin login]
- Password: [Enter sample admin password]

Use dedicated demonstration accounts for testing.

## 9. How to Use the Location System

The location system provides three methods for
selecting a starting location.

### Method 1 - Search Location

1. Enter a city or location in Sri Lanka.
2. Click Search Location.
3. Select a location from the search results.
4. View the calculated distance to the villa.

### Method 2 - GPS Current Location

1. Click Use My Current Location.
2. Grant browser location permission if desired.
3. View the detected location on the map.
4. View the calculated distance to the villa.

### Method 3 - Select on Map

1. Click Select on Map.
2. Choose a starting location on the map.
3. View the selected location and distance.

### Get Directions

After selecting a starting location, click
Get Directions to open Google Maps and view
driving directions to the villa.

## 10. Villa Location

- City: Galle
- Province: Southern Province
- Country: Sri Lanka
- Latitude: 6.0329
- Longitude: 80.2168

The coordinates represent a demonstration location
used for this academic project.

## 11. Security Features

- Password hashing.
- PHP session-based authentication.
- PDO prepared statements.
- Input validation.
- Customer and administrator access control.
- Protection against unauthorized booking access.
- Database primary and foreign key relationships.
- Booking availability validation.
- Location access with user permission.

The application does not store GPS coordinates
in its database.

Location searches are sent to the geocoding service.
Selected coordinates are shared with Google Maps
when the user requests directions.

## 12. System Testing

The following areas are included in system testing:

| Test Area | Expected Result |
| --- | --- |
| Guest Registration | Create a valid guest account |
| Guest Login | Authenticate registered guests |
| Room Search | Display suitable available rooms |
| Booking | Save valid reservation details |
| Price Calculation | Calculate the total booking price |
| My Bookings | Display personal reservations |
| Cancellation | Cancel eligible pending bookings |
| Admin Login | Authenticate administrators |
| Room Management | Add, edit and delete rooms |
| Booking Management | Update reservation statuses |
| Map Loading | Display the interactive map |
| GPS Location | Detect location with permission |
| Location Search | Display matching locations |
| Manual Selection | Select a location on the map |
| Distance Calculation | Display straight-line distance |
| Google Maps | Open driving directions |
| Responsive Design | Adapt to different screen sizes |

## 13. Innovation and Additional Features

The main additional feature of this project is
the interactive map and location system.

Guests can select their starting location using
GPS, location search or manual map selection.

The system calculates the straight-line distance
to the villa and provides driving directions
through Google Maps.

These features improve usability and help guests
plan their journey before visiting the villa.

## 14. System Requirements and Limitations

- XAMPP with Apache and MySQL.
- A modern web browser.
- Internet access for maps and location search.
- Browser permission for GPS location detection.
- GPS requires localhost or a secure HTTPS website.
- Location accuracy depends on the user's device.
- Location search is limited to Sri Lanka.
- Calculated distance is a straight-line distance.
- The villa location is for demonstration purposes.
- Public map and geocoding services are intended
  for low-volume academic demonstration use.

## 15. Future Improvements

- Online payment integration.
- Email booking notifications.
- Customer reviews and ratings.
- Multi-language support.
- Advanced reporting and analytics.
- Road distance and estimated travel time.
- Live website hosting.
- Improved map service integration.

## 16. AI Usage Declaration

ChatGPT was used as an AI assistance tool during
the development of this project.

AI assistance was used for:

- Understanding web development concepts.
- Generating and improving code.
- Identifying and resolving errors.
- Improving user interface designs.
- Implementing map and location features.
- Preparing project documentation.

The developer is responsible for reviewing,
understanding, integrating and testing the
final system.

## 17. Project Information

- Project Name: Shanu Haven - Coastal Villa
- Module: IT1208 Web Technologies
- Assessment: Continuous Assessment 02
- Project Type: Web-Based Villa Reservation System
- Location: Galle, Sri Lanka

## 18. GitHub Repository

https://github.com/maleeshashanari/shanu-haven

## 19. Acknowledgements

- Leaflet.js
- OpenStreetMap contributors
- Nominatim
- Google Maps
- PHP
- MySQL
- XAMPP
- OpenAI ChatGPT

---

Shanu Haven - Your Perfect Coastal Escape