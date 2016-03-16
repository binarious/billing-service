<?php
namespace AppBundle\Service;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

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
     * @Inject("%shutdown_deadline%")
     */
    public $shutdownDeadline;

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
            $this->shutdownDeadline
        );
    }

    /**
     * Duns the given bill. The required mails will be sent here.
     * @param  Bill   $bill Bill entity
     */
    public function dun(Bill $bill)
    {
        // skip if already shut down
        if ($bill->getShutdownSince()) {
            return;
        }

        $projectName = $bill->getProject()->getName();
        switch ($bill->getReceivedDuns()) {
            // do first dun
            case null:
                $this->sendEmail('first', 'Zahlungserinnerung: ' . $projectName, $bill);
                $bill->doDun();
                break;
            // do second dun
            case 1:
                $this->sendEmail('second', 'Zweite Zahlungserinnerung: ' . $projectName, $bill);
                $bill->doDun();
                break;
            // do last dun
            case 2:
                $this->sendEmail('third', 'Warnung Abschaltung: ' . $projectName, $bill);
                $bill->doDun();
                break;
            // do shutdown
            case 3:
                $this->sendEmail('shutdown', 'Abschaltung: ' . $projectName, $bill, false);
                $bill->setShutdownSince(new \DateTime());
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
                    'email/' . $template . '.html.twig',
                    [
                        'bill' => $bill,
                        'firstDunDeadline' => $this->firstDunDeadline,
                        'secondDunDeadline' => $this->secondDunDeadline,
                        'shutdownDeadline' => $this->shutdownDeadline,
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}
