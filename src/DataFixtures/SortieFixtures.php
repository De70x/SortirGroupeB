<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $dateSortie = [
            '0' => new \DateTime('now'),
            '1' => new \DateTime('2020-04-01 10:00:00'),
            '2' => new \DateTime('2020-12-12 10:00:00'),

            ];

        for ($i = 0; $i < 25; $i++) {
            $sortie = new Sortie();
            $sortie->setNom("nom" . $i);
            $sortie->setDateHeureDebut($dateSortie[rand(0,2)]);
            $sortie->setDuree(rand(1, 100));
            $dateTemp = clone $sortie->getDateHeureDebut();
            $dateTemp->sub(new DateInterval('P10D'));
            $sortie->setDateLimiteInscription($dateTemp);
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
