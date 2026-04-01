# InventoryPro - Comprehensive Inventory & Sales Management System

![InventoryPro Logo](https://via.placeholder.com/150x50?text=InventoryPro) <!-- Replace with actual logo URL -->

**Transform your business operations with InventoryPro, the ultimate all-in-one inventory and sales management solution built on CodeIgniter 4. Streamline your workflows, boost efficiency, and drive profitability with powerful features designed for modern businesses.**

## 🚀 Why Choose InventoryPro?

InventoryPro is not just another inventory system—it's a comprehensive business management platform that empowers entrepreneurs, retailers, wholesalers, and manufacturers to take control of their operations. With intuitive interfaces, real-time analytics, and scalable architecture, InventoryPro helps you:

- **Reduce operational costs** by automating inventory tracking and sales processes
- **Increase revenue** through better stock management and customer insights
- **Make data-driven decisions** with detailed reports and analytics
- **Scale effortlessly** as your business grows
- **Stay compliant** with built-in audit trails and secure data handling

Whether you're running a small retail shop, managing a wholesale operation, or overseeing a manufacturing facility, InventoryPro adapts to your needs and grows with your success.

## ✨ Key Features & Modules

InventoryPro is organized into interconnected modules that work seamlessly together to provide a complete business management ecosystem:

### 🔐 Authentication & User Management
- Secure user authentication with role-based access control
- Multi-user support with customizable permissions
- Session management and security features
- **Benefits**: Ensures data security and operational integrity across your team

### 📊 Dashboard
- Real-time overview of key metrics (sales, inventory levels, profits)
- Interactive charts and graphs for quick insights
- Customizable widgets for personalized views
- **Benefits**: Get a bird's-eye view of your business performance at a glance

### 📦 Inventory Management
- Product catalog with categories, SKUs, and detailed specifications
- Real-time stock tracking with low-stock alerts
- Barcode/QR code support for efficient scanning
- Batch management and expiration tracking
- **Benefits**: Never run out of stock or overstock again; optimize your inventory turnover

### 💰 Sales Management
- Point-of-sale (POS) functionality
- Customer relationship management (CRM)
- Invoice generation and payment processing
- Multi-currency support (LRD, USD)
- Sales analytics and trend analysis
- **Benefits**: Close more sales, understand customer behavior, and improve cash flow

### 🛒 Purchase Management
- Supplier management and vendor relations
- Purchase order creation and tracking
- Automated reorder points
- Cost analysis and supplier performance metrics
- **Benefits**: Streamline procurement processes and negotiate better supplier terms

### 🏭 Production Management
- Production job scheduling and tracking
- Material requirements planning (MRP)
- Work-in-progress monitoring
- Quality control and batch tracking
- **Benefits**: Optimize production efficiency and reduce waste in manufacturing operations

### 📈 Reports & Analytics
- Comprehensive sales, inventory, and financial reports
- Custom date range filtering
- Export capabilities (PDF, Excel)
- Drill-down functionality for detailed analysis
- **Benefits**: Make informed decisions with actionable insights and historical data

### ⚙️ Settings & Configuration
- System-wide settings and preferences
- User role and permission management
- Tax and currency configuration
- Backup and data export tools
- **Benefits**: Customize the system to fit your unique business processes

### 🔔 Notifications
- Automated alerts for low stock, expiring products, and overdue payments
- Email and in-app notifications
- Customizable notification rules
- **Benefits**: Stay proactive and responsive to critical business events

## 🏢 Business Applications

InventoryPro is versatile and can be deployed across various industries:

- **Retail Stores**: Manage inventory, process sales, and analyze customer trends
- **Wholesale Distributors**: Handle bulk orders, track shipments, and manage supplier relationships
- **Manufacturing Companies**: Plan production, track materials, and monitor quality control
- **E-commerce Businesses**: Integrate with online platforms for seamless order fulfillment
- **Service-Based Businesses**: Track service inventory and manage client relationships
- **Non-Profit Organizations**: Manage donations, inventory, and distribution programs

## 🛠️ Installation & Setup

### Prerequisites
- **PHP**: Version 8.2 or higher
- **Web Server**: Apache, Nginx, or similar with URL rewriting
- **Database**: MySQL 5.7+ or MariaDB 10.1+
- **Composer**: For dependency management
- **Node.js** (optional): For frontend asset compilation

### Step-by-Step Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/inventorypro.git
   cd inventorypro
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   - Copy `.env.example` to `.env`
   - Configure your database settings in `.env`:
     ```
     database.default.hostname = localhost
     database.default.database = inventorypro
     database.default.username = your_db_user
     database.default.password = your_db_password
     ```

4. **Database Setup**
   - Create a new MySQL database named `inventorypro`
   - Run the migrations:
     ```bash
     php spark migrate
     ```
   - (Optional) Seed the database with sample data:
     ```bash
     php spark db:seed
     ```

5. **Web Server Configuration**
   - Point your web server document root to the `public` folder
   - Ensure URL rewriting is enabled
   - For Apache, ensure `.htaccess` is present in the `public` folder

6. **Start the Application**
   ```bash
   php spark serve
   ```
   Access the application at `http://localhost:8080`

7. **Initial Setup**
   - Visit the application URL
   - Create an admin account
   - Configure system settings as needed

## 📖 Usage Guide

### Getting Started
1. Log in with your admin credentials
2. Explore the dashboard for an overview
3. Set up your initial inventory and product catalog
4. Configure users and permissions

### Key Workflows
- **Adding Products**: Navigate to Inventory > Products > Add New
- **Processing Sales**: Use the POS interface or create invoices manually
- **Generating Reports**: Go to Reports > Sales/Inventory and apply filters
- **Managing Users**: Access Settings > Users to add team members

### Best Practices
- Regularly backup your database
- Set up automated notifications for critical alerts
- Review reports weekly to identify trends
- Train your team on system usage for optimal adoption

## 🔧 Configuration

### Environment Variables
Key configuration options in `.env`:
- `app.baseURL`: Your application's base URL
- `database.default.*`: Database connection details
- `email.*`: Email configuration for notifications

### Customizing the System
- Modify views in `app/Views/` for UI changes
- Extend controllers in `app/Controllers/` for new functionality
- Add custom models in `app/Models/` for data operations

## 🤝 Contributing

We welcome contributions to InventoryPro! Here's how you can help:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please read our [Contributing Guidelines](CONTRIBUTING.md) for detailed information.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check our [Wiki](https://github.com/yourusername/inventorypro/wiki) for detailed guides
- **Issues**: Report bugs or request features on [GitHub Issues](https://github.com/yourusername/inventorypro/issues)
- **Community**: Join our [Discussion Board](https://github.com/yourusername/inventorypro/discussions) for questions and support

## 🙏 Acknowledgments

- Built with [CodeIgniter 4](https://codeigniter.com/)
- Frontend powered by [Bootstrap](https://getbootstrap.com/)
- Charts and analytics by [Chart.js](https://www.chartjs.org/)

---

**Ready to revolutionize your business operations? Get started with InventoryPro today!**

*InventoryPro - Where Efficiency Meets Profitability* 🚀
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - The end of life date for PHP 8.1 was December 31, 2025.
> - If you are still using below PHP 8.2, you should upgrade immediately.
> - The end of life date for PHP 8.2 will be December 31, 2026.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
