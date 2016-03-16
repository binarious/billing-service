<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Bill
 *
 * @ORM\Table(name="bill")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BillRepository")
 */
class Bill
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project", inversedBy="bills")
     */
    private $project;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     * @Assert\Date()
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="deadlineDays", type="integer")
     */
    private $deadlineDays;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastDun", type="date", nullable=true)
     */
    private $lastDun;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="shutdownSince", type="date", nullable=true)
     */
    private $shutdownSince;

    /**
     * @var int
     *
     * @ORM\Column(name="receivedDuns", type="integer", nullable=true)
     */
    private $receivedDuns;

    /**
     * @var string
     *
     * @ORM\Column(name="accountBalance", type="decimal", precision=10, scale=2)
     */
    private $accountBalance = 0.0;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Bill
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set deadlineDays
     *
     * @param integer $deadlineDays
     * @return Bill
     */
    public function setDeadlineDays($deadlineDays)
    {
        $this->deadlineDays = $deadlineDays;

        return $this;
    }

    /**
     * Get deadlineDays
     *
     * @return integer
     */
    public function getDeadlineDays()
    {
        return $this->deadlineDays;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Bill
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set lastDun
     *
     * @param \DateTime $lastDun
     * @return Bill
     */
    public function setLastDun($lastDun)
    {
        $this->lastDun = $lastDun;

        return $this;
    }

    /**
     * Get lastDun
     *
     * @return \DateTime
     */
    public function getLastDun()
    {
        return $this->lastDun;
    }

    /**
     * Set receivedDuns
     *
     * @param integer $receivedDuns
     * @return Bill
     */
    public function setReceivedDuns($receivedDuns)
    {
        $this->receivedDuns = $receivedDuns;

        return $this;
    }

    /**
     * Get receivedDuns
     *
     * @return integer
     */
    public function getReceivedDuns()
    {
        return $this->receivedDuns;
    }

    /**
     * Set accountBalance
     *
     * @param string $accountBalance
     * @return Bill
     */
    public function setAccountBalance($accountBalance)
    {
        $this->accountBalance = $accountBalance;

        return $this;
    }

    /**
     * Get accountBalance
     *
     * @return string
     */
    public function getAccountBalance()
    {
        return $this->accountBalance;
    }

    /**
     * Set project
     *
     * @param \AppBundle\Entity\Project $project
     * @return Bill
     */
    public function setProject(\AppBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \AppBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Bill
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function doDun()
    {
        $this->lastDun = new \DateTime();

        if ($this->receivedDuns === null) {
            $this->receivedDuns = 1;
            return;
        }

        $this->receivedDuns++;
    }

    /**
     * Set shutdownSince
     *
     * @param \DateTime $shutdownSince
     *
     * @return Bill
     */
    public function setShutdownSince($shutdownSince)
    {
        $this->shutdownSince = $shutdownSince;

        return $this;
    }

    /**
     * Get shutdownSince
     *
     * @return \DateTime
     */
    public function getShutdownSince()
    {
        return $this->shutdownSince;
    }
}
