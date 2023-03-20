<?php

use App\Models\ChildrenClassificationMembers;
use Illuminate\Database\Seeder;

class ChildrenClassification extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dummy_data = [
            [
                'parent_id' =>  5,
                'title' => 'kancil',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'jerapah',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'elang',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'buaya',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'merpati',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'beruang',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'harimau',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'macan',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'singa',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'chetah',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'gajah',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'Cobra',
                'user_id' => 10
            ],
            [
                'parent_id' =>  5,
                'title' => 'Python',
                'user_id' => 10
            ],
        ];

        ChildrenClassificationMembers::insert($dummy_data);
    }
}
