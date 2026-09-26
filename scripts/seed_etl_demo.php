<?php
/**
 * Seeder script for Warlito (Dean/Admin) and Gons (Faculty/User)
 * Sets up realistic IPCR forms with ETL weighting.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = getDB();
ensureIpcrColumns($db);

echo "Starting seed for ETL demonstration...\n";

// 1. Verify Users
$usersStmt = $db->query("SELECT id, username, name, role, department_id, position FROM users WHERE username IN ('warlito', 'gons')");
$users = [];
while ($row = $usersStmt->fetch(PDO::FETCH_ASSOC)) {
    $users[$row['username']] = $row;
}

if (!isset($users['warlito']) || !isset($users['gons'])) {
    die("Error: 'warlito' or 'gons' user not found in database.\n");
}

$warlito = $users['warlito'];
$gons    = $users['gons'];

echo "Found warlito (ID: {$warlito['id']}) and gons (ID: {$gons['id']}).\n";

// 2. Verify or fetch active timeline
$tlStmt = $db->query("SELECT * FROM timelines WHERE status = 'open' ORDER BY id DESC LIMIT 1");
$timeline = $tlStmt->fetch(PDO::FETCH_ASSOC);

if (!$timeline) {
    // If none open, get the latest timeline
    $tlStmt = $db->query("SELECT * FROM timelines ORDER BY id DESC LIMIT 1");
    $timeline = $tlStmt->fetch(PDO::FETCH_ASSOC);
}

if (!$timeline) {
    die("Error: No timeline found in database.\n");
}

$timelineId = $timeline['id'];
$periodStr  = ($timeline['semester'] ?: 'July to December') . ' ' . ($timeline['academic_year'] ?: '2026');
echo "Using Timeline ID: {$timelineId} ({$periodStr})\n";

// 3. Clean up any existing IPCR forms for these two users on this timeline
$delStmt = $db->prepare("DELETE FROM ipcr_forms WHERE user_id IN (?, ?) AND timeline_id = ?");
$delStmt->execute([$warlito['id'], $gons['id'], $timelineId]);
echo "Cleared old forms for warlito and gons on timeline {$timelineId}.\n";

// -------------------------------------------------------------
// 4. Seed GONS (Faculty / User)
// Preset: 6 ETL (Core 50%, Strategic 25%, Support 25%)
// Core Avg: 3.50 -> Weighted: 1.75
// Strategic Avg: 4.00 -> Weighted: 1.00
// Support Avg: 4.00 -> Weighted: 1.00
// Overall: 3.75 (Very Satisfactory)
// Status: pending (ready for Warlito to review!)
// -------------------------------------------------------------
$gonsFormStmt = $db->prepare("
    INSERT INTO ipcr_forms 
    (user_id, timeline_id, covered_period, date_submitted, status, overall_rating, etl_type, weight_core, weight_strategic, weight_support, remarks)
    VALUES (?, ?, ?, '2026-09-20', 'pending', 3.75, '6 ETL', 50.00, 25.00, 25.00, 'Submitted for Dean review')
");
$gonsFormStmt->execute([$gons['id'], $timelineId, $periodStr]);
$gonsFormId = $db->lastInsertId();
echo "Created IPCR form for gons (ID: {$gonsFormId}, Status: pending, ETL: 6 ETL).\n";

// Items for Gons
$gonsItems = [
    // Core (avg 3.50)
    [
        'function_type'     => 'core',
        'mfo'               => 'Higher and Advanced Education Services',
        'success_indicator' => 'Percentage of students passing the course with a grade of 2.5 or higher',
        'target'            => '90%',
        'accomplishment'    => '92%',
        'q_rating'          => 3.00,
        'e_rating'          => 4.00,
        't_rating'          => 4.00,
        'rating'            => 3.67,
        'remarks'           => 'Very Satisfactory'
    ],
    [
        'function_type'     => 'core',
        'mfo'               => 'Instruction and Curriculum Delivery',
        'success_indicator' => 'Timely submission of syllabus, instructional materials, and midterm/final grades',
        'target'            => '100%',
        'accomplishment'    => '100%',
        'q_rating'          => 3.00,
        'e_rating'          => 3.00,
        't_rating'          => 4.00,
        'rating'            => 3.33,
        'remarks'           => 'Satisfactory'
    ],
    // Strategic (avg 4.00)
    [
        'function_type'     => 'strategic',
        'mfo'               => 'Research and Innovation / Paper Presentation',
        'success_indicator' => 'Published research article or presented in national/international conference',
        'target'            => '100%',
        'accomplishment'    => '100%',
        'q_rating'          => 4.00,
        'e_rating'          => 4.00,
        't_rating'          => 4.00,
        'rating'            => 4.00,
        'remarks'           => 'Very Satisfactory'
    ],
    // Support (avg 4.00)
    [
        'function_type'     => 'support',
        'mfo'               => 'College and University Committees Support',
        'success_indicator' => 'Active participation in college committee meetings, accreditation, and department activities',
        'target'            => '100%',
        'accomplishment'    => '100%',
        'q_rating'          => 4.00,
        'e_rating'          => 4.00,
        't_rating'          => 4.00,
        'rating'            => 4.00,
        'remarks'           => 'Very Satisfactory'
    ],
];

$itemInsertStmt = $db->prepare("
    INSERT INTO ipcr_items 
    (ipcr_form_id, function_type, mfo, success_indicator, target, accomplishment, q_rating, e_rating, t_rating, rating, remarks, budget, measure)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, '')
");

foreach ($gonsItems as $item) {
    $itemInsertStmt->execute([
        $gonsFormId,
        $item['function_type'],
        $item['mfo'],
        $item['success_indicator'],
        $item['target'],
        $item['accomplishment'],
        $item['q_rating'],
        $item['e_rating'],
        $item['t_rating'],
        $item['rating'],
        $item['remarks']
    ]);
}
echo "Inserted " . count($gonsItems) . " line items for gons.\n";

// -------------------------------------------------------------
// 5. Seed WARLITO (Dean / Admin)
// Preset: 0 ETL (Core 70%, Strategic 15%, Support 15%)
// Core Avg: 4.17 -> Weighted: 2.92
// Strategic Avg: 4.00 -> Weighted: 0.60
// Support Avg: 4.00 -> Weighted: 0.60
// Overall: 4.12 (Very Satisfactory)
// Status: draft (Dean can edit, change weights, submit)
// -------------------------------------------------------------
$warlitoFormStmt = $db->prepare("
    INSERT INTO ipcr_forms 
    (user_id, timeline_id, covered_period, date_submitted, status, overall_rating, etl_type, weight_core, weight_strategic, weight_support, remarks)
    VALUES (?, ?, ?, NULL, 'draft', 4.12, '0 ETL', 70.00, 15.00, 15.00, 'Dean Administrative IPCR draft')
");
$warlitoFormStmt->execute([$warlito['id'], $timelineId, $periodStr]);
$warlitoFormId = $db->lastInsertId();
echo "Created IPCR form for warlito (ID: {$warlitoFormId}, Status: draft, ETL: 0 ETL).\n";

// Items for Warlito
$warlitoItems = [
    // Core (avg 4.17)
    [
        'function_type'     => 'core',
        'mfo'               => 'College Academic Leadership and Management',
        'success_indicator' => 'Effective supervision of faculty teaching assignments, curriculum review, and instructional delivery',
        'target'            => '100%',
        'accomplishment'    => '100%',
        'q_rating'          => 4.00,
        'e_rating'          => 4.00,
        't_rating'          => 5.00,
        'rating'            => 4.33,
        'remarks'           => 'Very Satisfactory'
    ],
    [
        'function_type'     => 'core',
        'mfo'               => 'Faculty and Staff Performance Monitoring',
        'success_indicator' => 'Review and evaluation of IPCRs and college OPCR targets on or before semester deadline',
        'target'            => '100%',
        'accomplishment'    => '100%',
        'q_rating'          => 4.00,
        'e_rating'          => 4.00,
        't_rating'          => 4.00,
        'rating'            => 4.00,
        'remarks'           => 'Very Satisfactory'
    ],
    // Strategic (avg 4.00)
    [
        'function_type'     => 'strategic',
        'mfo'               => 'College Strategic Development and Linkages',
        'success_indicator' => 'Establishment of industry linkages / MoUs for student internships and curriculum advisory',
        'target'            => '100%',
        'accomplishment'    => '100%',
        'q_rating'          => 4.00,
        'e_rating'          => 4.00,
        't_rating'          => 4.00,
        'rating'            => 4.00,
        'remarks'           => 'Very Satisfactory'
    ],
    // Support (avg 4.00)
    [
        'function_type'     => 'support',
        'mfo'               => 'Institutional Governance and Council Meetings',
        'success_indicator' => 'Active attendance and representation in Academic Council and Administrative meetings',
        'target'            => '100%',
        'accomplishment'    => '100%',
        'q_rating'          => 4.00,
        'e_rating'          => 4.00,
        't_rating'          => 4.00,
        'rating'            => 4.00,
        'remarks'           => 'Very Satisfactory'
    ],
];

foreach ($warlitoItems as $item) {
    $itemInsertStmt->execute([
        $warlitoFormId,
        $item['function_type'],
        $item['mfo'],
        $item['success_indicator'],
        $item['target'],
        $item['accomplishment'],
        $item['q_rating'],
        $item['e_rating'],
        $item['t_rating'],
        $item['rating'],
        $item['remarks']
    ]);
}
echo "Inserted " . count($warlitoItems) . " line items for warlito.\n";

echo "\n--- SEEDING COMPLETED SUCCESSFULLY ---\n";
