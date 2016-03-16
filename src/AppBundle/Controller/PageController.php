<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Page controller.
 */
class PageController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $allBills = $em->getRepository('AppBundle:Bill')->findByAdmin(
            $this->getUser()->getId()
        );
        $unpayed = $em->getRepository('AppBundle:Bill')->findUnpayedByAdmin(
            $this->getUser()->getId()
        );
        $offline = $em->getRepository('AppBundle:Bill')->findOfflineByAdmin(
            $this->getUser()->getId()
        );

        $cntAll = count($allBills);
        $cntOff = count($offline);
        $sumAmt = 0;
        $sumDun = 0;

        foreach ($allBills as $bill) {
            $sumDun += $bill->getReceivedDuns();
            $sumAmt += $bill->getAmount();
        }

        return $this->render('page/index.html.twig', [
            'allBills' => $allBills,
            'unpayed' => $unpayed,
            'offline' => $offline,
            'cntAll' => $cntAll,
            'cntOff' => $cntOff,
            'sumAmt' => $sumAmt,
            'sumDun' => $sumDun
        ]);
    }

    /**
     * @Route("/test")
     * @Method("GET")
     */
    public function testAction()
    {
        $bs = $this->get('bill_service');
        $due = $bs->getDue();

        foreach ($due as $d) {
            $bs->dun($d);
        }

        return $this->render('page/index.html.twig', []);
    }
}
