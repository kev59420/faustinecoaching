<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use App\Form\Type\ArticleType;

/**
 * @Route("/Recette")
 */


class RecetteController extends AbstractController {

/**
 * @Route("/{page}", name="recette_homepage", requirements={"page" = "\d+"}, defaults={"page"="1"})
 */


	public function index($page){

		if ( $page < 1)
		{
			throw $this->createNotFoundException('page'.$page.'inexistante.');
		}

		$articles = $this->getDoctrine()
						->getManager()
						->getRepository(Article::class)
						->getId(2);
		

		return $this->render("recette/index.html.twig",[ "articles" => $articles ]);
	}

	/**
	 * @Route("/view/{id}", name="recette_view", requirements={"id" = "\d+"})
	 */

	public function view(Article $article){


		if(!$article){

			throw $this->createNotFoundException("not article found for article".$article->getId());
		}


		return $this->render('recette/Articles/view.html.twig', [ 'article' => $article ]);


	}


	/**
	 * @Route("/add", name="recette_add")
	 */

	public function add(ValidatorInterface $validator, Request $request){

		$article = new Article();

		$form = $this->createForm(ArticleType::class, $article);

		$form->handleRequest($request);


		if ($form->isSubmitted() && $form->isValid()) {

	        $article = $form->getData();

	     	$errors = $validator->validate($article);

	   		 if (count($errors) > 0) {

		        $errorsString = (string) $errors;

		        return new Response($errorsString);
	   		 }

	        $entityManager = $this->getDoctrine()->getManager();
	        $entityManager->persist($article);
	        $entityManager->flush();

        	return $this->redirectToRoute('article_view',[ 'id' => $article->getId() ]);
    	}

		return $this->render('recette/Articles/add.html.twig',[ 'form' => $form->createView()]);


	}

	/**
	 * @Route("/edit/{id}", name="recette_edit", requirements={"id" = "\d+"})
	 */

	public function edit($id, Request $request){

		if($request->isMethod('POST')){

			$this->addFlash('notice','Annonce bien modifiée');

			return $this->redirectToRoute('article_view', ['id' => 5]);
		}

		return $this->render('recette/Arcticles/edit.html.twig');

		}

	/**
	 * @Route("/delete/{id}", name="recette_delete", requirements={"id" = "\d+"})
	 */

	public function delete(Article $article){
		/*
		$article = $this->getDoctrine()
					->getRepository(Article::class)
					->find($id);
		*/
		$entitymanager = $this->getDoctrine()->getManager();
		$entitymanager->remove($article);
		$entitymanager->flush();

		return $this->render('recette/Articles/delete.html.twig');

	}

}