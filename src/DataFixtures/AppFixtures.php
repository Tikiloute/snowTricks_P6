<?php

namespace App\DataFixtures;

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
        $u1 = new User();
        $plaintextPassword = 'tiki';

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->hasher->hashPassword(
            $u1,
            $plaintextPassword
        );
        $u1->setEmail('bruno.etcheverry@hotmail.fr')
            ->setRoles(array('ROLE_ADMIN'))
            ->setPassword($hashedPassword);

        $manager->persist($u1);

        $manager->flush();
    }
}
