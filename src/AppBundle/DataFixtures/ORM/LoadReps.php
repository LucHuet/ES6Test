<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;
use AppBundle\Entity\RepLog;

class LoadReps implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $items = array_flip(RepLog::getThingsYouCanDrinkChoices());

        $names = array(
            array('Jon', 'Snow'),
            array('Deanerys', 'Thargaryen'),
            array('Arya', 'Stark'),
        );

        foreach ($names as $name) {
            $firstName = $name[0];
            $lastName = $name[1];

            $user = new User();
            $username = sprintf('%s_%s', $firstName, $lastName);
            $username = strtolower($username);
            $username = str_replace(' ', '', $username);
            $username = str_replace('.', '', $username);
            $user->setUsername($username);
            $user->setEmail($user->getUsername().'@example.com');
            $user->setPlainPassword('pumpup');
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEnabled(true);
            $manager->persist($user);

            for ($j = 0; $j < rand(1, 5); $j++) {
                $repLog = new RepLog();
                $repLog->setUser($user);
                $repLog->setReps(rand(1, 30));
                $repLog->setItem(array_rand($items));
                $manager->persist($repLog);
            }
        }

        $manager->flush();
    }
}
