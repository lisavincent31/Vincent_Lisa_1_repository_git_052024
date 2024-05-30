<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

use App\Entity\Client;

class ClientFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un administrateur
        $admin = new Client();
        $admin->setEmail("admin@bilemoapi.com");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, "s3cr3t"));
        $manager->persist($admin);

        // Création d'un client
        $client = new Client();
        $client->setEmail("client@bilemoapi.com");
        $client->setRoles(["ROLE_USER"]);
        $client->setPassword($this->passwordHasher->hashPassword($client, "password"));
        $manager->persist($client);

        

        $manager->flush();
    }
}
