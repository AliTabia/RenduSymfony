<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;
use App\Form\AuthorType;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;




class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'list_authors')]
    public function list(AuthorRepository $authorRepository): Response
    {
        // Récupérer tous les auteurs
        $authors = $authorRepository->findAll();

        // Passer les auteurs à la vue Twig
        return $this->render('author/list.html.twig', [
            'authors' => $authors,
        ]);
    }


    #[Route('/author/add', name: 'add_author')]
    public function addAuthor(Request $request, EntityManagerInterface $entityManager): Response
    {
        $author = new Author();

        // Créer le formulaire
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister et sauvegarder l'auteur
            $entityManager->persist($author);
            $entityManager->flush();

            // Redirection après l'ajout
            return $this->redirectToRoute('list_authors');
        }

        // Afficher le formulaire
        return $this->render('author/add_author.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/author/edit/{id}', name: 'edit_author')]
    public function editAuthor(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'auteur à partir de son ID
        $author = $entityManager->getRepository(Author::class)->find($id);

        // Si l'auteur n'existe pas, renvoyer une erreur
        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé');
        }

        // Créer le formulaire et le préremplir avec les données existantes
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, mettre à jour les informations
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Mettre à jour l'auteur

            // Redirection après la mise à jour
            return $this->redirectToRoute('list_authors');
        }

        // Rendre le formulaire dans le template Twig
        return $this->render('author/edit_author.html.twig', [
            'form' => $form->createView(),
            'author' => $author, // Passer les informations de l'auteur
        ]);
    }
    #[Route('/author/delete/{id}', name: 'delete_author', methods: ['POST'])]
    public function deleteAuthor(int $id, Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Récupérer l'auteur à supprimer
        $author = $entityManager->getRepository(Author::class)->find($id);

        // Vérifier si l'auteur existe
        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé.');
        }

        // Vérifier le token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('delete' . $id, $submittedToken))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Supprimer l'auteur
        $entityManager->remove($author);
        $entityManager->flush();

        // Rediriger vers la liste des auteurs après suppression
        return $this->redirectToRoute('list_authors');
    }

    #[Route('/authors/search', name: 'search_authors')]
public function searchAuthors(Request $request, AuthorRepository $authorRepository): Response
{
    // Retrieve the filter values from the request
    $minBooks = $request->query->getInt('min_books', 0);  // Default to 0 if not set
    $maxBooks = $request->query->getInt('max_books', PHP_INT_MAX);  // Default to a large number if not set

    // Retrieve authors based on the book count range
    $authors = $authorRepository->findAuthorsByBookCountRange($minBooks, $maxBooks);

    // Pass the authors and filter values to the view
    return $this->render('author/list.html.twig', [
        'authors' => $authors,
        'min_books' => $minBooks,  // Pass min_books to the template
        'max_books' => $maxBooks,  // Pass max_books to the template
    ]);
}

#[Route('/authors/delete-no-books', name: 'delete_authors_no_books')]
public function deleteAuthorsWithNoBooks(AuthorRepository $authorRepository): Response
{
    // Call the repository method to delete authors with no books
    $authorRepository->deleteAuthorsWithNoBooks();

    // Optionally, add a flash message to notify the user
    $this->addFlash('success', 'All authors with no books have been deleted.');

    // Redirect to the authors list page
    return $this->redirectToRoute('list_authors');
}
}
