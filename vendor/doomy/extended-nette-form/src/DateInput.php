<?php

namespace Doomy\ExtendedNetteForm;

use Nette\Forms\Form as NetteForm;
use Nette\Utils\Html;
use Nette\Forms\Helpers;
use Nette\Forms\Controls\BaseControl;

class DateInput extends BaseControl
{
    protected $date;
    private $minDate;
    private $maxDate;

    public function __construct($caption)
    {
        parent::__construct($caption);
        $this->setRequired(false);
    }

    public function setValue($value) {
        if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable)
            $this->date = $value;
        else $this->date = \DateTime::createFromFormat("d.m.Y", $value);
    }

    public function getValue() {
        return $this->date;
    }

    public function loadHttpData(): void
    {
        $dateStr = $this->getHttpData(NetteForm::DATA_LINE);
        $dateStr .= " 0:00:00"; // we're only working with dates here
        $this->date =  \DateTime::createFromFormat("d.m.Y H:i:s", $dateStr);
    }

    public function isFilled(): bool
    {
        return TRUE;
    }

    public function setMinDate($minDate) {
        $this->minDate = $minDate;
    }

    public function setMaxDate($maxDate) {
        $this->maxDate = $maxDate;
    }

    public function getControl()
    {
        $name = $this->getHtmlName();
        $attributes = [
            'name'  => $name,
            'id'    => $this->getHtmlId(),
            'value' => $this->date ? date_format($this->date, "d.m.Y") : '',
            'type'  => 'text',
            'class' => 'datepicker form-control',
            'data-nette-rules' => Helpers::exportRules($this->getRules()) ?: NULL
        ];
        if ($this->minDate) $attributes['data-min-date'] = $this->minDate->format('d.m.Y');
        if ($this->maxDate) $attributes['data-max-date'] = $this->maxDate->format('d.m.Y');

        return Html::el('input', $attributes);
    }
}