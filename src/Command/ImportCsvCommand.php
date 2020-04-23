<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportCsvCommand extends Command
{
    private $container;
    private $encoder;
    private $siteRepository;

    public function __construct(UserPasswordEncoderInterface $encoder, ContainerInterface $container, SiteRepository $siteRepository)
    {
        parent::__construct();
        $this->encoder = $encoder;
        $this->container = $container;
        $this->siteRepository = $siteRepository;
    }

    //Nom et description de la commande
    protected static $defaultName = 'app:import-csv';

    protected function configure()
    {
        $this->setDescription('Import fichier CSV');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //Affichage du lancement du script
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        //Import du csv
        $this->import($input, $output);

        //Affichage de la fin du script
        $now = new \DateTime();
        $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');
        return 1;
    }

    protected function import(InputInterface $input, OutputInterface $output)
    {

        $data = $this->get($input, $output);
        $em = $this->container->get('doctrine')->getManager();

        $batchSize = 1;
        $i = 1;

        if (is_array($data) || is_object($data)) {
            foreach ($data as $row) {
                $user = $em->getRepository(User::class)
                    ->findOneByMail($row['mail']);
                //Si le user n'existe pas
                if (!is_object($user)) {
                    $user = new User();
                }

                $user->setNom($row['nom']);
                $user->setPrenom($row['prenom']);
                $user->setUsername($row['username']);
                $user->setPassword($row['password']);
                $user->setTelephone($row['telephone']);
                $user->setMail($row['mail']);
                $user->setAdministrateur($row['administrateur']);
                $user->setActif($row['actif']);
                $site = $this->siteRepository->find($row['site']);
                $user->setSite($site);
                $user->setRoles($row['roles']);

                $hashed = $this->encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($hashed);
                $em->persist($user);

                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear();

                    $now = new \DateTime();
                    $output->writeln(' of users imported ... | ' . $now->format('d-m-Y G:i:s'));
                }
                $i++;
            }
        }

        $em->flush();
        $em->clear();
    }

    protected function get(InputInterface $input, OutputInterface $output)
    {
        $fileName = $this->container->getParameter('fichier_import');

        $converter = $this->container->get('import.csvtoarray');
        $data = $converter->convert($fileName, ';');

        return $data;
    }

}
