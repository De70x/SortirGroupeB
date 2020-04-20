<?php
namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface {


    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return array(
            VilleFixtures::class
        );
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        for ($i=0;$i < 10 ; $i++) {
            $lieu = new Lieu();
            $lieu->setNom('lieu'.$i);
            $lieu->setRue('rue'.$i);
            $lieu->setLatitude(rand(0,25));
            $lieu->setLongitude(rand(0,25));
            $lieu->setVille($this->getReference("ville".rand(1,9)));




            $manager->persist($lieu);
        }
        $manager->flush();
    }
}