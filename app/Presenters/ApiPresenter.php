<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Http\Request;

class ApiPresenter extends Presenter
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function renderInvoice()
    {

    }

    private function restrictHttpMethods()
    {

    }

}
