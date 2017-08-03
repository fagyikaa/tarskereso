<?php

namespace Core\CommonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAddressData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface {

    private $addressesSQL;
    private $container;

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
        $this->addressesSQL = __DIR__ . '/../AddressesSQL/addresses.sql';
    }

    public function load(ObjectManager $manager) {
        $sql = file_get_contents($this->addressesSQL);
        $manager->getConnection()->exec($sql);

        $manager->flush();
    }

    public function getOrder() {
        return 1;
    }

}
