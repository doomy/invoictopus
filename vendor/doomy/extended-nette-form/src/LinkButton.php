<?php


namespace Doomy\ExtendedNetteForm;


use Nette\Forms\Controls\BaseControl;

class LinkButton extends BaseControl
{
    public function __construct($caption = NULL, $link = NULL)
    {
        parent::__construct($caption);
        $this->control->setName('a');
        $this->setAttribute("href", $link);
        $this->control->setText($caption);
        $this->caption = null;
    }


    public function getLabel($caption = NULL)
    {
        return NULL;
    }

}