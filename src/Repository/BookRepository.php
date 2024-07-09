<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findPaginatedBooks(int $page = 1, int $limit = 10, array $filters = null): Paginator
    {
        $qb = $this->createQueryBuilder('b');

        if($filters) {
            foreach ($filters as $key => $value) {
                switch ($key) {
                    case 'title':
                        $qb->andWhere('b.title LIKE :title');
                        $qb->setParameter('title', '%' . $value . '%');
                        break;
                    case 'authorLastName':
                        $qb->leftJoin('b.authors', 'a');
                        $qb->andWhere('a.last_name LIKE :authorLastName');
                        $qb->setParameter('authorLastName', '%' . $value . '%');
                        break;
                }
            }
        }

        $qb->orderBy('b.id', 'ASC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, true);
    }

}
