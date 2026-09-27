<?php

namespace Database\Seeders;

use App\Enums\Level;
use App\Models\Rubric;
use Illuminate\Database\Seeder;

/**
 * Faculty ranking instruments.
 *
 * Basic Education is transcribed from the legacy self-rating form (Faculty
 * Manual, 2017 revision). For Tertiary, section 1 is transcribed from the
 * legacy screen; sections 2–7 are a starting structure to be checked
 * against the Faculty Manual — every item is editable under Rubrics.
 *
 * Node keys: code, title, guide, pct (weight %), credit (credit points),
 * field / related (tertiary credit points), max (cap), children.
 * A node without children is scorable.
 */
class RubricSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed(Level::BasicEd, 'Basic Education Faculty Ranking Instrument', 400, $this->basicEd());
        $this->seed(Level::Tertiary, 'College Faculty Ranking Instrument', 180, $this->tertiary());
    }

    private function seed(Level $level, string $name, float $total, array $nodes): void
    {
        if (Rubric::where('level', $level)->exists()) {
            return;
        }

        $rubric = Rubric::create([
            'level' => $level, 'name' => $name, 'total_points' => $total, 'is_active' => true,
            'description' => 'Based on the DWCL Faculty Manual (2017 revision).',
        ]);

        $this->insert($rubric, $nodes, null);
    }

    private function insert(Rubric $rubric, array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $i => $node) {
            $item = $rubric->items()->create([
                'parent_id' => $parentId,
                'code' => $node['code'] ?? null,
                'title' => $node['title'],
                'guide' => $node['guide'] ?? null,
                'weight_percent' => $node['pct'] ?? null,
                'credit_points' => $node['credit'] ?? null,
                'credit_in_field' => $node['field'] ?? null,
                'credit_related' => $node['related'] ?? null,
                'max_points' => $node['max'] ?? $node['credit'] ?? null,
                'is_scorable' => empty($node['children']),
                'sort_order' => $i,
            ]);

            $this->insert($rubric, $node['children'] ?? [], $item->id);
        }
    }

    private function basicEd(): array
    {
        return [
            ['code' => '1', 'title' => 'Educational Attainment', 'pct' => 40, 'credit' => 160, 'children' => [
                ['code' => '1.1', 'title' => 'Degrees Earned', 'pct' => 20, 'max' => 80, 'children' => [
                    ['code' => '1.1.1', 'title' => 'Baccalaureate', 'children' => [
                        ['title' => 'BSE / BSEEd (or its equivalent)'],
                        ['title' => 'BS / AB + LET Passer', 'guide' => '5 points'],
                    ]],
                    ['code' => '1.1.2', 'title' => 'CS Exam (Professional)', 'guide' => '2 points', 'max' => 2],
                ]],
                ['code' => '1.2', 'title' => 'Professional Growth', 'pct' => 20, 'max' => 80, 'children' => [
                    ['code' => '1.2.1', 'title' => 'Advanced Training', 'children' => [
                        ['title' => 'MA / MS units', 'guide' => '1 pt / 6 units'],
                        ['title' => 'MA / MS + SO', 'guide' => '40 points', 'max' => 40],
                    ]],
                    ['code' => '1.2.2', 'title' => 'Seminars', 'max' => 25, 'children' => [
                        ['title' => 'International', 'guide' => 'Attended 2 · Echoed 3 · Related field 1 — pts per 8 hrs'],
                        ['title' => 'National', 'guide' => 'Attended 1 · Echoed 2 · Related field ½ — pts per 8 hrs'],
                        ['title' => 'Regional', 'guide' => 'Attended ½ · Echoed 1 · Related field ¼ — pts per 8 hrs'],
                        ['title' => 'Local', 'guide' => 'Attended ¼ · Echoed ½ · Related field ⅛ — pts per 8 hrs'],
                    ]],
                    ['code' => '1.2.3', 'title' => 'As Resource Speaker', 'max' => 15, 'children' => [
                        ['title' => 'Trainer / day', 'guide' => "Nat'l 10 · Reg'l/Prov'l 8 · District 6 · School 5"],
                        ['title' => 'Resource Speaker / topic', 'guide' => "Nat'l 8 · Reg'l/Prov'l 6 · District 5 · School 3"],
                        ['title' => 'Facilitator / day', 'guide' => "Nat'l 4 · Reg'l/Prov'l 3 · District 2 · School 1"],
                    ]],
                    ['code' => '1.2.4', 'title' => 'Completed Certificate of Proficiency', 'max' => 5],
                ]],
            ]],
            ['code' => '2', 'title' => 'Teaching Experience', 'pct' => 25, 'credit' => 100, 'children' => [
                ['code' => '2.1', 'title' => 'Status of Employment', 'max' => 100, 'children' => [
                    ['code' => '2.1.1', 'title' => 'Full Time within DWCL', 'guide' => '4 pts / yr of service'],
                    ['code' => '2.1.2', 'title' => 'Part Time (subject loading for 1 school year)', 'children' => [
                        ['title' => '1–2 subjects', 'guide' => '1 pt / year'],
                        ['title' => '3–4 subjects', 'guide' => '2 pts / year'],
                    ]],
                    ['code' => '2.1.3', 'title' => 'Outside DWCL', 'guide' => '1 pt / 3 yrs'],
                ]],
            ]],
            ['code' => '3', 'title' => 'Faculty Performance Rating (Average Performance)', 'pct' => 20, 'credit' => 80, 'children' => [
                ['code' => '3.1', 'title' => 'Mode for the last 3 years', 'max' => 80, 'children' => [
                    ['title' => '4.0 – 4.5 Very Satisfactory', 'guide' => '60 points', 'max' => 60],
                    ['title' => '3.6 – 3.9 Satisfactory', 'guide' => '40 points', 'max' => 40],
                ]],
            ]],
            ['code' => '4', 'title' => 'Community Extension Services', 'pct' => 10, 'credit' => 40, 'children' => [
                ['code' => '4.1', 'title' => 'Professional Organizations, Societies, Civic, Social, Cultural, Religious Clubs, Groups, etc.', 'max' => 20, 'children' => [
                    ['title' => 'International', 'guide' => '5 pts / org'],
                    ['title' => 'National', 'guide' => '4 pts / org'],
                    ['title' => 'Regional', 'guide' => '3 pts / org'],
                    ['title' => 'Division / Provincial / School', 'guide' => '1 pt / org'],
                    ['title' => 'Officership', 'guide' => 'Additional 1 pt'],
                    ['title' => 'Life membership', 'guide' => 'Additional 1 pt'],
                ]],
                ['code' => '4.2', 'title' => 'Services Rendered without Remuneration from DWCL', 'max' => 10, 'children' => [
                    ['title' => 'Coach, trainer, facilitator', 'guide' => '0.5 pts / event'],
                    ['title' => 'Area Chair — PAASCU', 'guide' => '5 pts / visit'],
                    ['title' => 'Committee Member — PAASCU', 'guide' => '2 pts / visit'],
                    ['title' => 'Chairman in other school activities', 'guide' => '3 pts / activity'],
                    ['title' => 'Member in other school activities', 'guide' => '2 pts / activity'],
                    ['title' => 'Membership in Academic Committees (Co- and Extra-Curricular, Ad Hoc, RTC, etc.)', 'guide' => '2 pts / yr'],
                ]],
                ['code' => '4.3', 'title' => 'Awards, Citations, Plaques, Certificates', 'max' => 10, 'children' => [
                    ['title' => 'International', 'guide' => '5 pts / award'],
                    ['title' => 'National', 'guide' => '4 pts / award'],
                    ['title' => 'Regional', 'guide' => '2 pts / award'],
                    ['title' => 'Division / Provincial', 'guide' => '1 pt / award'],
                ]],
            ]],
            ['code' => '5', 'title' => 'Research Productivity (excluding theses, dissertations and commissioned research)', 'pct' => 5, 'credit' => 20, 'children' => [
                ['code' => '5.1', 'title' => 'Research Work / Participation in Research', 'max' => 20, 'children' => [
                    ['title' => 'Main Researcher / Project Manager', 'guide' => '5 pts'],
                    ['title' => 'Co-Researcher / Research or Statistics Consultant', 'guide' => '3 pts'],
                    ['title' => 'Enumerator / Interviewer / Data Gatherer', 'guide' => '1 pt'],
                    ['title' => 'Research Leader', 'guide' => '2 pts'],
                ]],
                ['code' => '5.2', 'title' => 'Research output / modules, kits, manuals and other teaching materials submitted', 'guide' => '5–20 pts as recommended by the Reviewing Committee; shared equally by group members', 'max' => 20],
                ['code' => '5.3', 'title' => 'Adoption of modules / kits', 'guide' => 'Additional 5 pts', 'max' => 5],
            ]],
        ];
    }

    private function tertiary(): array
    {
        return [
            ['code' => '1', 'title' => 'Educational Attainment', 'guide' => 'Maximum 60 points, cumulative', 'max' => 60, 'children' => [
                ['title' => 'Doctorate', 'field' => 50, 'related' => 45],
                ['title' => 'Extra Doctorate', 'field' => 15, 'related' => 5],
                ['title' => "Master's Degree with Thesis", 'field' => 35, 'related' => 30],
                ['title' => "Master's Degree without Thesis / Seminar Paper", 'field' => 30, 'related' => 25],
                ['title' => "Extra Master's Degree with Thesis", 'field' => 5, 'related' => 4],
                ['title' => "Extra Master's Degree without Thesis / Seminar Paper", 'field' => 5, 'related' => 3],
                ['title' => "Extra Bachelor's Degree", 'field' => 3, 'related' => 2],
                ['title' => 'Board Exam', 'field' => 5, 'related' => 3],
            ]],
            ['code' => '2', 'title' => 'Teaching and Professional Experience', 'max' => 25, 'children' => [
                ['title' => 'College teaching in DWCL', 'guide' => 'per year of service', 'field' => 2, 'related' => 1.5],
                ['title' => 'College teaching in other HEIs', 'guide' => 'per year', 'field' => 1, 'related' => 0.5],
                ['title' => 'Industry / professional practice', 'guide' => 'per year', 'field' => 1, 'related' => 0.5],
                ['title' => 'Administrative designation (Dean, Chair, Coordinator)', 'guide' => 'per year', 'field' => 1, 'related' => 1],
            ]],
            ['code' => '3', 'title' => 'Faculty Performance Evaluation (mode of the last 3 years)', 'max' => 20, 'children' => [
                ['title' => 'Outstanding', 'field' => 20],
                ['title' => 'Very Satisfactory', 'field' => 15],
                ['title' => 'Satisfactory', 'field' => 10],
            ]],
            ['code' => '4', 'title' => 'Research and Publications', 'max' => 30, 'children' => [
                ['title' => 'Article in refereed international journal', 'field' => 10, 'related' => 8],
                ['title' => 'Article in refereed national journal', 'field' => 8, 'related' => 6],
                ['title' => 'Article in institutional journal', 'field' => 5, 'related' => 4],
                ['title' => 'Paper presented — international', 'field' => 6, 'related' => 5],
                ['title' => 'Paper presented — national', 'field' => 4, 'related' => 3],
                ['title' => 'Paper presented — regional / local', 'field' => 2, 'related' => 1],
                ['title' => 'Completed institutional research', 'field' => 3, 'related' => 2],
                ['title' => 'Published textbook / instructional material', 'field' => 5, 'related' => 4],
            ]],
            ['code' => '5', 'title' => 'Professional Development', 'max' => 20, 'children' => [
                ['title' => 'Seminars / trainings attended — international', 'guide' => 'per 8 hrs', 'field' => 3, 'related' => 2],
                ['title' => 'Seminars / trainings attended — national', 'guide' => 'per 8 hrs', 'field' => 2, 'related' => 1],
                ['title' => 'Seminars / trainings attended — regional / local', 'guide' => 'per 8 hrs', 'field' => 1, 'related' => 0.5],
                ['title' => 'Resource speaker / trainer', 'guide' => 'per engagement', 'field' => 4, 'related' => 3],
                ['title' => 'Professional organization — officer', 'guide' => 'per org', 'field' => 2, 'related' => 1],
                ['title' => 'Professional organization — member', 'guide' => 'per org', 'field' => 1, 'related' => 0.5],
            ]],
            ['code' => '6', 'title' => 'Community Extension Services', 'max' => 15, 'children' => [
                ['title' => 'Project leader / coordinator', 'guide' => 'per project', 'field' => 5, 'related' => 4],
                ['title' => 'Project member / facilitator', 'guide' => 'per project', 'field' => 3, 'related' => 2],
                ['title' => 'Participant', 'guide' => 'per activity', 'field' => 1, 'related' => 0.5],
            ]],
            ['code' => '7', 'title' => 'Awards and Recognition', 'max' => 10, 'children' => [
                ['title' => 'International', 'field' => 5, 'related' => 4],
                ['title' => 'National', 'field' => 4, 'related' => 3],
                ['title' => 'Regional', 'field' => 3, 'related' => 2],
                ['title' => 'Institutional', 'field' => 2, 'related' => 1],
            ]],
        ];
    }
}
