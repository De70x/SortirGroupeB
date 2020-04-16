<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        
        for ($i = 0; $i < 25; $i++) {
            $sortie = new Sortie();
            $sortie->setNom("nom" . $i);
            $sortie->setDateHeureDebut(new \DateTime('now'));
            $sortie->setDuree(rand(1, 100));
            $sortie->setDateLimiteInscription((new \DateTime()));
            $sortie->setNbInscriptionsMax(rand(10, 100));
            $sortie->setOrganisateur($this->getReference("user1"));


            $manager->persist($sortie);
        }
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return array(
        UserFixtures::class
    );


    }
}
