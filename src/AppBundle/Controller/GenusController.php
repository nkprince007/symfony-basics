<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Genus;
use AppBundle\Entity\GenusNote;
use AppBundle\Service\MarkdownTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GenusController extends Controller {

    /**
     * @Route("/genus/new")
     */
    public function newAction() {
        $genus = new Genus();
        $genus->setName('Octopus'.rand(1,100));
        $genus->setSubFamily('Octopodinae');
        $genus->setSpeciesCount(rand(100,99999));

        $note = new GenusNote();
        $note->setUserAvatarFileName('ryan.jpeg');
        $note->setUsername('AquaWeaver');
        $note->setNote('I counted 8 legs... as they wrapped around me.');
        $note->setCreatedAt(new \DateTime('-1 month'));
        $note->setGenus($genus);

        $em = $this->getDoctrine()->getManager();
        $em->persist($genus);
        $em->persist($note);
        $em->flush();

        return new Response('<html><body>Genus created!</body></html>');
    }

    /**
     * @Route("/genus")
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $genuses = $em->getRepository('AppBundle:Genus')->findAllPublishedOrderedByRecentlyActive();

        return $this->render('genus/list.html.twig',[
            'genuses' => $genuses
        ]);
    }

    /**
     * @Route("/genus/{genusname}", name="genus_show")
     */
    public function showAction($genusname) {

        $em = $this->getDoctrine()->getManager();
        $genus = $em->getRepository('AppBundle:Genus')->findOneBy(['name' => $genusname]);

        if(!$genus) {
            throw $this->createNotFoundException('genus not found');
        }

        $markdownParser = $this->get('app.markdown_transformer');
        $funFact = $markdownParser->parse($genus->getFunFact());

        $recentNotes = $em->getRepository('AppBundle:GenusNote')->findAllRecentNotesForGenius($genus);

        $this->get('logger')->info('Showing genus: '.$genusname);

        return $this->render('genus/show.html.twig',array(
            'genus' => $genus,
            'recentNoteCount' => count($recentNotes),
            'funFact' => $funFact
        ));
    }

    /**
     * @Route("/genus/{name}/notes", name="genus_show_notes")
     * @Method("GET")
     */
    public function getNotesAction(Genus $genus) {
        $notes = [];

        foreach ($genus->getNotes() as $note) {
            $notes[] = [
                'id' => $note->getId(),
                'username' => $note->getUsername(),
                'avatarUri' => '/images/'.$note->getUserAvatarFilename(),
                'note' => $note->getNote(),
                'date' => $note->getCreatedAt()->format('M d, Y')
            ];
        }

        $data = [
            "notes" => $notes
        ];

        return new JsonResponse($data);
    }

}

?>