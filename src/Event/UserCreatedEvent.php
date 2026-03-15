<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\User;

class UserCreatedEvent extends Event
{
    public const NAME = 'user.created';
        
    public function __construct(
        private readonly User $user,
    ){}

    public function getUser(): User
    {
        return $this->user;
    }

}