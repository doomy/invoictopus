<?php

namespace Invoictopus\Invoice\View;

final readonly class InvoiceItemView
{
    public function __construct(
        private string $itemName,
        private int $amount,
        private float $price,
        private float $vatPrice,
        private int $vatRate,
        private string $currency,
        private float $total
    ) {}

    public function getInvoiceId(): int
    {
        return $this->invoiceId;
    }

    public function getItemName(): string
    {
        return $this->itemName;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getVatPrice(): float
    {
        return $this->vatPrice;
    }

    public function getVatRate(): int
    {
        return $this->vatRate;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

}