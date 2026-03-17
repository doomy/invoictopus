<?php

declare(strict_types=1);

namespace Invoictopus\Invoice;

use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\Attribute\Column\Identity;
use Doomy\Repository\TableDefinition\Attribute\Table;

#[Table('t_invoice_item')]
class Item extends Entity
{
    public function __construct(
        #[Identity]
        public ?int $id = null,
        public ?int $invoice_id = null,
        public ?string $item_name = null,
        public ?int $amount = null,
        public ?float $price = null,
        public ?int $vat_rate = null,
        public ?string $currency = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int|string $id): void
    {
        $this->id = (int) $id;
    }

    public function getInvoice_id(): ?int
    {
        return $this->invoice_id;
    }

    public function getItem_name(): ?string
    {
        return $this->item_name;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getVat_rate(): ?int
    {
        return $this->vat_rate;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
