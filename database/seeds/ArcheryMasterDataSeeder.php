<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArcheryMasterDataSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     *
     * @throws Exception
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $archery_master_age_categories = [
                [
                    "id" => "umum",
                    "label" => "Umum",
                    "description" => ""
                ],
                [
                    "id" => "U-12",
                    "label" => "U-12",
                    "description" => ""
                ],
                [
                    "id" => "U-15",
                    "label" => "U-15",
                    "description" => ""
                ],
            ];
            $archery_master_team_categories = [
                [
                    "id" => "individu",
                    "label" => "Individu",
                    "description" => "",
                    "type" => "Individu"
                ],
                [
                    "id" => "mix_team",
                    "label" => "Mix Team",
                    "description" => "",
                    "type" => "Team"
                ],
                [
                    "id" => "male_team",
                    "label" => "Male Team",
                    "description" => "",
                    "type" => "Team"
                ],
                [
                    "id" => "female_team",
                    "label" => "Female Team",
                    "description" => "",
                    "type" => "Team"
                ],
            ];
            $archery_master_competition_categories = [
                [
                    "id" => "Recurved",
                    "label" => "Recurved",
                    "description" => ""
                ],
                [
                    "id" => "Bound",
                    "label" => "Bound",
                    "description" => ""
                ],
                [
                    "id" => "Traditional",
                    "label" => "Traditional",
                    "description" => ""
                ],
                [
                    "id" => "Barebow",
                    "label" => "Barebow",
                    "description" => ""
                ],
            ];
            $archery_master_distances = [
                [
                    "id" => "5",
                    "label" => "5m",
                    "description" => ""
                ],
                [
                    "id" => "10",
                    "label" => "10m",
                    "description" => ""
                ],
                [
                    "id" => "15",
                    "label" => "15m",
                    "description" => ""
                ],
                [
                    "id" => "18",
                    "label" => "18m",
                    "description" => ""
                ],
                [
                    "id" => "20",
                    "label" => "20m",
                    "description" => ""
                ],
                [
                    "id" => "30",
                    "label" => "30m",
                    "description" => ""
                ],
                [
                    "id" => "40",
                    "label" => "40m",
                    "description" => ""
                ],
                [
                    "id" => "50",
                    "label" => "50m",
                    "description" => ""
                ],
            ];
            $archery_master_round_tipes = [
                [
                    "id" => "qualification",
                    "label" => "Qualification",
                    "description" => ""
                ]
            ];
            $archery_master_targets = [
                [
                    "id" => "public",
                    "label" => "Public",
                    "description" => ""
                ],
                [
                    "id" => "specific",
                    "label" => "Specific Audience",
                    "description" => ""
                ]
            ];
            $archery_master_registration_tipes = [
                [
                    "id" => "normal",
                    "label" => "Normal",
                    "description" => ""
                ],
                [
                    "id" => "early_bird",
                    "label" => "Early Bird",
                    "description" => ""
                ]
            ];

            DB::table('archery_master_age_categories')->insert($archery_master_age_categories);
            DB::table('archery_master_team_categories')->insert($archery_master_team_categories);
            DB::table('archery_master_competition_categories')->insert($archery_master_competition_categories);
            DB::table('archery_master_distances')->insert($archery_master_distances);
            DB::table('archery_master_round_tipes')->insert($archery_master_round_tipes);
            DB::table('archery_master_targets')->insert($archery_master_targets);
            DB::table('archery_master_registration_tipes')->insert($archery_master_registration_tipes);

            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur ' . $e);
            DB::rollBack();
        }
    }
}
