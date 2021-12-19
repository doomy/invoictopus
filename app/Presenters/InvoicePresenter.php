<?php

namespace App\Presenters;

use Doomy\DataGrid\Component\DataGrid;
use Doomy\DataGrid\DataGridEntryFactory;
use Doomy\Helper\StringTools;
use Doomy\Ormtopus\DataEntityManager;
use Invoictopus\Invoice\Invoice;
use Invoictopus\Invoice\Item;
use Invoictopus\Response\MpdfResponse;
use Invoictopus\TemplateFilter\Price;
use Latte\Engine as LatteEngine;
use Doomy\ExtendedNetteForm\Form;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;

class InvoicePresenter extends Presenter
{
    private const VAT_RATE = 21;

    private $lastInvoice;
    private int $itemCount = 1;

    private DataEntityManager $data;
    private DataGridEntryFactory $dataGridEntryFactory;

    public function __construct(DataEntityManager $data, DataGridEntryFactory $dataGridEntryFactory)
    {
        $this->data = $data;
        $this->dataGridEntryFactory = $dataGridEntryFactory;
    }

    public function renderForm() {


    }

    public function renderGenerate() {
        $latte = new LatteEngine();
        $latte->addFilter('price', new Price());
        $templateData = $this->getTemplateData();
        $html = $latte->renderToString(__DIR__ . '/../templates/invoice.latte', $templateData);
        $this->saveInvoice($templateData);

        $this->sendResponse(new MpdfResponse($html, $this->assembleInvoiceFilename($templateData)));
    }

    public function renderList(): void {

    }


    public function createComponentInvoiceForm() {
        $invoiceDate = new \DateTimeImmutable();
        $dueDate = $invoiceDate->add(new \DateInterval('P15D'));


        /** @var Invoice */
        $lastInvoice = $this->getLastInvoice();
        $referenceInvoice = NULL;
        if (isset($_POST['invoice_id'])) {
            $referenceInvoice = $this->data->findById(Invoice::class, $_POST['invoice_id']);
        }
        if (empty($referenceInvoice)) {
            $referenceInvoice = $lastInvoice;
        }
        $form = new Form();
        $form->setAction($this->link('Invoice:generate'));
        $form->addInteger('ID', 'Invoice id')->setDefaultValue((int)$lastInvoice->ID+1);
        $form->addText('SUPPLIER_NAME')->setDefaultValue($referenceInvoice->SUPPLIER_NAME);
        $form->addText('SUPPLIER_ADDRESS_1')->setDefaultValue($referenceInvoice->SUPPLIER_ADDRESS_1);
        $form->addText('SUPPLIER_ADDRESS_2')->setDefaultValue($referenceInvoice->SUPPLIER_ADDRESS_2);
        $form->addText('SUPPLIER_COMPANY_NR')->setDefaultValue($referenceInvoice->SUPPLIER_COMPANY_NR);
        $form->addText('SUPPLIER_VAT_NR')->setDefaultValue($referenceInvoice->SUPPLIER_VAT_NR);
        $form->addText('CUSTOMER_NAME')->setDefaultValue($referenceInvoice->CUSTOMER_NAME);
        $form->addText('CUSTOMER_ADDRESS_1')->setDefaultValue($referenceInvoice->CUSTOMER_ADDRESS_1);
        $form->addText('CUSTOMER_ADDRESS_2')->setDefaultValue($referenceInvoice->CUSTOMER_ADDRESS_2);
        $form->addText('CUSTOMER_COMPANY_NR')->setDefaultValue($referenceInvoice->CUSTOMER_COMPANY_NR);
        $form->addText('CUSTOMER_VAT_NR')->setDefaultValue($referenceInvoice->CUSTOMER_VAT_NR);
        $form->addText('BANK_ACCOUNT_NR')->setDefaultValue($referenceInvoice->BANK_ACCOUNT_NR);
        $form->addDate('INVOICE_DATE')->setDefaultValue($invoiceDate);
        $form->addDate('TAXABLE_DATE')->setDefaultValue((new \DateTime())->modify("last day of previous month"));
        $form->addDate('DUE_DATE')->setDefaultValue($dueDate);
        $items = !empty($referenceInvoice) ? $referenceInvoice->getItems() : [];
        $item = array_shift($items);
        $itemsContainer = $form->addContainer('ITEMS');
        for ($i = 1; $i <= $this->itemCount; $i++) {
            $itemContainer = $itemsContainer->addContainer((string) $i-1);
            $itemContainer->addText('NAME')->setDefaultValue($item->ITEM_NAME);
            $itemContainer->addInteger('AMOUNT')->setDefaultValue($item->AMOUNT);
            $itemContainer->addInteger('PRICE')->setDefaultValue($item->PRICE);
            $itemContainer->addInteger('VAT_RATE', 'VAT rate(%)')->setDefaultValue($item->VAT_RATE);
        }
        $form->addSubmit('Generate', 'Generate');

        return $form;
    }

    public function createComponentInvoiceTemplateForm(): ?IComponent
    {
        $invoices = $this->data->findAll(Invoice::class);
        $items = [];
        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
            $items[$invoice->ID] = sprintf("Invoice %d", $invoice->ID) . ": " . $invoice->CUSTOMER_NAME;
        }
        $items = array_reverse($items, TRUE);
        $form = new Form();
        $form->addSelect('invoice_id', 'Select reference invoice', $items);
        $form->addSubmit("go", "Go!");
        $form->setAction($this->link('Invoice:form'));
        return $form;
    }

    private function getTemplateData(): array
    {
        $invoiceItems = $this->getInvoiceItems();

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
            'invoicedItems' => $invoiceItems,
            'vatRate' => static::VAT_RATE,
            'total' => [
                'amount' => $this->calculateItemsTotalPrice($invoiceItems),
                'currency' => 'Kč',
            ],
        ];
    }

    private function getLastInvoice(): ?Invoice
    {
        if (isset($this->lastInvoice)) {
            return $this->lastInvoice;
        }

        $lastInvoice = $this->data->findOne(
            Invoice::class, [], 'ID DESC'
        );

        if (empty($lastInvoice)) {
            $lastInvoice = $this->data->create(Invoice::class, ['ID' => 0]);
        }

        return $this->lastInvoice = $lastInvoice;
    }

    private function calculateTotalPrice(int $amount, float $itemPrice, int $vatRate) {
        if ($vatRate > 0) {
            $vat = ($itemPrice / 100) * $vatRate;
            $itemPrice += $vat;
        }

        return $amount * $itemPrice;
    }

    private function saveInvoice(array $invoiceData) {
        $this->data->save(
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
                'DUE_DATE' => new \DateTime($invoiceData['dueDate']),
            ]
        );
        foreach ($invoiceData['invoicedItems'] as $item) {
            $this->data->save(
                Item::class,
                [
                    'INVOICE_ID' => $invoiceData['invoiceNr'],
                    'ITEM_NAME' => $item['item'],
                    'AMOUNT' => $item['amount'],
                    'PRICE' => $item['price'],
                    'VAT_RATE' => $item['vatRate'],
                    'CURRENCY' => $item['currency'],
                ]
            );
        }
    }

    private function getInvoiceItems(): array
    {
        $items = [];
        foreach ($this->getHttpRequest()->getPost('ITEMS') as $item) {
            $items[] = [
                'item' => $item['NAME'],
                'amount' => $item['AMOUNT'],
                'price' => $item['PRICE'],
                'vatRate' => static::VAT_RATE,
                'total' => $this->calculateTotalPrice($item['AMOUNT'], $item['PRICE'], static::VAT_RATE),
                'vatPrice' => ($item['PRICE'] / 100) * static::VAT_RATE,
                'currency' => 'Kč'
            ];
        }
        return $items;
    }

    private function calculateItemsTotalPrice(array $items): float
    {
        $sum = (float) 0;

        foreach ($items as $item) {
            $sum += (float) $item['total'];
        }

        return $sum;
    }

    private function assembleInvoiceFilename(array $templateData): string
    {
        $invoiceDate = new \DateTime($templateData['invoiceDate']);

        return sprintf(
            '%s-%s-%s.pdf',
            sprintf('%04d', $templateData['invoiceNr']),
            $invoiceDate->format('Y-m-d'),
            strtolower(StringTools::normalizeStringForUri($templateData['customerName']))
        );
    }

    public function createComponentInvoiceDataGrid(): DataGrid
    {
        $dataGrid = new DataGrid($this->dataGridEntryFactory, $this->data, Invoice::class, [], FALSE, FALSE);
        $dataGrid->setReadOnly(FALSE);
        $dataGrid->setPreventAdd(TRUE);
        $dataGrid->setCustomOrderBy('ID DESC');
        $dataGrid->onEvent(DataGrid::EVENT_ITEM_DELETED, function(int $invoiceId) {
            $this->data->delete(Item::class, ['INVOICE_ID' => $invoiceId]);
            $this->data->deleteById(Invoice::class, $invoiceId);
        });
        return $dataGrid;
    }

}
