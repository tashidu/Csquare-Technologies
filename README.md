# ERP System - Csquare Technologies Internship Assignment

A comprehensive Enterprise Resource Planning (ERP) system built with PHP and MySQL for managing customers, items, and generating detailed reports.

## 🚀 Features

### Customer Management (Task 1)
- ✅ Add, edit, view, and delete customers
- ✅ Form validation for all required fields
- ✅ Fields: Title (Mr/Mrs/Miss/Dr), First Name, Last Name, Contact Number, District
- ✅ Search and filter functionality
- ✅ Customer list with pagination and export options

### Item Management (Task 2)
- ✅ Add, edit, view, and delete items
- ✅ Form validation for all required fields
- ✅ Fields: Item Code, Item Name, Item Category, Item Sub Category, Quantity, Unit Price
- ✅ Category and subcategory selection dropdowns
- ✅ Low stock alerts and inventory management
- ✅ Search and filter functionality

### Reports (Task 3)
- ✅ **Invoice Report**: Date range filtering, customer filtering, invoice details
- ✅ **Invoice Item Report**: Detailed item-wise invoice breakdown
- ✅ **Item Report**: Complete inventory report with category filtering
- ✅ Export to CSV and print functionality
- ✅ Interactive charts and statistics

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Libraries**: jQuery 3.6.0

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser (Chrome, Firefox, Safari, Edge)

## 🔧 Installation & Setup

### 1. Clone/Download the Project
```bash
git clone [repository-url]
# OR download and extract the ZIP file
```

### 2. Database Setup
1. Open phpMyAdmin or your preferred MySQL client
2. Create a new database named `assignment`
3. Import the database file:
   ```sql
   mysql -u username -p assignment < database/assignment.sql
   ```
   OR use phpMyAdmin to import `database/assignment.sql`

### 3. Configure Database Connection
1. Open `config/database.php`
2. Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');        // Your MySQL host
   define('DB_USERNAME', 'root');         // Your MySQL username
   define('DB_PASSWORD', '');             // Your MySQL password
   define('DB_NAME', 'assignment');       // Database name
   ```

### 4. Web Server Setup

#### Option A: Using XAMPP/WAMP/MAMP
1. Copy the project folder to your web server directory:
   - XAMPP: `C:\xampp\htdocs\erp-system\`
   - WAMP: `C:\wamp64\www\erp-system\`
   - MAMP: `/Applications/MAMP/htdocs/erp-system/`

2. Start Apache and MySQL services

3. Access the application:
   ```
   http://localhost/erp-system/
   ```

#### Option B: Using PHP Built-in Server
1. Navigate to the project directory
2. Run the PHP development server:
   ```bash
   php -S localhost:8000
   ```
3. Access the application:
   ```
   http://localhost:8000
   ```

### 5. Verify Installation
1. Open your web browser
2. Navigate to the application URL
3. You should see the dashboard with sample data
4. Test the functionality by navigating through different sections

## 📁 Project Structure

```
erp-system/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom CSS styles
│   └── js/
│       ├── script.js          # Main JavaScript functions
│       └── validation.js      # Form validation scripts
├── classes/
│   ├── Customer.php           # Customer management class
│   ├── Item.php              # Item management class
│   └── Report.php            # Report generation class
├── config/
│   └── database.php          # Database configuration
├── customer/
│   ├── index.php             # Customer list
│   ├── add.php               # Add customer form
│   ├── edit.php              # Edit customer form
│   ├── view.php              # View customer details
│   └── delete.php            # Delete customer
├── database/
│   └── assignment.sql        # Database schema and sample data
├── includes/
│   ├── header.php            # Common header
│   └── footer.php            # Common footer
├── item/
│   ├── index.php             # Item list
│   ├── add.php               # Add item form
│   ├── edit.php              # Edit item form
│   ├── view.php              # View item details
│   └── delete.php            # Delete item
├── reports/
│   ├── invoice_report.php    # Invoice report
│   ├── invoice_item_report.php # Invoice item report
│   └── item_report.php       # Item inventory report
├── index.php                 # Main dashboard
└── README.md                 # This file
```

## 🔍 Key Features & Functionality

### Dashboard
- Overview statistics (customers, items, invoices, revenue)
- Quick action buttons
- Recent customers and items
- Responsive design

### Customer Management
- Complete CRUD operations
- Advanced search and filtering
- Contact number validation (10 digits)
- District selection from predefined list
- Invoice history for each customer
- Export and print functionality

### Item Management
- Complete CRUD operations
- Category and subcategory management
- Stock level monitoring with alerts
- Price and quantity validation
- Sales history tracking
- Low stock notifications

### Reporting System
- **Invoice Report**: Filter by date range and customer
- **Invoice Item Report**: Detailed breakdown of invoice items
- **Item Report**: Complete inventory overview
- Export to CSV functionality
- Print-friendly layouts
- Interactive statistics and charts

## 🎯 Assumptions Made

### Database Assumptions
1. **Foreign Key Relationships**: 
   - Customer district references district.id
   - Item category references item_category.id
   - Item subcategory references item_subcategory.id
   - Invoice customer references customer.id
   - Invoice_master item_id references item.id

2. **Data Types**:
   - Contact numbers are stored as VARCHAR(10) for Sri Lankan mobile numbers
   - Prices are stored as VARCHAR but validated as DECIMAL for calculations
   - Quantities are stored as VARCHAR but validated as integers

### Business Logic Assumptions
1. **Contact Number Format**: Sri Lankan mobile numbers (10 digits starting with 0)
2. **Currency**: All prices are in Sri Lankan Rupees (LKR)
3. **Stock Management**: Items with quantity < 10 are considered "low stock"
4. **Deletion Rules**: 
   - Customers with existing invoices cannot be deleted
   - Items with invoice entries cannot be deleted

### UI/UX Assumptions
1. **Responsive Design**: Optimized for desktop, tablet, and mobile devices
2. **Browser Compatibility**: Modern browsers (Chrome, Firefox, Safari, Edge)
3. **User Experience**: Intuitive navigation with breadcrumbs and clear action buttons
4. **Data Validation**: Client-side and server-side validation for all forms

### Security Assumptions
1. **Input Sanitization**: All user inputs are sanitized using htmlspecialchars()
2. **SQL Injection Prevention**: Prepared statements used for all database queries
3. **Session Management**: Basic session handling for success/error messages

## 🐛 Known Limitations

1. **Authentication**: No user login system implemented (as not required in assignment)
2. **File Uploads**: No image upload functionality for items/customers
3. **Advanced Reporting**: No graphical charts (only statistical summaries)
4. **Email Notifications**: No email alerts for low stock items
5. **Audit Trail**: No logging of user actions and changes

## 🔧 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database name exists

2. **Permission Errors**
   - Ensure web server has read permissions on all files
   - Check file ownership and permissions

3. **CSS/JS Not Loading**
   - Verify file paths in includes/header.php
   - Check web server configuration
   - Clear browser cache

4. **Form Validation Issues**
   - Ensure JavaScript is enabled in browser
   - Check browser console for errors
   - Verify jQuery library is loading

## 📞 Support

For any issues or questions regarding this project, please contact:

- **Email**: hr@csquarefintech.com
- **CC**: luckshinif@csquarefintech.com, support@csqure.cloud

## 📄 License

This project is developed as part of the Csquare Technologies internship assignment.

---

**Developed by**: Tashidu Vinuka 
/*/*Date**:  07/06/2025
**Version**: 1.0.0
