<?php

namespace Invoictopus\Invoice\Service;

use Doomy\Ormtopus\DataEntityManager;
use Invoictopus\Invoice\Invoice;
use Invoictopus\Invoice\InvoiceFactory;
use Invoictopus\Invoice\InvoiceItemFactory;
use Invoictopus\Invoice\Item;

final readonly class InvoiceService
{
    public function __construct(
        private DataEntityManager $data,
        private InvoiceFactory $invoiceFactory,
        private InvoiceItemFactory $invoiceItemFactory,
    ) {}

    public function getInvoiceItems(
        ?Invoice $invoice
    ): array {
        if ($invoice === null) {
            return [];
        }

        return $this->data->findAll(Item::class, ['invoice_id' => $invoice->id]);
    }

    /**
     * @param array<string, mixed> $invoiceData
     * @param Item[] $items
     * @return void
     */
    public function saveInvoice(array $invoiceData, array $items): void
    {
        $invoice = $this->invoiceFactory->createFromInvoiceFormData($invoiceData);
        $this->data->save(Invoice::class, $invoice);
        foreach ($items as $item) {
            $this->data->save(Item::class, $item);
        }
    }
}