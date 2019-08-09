ALTER TABLE t_invoice
    CHANGE invoiceDate InvoiceDate DATE NOT NULL,
    CHANGE taxableDate taxableDate DATE NOT NULL,
    CHANGE dueDate dueDate DATE NOT NULL,
    DROP COLUMN total_amount;