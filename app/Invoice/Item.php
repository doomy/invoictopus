<?php

namespace Invoictopus\Invoice;

use Doomy\Repository\Model\Entity;

class Item extends Entity
{
    const TABLE = 't_invoice_item';

    public $INVOICE_ID;
    public $ITEM_NAME;
    public $AMOUNT;
    public $PRICE;
    public $VAT_RATE;
    public $CURRENCY;
}