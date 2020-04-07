<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        //Создаём 20 рейсов
        for ($i=0;$i<20;$i++) {
            $flight = new \App\Entity\Flights();
            $flight->setSeats(150);
            $flight->setStatus('wait');
            $manager->persist($flight);
        }

        $manager->flush();
    }
}
