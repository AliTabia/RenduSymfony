<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

     public function findAuthorsByBookCountRange(int $min, int $max): array
    {
        $entityManager = $this->getEntityManager();

        // DQL Query
        $dql = 'SELECT a 
                FROM App\Entity\Author a
                LEFT JOIN a.books b
                GROUP BY a.id
                HAVING COUNT(b.id) BETWEEN :min AND :max';

        $query = $entityManager->createQuery($dql)
            ->setParameter('min', $min)
            ->setParameter('max', $max);

        return $query->getResult();
    }

    public function deleteAuthorsWithNoBooks(): void
{
    $entityManager = $this->getEntityManager();

    // DQL Query to delete authors with no books
    $dql = 'DELETE FROM App\Entity\Author a
             WHERE (SELECT COUNT(b.id) FROM App\Entity\Book b WHERE b.author = a) = 0';

    $query = $entityManager->createQuery($dql);
    $query->execute();
}



}
