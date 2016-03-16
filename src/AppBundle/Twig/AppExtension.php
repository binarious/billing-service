<?php
namespace AppBundle\Twig;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

use AppBundle\Entity\Bill;

/**
 * @Service("app.twig_extension")
 * @Tag("twig.extension")
 */
class AppExtension extends \Twig_Extension
{
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

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('nextBillStep', array($this, 'nextBillStepFilter')),
        );
    }

    public function nextBillStepFilter(Bill $bill)
    {
        if ($bill->getShutdownSince()) {
            return;
        }

        $when = '';
        $action = '';
        $icon = '';
        $today = new \DateTime();

        switch($bill->getReceivedDuns()) {
            case null:
                $action = 'Erste Zahlungserinnerung';
                $icon = 'comment';
                $when = (int) $today->diff(
                    $bill->getDate()->modify('+' . $bill->getDeadlineDays() . ' days')
                )->format("%r%a");
                break;
            case 1:
                $action = 'Zweite Zahlungserinnerung';
                $icon = 'comment';
                $when = (int) $today->diff(
                    $bill->getLastDun()->modify('+' . $this->firstDunDeadline . ' days')
                )->format("%r%a");
                break;
            case 2:
                $action = 'Warnung vor Abschaltung';
                $icon = 'exclamation-triangle';
                $when = (int) $today->diff(
                    $bill->getLastDun()->modify('+' . $this->secondDunDeadline . ' days')
                )->format("%r%a");
                break;
            case 3:
                $action = 'Abschaltung';
                $icon = 'power-off';
                $when = (int) $today->diff(
                    $bill->getLastDun()->modify('+' . $this->shutdownDeadline . ' days')
                )->format("%r%a");
                break;
        }

        $when++; // action take place a day after
        $when = $when === 1 ? 'morgen' : 'in ' . $when . ' Tagen';

        return '<span class="badge">' . $when . '</span>
                <i class="fa fa-fw fa-' . $icon . '"></i> ' . $action . ' ' .
                $bill->getName();
    }

    public function getName()
    {
        return 'app_extension';
    }
}
