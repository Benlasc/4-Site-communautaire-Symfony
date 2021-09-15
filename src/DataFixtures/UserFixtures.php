<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Trick;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $encoder;
    private $em;

    public function __construct(UserPasswordHasherInterface $encoder, EntityManagerInterface $entityManager)
    {
        $this->encoder = $encoder;
        $this->em = $entityManager;
    }

    public function load(\Doctrine\Persistence\ObjectManager $manager)
    {
        $usersData = [
            0 => [
                'name' => 'Admin',
                'email' => 'admin@domain.fr',
                'role' => ['ROLE_Admin'],
                'password' => 'Azerty20'
            ],
            1 => [
                'name' => 'User',
                'email' => 'user@domain.fr',
                'role' => ['ROLE_USER'],
                'password' => 'Azerty20'
            ]
        ];

        foreach ($usersData as $i => $user) {
            $newUser = new User();
            $newUser->setEmail($user['email']);
            $newUser->setName($user['name']);
            $newUser->setConfirmed(true);
            $newUser->setRegistrationDate(new DateTime('now'));
            $newUser->setPassword($this->encoder->hashPassword($newUser, $user['password']));
            $newUser->setRoles($user['role']);
            $this->em->persist($newUser);

            // Création de 10 articles par l'utilisateur 'User'
            if ($i == 1) {
                $trickRepository = $this->em->getRepository(Trick::class);
                for ($i = 0; $i < 10; $i++) {
                    $trick = $trickRepository->find($i + 1);

                    //Ajout des commentaires sur la première figure
                    if ($i == 0) {
                        for ($j = 0; $j < 22; $j++) {
                            $trick->addComment((new Comment())
                                    ->setContent(
                                        '
                                    Aut quia facere sit labore enim qui corporis harum vel consequatur placeat. 33 laboriosam consequuntur et dolor quia ad soluta nisi in incidunt autem. Hic odit galisum nesciunt internos et placeat minima quo quia porro.'
                                    )
                                    ->setAuthor($newUser)
                                    ->setDate(new DateTime())
                                    ->setTrick($trick)
                            );
                        }
                    }

                    $trick->setDescription(
                        'Lorem ipsum dolor sit amet. Et deleniti eaque nam ipsum soluta et alias perspiciatis id quia quia eos nisi quia ut ullam galisum. Et dolor animi ut nihil officia eum praesentium sint qui voluptas esse et obcaecati reprehenderit est iusto fugit qui labore deleniti? Et maiores aliquam a dolores quia ad eius possimus qui autem sapiente eos voluptatum facere. 33 galisum quos et saepe reiciendis in nulla odio sit quos aspernatur vel eius sit animi consequatur. Ut earum pariatur eos fugiat qui repellat quisquam. Qui nostrum dolore sed commodi architecto et adipisci nisi ut repellat enim. Est incidunt necessitatibus ut internos quibusdam et delectus fuga. Est iure deserunt et voluptate sunt est officiis voluptatem rerum voluptas id reprehenderit consequatur qui fugit dolorem. Eum sapiente adipisci cum magnam placeat et error mollitia qui quod quia in amet voluptatum sit quaerat quae.
        
                        Et atque eius et tenetur atque est provident consequuntur hic fuga harum? Sed unde quia quo consequatur consectetur eum repudiandae soluta. Ut dolore quas et soluta culpa ut nihil amet aut pariatur repellendus et internos sunt sit architecto vero. Et dignissimos esse aut cumque esse sed quas esse et voluptatem illum id commodi deserunt a quis explicabo. Qui debitis laudantium et fugiat quod ad porro illo aut fuga itaque. Id mollitia ex impedit rerum et Quis corporis et maxime voluptatem aut incidunt totam! Aut sint quia ad libero mollitia non eveniet incidunt et dolorem sequi qui debitis magnam est odio natus vel alias dolor.'
                    );
                    $trick->setAuthor($newUser);
                    $trick->setCreationDate(new DateTime());
                }
            }
        }
        $this->em->flush();
    }
}
