<?php

declare(strict_types=1);

namespace Invoictopus\Invoice;

use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\Attribute\Column\Identity;
use Doomy\Repository\TableDefinition\Attribute\Table;

#[Table('t_invoice')]
class Invoice extends Entity
{
    public function __construct(
        #[Identity]
        public ?int $id = null,
        public ?string $supplier_name = null,
        public ?string $supplier_address_1 = null,
        public ?string $supplier_address_2 = null,
        public ?string $supplier_company_nr = null,
        public ?string $supplier_vat_nr = null,
        public ?string $customer_name = null,
        public ?string $customer_address_1 = null,
        public ?string $customer_address_2 = null,
        public ?string $customer_company_nr = null,
        public ?string $customer_vat_nr = null,
        public ?string $bank_account_nr = null,
        public ?\DateTimeInterface $invoice_date = null,
        public ?\DateTimeInterface $taxable_date = null,
        public ?\DateTimeInterface $due_date = null,
        public ?string $total_currency = null,
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

    public function getSupplier_name(): ?string
    {
        return $this->supplier_name;
    }

    public function getSupplier_address_1(): ?string
    {
        return $this->supplier_address_1;
    }

    public function getSupplier_address_2(): ?string
    {
        return $this->supplier_address_2;
    }

    public function getSupplier_company_nr(): ?string
    {
        return $this->supplier_company_nr;
    }

    public function getSupplier_vat_nr(): ?string
    {
        return $this->supplier_vat_nr;
    }

    public function getCustomer_name(): ?string
    {
        return $this->customer_name;
    }

    public function getCustomer_address_1(): ?string
    {
        return $this->customer_address_1;
    }

    public function getCustomer_address_2(): ?string
    {
        return $this->customer_address_2;
    }

    public function getCustomer_company_nr(): ?string
    {
        return $this->customer_company_nr;
    }

    public function getCustomer_vat_nr(): ?string
    {
        return $this->customer_vat_nr;
    }

    public function getBank_account_nr(): ?string
    {
        return $this->bank_account_nr;
    }

    public function getInvoice_date(): ?\DateTimeInterface
    {
        return $this->invoice_date;
    }

    public function getTaxable_date(): ?\DateTimeInterface
    {
        return $this->taxable_date;
    }

    public function getDue_date(): ?\DateTimeInterface
    {
        return $this->due_date;
    }

    public function getTotal_currency(): ?string
    {
        return $this->total_currency;
    }
}
