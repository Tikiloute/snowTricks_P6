<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        
        // $product = new Product();
        // $manager->persist($product);
        // $u1 = new User();
        // $plaintextPassword = 'tiki';

        // // hash the password (based on the security.yaml config for the $user class)
        // $hashedPassword = $this->hasher->hashPassword(
        //     $u1,
        //     $plaintextPassword
        // );
        // $u1->setEmail('bruno.etcheverry@celemma.eu')
        //     ->setRoles(array('ROLE_ADMIN'))
        //     ->setName('Bruno')
        //     ->setPassword($hashedPassword);

        $c1 = new Category();
        $c1->setName("Les grabs");
        $manager->persist($c1);

        $c2 = new Category();
        $c2->setName("Les rotations");
        $manager->persist($c2);

        $c3 = new Category();
        $c3->setName("Les flips");
        $manager->persist($c3);

        $c4 = new Category();
        $c4->setName("Les rotations désaxées");
        $manager->persist($c4);

        $c5 = new Category();
        $c5->setName("Les slides");
        $manager->persist($c5);

        $c6 = new Category();
        $c6->setName("Les one foot tricks");
        $manager->persist($c6);

        $c7 = new Category();
        $c7->setName("Old school");
        $manager->persist($c7);

        $manager->flush();
    }
}
