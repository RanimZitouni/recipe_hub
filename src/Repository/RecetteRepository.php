<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    /**
     * Find recipes by optional filters: title (partial), category, difficulty, tag.
     *
     * @param string|null $titre
     * @param \App\Entity\CategorieRecette|null $cat
     * @param string|null $diff
     * @param \App\Entity\TagRecette|null $tag
     *
     * @return Recette[]
     */
    public function findByFilters(?string $titre, ?\App\Entity\CategorieRecette $cat, ?string $diff, ?\App\Entity\TagRecette $tag): array
    {
        $qb = $this->createQueryBuilder('r');

        if ($titre) {
            $qb->andWhere('r.titre LIKE :titre')
               ->setParameter('titre', '%' . $titre . '%');
        }
        if ($cat) {
            $qb->andWhere('r.categorie = :cat')
               ->setParameter('cat', $cat);
        }
        if ($diff) {
            $qb->andWhere('r.difficulte = :diff')
               ->setParameter('diff', $diff);
        }
        if ($tag) {
            $qb->innerJoin('r.tags', 't')
               ->andWhere('t = :tag')
               ->setParameter('tag', $tag);
        }

        return $qb->orderBy('r.dateCreation', 'DESC')
                  ->getQuery()->getResult();
    }

    /**
     * Return last published recipes.
     *
     * @return Recette[]
     */
    public function findLastPublished(int $limit = 3): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.publiee = true')
            ->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Recette[] Returns an array of Recette objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recette
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
