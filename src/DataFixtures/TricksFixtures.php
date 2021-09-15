<?php

namespace App\DataFixtures;

use App\Entity\Groupe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Trick;
use App\services\Slug;
use DateTime;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class TricksFixtures extends Fixture
{

    private $slug;

    public function __construct(Slug $slug)
    {
        $this->slug = $slug;
    }

    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager 
    public function load(ObjectManager $manager)
    {
        $tricks = [
            'Position sur la planche' => [
                'Regular',
                'Goofy',
                'Switch-stance and fakie',
            ],
            'Figure sans rotation' => [
                'Ollie',
                'Nollie',
                'Switch ollie',
                'Fakie ollie (Switch Nollie)',
                'Shifty',
                'Air-to-fakie',
                'Poptart',
            ],
            'Grab' => [
                'One-Two',
                'A B',
                'Beef Carpaccio',
                'Beef Curtains',
                'Bloody Dracula',
                'Canadian Bacon',
                'Cannonball/UFO',
                'Chicken salad',
                'China air (West Coast) /Korean air (East Coast)',
                'Crail',
                'Cross-rocket',
                'Drunk Driver',
                'Frontside grab/indy',
                'Japan air',
                'Lien air',
                'Korean bacon',
                'Melon',
                'Watermelon (water)',
                'Method',
                'Power method, cross bone, or Palmer method',
                'Suitcase',
                'Mindy, Super',
                'Mule kick',
                'Mute',
                'Nose Grab',
                'Nuclear',
                'Pickpocket',
                'Perfect',
                'Roast beef',
                'Rocket Air',
                'Rusty Trombone',
                'Seatbelt',
                'Slob',
                'Stiffy',
                'Stalefish',
                'Stale Method',
                'Squirrel',
                'Swiss cheese air',
                'Tailfish',
                'Tail Grab',
                'Taipan air',
                'Tindy',
                'Truck driver',
            ],
            'Rotation' => [
                'Rotation',
            ],
            'Flip / rotation désaxée' => [
                'Back flip',
                'Layout Backflip',
                'Front flip',
                'Wildcat',
                'Tamedog',
                'Superman Flip',
                'Cork',
                'Lando-Roll',
                'Backside Misty',
                'Frontside Misty',
                'Chicane',
                'Underflip',
                'Frontside Rodeo',
                'Backside Rodeo flip',
                'Ninety Roll',
                'Rippey flip',
                'Crippler',
                'McTwist',
                'Double McTwist (The Tomahawk)',
                'Michalchuk',
                'Doublechuk',
                'Sato flip',
            ],
            'Handplant ' => [
                'Invert',
                'Handplant',
                'Sad plant',
                'Elguerial',
                'Eggplant',
                'Eggflip',
                'McEgg',
                'Andrecht',
                'Miller flip',
                'HoHo',
                'Killer Stand',
                'Fresh',
                'J-Tear',
            ],
            'Slide' => [
                '50-50',
                'Boardslide',
                'Lipslide',
                'Bluntslide',
                'Noseblunt',
                'Noseslide',
                'Tailslide',
                'Nosepress',
                'Tailpress',
                'MJ',
                'HJ',
                'Zeach',
                'The Gutterball',
            ],
            'Stall' => [
                'Nose-pick',
                'Board-stall : Disaster',
                'Nose-stall',
                'Tail-stall',
                'Blunt-stall',
                'Tail-block',
                'Nose-block',
            ],
            'Tweaks et variations' => [
                'One-footed',
                'Shifty',
                'Stiffy',
                'Stink-bug',
                'Tuck knee',
                'Tweak',
                'Poke',
            ],
            'Autres figures' => [
                'Jib',
                'Butter',
                'Manual : Nose manual',
                'Pretzel',
                'Sameway ou Bagel',
                'Disaster',
                'Bonk',
                'Penguin Walk',
                'Tail or Nose Tap',
                'Revert',
            ],
        ];

        foreach ($tricks as $groupe => $tricksGroupe) {
            // Entregistrement des groupes
            $newGroupe = (new Groupe())->setName($groupe);
            $manager->persist($newGroupe);

            // Entregistrement des figures            
            foreach ($tricksGroupe as $trick) {
                $newTrick = (new Trick())->setName($trick)->setGroupe($newGroupe);
                $newTrick->setSlug($this->slug->Slug($newTrick->getName()));
                $manager->persist($newTrick);
            }
        }

        // On déclenche l'enregistrement 
        $manager->flush();
    }
}
