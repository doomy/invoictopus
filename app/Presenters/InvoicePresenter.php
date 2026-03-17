<?php

namespace App\Presenters;

use Invoictopus\Currency\Currency;
use Defr\QRPlatba\QRPlatba;
use Doomy\DataGrid\Component\DataGrid;
use Doomy\DataGrid\DataGridEntryFactory;
use Doomy\Helper\StringTools;
use Doomy\Ormtopus\DataEntityManager;
use Invoictopus\Invoice\Invoice;
use Invoictopus\Invoice\InvoiceFactory;
use Invoictopus\Invoice\Item;
use Invoictopus\Invoice\Service\InvoiceService;
use Invoictopus\Invoice\View\InvoiceItemView;
use Invoictopus\Invoice\View\InvoiceItemViewFactory;
use Invoictopus\Response\MpdfResponse;
use Invoictopus\TemplateFilter\Price;
use Latte\Engine as LatteEngine;
use Doomy\ExtendedNetteForm\Form;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Invoictopus\Invoice\InvoiceItemFactory;

class InvoicePresenter extends Presenter
{
    private const VAT_RATE = 0;

    private $lastInvoice;
    private int $itemCount = 1;

    private DataEntityManager $data;
    private DataGridEntryFactory $dataGridEntryFactory;
    private string $dataEnvironment;

    public function __construct(
        DataEntityManager $data,
        DataGridEntryFactory $dataGridEntryFactory,
        string $dataEnvironment = 'production',
        private readonly InvoiceItemFactory $invoiceItemFactory,
        private readonly InvoiceFactory $invoiceFactory,
        private readonly InvoiceService $invoiceService,
        private readonly InvoiceItemViewFactory $invoiceItemViewFactory
    ) {
        $this->data = $data;
        $this->dataGridEntryFactory = $dataGridEntryFactory;
        $this->dataEnvironment = $dataEnvironment;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->dataEnvironment = $this->dataEnvironment;
    }

    public function renderForm() {


    }

    public function renderGenerate() {
        $latte = new LatteEngine();
        $latte->addFilter('price', new Price());

        $itemsRawData = $this->getInvoiceItemsRawdata();
        $invoiceId = (int) $this->getHttpRequest()->getPost('ID') ?? throw new \RuntimeException("Invoice number is required.");
        $invoiceItems = $this->invoiceItemFactory->createItemsFromData($itemsRawData, $invoiceId);
        $templateData = $this->getTemplateData($invoiceItems, $invoiceId);
        $html = $latte->renderToString(__DIR__ . '/../templates/invoice.latte', $templateData);
        $this->invoiceService->saveInvoice($templateData, $invoiceItems);

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
        $form->addInteger('ID', 'Invoice id')->setDefaultValue((int)$lastInvoice->id+1);
        $form->addText('SUPPLIER_NAME')->setDefaultValue($referenceInvoice->supplier_name);
        $form->addText('SUPPLIER_ADDRESS_1')->setDefaultValue($referenceInvoice->supplier_address_1);
        $form->addText('SUPPLIER_ADDRESS_2')->setDefaultValue($referenceInvoice->supplier_address_2);
        $form->addText('SUPPLIER_COMPANY_NR')->setDefaultValue($referenceInvoice->supplier_company_nr);
        $form->addText('SUPPLIER_VAT_NR')->setDefaultValue($referenceInvoice->supplier_vat_nr);
        $form->addText('CUSTOMER_NAME')->setDefaultValue($referenceInvoice->customer_name);
        $form->addText('CUSTOMER_ADDRESS_1')->setDefaultValue($referenceInvoice->customer_address_1);
        $form->addText('CUSTOMER_ADDRESS_2')->setDefaultValue($referenceInvoice->customer_address_2);
        $form->addText('CUSTOMER_COMPANY_NR')->setDefaultValue($referenceInvoice->customer_company_nr);
        $form->addText('CUSTOMER_VAT_NR')->setDefaultValue($referenceInvoice->customer_vat_nr);
        $form->addText('BANK_ACCOUNT_NR')->setDefaultValue($referenceInvoice->bank_account_nr);
        $form->addDate('INVOICE_DATE')->setDefaultValue($invoiceDate);
        $form->addDate('TAXABLE_DATE')->setDefaultValue((new \DateTime())->modify("last day of previous month"));
        $form->addDate('DUE_DATE')->setDefaultValue($dueDate);
        $items = !empty($referenceInvoice) ? $this->data->findAll(Item::class, ['invoice_id' => $referenceInvoice->id]) : [];
        $item = array_shift($items);
        $itemsContainer = $form->addContainer('ITEMS');
        for ($i = 1; $i <= $this->itemCount; $i++) {
            $itemContainer = $itemsContainer->addContainer((string) $i-1);
            $itemContainer->addText('NAME')->setDefaultValue($item->item_name);
            $itemContainer->addInteger('AMOUNT')->setDefaultValue($item->amount);
            $itemContainer->addInteger('PRICE')->setDefaultValue($item->price);
            $itemContainer->addInteger('VAT_RATE', 'VAT rate(%)')->setDefaultValue($item->vat_rate);
        }
        $form->addSubmit('Generate', 'Generate');
        $form->onSuccess[] = function () {};

        return $form;
    }

    public function createComponentInvoiceTemplateForm(): ?IComponent
    {
        $invoices = $this->data->findAll(Invoice::class);
        $items = [];
        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
            $items[$invoice->id] = sprintf("Invoice %d", $invoice->id) . ": " . $invoice->customer_name;
        }
        $items = array_reverse($items, TRUE);
        $form = new Form();
        $form->addSelect('invoice_id', 'Select reference invoice', $items);
        $form->addSubmit("go", "Go!");
        $form->setAction($this->link('Invoice:form'));
        $form->onSuccess[] = function () {};
        return $form;
    }

    private function getTemplateData(array $invoiceItems, int $invoiceId): array
    {
        $invoiceItemViews = $this->invoiceItemViewFactory->createFromInvoiceItems($invoiceItems);
        $totalAmount = $this->calculateItemsTotalPrice($invoiceItemViews);

        $invoiceNr = (string) $invoiceId;
        $bankAccountNr = $this->getHttpRequest()->getPost('BANK_ACCOUNT_NR');
        $dueDate = $this->getHttpRequest()->getPost('DUE_DATE');

        $qrPlatba = new QRPlatba();
        $qrPlatba
            ->setAccount($bankAccountNr)
            ->setVariableSymbol($invoiceNr)
            ->setMessage(sprintf("Faktura %d", $invoiceNr))
            ->setAmount($totalAmount)
            ->setCurrency(Currency::CZK->name);
            //->setDueDate(\DateTime::createFromFormat("d.m.Y", $dueDate));

        $supplierVatNr = trim($this->getHttpRequest()->getPost('SUPPLIER_VAT_NR'));
        if ($supplierVatNr === '') {
            $supplierVatNr = null;
        }

        return [
            'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
            'invoiceNr' => $invoiceNr,
            'supplierName' => $this->getHttpRequest()->getPost('SUPPLIER_NAME'),
            'supplierAddress1' => $this->getHttpRequest()->getPost('SUPPLIER_ADDRESS_1'),
            'supplierAddress2' => $this->getHttpRequest()->getPost('SUPPLIER_ADDRESS_2'),
            'supplierCompanyNr' => $this->getHttpRequest()->getPost('SUPPLIER_COMPANY_NR'),
            'supplierVatNr' => $supplierVatNr,
            'customerName' => $this->getHttpRequest()->getPost('CUSTOMER_NAME'),
            'customerAddress1' => $this->getHttpRequest()->getPost('CUSTOMER_ADDRESS_1'),
            'customerAddress2' => $this->getHttpRequest()->getPost('CUSTOMER_ADDRESS_2'),
            'customerCompanyNr' => $this->getHttpRequest()->getPost('CUSTOMER_COMPANY_NR'),
            'customerVatNr' => $this->getHttpRequest()->getPost('CUSTOMER_VAT_NR'),
            'bankAccountNr' => $bankAccountNr,
            'invoiceDate' => $this->getHttpRequest()->getPost('INVOICE_DATE'),
            'taxableDate' => $this->getHttpRequest()->getPost('TAXABLE_DATE'),
            'dueDate' => $dueDate,
            'invoicedItems' => $invoiceItemViews,
            'vatRate' => static::VAT_RATE,
            'total' => [
                'amount' => $totalAmount,
                'currency' => Currency::CZK->value,
            ],
            'qrCode' => $qrPlatba->getQRCodeImage(true, 150)
        ];
    }

    private function getLastInvoice(): ?Invoice
    {
        if (isset($this->lastInvoice)) {
            return $this->lastInvoice;
        }

        $lastInvoice = $this->data->findOne(
            Invoice::class, [], 'id DESC'
        );

        if (empty($lastInvoice)) {
            $lastInvoice = new Invoice(id: 0);
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




    private function getInvoiceItemsRawdata(): array
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

    /**
     * @param InvoiceItemView[] $items
     */
    private function calculateItemsTotalPrice(array $items): float
    {
        $sum = (float) 0;

        foreach ($items as $item) {
            $sum += $item->getTotal();
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
        $dataGrid->setCustomOrderBy('id DESC');
        $dataGrid->onEvent(DataGrid::EVENT_ITEM_DELETED, function(int $invoiceId) {
            $this->data->delete(Item::class, ['invoice_id' => $invoiceId]);
            $this->data->deleteById(Invoice::class, $invoiceId);
        });
        return $dataGrid;
    }

}
