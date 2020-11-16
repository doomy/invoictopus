<?php

namespace Invoictopus\Response;

use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Output\Destination;

class MpdfResponse implements \Nette\Application\IResponse
{
    private $html;
    private ?string $filename;

    public function __construct(string $html, ?string $filename = NULL)
    {
        $this->html = $html;
        $this->filename = $filename;
    }

    public function send(\Nette\Http\IRequest $httpRequest, \Nette\Http\IResponse $httpResponse): void
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'default_font' => 'Arial',
            'mode' => 'utf-8',
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/../../www/fonts',
            ]),
            'fontdata' => $fontData + [
                'arial' => [
                    'R' => 'arial.ttf',
                    'I' => 'arial.ttf',
                    'B' => 'arialbd.ttf'
                ]
            ],
            'allow_output_buffering' => TRUE
        ]);
        $mpdf->AddFontDirectory(__DIR__ . '/../../www/fonts');
        $mpdf->AddFont('arial');
        $mpdf->SetFont('Arial');
        @$mpdf->WriteHtml($this->html);
        $mpdf->Output($this->filename, Destination::INLINE);
    }
}
