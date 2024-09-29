<?php

namespace App\Repository;

use App\Entity\Model;
use App\Entity\Movimentacao;
use Illuminate\Support\Collection;

class MovimentacaoRepository extends Repository
{
    public function all(): Collection
    {
        return parent::all()->map(function (Movimentacao $movimentacao) {
            $movimentacao = $this->getEntityManager()
                ->find(self::getModelName(), $movimentacao->getId());
            $movimentacao->setValor($movimentacao->getItems()->count());
            $movimentacao->setItems($movimentacao->getItems());
            return $movimentacao;
        });
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
