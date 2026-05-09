<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@recipehub.com');
        $admin->setPseudo('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);
        $this->addReference('user_admin', $admin);

        // Cuisinier
        $chef = new User();
        $chef->setEmail('chef@recipehub.com');
        $chef->setPseudo('chef');
        $chef->setRoles(['ROLE_CUISINIER']);
        $chef->setPassword($this->hasher->hashPassword($chef, 'chef123'));
        $manager->persist($chef);
        $this->addReference('user_chef', $chef);

        $faker = Factory::create('fr_FR');
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $email = $faker->unique()->safeEmail;
            $user->setEmail($email);
            $user->setPseudo($faker->userName);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }
}

