<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Notification
{
    /** @var EntityManager $em */
    protected $em;

    /** @var  ContainerInterface $container */
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    private function createNotification($type, $message)
    {
        try {
            $notificationType = $this->em->getRepository('AppBundle:NotificationType')->find($type);
            $notification = new \AppBundle\Entity\Notification();
            $notification->setType($notificationType)
                ->setMessage($message);

            $this->em->persist($notification);

            return $notification;
        } catch (\Exception $exception) {
            $this->container->get('logger')->error('Erro ao criar Notification: '.$exception->getMessage());
        }
        return false;
    }

    public function sendNotificationToUser(User $user, $type, $message)
    {
        try {
            if (!$notification = $this->createNotification($type, $message)) {
                throw new \Exception('Erro ao criar Notification');
            }

            $notification->addUser($user);
            $this->em->persist($notification);
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->container->get('logger')->error('Erro ao enviar Notification: '.$exception->getMessage());
        }
    }

    public function removeNotificationFromUser(\AppBundle\Entity\Notification $notification, User $user)
    {
        try {
            $this->em->remove($notification);
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->container->get('logger')->error('Erro ao remover Notification: '.$exception->getMessage());
        }
    }
}