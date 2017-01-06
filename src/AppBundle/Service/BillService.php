<?php
namespace AppBundle\Service;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use AppBundle\TCPDF\TCPDFCustomFooter;
use AppBundle\Entity\Bill;

/**
 * @Service("bill_service")
 */
class BillService
{
    /**
     * @Inject("doctrine.orm.entity_manager")
     */
    public $em;

    /**
     * @Inject("templating")
     */
    public $templating;

    /**
     * @Inject("mailer")
     */
    public $mailer;

    /**
     * @Inject("%first_dun_deadline%")
     */
    public $firstDunDeadline;

    /**
     * @Inject("%second_dun_deadline%")
     */
    public $secondDunDeadline;

    /**
     * @Inject("%laststep_deadline%")
     */
    public $laststepDeadline;

    /**
     * @Inject("%sender_address%")
     */
    public $senderAddress;

    /**
     * Queries all due bills based on the configured deadlines.
     * @return array Doctrine result
     */
    public function getDue()
    {
        return $this->em->getRepository('AppBundle:Bill')->findDue(
            $this->firstDunDeadline,
            $this->secondDunDeadline,
            $this->laststepDeadline
        );
    }

    /**
     * Duns the given bill. The required mails will be sent here.
     * @param  Bill   $bill Bill entity
     */
    public function dun(Bill $bill)
    {
        $projectName = $bill->getProject()->getName();
        switch ($bill->getReceivedDuns()) {
            // do first dun
            case null:
                $this->sendEmail('first', 'Zahlungserinnerung: ' . $projectName, $bill);
                $bill->doDun();
                break;
            // do second dun
            case 1:
                $this->sendEmail('second', 'Mahnung: ' . $projectName, $bill);
                $bill->doDun();
                break;
            // do last dun
            case 2:
                $this->sendEmail('third', 'Letzte Mahnung: ' . $projectName, $bill);
                $bill->doDun();
                break;
        }

        $this->em->persist($bill);
        $this->em->flush();
    }

    /**
     * Sets the bill as payed and send information mails to the customer and
     * admin if a shutdown was announced previously.
     *
     * @param Bill $bill Bill entity
     */
    public function setPayed(Bill $bill)
    {
        $bill->setAccountBalance($bill->getAmount());
        $this->em->persist($bill);
        $this->em->flush();

        $projectName = $bill->getProject()->getName();
        if ($bill->getReceivedDuns() === 3) {
            $this->sendEmail('regeneration', 'Zahlung erhalten: ' . $projectName, $bill);

            if ($bill->getShutdownSince()) {
                $this->sendEmail('startup', 'Anschaltung: ' . $projectName, $bill, false);
            }
        }
    }

    private function sendEmail($template, $subject, Bill $bill, $toCustomer = true)
    {
        $customer = $bill->getProject()->getCustomer();
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->senderAddress)
            ->setTo($toCustomer ? $customer->getEmail() : $customer->getAdmin()->getEmail())
            ->setBcc($toCustomer ? $customer->getAdmin()->getEmail() : null)
            ->setBody(
                $this->templating->render(
                    ':email:' . $template . '.html.twig',
                    [
                        'bill' => $bill,
                        'firstDunDeadline' => $this->firstDunDeadline,
                        'secondDunDeadline' => $this->secondDunDeadline,
                        'laststepDeadline' => $this->laststepDeadline,
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function getNextBillName()
    {
        $year = date('Y');
        $count = count($this->em->getRepository('AppBundle:Bill')->findByYear(date('Y'))) + 1;

        return str_pad(
            $count,
            3,
            '0',
            STR_PAD_LEFT
        ) . '-' . $year;
    }

    public function generatePdf(Bill $bill)
    {
        $template = 'modern';
        $footer = $this->templating->render(
            ':bill:templates/' . $template . '.footer.html.twig',
            [
                'bill' => $bill,
            ]
        );

        $pdf = new TCPDFCustomFooter(\PDF_PAGE_ORIENTATION, \PDF_UNIT, 'LETTER', true, 'UTF-8', false);

        $pdf->setFooterHtml($footer);
        $pdf->SetAuthor('Martin Bieder');
        $pdf->SetTitle('Rechnung ' . $bill->getName());

        // set font
        $pdf->SetFont('dejavusans', '', 10);

        // add a page
        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTML(
            $this->templating->render(
                ':bill:templates/' . $template . '.html.twig',
                [
                    'bill' => $bill,
                ]
            ),
            true,
            false,
            true,
            false,
            ''
        );

        $pdf->Output($bill->getName() . '.pdf', 'I');
    }
}
