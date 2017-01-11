<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AppBundle\Entity\Admin;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Project;
use AppBundle\Entity\Bill;

class BillServiceTest extends KernelTestCase
{
    private $service;
    private $em;
    private $testData = [];
    private $dunTimes = [];
    private $container;

    public function setUp()
    {
        self::bootKernel();
        $container = static::$kernel->getContainer();
        $this->service = $container->get('bill_service');
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->dunTimes = [
            $container->getParameter('first_dun_deadline'),
            $container->getParameter('second_dun_deadline'),
            $container->getParameter('laststep_deadline'),
        ];

        $this->testData['admin'] = new Admin();
        $this->testData['admin']->setName('Admin'.time());
        $this->testData['admin']->setPassword('Admin');
        $this->testData['admin']->setEmail('none@localhost');
        $this->testData['customer'] = new Customer();
        $this->testData['customer']->setName('Customer');
        $this->testData['customer']->setEmail('none@localhost');
        $this->testData['customer']->setAdmin($this->testData['admin']);
        $this->testData['project'] = new Project();
        $this->testData['project']->setName('Testprojekt');
        $this->testData['project']->setCustomer($this->testData['customer']);

        $this->testData['bill'] = new Bill();
        $this->resetBill();

        foreach ($this->testData as $data) {
            $this->em->persist($data);
        }
        $this->em->flush();

        $this->container = $container;
    }

    private function resetBill($flush = false)
    {
        $this->testData['bill']->setProject($this->testData['project']);
        $this->testData['bill']->setName('001-2015');
        $this->testData['bill']->setDate(new \DateTime('-1 week'));
        $this->testData['bill']->setAccountBalance(0.0);
        $this->testData['bill']->setDeadlineDays(6);
        $this->testData['bill']->setAmount(144);
        $this->testData['bill']->setToken('empty');

        if ($flush) {
            $this->em->persist($this->testData['bill']);
            $this->em->flush();
        }
    }

    public function tearDown()
    {
        foreach ($this->testData as $data) {
            $this->em->remove($data);
        }
        $this->em->flush();
    }

    public function testFindDueWithoutReceivedDuns()
    {
        $this->resetBill(true);

        // checking day border
        $due = $this->service->getDue();
        $this->assertEquals(count($due), 1);

        $bill = $due[0];
        $bill->setDeadlineDays(7);
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);

        // checking account balance consideration
        $bill->setDeadlineDays(6);
        $bill->setAccountBalance($bill->getAmount());
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);
    }

    public function testFindDueWithOneReceivedDun()
    {
        $this->resetBill();
        $this->testData['bill']->setReceivedDuns(1);
        $this->testData['bill']->setLastDun(new \DateTime('-2 weeks -1 day'));
        $this->em->persist($this->testData['bill']);
        $this->em->flush();

        // checking day border
        $due = $this->service->getDue();
        $this->assertEquals(count($due), 1);

        $bill = $due[0];
        $bill->setLastDun(new \DateTime('-2 weeks'));
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);

        // checking account balance consideration
        $bill->setLastDun(new \DateTime('-2 weeks -1 day'));
        $bill->setAccountBalance($bill->getAmount());
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);
    }

    public function testFindDueWithTwoReceivedDun()
    {
        $this->resetBill();
        $this->testData['bill']->setReceivedDuns(2);
        $this->testData['bill']->setLastDun(new \DateTime('-2 weeks -1 day'));
        $this->em->persist($this->testData['bill']);
        $this->em->flush();

        // checking day border
        $due = $this->service->getDue();
        $this->assertEquals(count($due), 1);

        $bill = $due[0];
        $bill->setLastDun(new \DateTime('-2 weeks'));
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);

        // checking account balance consideration
        $bill->setLastDun(new \DateTime('-2 weeks -1 day'));
        $bill->setAccountBalance($bill->getAmount());
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);
    }

    public function testFindDueWithThreeReceivedDun()
    {
        $this->resetBill();
        $this->testData['bill']->setReceivedDuns(3);
        $this->testData['bill']->setLastDun(new \DateTime('-6 days'));
        $this->em->persist($this->testData['bill']);
        $this->em->flush();

        // checking day border
        $due = $this->service->getDue();
        $this->assertEquals(count($due), 1);

        $bill = $due[0];
        $bill->setLastDun(new \DateTime('-5 days'));
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);

        // checking account balance consideration
        $bill->setLastDun(new \DateTime('-6 day'));
        $bill->setAccountBalance($bill->getAmount());
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);

        // checking shutdown information consideration
        $bill->setLastDun(new \DateTime('-6 day'));
        $bill->setAccountBalance(0);
        $bill->setShutdownSince(new \DateTime());
        $this->em->persist($bill);
        $this->em->flush();

        $due = $this->service->getDue();
        $this->assertEquals(count($due), 0);
    }

    public function testDun()
    {
        $this->resetBill();
        $today = new \DateTime();

        // first dun
        $this->service->dun($this->testData['bill']);
        $this->assertEquals($this->testData['bill']->getReceivedDuns(), 1);
        $this->assertEquals(
            $this->testData['bill']->getLastDun()->format('d.m.Y'),
            $today->format('d.m.Y')
        );
        $this->assertNull($this->testData['bill']->getShutdownSince());

        // second dun
        $this->service->dun($this->testData['bill']);
        $this->assertEquals($this->testData['bill']->getReceivedDuns(), 2);
        $this->assertEquals(
            $this->testData['bill']->getLastDun()->format('d.m.Y'),
            $today->format('d.m.Y')
        );
        $this->assertNull($this->testData['bill']->getShutdownSince());

        // third dun
        $this->service->dun($this->testData['bill']);
        $this->assertEquals($this->testData['bill']->getReceivedDuns(), 3);
        $this->assertEquals(
            $this->testData['bill']->getLastDun()->format('d.m.Y'),
            $today->format('d.m.Y')
        );
        $this->assertNull($this->testData['bill']->getShutdownSince());

        // no automatic shutdown dun
        $this->service->dun($this->testData['bill']);
        $this->assertEquals($this->testData['bill']->getReceivedDuns(), 3);
        $this->assertEquals(
            $this->testData['bill']->getLastDun()->format('d.m.Y'),
            $today->format('d.m.Y')
        );
        $this->assertNull(
            $this->testData['bill']->getShutdownSince()
        );
    }
}
