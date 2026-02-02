# AGENTS.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

Invoictopus is an invoice generation system built with Nette Framework 3.x and PHP 8.1+. It generates PDF invoices with QR payment codes for Czech businesses.

## Architecture

### Framework: Nette 3.x
- **Application Structure**: Presenter-based MVC pattern
- **Routing**: Centralized in `app/Router/RouterFactory.php` with default route to `Invoice:form`
- **Configuration**: NEON files in `app/config/` (common.neon + local.neon)
- **Templates**: Latte templating engine (`.latte` files)
- **Bootstrap**: `app/Bootstrap.php` initializes the application with Tracy debugger, timezone set to Europe/Prague

### Data Layer Architecture
Uses custom ORM libraries (doomy/* packages):
- **Entities**: Extend `Doomy\Repository\Model\Entity` (see `app/Invoice/Invoice.php`, `app/Invoice/Item.php`)
- **Data Access**: `Doomy\Ormtopus\DataEntityManager` for CRUD operations
- **Database Connection**: Custom Dibi wrapper via `Doomy\CustomDibi\Connection`
- **Relationships**: 1:N relations via `get1NRelation()` method on entities (e.g., Invoice->getItems())

Entity structure:
- Define `TABLE` constant for database table name
- Define `IDENTITY_COLUMN` constant for primary key (optional, defaults to 'id')
- Public properties map to database columns (UPPERCASE convention)

### Key Domain Models
- **Invoice** (`app/Invoice/Invoice.php`): Main invoice entity with supplier/customer data
- **Item** (`app/Invoice/Item.php`): Invoice line items
- **Currency** (`app/Currency.php`): PHP 8.1 enum for currency types (currently only CZK)

### Presenter Structure
- **InvoicePresenter**: Main presenter handling invoice form, generation (PDF via mPDF), and listing
- **MigrationPresenter**: Database migration management at `/migration/migrate`
- **ApiPresenter**: API endpoints
- Error presenters for 4xx errors

## Development Commands

### Setup
1. Copy `.env.example` to `.env` and configure `HOST_PORT`
2. Configure database in `app/config/local.neon` (dibi_prod parameters)
3. Start application:
```bash
docker-compose up -d
```
4. Run database migrations:
   Access `http://localhost:8081/migration/migrate` in browser

### Docker Environment
- Container name: `invoictopus-web`
- Default port: 8081 (configurable via `.env` HOST_PORT)
- Document root: `/var/www/html/www`
- PHP 8.1 with Apache
- Auto-installs Composer dependencies on build

### Database Migrations
- Migration files: `sql/` directory (numbered: `001_*.sql`, `002_*.sql`, etc.)
- Run via: `http://localhost:{HOST_PORT}/migration/migrate`
- Managed by `doomy/migrator` package
- Schema includes `t_invoice` and `t_invoice_item` tables

### Directory Permissions
Ensure `temp/` and `log/` directories are writable (775 permissions)

## Coding Patterns

### Form Handling in Presenters
- Use `Doomy\ExtendedNetteForm\Form` (extended Nette forms)
- Forms created in `createComponent*Form()` methods
- Default values populated from last invoice or selected reference invoice
- Form submission handled via `onSuccess[]` callbacks

### PDF Generation
- Template: `app/templates/invoice.latte`
- Response: `Invoictopus\Response\MpdfResponse` wraps mPDF library
- QR payment codes via `dfridrich/qr-platba` package
- Custom Latte filter for price formatting: `Invoictopus\TemplateFilter\Price`

### Service Injection
Services injected via constructor with type hints (Nette DI autowiring):
```php
public function __construct(DataEntityManager $data, DataGridEntryFactory $dataGridEntryFactory)
```

### Data Grid Component
- Uses `Doomy\DataGrid` package
- Created in `createComponent*DataGrid()` methods
- Supports CRUD operations and custom event handlers

## Database Naming Conventions
- Tables: `t_*` prefix (e.g., `t_invoice`, `t_invoice_item`)
- Columns: Mix of UPPER_CASE and camelCase in schema (being standardized to UPPER_CASE in entity classes)
- Primary keys: `ID` or `id` depending on migration version

## TODO Items
- Implement Czech translations (currently Czech-only)
