<?php

namespace Invoictopus\TemplateFilter;

class Price
{
    public function __invoke($number)
    {
        return number_format($number, 2, ",", " ");
    }

}