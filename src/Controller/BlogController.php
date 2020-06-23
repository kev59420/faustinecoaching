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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/Blog")
 */


class BlogController extends AbstractController {

/**
 * @Route("/{page}", name="blog_homepage", requirements={"page" = "\d+"}, defaults={"page"="1"})
 */


	public function index($page){

		if ( $page < 1)
		{
			throw $this->createNotFoundException('page'.$page.'inexistante.');
		}

		$articles = $this->getDoctrine()
						->getManager()
						->getRepository(Article::class)
						->getId(10);
		

		return $this->render("Blog/index.html.twig",[ "articles" => $articles ]);
	}

	/**
	 * @Route("/view/{id}", name="article_view", requirements={"id" = "\d+"})
	 */

	public function view(Article $article){


		if(!$article){

			throw $this->createNotFoundException("not article found for article".$article->getId());
		}


		return $this->render('Blog/Articles/view.html.twig', [ 'article' => $article ]);


	}


	/**
	 * @Route("/add", name="article_add")
	 */

	public function add(ValidatorInterface $validator,SluggerInterface $slugger, Request $request){

		$article = new Article();

		$form = $this->createForm(ArticleType::class, $article);

		$form->handleRequest($request);


		if ($form->isSubmitted() && $form->isValid()) {

			$imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('img_blog__directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $article->setImageFilename($newFilename);
            }

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

		return $this->render('Blog/Articles/add.html.twig',[ 'form' => $form->createView()]);


	}

	/**
	 * @Route("/edit/{id}", name="article_edit", requirements={"id" = "\d+"})
	 */

	public function edit($id, Request $request){

		if($request->isMethod('POST')){

			$this->addFlash('notice','Annonce bien modifiée');

			return $this->redirectToRoute('article_view', ['id' => 5]);
		}

		return $this->render('Blog/Arcticles/edit.html.twig');

		}

	/**
	 * @Route("/delete/{id}", name="article_delete", requirements={"id" = "\d+"})
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

		return $this->render('Blog/Articles/delete.html.twig');

	}

}