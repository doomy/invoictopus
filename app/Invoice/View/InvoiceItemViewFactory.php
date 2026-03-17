<?php

namespace Invoictopus\Invoice\View;

use Invoictopus\Invoice\Item;

final readonly class InvoiceItemViewFactory
{
    public function createFromInvoiceItems(array $items): array
    {
        $itemViews = [];
        foreach ($items as $item) {
            $itemViews[] = $this->createFromInvoiceItem($item);
        }

        return $itemViews;
    }

    private function createFromInvoiceItem(
        Item $item
    ): InvoiceItemView {
        return new InvoiceItemView(
            itemName: $item->getItem_name(),
            amount: $item->getAmount(),
            price: $item->getPrice(),
            vatPrice: round($item->getPrice() * ($item->getVat_rate() / 100), 2),
            vatRate: $item->getVat_rate(),
            currency: $item->getCurrency(),
            total: $this->calculateTotalPrice($item->getAmount(), $item->getPrice(), $item->getVat_rate(),)
        );
    }

    private function calculateTotalPrice(int $amount, float $itemPrice, int $vatRate): float {
        if ($vatRate > 0) {
            $vat = ($itemPrice / 100) * $vatRate;
            $itemPrice += $vat;
        }

        return $amount * $itemPrice;
    }

}