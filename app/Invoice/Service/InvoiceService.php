<?php

namespace Invoictopus\Invoice\Service;

use Doomy\Ormtopus\DataEntityManager;
use Invoictopus\Invoice\Invoice;
use Invoictopus\Invoice\Item;

final readonly class InvoiceService
{
    public function __construct(
        private DataEntityManager $data
    ) {}

    public function getInvoiceItems(
        ?Invoice $invoice
    ): array {
        if ($invoice === null) {
            return [];
        }

        return $this->data->findAll(Item::class, ['invoice_id' => $invoice->id]);
    }
}