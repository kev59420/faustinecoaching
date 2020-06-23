<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Envoi;
use App\Form\Type\EnvoiType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;




/**
 * @Route("/")
 */


class HomeController extends AbstractController {

/**
 * @Route("/", name="home_homepage")
 */


	public function index(){


		$articles = $this->getDoctrine()
						->getManager()
						->getRepository(Article::class)
						->getId(2);
		

		return $this->render("Home/index.html.twig",[ "articles" => $articles ]);
	}

 /**
 * @Route("/Contact", name="contact")
 */

	public function contact(Request $request){

		$message= new Envoi();

		$form = $this->createForm(EnvoiType::class, $message);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

            $article = $form->getData();

	        $entityManager = $this->getDoctrine()->getManager();
	        $entityManager->persist($message);
	        $entityManager->flush();

	        $content = $this->renderView('Contact/email.txt.twig', [
	        	'message' => $message ]);

	        mail('faustine.coaching59@gmail.com',$message->getNom()."- Demande de suivi personnel ".$message->getProgramme(),$content);

	        return $this->redirectToRoute('home_homepage');
	    }

		return $this->render("Contact/contact.html.twig",[ "form" => $form->createView() ]);
	}
}

