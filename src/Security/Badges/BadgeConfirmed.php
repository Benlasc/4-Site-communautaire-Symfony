<?php

namespace App\Security\Badges;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class BadgeConfirmed implements BadgeInterface
{
    
    private $resolved = false;    

    public function markResolved(): void
    {
        $this->resolved = true;
    }

    public function isResolved(): bool
    {
        return $this->resolved;
    }
}
