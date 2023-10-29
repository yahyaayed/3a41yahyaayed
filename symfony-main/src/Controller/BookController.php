<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use PHPUnit\Framework\Constraint\Count;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/AfficheBook', name: 'app_AfficheBook')]
    public function Affiche(BookRepository $repository)
    {
  
        $publishedBooks = $this->getDoctrine()->getRepository(Book::class)->findBy(['published' => true]);
        
        $numPublishedBooks = count($publishedBooks);
        $numUnPublishedBooks = count($this->getDoctrine()->getRepository(Book::class)->findBy(['published' => false]));

        if ($numPublishedBooks > 0) {
            return $this->render('book/Affiche.html.twig', ['publishedBooks' => $publishedBooks, 'numPublishedBooks' => $numPublishedBooks, 'numUnPublishedBooks' => $numUnPublishedBooks]);

        } else {
           
            return $this->render('book/no_books_found.html.twig');
        }

    }

    #[Route('/AddBook', name: 'app_AddBook')]
    public function Add(Request $request)
    {
        $book = new Book();
        $form = $this->CreateForm(BookType::class, $book);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //  $book->setPublished(true);
            $author = $book->getAuthor();
            if ($author instanceof Author) {
                $author->setNbBooks($author->getNbBooks() + 1);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('app_AfficheBook');
        }
        return $this->render('book/Add.html.twig', ['f' => $form->createView()]);

    }


    #[Route('/editbook/{ref}', name: 'app_editBook')]
    public function edit(BookRepository $repository, $ref, Request $request)
    {
        $author = $repository->find($ref);
        $form = $this->createForm(BookType::class, $author);
        $form->add('Edit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute("app_AfficheBook");
        }

        return $this->render('book/edit.html.twig', [
            'f' => $form->createView(),
        ]);
    }


    #[Route('/deletebook/{ref}', name: 'app_deleteBook')]
    public function delete($ref, BookRepository $repository)
    {
        $book = $repository->find($ref);


        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();


        return $this->redirectToRoute('app_AfficheBook');
    }
    #[Route('/ShowBook/{ref}', name: 'app_detailBook')]

    public function showBook($ref, BookRepository $repository)
    {
        $book = $repository->find($ref);
        if (!$book) {
            return $this->redirectToRoute('app_AfficheBook');
        }

        return $this->render('book/show.html.twig', ['b' => $book]);

}
}
