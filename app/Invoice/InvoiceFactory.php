<?php

namespace Invoictopus\Invoice;

final readonly class InvoiceFactory
{
    /**
     * @param array<string, mixed> $invoiceData
     * @return Invoice
     */
    public function createFromInvoiceFormData(array $invoiceData): Invoice
    {
        return new Invoice(
            id: (int) $invoiceData['invoiceNr'],
            supplier_name: $invoiceData['supplierName'],
            supplier_address_1: $invoiceData['supplierAddress1'],
            supplier_address_2: $invoiceData['supplierAddress2'],
            supplier_company_nr: $invoiceData['supplierCompanyNr'],
            supplier_vat_nr: $invoiceData['supplierVatNr'],
            customer_name: $invoiceData['customerName'],
            customer_address_1: $invoiceData['customerAddress1'],
            customer_address_2: $invoiceData['customerAddress2'],
            customer_company_nr: $invoiceData['customerCompanyNr'],
            customer_vat_nr: $invoiceData['customerVatNr'],
            bank_account_nr: $invoiceData['bankAccountNr'],
            invoice_date: new \DateTime($invoiceData['invoiceDate']),
            taxable_date: new \DateTime($invoiceData['taxableDate']),
            due_date: new \DateTime($invoiceData['dueDate']),
            total_currency: $invoiceData['total']['currency'],
        );
    }

}