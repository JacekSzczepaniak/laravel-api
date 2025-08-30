<?php


namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Api\Models\Person;

class PeopleSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $faker = Factory::create();

        $createdBy = 1;
        $updatedBy = 1;

        for ($i = 0; $i < 15; $i++) {
            $person = Person::create([
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);

            $emails = array_unique([$faker->unique()->safeEmail(), $faker->unique()->safeEmail()]);
            $isPrimary = true;
            foreach ($emails as $email) {
                $person->contacts()->create([
                    'type' => 'email',
                    'value' => $email,
                    'is_primary' => $isPrimary,
                    'created_by' => $createdBy,
                    'updated_by' => $updatedBy,
                ]);
                $isPrimary = false;
            }

            $person->contacts()->create([
                'type' => 'phone',
                'value' => $faker->e164PhoneNumber(),
                'is_primary' => true,
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);
        }

        $john = Person::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'created_by' => $createdBy,
            'updated_by' => $updatedBy,
        ]);

        $john->contacts()->createMany([
            [
                'type' => 'email',
                'value' => 'john.doe@example.com',
                'is_primary' => true,
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ],
            [
                'type' => 'phone',
                'value' => '+48123123123',
                'is_primary' => true,
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ],
        ]);

        Model::reguard();
    }
}
