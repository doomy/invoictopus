# Invoictopus

Invoice generation system built with Nette Framework 3.x and PHP 8.1+. Generates professional PDF invoices with QR payment codes for Czech businesses.

## Features

- 📄 PDF invoice generation with mPDF
- 💳 QR payment codes for Czech banking system
- 📋 Invoice template system for quick reuse
- 🗄️ Invoice history and management
- 🔄 Database migrations
- 🐳 Docker-based development environment

## Requirements

- Docker and Docker Compose
- MySQL/MariaDB database
- PHP 8.1+ (handled by Docker)

## Installation

### 1. Clone and Configure

```bash
git clone <repository-url>
cd invoictopus
```

### 2. Environment Setup

Copy the environment template:

```bash
cp .env.example .env
```

Edit `.env` to set your desired port (default is 8081):

```
HOST_PORT=8081
```

### 3. Database Configuration

Copy and configure the local configuration:

```bash
cp app/config/local.neon.example app/config/local.neon
```

Edit `app/config/local.neon` with your database credentials:

```neon
parameters:
    dibi_prod:
        driver: mysqli
        host: localhost
        username: your_username
        password: your_password
        database: invoictopus
        charset: utf8mb4
```

### 4. Start Docker Environment

```bash
docker-compose up -d
```

The application will be available at `http://localhost:8081`

### 5. Run Database Migrations

Navigate to the migration endpoint in your browser:

```
http://localhost:8081/migration/migrate
```

This will create the necessary database tables (`t_invoice` and `t_invoice_item`).

### 6. Set Directory Permissions

Ensure temp and log directories are writable:

```bash
chmod 775 temp/ log/ www/upload/
```

## Usage

### Creating Invoices

1. Navigate to `http://localhost:8081` (or your configured port)
2. Fill in the invoice form with supplier and customer details
3. Add invoice items (products/services)
4. Click "Generate" to create a PDF invoice

### Using Invoice Templates

The system allows you to reuse previous invoice data:

1. Select a reference invoice from the dropdown
2. The form will auto-populate with that invoice's data
3. Modify as needed and generate a new invoice

### Managing Invoices

Access the invoice list at:
```
http://localhost:8081/invoice/list
```

## Project Structure

```
invoictopus/
├── app/
│   ├── config/          # Nette configuration files
│   ├── Invoice/         # Domain models (Invoice, Item)
│   ├── Presenters/      # Controllers (InvoicePresenter, etc.)
│   ├── Response/        # Custom responses (MpdfResponse)
│   ├── Router/          # Routing configuration
│   ├── TemplateFilter/  # Latte filters
│   └── templates/       # Latte templates
├── sql/                 # Database migrations
├── www/                 # Public web root
│   ├── css/
│   ├── fonts/
│   └── upload/         # Generated PDF storage
├── temp/               # Cache and temporary files
├── log/                # Application logs
└── vendor/             # Composer dependencies
```

## Development

### Docker Commands

Start the application:
```bash
docker-compose up -d
```

Stop the application:
```bash
docker-compose down
```

View logs:
```bash
docker-compose logs -f
```

Access container shell:
```bash
docker exec -it invoictopus-web bash
```

### Adding Database Migrations

1. Create a new SQL file in `sql/` directory with sequential numbering:
   ```
   sql/005_your_migration_name.sql
   ```

2. Run migrations via browser:
   ```
   http://localhost:8081/migration/migrate
   ```

### Working with the Custom ORM

Entities extend `Doomy\Repository\Model\Entity`:

```php
class Invoice extends Entity
{
    const TABLE = 't_invoice';
    const IDENTITY_COLUMN = 'ID';
    
    public $ID;
    public $CUSTOMER_NAME;
    // ... other properties map to database columns
}
```

Data operations use `DataEntityManager`:

```php
// Find all
$invoices = $this->data->findAll(Invoice::class);

// Find one
$invoice = $this->data->findById(Invoice::class, $id);

// Save
$this->data->save(Invoice::class, ['ID' => 1, 'CUSTOMER_NAME' => 'John']);

// Delete
$this->data->deleteById(Invoice::class, $id);
```

## Technology Stack

- **Framework**: Nette 3.x
- **PHP Version**: 8.1+
- **Template Engine**: Latte
- **PDF Generation**: mPDF
- **QR Codes**: dfridrich/qr-platba
- **Database**: MySQL/MariaDB with custom Dibi wrapper
- **ORM**: Custom doomy/ormtopus library
- **Web Server**: Apache (via Docker)

## TODO

- [ ] Implement Czech translations (currently Czech-only interface)
- [ ] Add multi-currency support
- [ ] Implement invoice numbering configuration
- [ ] Add email delivery for invoices

## License

[MIT, BSD-3-Clause, GPL-2.0, GPL-3.0]

## Support

For issues or questions, please open an issue in the repository.
