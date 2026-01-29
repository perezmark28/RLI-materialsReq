# RLI Material Request System

A complete full-stack material request system built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## Features

- Professional corporate design with responsive layout
- Dynamic table with add/remove row functionality
- Automatic amount calculation (Quantity × Price)
- Secure database operations using prepared statements
- Form validation on both client and server side

## Setup Instructions

### 1. Database Setup

1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Import the database schema:
   - Click on "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go" to execute

Alternatively, you can run the SQL file directly in phpMyAdmin's SQL tab.

### 2. Database Configuration

The system uses XAMPP default settings:
- **Host:** localhost
- **Username:** root
- **Password:** (empty)
- **Database:** rli_systems

If you need to change these settings, edit `includes/db_connect.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rli_systems');
```

### 3. File Structure

```
RLI-materialsReq/
├── database/
│   └── schema.sql              # Database schema
├── includes/
│   └── db_connect.php          # Database connection
├── index.php                    # Main form page
├── save_request.php             # Form processing
├── assets/
│   ├── css/
│   │   └── style.css           # Styling
│   └── js/
│       └── main.js             # JavaScript functionality
└── README.md                    # This file
```

### 4. Access the Application

1. Make sure XAMPP Apache and MySQL services are running
2. Open your browser and navigate to:
   ```
   http://localhost/RLI-materialsReq/
   ```

## Usage

1. Fill in the form fields:
   - **Particulars:** Description of the request
   - **Requested By:** Name of the requester
   - **Date Requested:** Date when the request is made
   - **Date Needed:** Date when materials are needed

2. Add items to the table:
   - Click "Add New Row" to add new items
   - Fill in Item Name, Specs, Quantity, Unit, and Price
   - Amount is automatically calculated (Quantity × Price)
   - Optionally add a link for each item
   - Click "Remove" to delete a row

3. Submit the form:
   - Click "Submit Request" to save the data
   - The system will validate all fields before submission

## Database Schema

### material_requests
- `id` - Primary key
- `requester_name` - Name of requester
- `date_requested` - Request date
- `date_needed` - Needed date
- `particulars` - Request details
- `status` - Request status (default: 'pending')
- `created_at` - Timestamp

### request_items
- `id` - Primary key
- `request_id` - Foreign key to material_requests
- `item_no` - Item number
- `item_name` - Name of item
- `specs` - Item specifications
- `quantity` - Quantity needed
- `unit` - Unit of measurement
- `price` - Unit price
- `amount` - Calculated amount (quantity × price)
- `item_link` - Link to item (optional)

## Security Features

- Prepared statements to prevent SQL injection
- Input validation on both client and server
- XSS protection
- Transaction support for data integrity

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Edge (latest)
- Safari (latest)

## Troubleshooting

### Database Connection Error
- Ensure MySQL service is running in XAMPP
- Verify database credentials in `includes/db_connect.php`
- Make sure the database `rli_systems` exists

### Form Not Submitting
- Check browser console for JavaScript errors
- Ensure all required fields are filled
- Verify at least one item row exists

### Styling Issues
- Clear browser cache
- Verify CSS file path is correct
- Check browser developer tools for 404 errors

## License

This project is for internal use by RLI.
