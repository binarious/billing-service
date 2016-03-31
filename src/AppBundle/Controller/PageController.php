<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
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
     * Route for changing the admin password
     *
     * @Route("/change-password", name="change_password")
     * @Method({"GET", "POST"})
     */
    public function changePasswordAction(Request $request)
    {
        $form = $this->createForm('AppBundle\Form\ChangePasswordType');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $emAdmin = $em->getRepository('AppBundle:Admin')->find(
                $this->getUser()->getId()
            );

            if ($this->get('security.password_encoder')
                ->isPasswordValid($emAdmin, $form->getData()['currentPassword'])) {
                $hash = $this->get('security.password_encoder')
                             ->encodePassword($emAdmin, $form->getData()['newPassword']);

                $emAdmin->setPassword($hash);

                $em->persist($emAdmin);
                $em->flush();

                return $this->redirectToRoute('homepage');
            }

            $form->get('currentPassword')->addError(
                new FormError('Das aktuelle Passwort ist ungültig.')
            );
        }

        return $this->render('page/changePassword.html.twig', array(
            'form' => $form->createView()
        ));
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
