<?php
namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture {


    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $i=0;
            $etat1 = new Etat();
            $etat2 = new Etat();
            $etat3 = new Etat();
            $etat4 = new Etat();
            $etat5 = new Etat();
            $etat6 = new Etat();

            $etat1->setLibelle(Etat::CREEE);
            $this->setReference('Etat1',$etat1);
            $manager->persist($etat1);

            $etat2->setLibelle(Etat::OUVERTE);
            $this->setReference('Etat2',$etat2);
            $manager->persist($etat2);

            $etat3->setLibelle(Etat::CLOTUREE);
            $this->setReference('Etat3',$etat3);
            $manager->persist($etat3);

            $etat4->setLibelle(Etat::ANNULEE);
            $this->setReference('Etat4',$etat4);
            $manager->persist($etat4);

            $etat5->setLibelle(Etat::EN_COURS);
            $this->setReference('Etat5',$etat6);
            $manager->persist($etat5);

            $etat6->setLibelle(Etat::PASSEE);
            $this->setReference('Etat6',$etat6);
            $manager->persist($etat6);

        $manager->flush();

    }
}