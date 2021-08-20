<?php

namespace App\Security\Voter;

use App\Entity\Trick;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorVoter extends Voter
{
    const AUTHOR_EDIT = "author_edit";
    const AUTHOR_DELETE = "author_delete";

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $trick): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::AUTHOR_EDIT, self::AUTHOR_DELETE])
            && $trick instanceof Trick;
    }

    protected function voteOnAttribute(string $attribute, $trick, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) return true;

        // On vérifie si la figure n'a pas plus d'auteur
        if (null === $trick->getAuthor()) return false;


        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::AUTHOR_EDIT:
                // on vérifie si on peut éditer
                return $this->canEdit($trick, $user);
                break;
            case self::AUTHOR_DELETE:
                // on vérifie si on peut supprimer
                return $this->canDelete($trick, $user);
                break;
        }

        return false;
    }

    private function canEdit(Trick $trick, User $user)
    {
        // L'auteur de la figure peut la modifier
        return $user === $trick->getAuthor();
    }

    private function canDelete(Trick $trick, User $user)
    {
        // L'auteur de la figure peut la supprimer
        return $user === $trick->getAuthor();
    }
}
