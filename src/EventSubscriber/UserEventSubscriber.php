<?php

namespace App\EventSubscriber;

use App\Event\UserCreatedEvent;
use App\Service\EmailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger
    ){}

    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedEvent::NAME => 'onUserCreated',
        ];
    }

    public function onUserCreated(UserCreatedEvent $event): void
    {
        try {
            $user = $event->getUser();
            $this->emailService->sendWelcomeEmail($user);
            $this->logger->info('Welcome email sent to user: ' . $user->getEmail());
        } catch (\Exception $e) {
            $this->logger->error('Failed to send welcome email: ' . $e->getMessage());
        }
    }
}