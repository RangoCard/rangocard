<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationType
 *
 * @ORM\Table(name="notification_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationTypeRepository")
 */
class NotificationType
{
    const NEW_SEAL = 1;
    const NEW_RESTAURANT = 2;
    const NEW_SALE = 3;

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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="type")
     */
    private $notifications;

    /**
     * NotificationType constructor.
     */
    public function __construct()
    {
        $this->notifications = new ArrayCollection();
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
     * @return NotificationType
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
     * @return ArrayCollection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param Notification $notification
     * @return NotificationType
     */
    public function addNotification(Notification $notification)
    {
        $this->notifications->add($notification);
        return $this;
    }

    /**
     * @param Notification $notification
     * @return NotificationType
     */
    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
        return $this;
    }
}

