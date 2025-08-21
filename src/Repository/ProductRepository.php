<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // public function findByIdUp($value): array
    // {
    //     return $this->createQueryBuilder('p') // retourner la requete
    //        ->andWhere('p.id > :val') // ajoute des critères val = $value
    //        ->setParameter('val', $value) // on set les parametres
    //        ->orderBy('p.id', 'ASC') // on definit les criteres
    //        ->setMaxResults(10) // definit le nombre de resultat
    //        ->getQuery()
    //        ->getResult()
    //     ;
    // }

    public function SearchEngine(string $query) 
    {
        return $this->createQueryBuilder('p')  // crée un objet de requete qui permet de construire la requête de recherche
           ->Where('p.name LIKE :query') // recherche les éléments dont le nom contient la requete de recherche
           ->orWhere('p.description LIKE :query') // OU recherche les elements dont la description contient la requete de recherche
           ->setParameter('query', '%' . $query . '%') // defini la valeur de la variable "query" pour la requete
           ->getQuery() // execute la requete et recupere les resultats
           ->getResult()
        ;
    }



    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p') 
    //            ->andWhere('p.exampleField = :val') 
    //            ->setParameter('val', $value) 
    //            ->orderBy('p.id', 'ASC') 
    //            ->setMaxResults(10) 
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
