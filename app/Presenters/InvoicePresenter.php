<?php

namespace App\Presenters;

use Invoictopus\Invoice\Invoice;
use Invoictopus\Invoice\Item;
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
        $templateData = $this->getTemplateData();
        $html = $latte->renderToString(__DIR__ . '/../templates/invoice.latte', $templateData);
        $this->saveInvoice($templateData);
        $response = new MpdfResponse($html);

        $this->sendResponse($response);
    }

    public function createComponentInvoiceForm() {
        $invoiceDate = new \DateTimeImmutable();
        $dueDate = $invoiceDate->add(new \DateInterval('P15D'));


        /** @var Invoice */
        $lastInvoice = $this->getLastInvoice();
        $form = new Form();
        $form->setAction($this->link('Invoice:generate'));
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
        $form->addInteger('ITEM_AMOUNT')->setDefaultValue($item->AMOUNT);
        $form->addInteger('ITEM_PRICE')->setDefaultValue($item->PRICE);
        $form->addInteger('ITEM_VAT_RATE', 'VAT rate(%)')->setDefaultValue($item->VAT_RATE);
        $form->addSubmit('Generate', 'Generate');

        return $form;
    }

    private function getTemplateData(): array
    {
        $itemAmount = (int)$this->getHttpRequest()->getPost('ITEM_AMOUNT');
        $itemPrice = (float)$this->getHttpRequest()->getPost('ITEM_PRICE');
        $itemVatRate = $this->getHttpRequest()->getPost('ITEM_VAT_RATE');
        $itemTotalPrice = $this->calculateTotalPrice($itemAmount, $itemPrice, $itemVatRate);

        return [
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'invoiceNr' => $this->getHttpRequest()->getPost('ID'),
            'supplierName' => $this->getHttpRequest()->getPost('SUPPLIER_NAME'),
            'supplierAddress1' => $this->getHttpRequest()->getPost('SUPPLIER_ADDRESS_1'),
            'supplierAddress2' => $this->getHttpRequest()->getPost('SUPPLIER_ADDRESS_2'),
            'supplierCompanyNr' => $this->getHttpRequest()->getPost('SUPPLIER_COMPANY_NR'),
            'supplierVatNr' => $this->getHttpRequest()->getPost('SUPPLIER_VAT_NR'),
            'customerName' => $this->getHttpRequest()->getPost('CUSTOMER_NAME'),
            'customerAddress1' => $this->getHttpRequest()->getPost('CUSTOMER_ADDRESS_1'),
            'customerAddress2' => $this->getHttpRequest()->getPost('CUSTOMER_ADDRESS_2'),
            'customerCompanyNr' => $this->getHttpRequest()->getPost('CUSTOMER_COMPANY_NR'),
            'customerVatNr' => $this->getHttpRequest()->getPost('CUSTOMER_VAT_NR'),
            'bankAccountNr' => $this->getHttpRequest()->getPost('BANK_ACCOUNT_NR'),
            'invoiceDate' => $this->getHttpRequest()->getPost('INVOICE_DATE'),
            'taxableDate' => $this->getHttpRequest()->getPost('TAXABLE_DATE'),
            'dueDate' => $this->getHttpRequest()->getPost('DUE_DATE'),
            'invoicedItems' => [
                [
                    'item' => $this->getHttpRequest()->getPost('ITEM_NAME'),
                    'amount' => $itemAmount,
                    'price' => $itemPrice,
                    'vatRate' => $itemVatRate,
                    'total' => $itemTotalPrice,
                    'currency' => 'CZK'
                ]
            ],
            'total' => [
                'amount' => $itemTotalPrice,
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

    private function calculateTotalPrice(int $amount, float $itemPrice, int $vatRate) {
        if ($vatRate > 0) {
            $vat = ($itemPrice / 100) * $vatRate;
            $itemPrice += $vat;
        }

        return $itemPrice;
    }

    private function saveInvoice(array $invoiceData) {
        $this->dataProvider->save(
            Invoice::class,
            [
                'ID' => $invoiceData['invoiceNr'],
                'SUPPLIER_NAME' => $invoiceData['supplierName'],
                'SUPPLIER_ADDRESS_1' => $invoiceData['supplierAddress1'],
                'SUPPLIER_ADDRESS_2' => $invoiceData['supplierAddress2'],
                'SUPPLIER_COMPANY_NR' => $invoiceData['supplierCompanyNr'],
                'SUPPLIER_VAT_NR' => $invoiceData['supplierVatNr'],
                'CUSTOMER_NAME' => $invoiceData['customerName'],
                'CUSTOMER_ADDRESS_1' => $invoiceData['customerAddress1'],
                'CUSTOMER_ADDRESS_2' => $invoiceData['customerAddress2'],
                'CUSTOMER_COMPANY_NR' => $invoiceData['customerCompanyNr'],
                'CUSTOMER_VAT_NR' => $invoiceData['customerVatNr'],
                'BANK_ACCOUNT_NR' => $invoiceData['bankAccountNr'],
                'INVOICE_DATE' => new \DateTime($invoiceData['invoiceDate']),
                'TAXABLE_DATE' => new \DateTime($invoiceData['taxableDate']),
                'DUE_DATE' => new \DateTime($invoiceData['dueDate'])
            ]
        );
        foreach ($invoiceData['invoicedItems'] as $item) {
            $this->dataProvider->save(
                Item::class,
                [
                    'INVOICE_ID' => $invoiceData['invoiceNr'],
                    'ITEM_NAME' => $item['item'],
                    'AMOUNT' => $item['amount'],
                    'PRICE' => $item['price'],
                    'VAT_RATE' => $item['vatRate'],
                    'CURRENCY' => $item['currency']
                ]
            );
        }
    }

}