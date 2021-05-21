<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Article;
use \DateTime;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

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
        $manager->flush();
    }
}
