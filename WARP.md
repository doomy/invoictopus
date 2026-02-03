# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

Invoictopus is a Czech invoice generation system built with Nette Framework 3.x and PHP 8.1+. It generates PDF invoices with QR payment codes (QR Platba) for Czech banking, manages invoice data, and provides a web interface for invoice creation and management.

## Architecture

### Framework: Nette 3.x with Custom ORM
- **Nette Framework**: MVC pattern with Presenters, Components, and Latte templates
- **Custom ORM Stack**: Uses custom `doomy/*` packages instead of Doctrine or Nette Database ORM
  - `Doomy\Ormtopus\DataEntityManager` - main data manager for entity operations
  - `Doomy\Repository` - repository pattern implementation with `RepoFactory` and `EntityFactory`
  - `Doomy\CustomDibi\Connection` - database connection layer
  - Entities extend `Doomy\Repository\Model\Entity` base class
  
### Database Layer
- **Entity Structure**: Entities define their table and identity column as class constants (`TABLE`, `IDENTITY_COLUMN`)
- **Relationships**: Entities use methods like `get1NRelation()` to fetch related entities (e.g., `Invoice::getItems()`)
- **Migrations**: SQL migrations in `sql/` directory, managed via `Doomy\Migrator\Migrator`

### Configuration
- Configuration is in `app/config/common.neon` (Neon format, YAML-like)
- Local environment config should be in `app/config/local.neon` (not tracked in git)
- Services are registered in the DI container via Neon config
- Database connection configured via `%dibi_prod%` parameter in `local.neon`

### Application Structure
- **Entry Point**: `www/index.php` bootstraps via `App\Bootstrap::boot()`
- **Bootstrap**: Sets timezone to `Europe/Prague`, enables Tracy debugger, loads configs
- **Routing**: Defined in `app/Router/RouterFactory.php`, default route goes to `Invoice:form`
- **Presenters**: Located in `app/Presenters/`, main one is `InvoicePresenter`
  - `InvoicePresenter` - handles invoice form, generation (PDF via mPDF), and listing
  - `MigrationPresenter` - runs database migrations
  - `ApiPresenter` - API endpoints
- **Entities**: `app/Invoice/` contains domain entities (`Invoice`, `Item`)
- **Templates**: Latte templates in `app/templates/` and `app/Presenters/templates/`
- **Custom Components**: 
  - `app/Response/MpdfResponse.php` - custom response for PDF generation
  - `app/TemplateFilter/Price.php` - Latte filter for price formatting

### Key Dependencies
- `nette/*` - Framework components
- `mpdf/mpdf` - PDF generation
- `dfridrich/qr-platba` - QR payment code generation for Czech banks
- `doomy/datagrid` - DataGrid component for listing
- `doomy/extended-nette-form` - Extended form components

## Development Commands

### Environment Setup
```bash
# Start development environment (Docker)
docker-compose up -d

# Stop environment
docker-compose down

# View logs
docker-compose logs -f web
docker-compose logs -f db
```

### Dependencies
```bash
# Install PHP dependencies
composer install

# Update dependencies
composer update
```

### Database

#### Migrations
Run migrations via the web interface by accessing:
```
http://localhost:8080/migration/migrate
```

Or manually execute SQL files in order from `sql/` directory:
```bash
# Import SQL dump (includes schema and data)
docker exec -i invoictopus-db mysql -uinvoictopus -plocalpass invoictopus < invoictopus_dump.sql

# Or run individual migrations
docker exec -i invoictopus-db mysql -uinvoictopus -plocalpass invoictopus < sql/001_invoice_table.sql
```

#### Database Access
```bash
# Connect to MySQL
docker exec -it invoictopus-db mysql -uinvoictopus -plocalpass invoictopus

# Or as root
docker exec -it invoictopus-db mysql -uroot -proot invoictopus
```

### Access Points
- **Web Interface**: http://localhost:8080
- **Default Page**: Invoice form at http://localhost:8080/invoice/form
- **Database**: localhost:3306 (user: `invoictopus`, pass: `localpass`)

## Development Workflow

### Adding New Entities
1. Create entity class in appropriate namespace extending `Doomy\Repository\Model\Entity`
2. Define `TABLE` and `IDENTITY_COLUMN` constants
3. Add public properties matching database columns (use UPPERCASE to match DB column naming convention)
4. Create migration SQL file in `sql/` with sequential numbering (e.g., `005_description.sql`)
5. Run migration via `/migration/migrate` endpoint

### Working with Forms
- Use `Doomy\ExtendedNetteForm\Form` instead of standard Nette forms
- Forms use POST data directly in presenters (see `InvoicePresenter::getTemplateData()`)
- Form containers are used for repeating elements (e.g., invoice items)

### Generating PDFs
- Template rendering uses Latte engine directly (not presenter template)
- Custom filters can be added via `$latte->addFilter()`
- `MpdfResponse` handles PDF output with proper headers and filename

### Database Column Naming
The codebase uses mixed column naming conventions inherited from migrations:
- Some columns use UPPERCASE (newer style): `ID`, `SUPPLIER_NAME`
- Some columns use camelCase (older style): `customerName`, `bankAccountNr`
- When adding new columns, prefer UPPERCASE style for consistency with entity properties

## Project-Specific Notes

### Currency and Localization
- Currently Czech-only (translations TODO)
- Default currency: CZK (Czech Koruna)
- Currency handled via `Invoictopus\Currency\Currency` enum
- VAT rate configurable per item, default is 0% (see `InvoicePresenter::VAT_RATE`)

### Invoice Business Logic
- Invoice IDs are auto-incremented from last invoice
- Default payment terms: 15 days from invoice date
- Default taxable date: last day of previous month
- Forms pre-fill with data from selected reference invoice (or last invoice)
- QR payment codes include: account number, variable symbol (invoice ID), amount, currency

### File Permissions
The directories `temp/`, `log/`, and `www/upload/` must be writable by the web server. Docker setup handles this automatically, but for local development:
```bash
chmod -R 775 temp/ log/ www/upload/
```
