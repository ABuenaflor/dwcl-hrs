<?php

namespace Database\Seeders;

use App\Enums\Level;
use App\Models\AcademicRank;
use App\Models\Campus;
use Illuminate\Database\Seeder;

/**
 * DWCL's structure per the thesis company profile: the North Campus hosts
 * Basic Education, the South Campus the college schools and the HRDO.
 */
class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $north = Campus::firstOrCreate(['name' => 'North Campus'], ['description' => 'Basic Education — Grade School, Junior and Senior High School']);
        $south = Campus::firstOrCreate(['name' => 'South Campus'], ['description' => 'College Department and Graduate School']);

        $departments = [
            [$north, 'Grade School', 'GS', Level::BasicEd],
            [$north, 'Junior High School', 'JHS', Level::BasicEd],
            [$north, 'Senior High School', 'SHS', Level::BasicEd],
            [$south, 'School of Engineering and Computer Studies', 'SOECS', Level::Tertiary],
            [$south, 'School of Education, Arts and Sciences', 'SEAS', Level::Tertiary],
            [$south, 'School of Business Management and Accountancy', 'SBMA', Level::Tertiary],
            [$south, 'School of Nursing', 'SON', Level::Tertiary],
            [$south, 'School of Hospitality Management', 'SHOM', Level::Tertiary],
            [$south, 'Graduate School of Business and Management', 'GSBM', Level::Tertiary],
            [$south, 'Human Resources and Development Office', 'HRDO', Level::NonAcademic],
            [$south, 'Finance and Accounting Office', 'FAO', Level::NonAcademic],
            [$south, 'Registrar\'s Office', 'REG', Level::NonAcademic],
            [$south, 'Library', 'LIB', Level::NonAcademic],
            [$south, 'Property and Supply Office', 'PSO', Level::NonAcademic],
        ];

        foreach ($departments as [$campus, $name, $code, $level]) {
            $campus->departments()->firstOrCreate(['name' => $name], ['code' => $code, 'level' => $level]);
        }

        // Point thresholds are starting values; HRDO adjusts them under Rubrics & Ranks.
        $ranks = [
            Level::BasicEd->value => [
                'Teacher I' => 0, 'Teacher II' => 150, 'Teacher III' => 200,
                'Master Teacher I' => 250, 'Master Teacher II' => 300, 'Master Teacher III' => 350,
            ],
            Level::Tertiary->value => [
                'Instructor I' => 0, 'Instructor II' => 40, 'Instructor III' => 55,
                'Assistant Professor I' => 70, 'Assistant Professor II' => 80, 'Assistant Professor III' => 90,
                'Associate Professor I' => 100, 'Associate Professor II' => 110, 'Associate Professor III' => 120,
                'Professor I' => 130, 'Professor II' => 145, 'Professor III' => 160,
            ],
        ];

        foreach ($ranks as $level => $list) {
            $order = 0;
            foreach ($list as $name => $min) {
                AcademicRank::firstOrCreate(['level' => $level, 'name' => $name], ['min_points' => $min, 'sort_order' => ++$order]);
            }
        }
    }
}
