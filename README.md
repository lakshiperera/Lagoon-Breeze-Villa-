# Lagoon Breeze Villa - Negombo (Booking System)

A coastal villa and room booking web application for a small guest house in Negombo, Sri Lanka.
Built with PHP, MySQL (PDO), HTML5, CSS3 and vanilla JavaScript to run locally on XAMPP.

## Features
**Guests:** browse rooms, search by dates / guests / room type / max price (rooms with overlapping *Confirmed*
bookings are hidden), register and log in, book a room with live price calculation, view their own bookings,
cancel Pending bookings, and review Confirmed stays.

**Admin:** dashboard (total rooms, total bookings, pending approvals, confirmed revenue, recent reservations),
add / edit / delete rooms, confirm or cancel bookings (a booking cannot be confirmed if it overlaps another
Confirmed booking for the same room).

**Security basics:** `password_hash()` / `password_verify()`, PDO prepared statements, output escaping with
`htmlspecialchars()`, role checks with `$_SESSION`, server-side re-validation of all booking inputs.

## XAMPP setup
1. Install XAMPP and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy the `lagoon_breeze_villa` folder into `C:\xampp\htdocs\` (macOS/Linux: `/opt/lampp/htdocs/`).
3. Open `http://localhost/phpmyadmin`, click **Import**, choose `db_structure.sql` and click **Go**.
   This creates the database `lagoon_breeze_villa` with sample data.
4. Open `http://localhost/lagoon_breeze_villa/` in your browser.
5. If your MySQL has a password, change `$dbpass` in `includes/db.php`.

## Sample accounts (password for all: `Password123`)
| Role  | Email                    |
|-------|--------------------------|
| Admin | admin@lagoonbreeze.lk    |
| Guest | nimali@example.com       |
| Guest | kasun@example.com        |
| Guest | emma@example.com         |

## Folder structure
```
index.php  rooms.php  booking.php  my_bookings.php  register.php  login.php  logout.php
admin/dashboard.php  admin/manage_rooms.php  admin/manage_bookings.php
includes/db.php  includes/header.php  includes/footer.php
css/style.css  js/script.js  assets/images/*.svg  db_structure.sql
```

## Known limitations
No payment gateway, no email sending, and no CSRF tokens (kept simple for a student project).
Room images are fixed SVG placeholders chosen from a list.

## AI usage declaration
This project was generated with the help of an AI assistant (Claude by Anthropic) from a written specification.
I reviewed the code, set it up on XAMPP, tested the main features and take responsibility for the submitted work.
