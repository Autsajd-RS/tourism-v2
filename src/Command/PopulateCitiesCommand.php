<?php

namespace App\Command;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateCitiesCommand extends Command
{
    protected static $defaultName = "app:populate-cities";
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Fetching cities...");
        $json = file_get_contents(__DIR__ . '/DummyData/rs.json');
        $cities = json_decode($json, true);
        $output->writeln("Creating cities...");
        foreach ($cities as $city) {
            $newCity = new City();
            $newCity
                ->setName($city['city'])
                ->setLat($city['lat'])
                ->setLng($city['lng']);

            $this->entityManager->persist($newCity);
        }
        $output->writeln("Inserting cities to DB...");
        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}