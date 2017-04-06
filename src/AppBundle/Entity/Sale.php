<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Sale
 *
 * @ORM\Table(name="sale")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SaleRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Sale
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="reward", type="string", length=255)
     */
    private $reward;

    /**
     * @var int
     *
     * @ORM\Column(name="seal_limit", type="integer")
     */
    private $sealLimit = 10;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime")
     */
    private $endDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var Restaurant
     *
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="sales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     * })
     */
    private $restaurant;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Seal", cascade={"persist", "remove"}, mappedBy="sale")
     */
    private $seals;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UserPoints", mappedBy="sale")
     */
    private $points;

    /**
     * Sale constructor.
     */
    public function __construct()
    {
        $this->seals = new ArrayCollection();
        $this->points = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Sale
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

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Sale
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * @param string $reward
     * @return Sale
     */
    public function setReward($reward)
    {
        $this->reward = $reward;
        return $this;
    }

    /**
     * Set sealLimit
     *
     * @param integer $sealLimit
     *
     * @return Sale
     */
    public function setSealLimit($sealLimit)
    {
        $this->sealLimit = $sealLimit;

        return $this;
    }

    /**
     * Get sealLimit
     *
     * @return int
     */
    public function getSealLimit()
    {
        return $this->sealLimit;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return Sale
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return Sale
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return Sale
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Sale
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return Sale
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Restaurant
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * @param Restaurant $restaurant
     * @return Sale
     */
    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSeals()
    {
        return $this->seals;
    }

    /**
     * @param Seal $seal
     * @return Sale
     */
    public function addSeal(Seal $seal)
    {
        $this->seals->add($seal);
        return $this;
    }

    /**
     * @param Seal $seal
     * @return Sale
     */
    public function removeSeal(Seal $seal)
    {
        $this->seals->removeElement($seal);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPoints()
    {
        return $this->points;
    }

    public function addPoint(UserPoints $points)
    {
        $this->points->add($points);
        return $this;
    }

    public function removePoint(UserPoints $points)
    {
        $this->points->removeElement($points);
        return $this;
    }

    /**
     * On insert
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        if (!$this->createdAt instanceof \DateTime) {
            $this->createdAt = new \DateTime('now');
        }
    }

    /**
     * On update
     *
     * @ORM\PreUpdate()
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }
}

