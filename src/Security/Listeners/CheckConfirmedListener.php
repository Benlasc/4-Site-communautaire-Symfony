<?php

namespace App\Security\Listeners;

use App\Entity\User;
use App\Security\Badges\BadgeConfirmed;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class CheckConfirmedListener
{
    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if ($passport instanceof UserPassportInterface && $passport->hasBadge(BadgeConfirmed::class)) {

            /** @var User $user */
            $user = $passport->getUser();

            /** @var BadgeConfirmed $badge */
            $badge = $passport->getBadge(BadgeConfirmed::class);

            if ($user->getConfirmed() === True) {
                $badge->markResolved();
            }else {
                throw new CustomUserMessageAuthenticationException("Votre compte n'est pas encore activé.");               
            }
        }
        return;
    }
}
