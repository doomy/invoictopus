<?php

namespace Invoictopus\Invoice;

use Doomy\Repository\Model\Entity;

class Invoice extends Entity
{
    const TABLE = 't_invoice';
    public $ID;
    public $SUPPLIER_NAME;
    public $SUPPLIER_ADDRESS_1;
    public $SUPPLIER_ADDRESS_2;
    public $SUPPLIER_COMPANY_NR;
    public $SUPPLIER_VAT_NR;
    public $CUSTOMER_NAME;
    public $CUSTOMER_ADDRESS_1;
    public $CUSTOMER_ADDRESS_2;
    public $CUSTOMER_COMPANY_NR;
    public $CUSTOMER_VAT_NR;
    public $BANK_ACCOUNT_NR;
    public $INVOICE_DATE;
    public $TAXABLE_DATE;
    public $DUE_DATE;
    public $TOTAL_CURRENCY;

    public function getItems(): array {
        return $this->get1NRelation(Item::class, 'INVOICE_ID', $this->ID);
    }
}