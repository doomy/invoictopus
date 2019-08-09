<?php

namespace App\Presenters;

use Invoictopus\Invoice\Invoice;
use Invoictopus\Response\MpdfResponse;
use Invoictopus\TemplateFilter\Price;
use Latte\Engine as LatteEngine;
use Doomy\ExtendedNetteForm\Form;
use Nette\Application\UI\Presenter;
use Doomy\DataProvider\DataProvider;

class InvoicePresenter extends Presenter
{
    private $lastInvoice;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function renderForm() {


    }

    public function renderGenerate() {
        $latte = new LatteEngine();
        $latte->addFilter('price', new Price());
        $html = $latte->renderToString(__DIR__ . '/../templates/invoice.latte', $this->getTemplateData());
        $response = new MpdfResponse($html);

        $this->sendResponse($response);
    }

    public function createComponentInvoiceForm() {
        $invoiceDate = new \DateTimeImmutable();
        $dueDate = $invoiceDate->add(new \DateInterval('P15D'));


        /** @var Invoice */
        $lastInvoice = $this->getLastInvoice();
        $form = new Form();
        $form->addInteger('ID', 'Invoice id')->setDefaultValue((int)$lastInvoice->ID+1);
        $form->addText('SUPPLIER_NAME')->setDefaultValue($lastInvoice->SUPPLIER_NAME);
        $form->addText('SUPPLIER_ADDRESS_1')->setDefaultValue($lastInvoice->SUPPLIER_ADDRESS_1);
        $form->addText('SUPPLIER_ADDRESS_2')->setDefaultValue($lastInvoice->SUPPLIER_ADDRESS_2);
        $form->addText('SUPPLIER_COMPANY_NR')->setDefaultValue($lastInvoice->SUPPLIER_COMPANY_NR);
        $form->addText('SUPPLIER_VAT_NR')->setDefaultValue($lastInvoice->SUPPLIER_VAT_NR);
        $form->addText('CUSTOMER_NAME')->setDefaultValue($lastInvoice->CUSTOMER_NAME);
        $form->addText('CUSTOMER_ADDRESS_1')->setDefaultValue($lastInvoice->CUSTOMER_ADDRESS_1);
        $form->addText('CUSTOMER_ADDRESS_2')->setDefaultValue($lastInvoice->CUSTOMER_ADDRESS_2);
        $form->addText('CUSTOMER_COMPANY_NR')->setDefaultValue($lastInvoice->CUSTOMER_COMPANY_NR);
        $form->addText('CUSTOMER_VAT_NR')->setDefaultValue($lastInvoice->CUSTOMER_VAT_NR);
        $form->addText('BANK_ACCOUNT_NR')->setDefaultValue($lastInvoice->BANK_ACCOUNT_NR);
        $form->addDate('INVOICE_DATE')->setDefaultValue($invoiceDate);
        $form->addDate('TAXABLE_DATE')->setDefaultValue((new \DateTime())->modify("last day of previous month"));
        $form->addDate('DUE_DATE')->setDefaultValue($dueDate);
        $items = $lastInvoice->getItems();
        $item = array_shift($items);
        $form->addText('ITEM_NAME')->setDefaultValue($item->ITEM_NAME);
        $form->addInteger('AMOUNT')->setDefaultValue($item->AMOUNT);

        /*
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);
        $form->addText()->setDefaultValue($lastInvoice->);*/
        return $form;
    }

    private function getTemplateData(): array
    {


        return [
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'invoiceNr' => 56,
            'supplierName' => 'Vladimír Bártek',
            'supplierAddress1' => 'Špitálská 669/8',
            'supplierAddress2' => '190 00 Praha 9',
            'supplierCompanyNr' => '04572777',
            'supplierVatNr' => 'Neplátce DPH',
            'customerName' => 'Shoptet s.r.o.',
            'customerAddress1' => 'Dvořeckého 628/8',
            'customerAddress2' => '169 00 Praha',
            'customerCompanyNr' => '28935675',
            'customerVatNr' => 'CZ28935675',
            'bankAccountNr' => '670100-2201399432/6210',
            'invoiceDate' => $invoiceDate,
            'taxableDate' => (new \DateTime())->modify("last day of previous month"),
            'dueDate' => $dueDate,
            'invoicedItems' => [
                [
                    'item' => 'PHP Vývoj na projektu',
                    'amount' => 1,
                    'price' => 75000,
                    'vatRate' => 0,
                    'total' => 75000,
                    'currency' => 'CZK'
                ]
            ],
            'total' => [
                'amount' => 75000,
                'currency' => 'CZK'
            ]
        ];
    }

    private function getLastInvoice(): ?Invoice
    {
        if (isset($this->lastInvoice)) {
            return $this->lastInvoice;
        }

        return $this->lastInvoice = $this->dataProvider->findOne(
            Invoice::class, [], 'ID DESC'
        );
    }

}