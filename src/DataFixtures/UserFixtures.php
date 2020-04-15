<?php


namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setNom("Michel".$i);
            $user->setPrenom("Michel".$i);
            $user->setUsername("peusdo".$i);
            $user->setPassword("mdp");
            $user->setTelephone("0642424242");
            $user->setMail("mail".$i."@michel.com");
            $user->setAdministrateur(false);
            $this->addReference('user'.$i,$user);


            $manager->persist($user);
        }
        $manager->flush();
    }



}
