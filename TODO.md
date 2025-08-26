`ctrl` + `shift` + `v` pour afficher en preview les fichiers en .md
## MoSCoW
M - MUST-HAVE /
S - SHOULD-HAVE /
C - COULD-HAVE /
W - WON'T-HAVE

## TODO

- M : page d'accueil

- ~~ M : image carré sur les produits pour cohesion~~
    - bloc meme taille

- M : footer
    - CGU -> creation d'une page
    - mentions legales -> creation d'une page
    - contactez nous -> creation d'une page

- M : gestion de stock
    - decrementer le stock confirm commande 
        - ~~ quand on paye sur stripe ~~
        - quand on appui sur pay on delivery ou quand on met en mark as delivered pour enlever du stock ?
    - ~~ empecher ajout de produit superieur au stock actuel ~~
    - empecher qu'on puisse faire plus de commande avec l'inspecteur

- S : ~~ description sur tout les produits ~~

- S : meta description 
    - `<head>` `<title> Titre </title>` `<meta name="description" content="La meta description de la page."/>` `</head>`

- S : categories navbar
    - mettre un lien vers une page books où il y a tous les livres sans sous categories
    - pareil pour goodies

- S : quand on met un produit dans le cart
    - soit on est pas redirigé soit laissé comme ca mais mettre la possibilité de revenir sur l'ancienne page ?

- S : Navbar
    - ~~ mettre dans la nav bar de la page app_cart de son controller cartcontroller les categories pour que ca s'affiche~~ 
    - ~~ pareil pour app_home_product_show dans homecontroller~~ 
    - ~~ pareil pour app_order dans ordercontroller~~ 
    - ~~ mettre un logo cart dans la navbar pour y etre redirigé sur app_cart ~~
    - booksite redirection sur a propos -> creation d'un page
    - admin editor pour modifier/ajouter les villes 
    

- C : nom du produit url slug

- C : suppression d'utilisateur 
    <!-- - ca supprime pas ses factures et ca garde les relations -->
    - soit ca supprime toutes ses données sauf les factures
    - soit ca supprime son identité mais pas ses relations / factures, ca devient juste une coquille vide

- C : section review sur les produits

- C : dependance CDN Bootstrap au cas où y a un bug chez eux on a toujours notre css du site qui fonctionne

- C : dire produit pas trouvé dans search barre

- C : nom des produit en slice aussi comme les description ? sinon c'est pas beau ca fait des cadre plus grand