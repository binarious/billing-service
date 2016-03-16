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
        return $this->render('page/index.html.twig', []);
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
