<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AuthorRepository;


class BookController extends AbstractController
{
    #[Route('/books', name: 'list_books')]
    public function listBooks(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll();
        $publishedCount = $bookRepository->count(['published' => true]);
        $unpublishedCount = $bookRepository->count(['published' => false]);

        return $this->render('book/list.html.twig', [
            'books' => $books,
            'publishedCount' => $publishedCount,
            'unpublishedCount' => $unpublishedCount,
        ]);
    }


    #[Route('/book/add', name: 'add_book')]
    public function addBook(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $author = $book->getAuthor(); // Assuming you have this method
        if ($author) {
            // Call getNbBooks() here
            $author->setNbBooks($author->getNbBooks() + 1);
        }
        $entityManager->persist($book);
        $entityManager->flush();

        return $this->redirectToRoute('list_books'); // Adjust as necessary
    }

    return $this->render('book/add_book.html.twig', [
        'form' => $form->createView(),
    ]);
    }

    #[Route('/book/edit/{id}', name: 'edit_book')]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('list_books');
        }

        return $this->render('book/edit_book.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book/delete/{id}', name: 'delete_book', methods: ['POST'])]
    public function delete(Book $book, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('list_books');
    }

    #[Route('/books/published', name: 'list_published_books')]
    public function listPublishedBooks(BookRepository $bookRepository): Response
    {
        // Fetch only published books
        $books = $bookRepository->findPublishedBooks();

        return $this->render('book/published_books.html.twig', [
            'books' => $books, // Pass the published books to the template
        ]);
    }

    #[Route('/books/{id}', name: 'show_book')]
    public function show(Book $book): Response
    {
        // The $book parameter is automatically fetched based on the ID in the route
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }
}
