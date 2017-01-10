<?php
namespace AppBundle\TCPDF;

class TCPDFCustomFooter extends \TCPDF
{
    protected $footer;

    public function setFooterHtml($footer)
    {
        $this->footer = $footer;
    }

    public function Footer()
    {
        $this->SetY(-30);
        $this->writeHTML($this->footer, true, false, false, false, '');
    }
}
