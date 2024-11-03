<?php

namespace App\Repository;

use App\Entity\Model;
use App\Entity\Movimentacao;

class MovimentacaoRepository extends Repository
{
    public function byId(int $id): ?Model
    {
        /** @var Movimentacao $m */
        $m = $this->executeQuery(
            $this->createQueryBuilder('m')
                ->leftJoin('m.items', 'i')
                ->addSelect('i')
                ->where('m.id = :id')
                ->setParameter('id', $id)
        )[0] ?? null;
        return $m;
    }
    /*public function add(Contabil $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Contabil $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }*/
//    /**
//     * @return Contabil[] Returns an array of Contabil objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
//
//    public function findOneBySomeField($value): ?Contabil
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
