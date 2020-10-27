ALTER TABLE t_invoice
    CHANGE customerName customer_name VARCHAR(255) NOT NULL ,
    CHANGE customerAddress1 customer_address_1 VARCHAR(255) NULL,
    CHANGE customerAddress2 customer_address_2 VARCHAR(255) NULL,
    CHANGE customerCompanyNr customer_company_nr VARCHAR(255) NULL,
    CHANGE customerVatNr customer_vat_nr VARCHAR(255) NULL,
    CHANGE bankAccountNr bank_account_nr VARCHAR(255) NULL,
    CHANGE invoiceDate invoice_date DATE NOT NULL,
    CHANGE taxableDate taxable_date DATE NOT NULL,
    CHANGE dueDate due_date DATE NOT NULL;

ALTER TABLE t_invoice_item
    CHANGE vatRate vat_rate TINYINT NOT NULL DEFAULT 0;