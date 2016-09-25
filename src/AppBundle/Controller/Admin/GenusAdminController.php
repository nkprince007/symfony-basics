<?php
/**
 * Created by PhpStorm.
 * User: nkprince007
 * Date: 25/09/16
 * Time: 3:19 PM
 */

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\GenusFormType;
use Symfony\Component\HttpFoundation\Request;

class GenusAdminController extends Controller{

    /**
     * @Route("/admin/genus",name="admin_genus_index")
     */
    public function indexAction() {
        $genuses = $this->getDoctrine()
            ->getRepository('AppBundle:Genus')
            ->findAll();
        return $this->render('admin/genus/list.html.twig', array(
            'genuses' => $genuses
        ));
    }

    /**
     * @Route("/admin/genus/new", name="admin_genus_new")
     */
    public function newAction(Request $request) {

        $form = $this->createForm(GenusFormType::class);

        //POST DATA HANDLING
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            dump($form->getData()); die;
        }

        return $this->render('admin/genus/new.html.twig', [
            'genusForm' => $form->createView()
        ]);
    }

}