<?php

namespace Invoictopus\Invoice;

final readonly class InvoiceItemFactory
{
    /**
     * @return Item[]
     */
    public function createItemsFromData(array $invoiceItemsData, int $invoiceId): array
    {
        $items = [];
        foreach ($invoiceItemsData as $itemData) {
            $items[] = new Item(
                invoice_id: $invoiceId,
                item_name: $itemData['item'],
                amount: (int)$itemData['amount'],
                price: (float)$itemData['price'],
                vat_rate: (int)$itemData['vatRate'],
                currency: $itemData['currency'],
            );
        }

        return $items;
    }

}