**Shop Bakery — Bakery Management System**
A dual-purpose web platform combining an e-commerce website for customers and a back-office management system for admins and managers.
Capstone Project II — Group 5 | INS306402

**Overview**
Shop Bakery allows customers to browse and order bakery products online, while giving admins and managers full control over products, inventory, orders, and sales — all in one unified system.

**Features**
 E-Commerce (Customer-facing)
•	Browse product catalog with search and categories
•	View product details with image gallery and customer reviews
•	Add to cart with optional gift card and message
•	Checkout with shipping fee calculation
•	Track order status in real time
•	View order history and contact the bakery
 
 Back-Office (Admin & Manager)
•	Product management (CRUD: add, edit, delete)
•	Inventory and stock tracking
•	Order processing and status updates
•	Sales reports and dashboard
•	User and role management

** User Roles**
Role	Access
Admin	Full system access — users, config, all modules
Manager	Operational access — products, orders, inventory, reports
Customer	E-commerce access — browse, order, track, message

**Tech Stack**
Layer	Technology
Frontend	HTML, CSS, JavaScript
Backend	PHP
Database	MySQL
Server	Apache (laragon)

**Project Structure**
Shop-Bakery/
├── includes/
│   ├── header.php          # Shared header
│   └── footer.php          # Shared footer
├── views/
│   ├── auth/               # Login, register
│   ├── customer/           # Cart, checkout, order tracking
│   └── admin/              # Dashboard, product/order/user management
├── connectdb.php            # Database connection
├── homepage.php             # Home page
├── shop.php                 # Product listing
├── product_details.php      # Product detail page
└── README.md

**Installation**
1.	Clone the repository
2.	git clone https://github.com/tungxtnd/Shop-Bakery.git
3.	Move to your server directory
4.	# For Laragon on Windows
5.	mv Shop-Bakery C:/laragon/www/
6.	Import the database
o	Open Laragon → http://Shop-Bakery.test
o	Create a new database: ql_bakery
o	Import the .sql file from the /database folder
7.	Configure database connection
Open connectdb.php and update your credentials:
$host     = "localhost";
$user     = "root_user";
$password = "admin123";
$dbname   = "ql_bakery";
8.	Run the project
o	Start Apache and MySQL in Laragon
o	Visit: http://localhost/Shop-Bakery/homepage.php

**Database Tables**
Table	Description
users	Customer and admin accounts
products	Bakery product catalog
product_images	Product image gallery
services	Gift card options
cart_items	Shopping cart entries
orders	Customer orders
order_details	Items within each order
reviews	Product reviews and ratings

**Contributors**
8 contributors — Capstone Project II | Group 5 | INS306402

**License**
This project is built for academic purposes as part of the Capstone Project II course.

