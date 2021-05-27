<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Article;
use \DateTime;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;
use App\Entity\Comment;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {

        // Instanciation du fichier qui nous servira à créer des fausses données aléatoire
        $faker = Faker\Factory::create('fr_FR');

        // Création du compte admin du site
        $admin = new User();

        //Hydratation du compte admin
        $admin
            ->setEmail('admin@mail.com')
            ->setRegistrationDate($faker->dateTimeBetween('-1 year', 'now'))
            ->setPseudonym('Batman')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword(
                $this->encoder->encodePassword($admin, 'Azerty12!')
            )
        ;

        $manager->persist($admin);

        // Création de 50 comptes utilisateurs
        for ($i = 0; $i < 50; $i++){

            // Création d'un nouveau compte
            $user = new User();

            // Hydratation du compte avec des données aléatoires
            $user
                ->setEmail($faker->email)
                ->setRegistrationDate($faker->dateTimeBetween('-1 year', 'now'))
                ->setPseudonym($faker->userName)
                ->setPassword($this->encoder->encodePassword($user, 'Azerty12!'))
            ;

            // Persistance de l'utilisateur
            $manager->persist($user);

            // Stockage de tous les utilisateurs dans un array php pour créer desc ommentaires après
            $users[] = $user;
        }

        // Création de 200 articles
        for ($i = 0; $i < 200; $i++){

            // Création d'un nouvel article
            $article = new Article();

            // Hydratation de l'article
            $article
                ->setPublicationDate($faker->dateTimeBetween($admin->getRegistrationDate(), 'now'))
                ->setAuthor($admin)
                ->setTitle($faker->sentence(10))
                ->setContent($faker->paragraph(15))
            ;

            // Persistance de l'article
            $manager->persist($article);

            // Création entre 0 et 10 comentaires aléatoires par article
            $rand = rand(0, 10);
            for ($j = 0; $j < $rand; $j++){
                // Création d'un nouveau commentaire
                $comment = new Comment();

                $comment
                    ->setArticle($article)
                    ->setPublicationDate($faker->dateTimeBetween('-1 year', 'now'))
                    ->setContent($faker->paragraph(5))
                    ->setAuthor($faker->randomElement($users))
                ;

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
