<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        for ($i = 0; $i < 5; $i++) {
            $site = new Site();
            $site->setNom('Site '.$i);
            $this->addReference('site'.$i,$site);

            $manager->persist($site);
        }
        $manager->flush();
    }

}
