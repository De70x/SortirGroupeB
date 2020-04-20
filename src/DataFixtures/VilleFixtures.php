<?php
namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture {


    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        for($i=0;$i < 10;$i++) {
            $ville = new Ville();
            $ville->setNom('Ville'.$i);
            $ville->setCodePostal('cp'.$i);
            $this->addReference('ville'.$i,$ville);
            $manager->persist($ville);
         }
            $manager->flush();

    }
}