DROP TABLE IF EXISTS t_invoice;
CREATE TABLE t_invoice (
    id INT NOT NULL AUTO_INCREMENT,
    supplier_name VARCHAR(255) NOT NULL,
    supplier_address_1 VARCHAR(255) NULL,
    supplier_address_2 VARCHAR(255) NULL,
    supplier_company_nr VARCHAR(255) NULL,
    supplier_vat_nr VARCHAR(255) NULL,
    customerName VARCHAR(255) NOT NULL,
    customerAddress1 VARCHAR(255) NULL,
    customerAddress2 VARCHAR(255) NULL,
    customerCompanyNr VARCHAR(255) NULL,
    customerVatNr VARCHAR(255) NULL,
    bankAccountNr VARCHAR(255) NULL,
    invoiceDate DATETIME NOT NULL,
    taxableDate DATETIME NOT NULL,
    dueDate DATETIME NOT NULL,
    total_amount FLOAT NOT NULL,
    total_currency VARCHAR(3) NOT NULL DEFAULT 'CZK',
    PRIMARY KEY(id)
);

DROP TABLE IF EXISTS t_invoice_item;
CREATE TABLE t_invoice_item (
    id INT NOT NULL AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    amount INT NOT NULL DEFAULT 1,
    price FLOAT NOT NULL,
    vatRate TINYINT NOT NULL DEFAULT 0,
    currency VARCHAR(255) NOT NULL DEFAULT 'CZK',
    PRIMARY KEY (id)
);