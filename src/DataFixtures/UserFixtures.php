<?php


namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $idSite = rand(0, 4);
            $user->setNom("Michel" . $i);
            $user->setPrenom("Michel" . $i);
            $user->setUsername("peusdo" . $i);
            $hashed = $this->encoder->encodePassword($user, 'mdp');
            $user->setSite($this->getReference('site' . $idSite));
            $user->setPassword($hashed);
            $user->setTelephone("0642424242");
            $user->setMail("mail" . $i . "@michel.com");
            if ($i = 0) {
                $user->setAdministrateur(true);
            } else {

            $user->setAdministrateur(false);
            }
            $this->addReference('user'.$i,$user);


            $manager->persist($user);
        }
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return array(
            SiteFixtures::class,
        );
    }

}
