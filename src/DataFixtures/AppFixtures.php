<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Client;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //
    }
}
