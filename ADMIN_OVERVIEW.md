# Admin Overview

## What We Have Built So Far

- Separate admin login page: [admin_login.php](admin_login.php)
- Admin authentication helper: [admin_auth.php](admin_auth.php)
- Protected admin dashboard: [admin_dashboard.php](admin_dashboard.php)
- Admin logout: [admin_logout.php](admin_logout.php)
- Admin table added to the schema: [db_schema](db_schema)
- Admin login link added to the user login page: [login.php](login.php)

## Current Admin Features In The Project

### Done Now

- Admin login with session-based access
- Dashboard access control for admins only
- Dashboard summary cards for:
  - total users
  - total products
  - total orders
  - total revenue
- Recent orders table on the dashboard
- Database-backed admin account in `admin_users`

### Planned Next

- Product management
  - add products
  - edit products
  - delete products
  - upload images
  - manage stock
  - set prices and discounts
- Category management
  - add categories
  - edit categories
  - delete categories
- Order management
  - view all customer orders
  - update order status
  - generate invoices
- User management
  - view users
  - block or unblock users
  - delete accounts
- Inventory management
  - stock monitoring
  - low stock alerts
  - availability tracking
- Payment management
  - view payment records
  - verify transactions
  - refund support if needed
- Reports and analytics
  - sales reports
  - monthly revenue reports
  - top-selling products
  - customer activity reports
- Review management
  - approve or reject reviews
  - remove inappropriate comments

## Admin Database Schema

The schema currently includes:

- `admin_users`
- `users`
- `products`
- `cart`
- `orders`

### Admin Table

- `admin_id`
- `username`
- `password`
- `full_name`
- `is_active`
- `created_at`

## Tests Run

- PHP error check passed on:
  - [admin_auth.php](admin_auth.php)
  - [admin_login.php](admin_login.php)
  - [admin_dashboard.php](admin_dashboard.php)
  - [admin_logout.php](admin_logout.php)
  - [login.php](login.php)
  - [db_schema](db_schema)

## Known Issues / Notes

- The dashboard task buttons are still placeholders and do not open real management pages yet.
- Admin passwords are still stored in plain text for now, which is fine for a class/demo project but not secure for production.
- The admin login is separate from the user login, which is the correct setup for this project.

## Recommended Next Step

Build product management next, because it is the main admin function that supports the rest of the store workflow.
