<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Bill;
use AppBundle\Form\BillType;

/**
 * Bill controller.
 *
 * @Route("/bill")
 */
class BillController extends Controller
{
    /**
     * Lists all Bill entities.
     *
     * @Route("/", name="bill")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $bills = $em->getRepository('AppBundle:Bill')->findByAdmin(
            $this->getUser()->getId()
        );

        return $this->render('bill/index.html.twig', array(
            'bills' => $bills,
        ));
    }

    /**
     * Creates a new Bill entity.
     *
     * @Route("/new", name="bill_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $bill = new Bill();
        $bill->setDate(new \DateTime());
        $form = $this->createForm('AppBundle\Form\BillType', $bill, [
            'admin' => $this->getUser()->getId()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $bill->updateAmount();
            $em->persist($bill);
            $em->flush();

            return $this->redirectToRoute('bill_show', array('id' => $bill->getId()));
        }

        return $this->render('bill/new.html.twig', array(
            'bill' => $bill,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Bill entity.
     *
     * @Route("/{id}", name="bill_show")
     * @Method("GET")
     */
    public function showAction(Bill $bill)
    {
        if ($bill->getProject()->getCustomer()->getAdmin()->getId()
                !== $this->getUser()->getId()) {
            throw $this->createNotFoundException();
        }

        $deleteForm = $this->createDeleteForm($bill);
        $payForm = $this->createPayForm($bill);

        return $this->render('bill/show.html.twig', array(
            'bill' => $bill,
            'delete_form' => $deleteForm->createView(),
            'pay_form' => $payForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Bill entity.
     *
     * @Route("/{id}/edit", name="bill_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Bill $bill)
    {
        if ($bill->getProject()->getCustomer()->getAdmin()->getId()
                !== $this->getUser()->getId()) {
            throw $this->createNotFoundException();
        }

        $deleteForm = $this->createDeleteForm($bill);
        $editForm = $this->createForm('AppBundle\Form\BillType', $bill, [
            'admin' => $this->getUser()->getId(),
            'edit' => true
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $bill->updateAmount();
            $em->persist($bill);
            $em->flush();

            return $this->redirectToRoute('bill_show', array('id' => $bill->getId()));
        }

        return $this->render('bill/edit.html.twig', array(
            'bill' => $bill,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Bill entity.
     *
     * @Route("/{id}", name="bill_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Bill $bill)
    {
        if ($bill->getProject()->getCustomer()->getAdmin()->getId()
                !== $this->getUser()->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createDeleteForm($bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($bill);
            $em->flush();
        }

        return $this->redirectToRoute('bill');
    }

    /**
     * Deletes a Bill entity.
     *
     * @Route("/{id}", name="bill_pay")
     * @Method("POST")
     */
    public function payAction(Request $request, Bill $bill)
    {
        if ($bill->getProject()->getCustomer()->getAdmin()->getId()
                !== $this->getUser()->getId() ||
            $bill->getAccountBalance() === $bill->getAmount()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createPayForm($bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('bill_service')->setPayed($bill);
        }

        return $this->redirectToRoute('bill_show', ['id' => $bill->getId()]);
    }

    /**
     * Creates a form to delete a Bill entity.
     *
     * @param Bill $bill The Bill entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Bill $bill)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('bill_delete', array('id' => $bill->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a form to pay a Bill entity.
     *
     * @param Bill $bill The Bill entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createPayForm(Bill $bill)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('bill_pay', array('id' => $bill->getId())))
            ->setMethod('POST')
            ->getForm()
        ;
    }
}
