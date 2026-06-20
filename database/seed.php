<?php
// ============================================================
// CSU-Piat AOPCR/IPCR System — Database Seeder
// Called by setup.php — do NOT run directly in production
// ============================================================

function runSeed(PDO $db): array {
    $log = [];

    // ----------------------------------------------------------
    // DEPARTMENTS
    // ----------------------------------------------------------
    $departments = [
        ['CEO',   'Office of the Campus Executive Officer', 'admin'],
        ['REG',   "Registrar's Office", 'admin'],
        ['ACCT',  'Accounting Office', 'admin'],
        ['HR',    'Human Resource Office', 'admin'],
        ['RDE',   'Research, Development & Extension Office', 'admin'],
        ['ITO',   'IT Office', 'admin'],
        ['PRMO',  'Partnership & Resource Mobilization Office', 'admin'],
        ['CAGRI', 'College of Agriculture', 'academic'],
        ['CCJA',  'College of Criminal Justice Administration', 'academic'],
        ['CICS',  'College of Information and Computing Sciences', 'academic'],
        ['CED',   'College of Education', 'academic'],
    ];

    $stmt = $db->prepare('INSERT IGNORE INTO departments (id, name, type) VALUES (?, ?, ?)');
    foreach ($departments as $d) {
        $stmt->execute($d);
    }
    $log[] = count($departments) . ' departments seeded.';

    // ----------------------------------------------------------
    // USERS
    // Passwords are hashed with bcrypt (password_hash)
    // Original plain-text passwords:
    //   superadmin / admin users → admin123
    //   user (faculty/staff)    → faculty123
    // ----------------------------------------------------------
    $adminPass   = password_hash('admin123',   PASSWORD_BCRYPT);
    $facultyPass = password_hash('faculty123', PASSWORD_BCRYPT);

    $users = [
        // Super Admin
        [1,  'superadmin',       $adminPass,   'superadmin', 'System Administrator',           'System Administrator',                 'CEO',   'sysadmin@piat.csu.edu.ph',     'Male',   'active',   'SA', "What is your mother's maiden name?", 'santos'],
        // Admins
        [2,  'admin',            $adminPass,   'admin',      'Dr. Maria Santos',               'Campus Executive Officer',             'CEO',   'maria.santos@piat.csu.edu.ph',   'Female', 'active',   'MS', "What is your mother's maiden name?", 'reyes'],
        [3,  'jose.reyes',       $adminPass,   'admin',      'Prof. Jose Reyes',               'Dean, College of Agriculture',         'CAGRI', 'jose.reyes@piat.csu.edu.ph',     'Male',   'active',   'JR', 'What city were you born in?',        'piat'],
        [4,  'anna.cruz',        $adminPass,   'admin',      'Prof. Anna Cruz',                'Dean, College of Education',           'CED',   'anna.cruz@piat.csu.edu.ph',      'Female', 'active',   'AC', "What is your pet's name?",           'lucky'],
        [5,  'carlo.bautista',   $adminPass,   'admin',      'Prof. Carlo Bautista',           'Dean, College of Information and Computing Sciences', 'CICS', 'carlo.bautista@piat.csu.edu.ph', 'Male', 'active', 'CB', "What is your mother's maiden name?", 'dela cruz'],
        [6,  'luz.domingo',      $adminPass,   'admin',      'Prof. Luz Domingo',              'Dean, College of Criminal Justice Administration', 'CCJA', 'luz.domingo@piat.csu.edu.ph', 'Female', 'active', 'LD', 'What city were you born in?', 'tuguegarao'],
        [7,  'carla.villanueva', $adminPass,   'admin',      'Ms. Carla Villanueva',           'University Registrar',                 'REG',   'carla.villanueva@piat.csu.edu.ph', 'Female', 'active', 'CV', "What is your mother's maiden name?", 'garcia'],
        // Users / Faculty & Staff
        [8,  'faculty',          $facultyPass, 'user',       'Mr. Ramon Castillo',             'Accountant III',                       'ACCT',  'ramon.castillo@piat.csu.edu.ph',  'Male',   'active',   'RC', "What is your pet's name?",           'brownie'],
        [9,  'sofia.mendoza',    $facultyPass, 'user',       'Ms. Sofia Mendoza',              'Human Resource Management Officer II', 'HR',    'sofia.mendoza@piat.csu.edu.ph',   'Female', 'active',   'SM', 'What city were you born in?',        'cagayan'],
        [10, 'diego.torres',     $facultyPass, 'user',       'Mr. Diego Torres',               'IT Officer I',                         'ITO',   'diego.torres@piat.csu.edu.ph',    'Male',   'active',   'DT', "What is your mother's maiden name?", 'valdez'],
        [11, 'elena.ramos',      $facultyPass, 'user',       'Dr. Elena Ramos',                'Research Director',                    'RDE',   'elena.ramos@piat.csu.edu.ph',     'Female', 'active',   'ER', 'What city were you born in?',        'manila'],
        [12, 'marco.delacruz',   $facultyPass, 'user',       'Prof. Marco Dela Cruz',          'Assistant Professor II',               'CAGRI', 'marco.delacruz@piat.csu.edu.ph',  'Male',   'active',   'MD', "What is your pet's name?",           'whitey'],
        [13, 'jasmine.aquino',   $facultyPass, 'user',       'Prof. Jasmine Aquino',           'Instructor I',                         'CED',   'jasmine.aquino@piat.csu.edu.ph',  'Female', 'active',   'JA', "What is your mother's maiden name?", 'pascual'],
        [14, 'patrick.soriano',  $facultyPass, 'user',       'Prof. Patrick Soriano',          'Assistant Professor I',                'CICS',  'patrick.soriano@piat.csu.edu.ph', 'Male',   'active',   'PS', 'What city were you born in?',        'lal-lo'],
        [15, 'lina.garcia',      $facultyPass, 'user',       'Prof. Lina Garcia',              'Instructor II',                        'CCJA',  'lina.garcia@piat.csu.edu.ph',     'Female', 'active',   'LG', "What is your pet's name?",           'mittens'],
        [16, 'anthony.pascual',  $facultyPass, 'user',       'Mr. Anthony Pascual',            'Administrative Aide VI',               'ACCT',  'anthony.pascual@piat.csu.edu.ph', 'Male',   'active',   'AP', "What is your mother's maiden name?", 'miranda'],
        [17, 'jennifer.vilar',   $facultyPass, 'user',       'Ms. Jennifer Vilar',             'Administrative Assistant II',          'REG',   'jennifer.vilar@piat.csu.edu.ph',  'Female', 'active',   'JV', 'What city were you born in?',        'sto. nino'],
        [18, 'bernard.hernandez',$facultyPass, 'user',       'Mr. Bernard Hernandez',          'Administrative Officer I',             'HR',    'bernard.hernandez@piat.csu.edu.ph','Male',  'active',   'BH', "What is your pet's name?",           'rex'],
        [19, 'rachel.espiritu',  $facultyPass, 'user',       'Ms. Rachel Espiritu',            'Instructor I',                         'CAGRI', 'rachel.espiritu@piat.csu.edu.ph', 'Female', 'active',   'RE', "What is your mother's maiden name?", 'flores'],
        [20, 'theresa.magno',    $facultyPass, 'user',       'Ms. Theresa Magno',              'Instructor II',                        'CED',   'theresa.magno@piat.csu.edu.ph',   'Female', 'active',   'TM', 'What city were you born in?',        'aparri'],
        [21, 'nelson.cabrera',   $facultyPass, 'user',       'Mr. Nelson Cabrera',             'Administrative Officer III',           'PRMO',  'nelson.cabrera@piat.csu.edu.ph',  'Male',   'active',   'NC', "What is your mother's maiden name?", 'delos reyes'],
        [22, 'gloria.ocampo',    $facultyPass, 'user',       'Ms. Gloria Ocampo',              'Administrative Assistant III',         'PRMO',  'gloria.ocampo@piat.csu.edu.ph',   'Female', 'active',   'GO', 'What city were you born in?',        'piat'],
        [23, 'victor.tomas',     $facultyPass, 'user',       'Mr. Victor Tomas',               'Project Development Officer II',       'PRMO',  'victor.tomas@piat.csu.edu.ph',    'Male',   'active',   'VT', "What is your pet's name?",           'askal'],
        [24, 'maricel.alvarez',  $facultyPass, 'user',       'Ms. Maricel Alvarez',            'Administrative Aide IV',               'CEO',   'maricel.alvarez@piat.csu.edu.ph', 'Female', 'active',   'MA', "What is your mother's maiden name?", 'santiago'],
        [25, 'roberto.pineda',   $facultyPass, 'user',       'Mr. Roberto Pineda',             'Driver II',                            'CEO',   'roberto.pineda@piat.csu.edu.ph',  'Male',   'active',   'RP', 'What city were you born in?',        'solana'],
        [26, 'cynthia.flores',   $facultyPass, 'user',       'Ms. Cynthia Flores',             'Records Officer II',                   'REG',   'cynthia.flores@piat.csu.edu.ph',  'Female', 'active',   'CF', "What is your pet's name?",           'snowball'],
        [27, 'dante.santos',     $facultyPass, 'user',       'Mr. Dante Santos',               'Registrar Aide',                       'REG',   'dante.santos@piat.csu.edu.ph',    'Male',   'inactive', 'DS', "What is your mother's maiden name?", 'bautista'],
        [28, 'lorena.macaraeg',  $facultyPass, 'user',       'Ms. Lorena Macaraeg',            'Administrative Aide VI',               'REG',   'lorena.macaraeg@piat.csu.edu.ph', 'Female', 'active',   'LM', 'What city were you born in?',        'gattaran'],
        [29, 'ferdie.lacson',    $facultyPass, 'user',       'Mr. Ferdie Lacson',              'Bookkeeper',                           'ACCT',  'ferdie.lacson@piat.csu.edu.ph',   'Male',   'active',   'FL', "What is your pet's name?",           'buddy'],
        [30, 'norma.belen',      $facultyPass, 'user',       'Ms. Norma Belen',                'Cashier II',                           'ACCT',  'norma.belen@piat.csu.edu.ph',     'Female', 'active',   'NB', "What is your mother's maiden name?", 'navarro'],
        [31, 'edgar.trinidad',   $facultyPass, 'user',       'Mr. Edgar Trinidad',             'Budget Officer II',                    'ACCT',  'edgar.trinidad@piat.csu.edu.ph',  'Male',   'active',   'ET', 'What city were you born in?',        'iguig'],
        [32, 'marian.capili',    $facultyPass, 'user',       'Ms. Marian Capili',              'Personnel Specialist',                 'HR',    'marian.capili@piat.csu.edu.ph',   'Female', 'active',   'MC', "What is your pet's name?",           'peanut'],
        [33, 'joel.medrano',     $facultyPass, 'user',       'Mr. Joel Medrano',               'Administrative Aide V',                'HR',    'joel.medrano@piat.csu.edu.ph',    'Male',   'active',   'JM', "What is your mother's maiden name?", 'acosta'],
        [34, 'florence.perez',   $facultyPass, 'user',       'Ms. Florence Perez',             'Research Associate II',               'RDE',   'florence.perez@piat.csu.edu.ph',  'Female', 'active',   'FP', 'What city were you born in?',        'baggao'],
        [35, 'renato.quimpo',    $facultyPass, 'user',       'Mr. Renato Quimpo',              'Extension Coordinator',                'RDE',   'renato.quimpo@piat.csu.edu.ph',   'Male',   'active',   'RQ', "What is your pet's name?",           'tiger'],
        [36, 'lydia.campos',     $facultyPass, 'user',       'Ms. Lydia Campos',               'Research Assistant I',                 'RDE',   'lydia.campos@piat.csu.edu.ph',    'Female', 'inactive', 'LC', "What is your mother's maiden name?", 'peralta'],
        [37, 'mark.abad',        $facultyPass, 'user',       'Mr. Mark Abad',                  'Computer Operator II',                 'ITO',   'mark.abad@piat.csu.edu.ph',       'Male',   'active',   'MA', 'What city were you born in?',        'lasam'],
        [38, 'anna.basco',       $facultyPass, 'user',       'Ms. Anna Basco',                 'IT Support Specialist',                'ITO',   'anna.basco@piat.csu.edu.ph',      'Female', 'active',   'AB', "What is your pet's name?",           'coco'],
        [39, 'mario.valencia',   $facultyPass, 'user',       'Prof. Mario Valencia',           'Assistant Professor I',                'CAGRI', 'mario.valencia@piat.csu.edu.ph',  'Male',   'active',   'MV', "What is your mother's maiden name?", 'querubin'],
        [40, 'arlene.tugade',    $facultyPass, 'user',       'Ms. Arlene Tugade',              'Instructor I',                         'CAGRI', 'arlene.tugade@piat.csu.edu.ph',   'Female', 'active',   'AT', 'What city were you born in?',        'maddela'],
        [41, 'joseph.santos',    $facultyPass, 'user',       'Mr. Joseph Santos',              'Laboratory Technician',                'CAGRI', 'joseph.santos@piat.csu.edu.ph',   'Male',   'active',   'JS', "What is your pet's name?",           'blackie'],
        [42, 'helen.rafael',     $facultyPass, 'user',       'Prof. Helen Rafael',             'Instructor I',                         'CCJA',  'helen.rafael@piat.csu.edu.ph',    'Female', 'active',   'HR', "What is your mother's maiden name?", 'tamayo'],
        [43, 'ronnie.mercado',   $facultyPass, 'user',       'Mr. Ronnie Mercado',             'Assistant Professor II',               'CCJA',  'ronnie.mercado@piat.csu.edu.ph',  'Male',   'active',   'RM', 'What city were you born in?',        'alcala'],
        [44, 'jessica.lim',      $facultyPass, 'user',       'Ms. Jessica Lim',                'Instructor II',                        'CCJA',  'jessica.lim@piat.csu.edu.ph',     'Female', 'active',   'JL', "What is your pet's name?",           'simba'],
        [45, 'arnold.fajardo',   $facultyPass, 'user',       'Mr. Arnold Fajardo',             'Instructor I',                         'CICS',  'arnold.fajardo@piat.csu.edu.ph',  'Male',   'active',   'AF', "What is your mother's maiden name?", 'cuevas'],
        [46, 'kristine.sy',      $facultyPass, 'user',       'Ms. Kristine Sy',                'Instructor II',                        'CICS',  'kristine.sy@piat.csu.edu.ph',     'Female', 'active',   'KS', 'What city were you born in?',        'tuao'],
        [47, 'erwin.cabantog',   $facultyPass, 'user',       'Mr. Erwin Cabantog',             'Laboratory Technician',                'CICS',  'erwin.cabantog@piat.csu.edu.ph',  'Male',   'inactive', 'EC', "What is your pet's name?",           'mochi'],
        [48, 'maria.santos2',    $facultyPass, 'user',       'Prof. Maria Santos',             'Assistant Professor I',                'CED',   'maria.santos2@piat.csu.edu.ph',   'Female', 'active',   'MS', "What is your mother's maiden name?", 'delos santos'],
        [49, 'oliver.navarro',   $facultyPass, 'user',       'Mr. Oliver Navarro',             'Instructor III',                       'CED',   'oliver.navarro@piat.csu.edu.ph',  'Male',   'active',   'ON', 'What city were you born in?',        'enrile'],
        [50, 'analiza.bueno',    $facultyPass, 'user',       'Ms. Analiza Bueno',              'Assistant Professor II',               'CED',   'analiza.bueno@piat.csu.edu.ph',   'Female', 'active',   'AB', "What is your pet's name?",           'nemo'],
    ];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO users (id, username, password, role, name, position, department_id, email, gender, status, avatar, security_question, security_answer)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($users as $u) {
        // Hash the security answer too
        $u[12] = password_hash(strtolower(trim($u[12])), PASSWORD_BCRYPT);
        $stmt->execute($u);
    }
    $log[] = count($users) . ' users seeded.';

    // ----------------------------------------------------------
    // TIMELINES
    // ----------------------------------------------------------
    $timelines = [
        [1, '2024-2025', '1st Semester', '2024-08-01', '2024-12-31', '2025-01-10', 'closed'],
        [2, '2024-2025', '2nd Semester', '2025-01-06', '2025-05-31', '2025-06-10', 'closed'],
        [3, '2025-2026', '1st Semester', '2025-08-01', '2025-12-31', '2026-01-10', 'closed'],
        [4, '2025-2026', '2nd Semester', '2026-01-05', '2026-05-31', '2026-06-15', 'open'],
    ];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO timelines (id, academic_year, semester, start_date, end_date, submission_deadline, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($timelines as $t) {
        $stmt->execute($t);
    }
    $log[] = count($timelines) . ' timelines seeded.';

    // ----------------------------------------------------------
    // KPI ITEMS
    // ----------------------------------------------------------
    $kpis = [
        // Core
        ['core',      'Instruction',      'Conduct effective and efficient delivery of instruction',               '100%',                      'Quantity/Quality/Timeliness/Efficiency/Effectiveness'],
        ['core',      'Instruction',      'Prepare and submit lesson plans and course syllabi on time',           '100% on time',              'Timeliness'],
        ['core',      'Instruction',      'Administer and check examinations and quizzes',                        '100% administered',         'Quantity/Quality'],
        ['core',      'Student Services', 'Advise and assist students in academic concerns',                      'All students assisted',     'Effectiveness'],
        ['core',      'Research',         'Conduct research relevant to academic programs',                       'At least 1 completed research/year', 'Quality/Quantity'],
        // Strategic
        ['strategic', 'Accreditation',   'Participate in accreditation activities and preparations',             '100% participation',        'Timeliness/Effectiveness'],
        ['strategic', 'Linkages & MOA',  'Establish partnership with industry partners',                         'At least 1 MOA/year',       'Quantity'],
        ['strategic', 'Sustainability',  'Implement green campus initiatives',                                    'At least 2 initiatives/semester', 'Effectiveness'],
        ['strategic', 'Innovation',      'Develop and adopt innovative teaching strategies',                     'At least 1 per semester',   'Quality'],
        // Support
        ['support',   'Administrative',  'Attend and participate in faculty/staff meetings',                     '100% attendance',           'Timeliness'],
        ['support',   'Administrative',  'Submit required reports on time',                                      '100% on time',              'Timeliness'],
        ['support',   'Community Extension', 'Participate in community extension activities',                    'At least 8 hours/semester', 'Quantity'],
        ['support',   'Capacity Building', 'Attend trainings, seminars, and workshops',                          'At least 2/year',           'Quantity'],
    ];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO kpi_items (category, mfo, success_indicator, target, measure)
         VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($kpis as $k) {
        $stmt->execute($k);
    }
    $log[] = count($kpis) . ' KPI items seeded.';

    // ----------------------------------------------------------
    // SAMPLE IPCR FORMS
    // ----------------------------------------------------------
    $ipcr_forms = [
        // id, user_id, timeline_id, covered_period, date_submitted, status, overall_rating, remarks, reviewed_by, reviewed_at
        [1, 8,  4, 'January - June 2026', '2026-06-10', 'approved',    4.5, null, 2, '2026-06-12 09:00:00'],
        [2, 9,  4, 'January - June 2026', '2026-06-08', 'approved',    4.2, null, 2, '2026-06-10 10:00:00'],
        [3, 10, 4, 'January - June 2026', '2026-06-12', 'reviewed',    4.0, null, 2, '2026-06-14 14:00:00'],
        [4, 12, 4, 'January - June 2026', '2026-06-14', 'pending',     0.0, null, null, null],
        [5, 13, 4, 'January - June 2026', '2026-06-11', 'disapproved', 2.8, 'Syllabi submitted late. Please revise and resubmit.', 4, '2026-06-13 11:00:00'],
        [6, 14, 4, 'January - June 2026', '2026-06-15', 'approved',    4.8, null, 5, '2026-06-17 09:00:00'],
        [7, 15, 4, 'January - June 2026', '2026-06-13', 'reviewed',    4.1, null, 6, '2026-06-15 15:00:00'],
    ];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO ipcr_forms (id, user_id, timeline_id, covered_period, date_submitted, status, overall_rating, remarks, reviewed_by, reviewed_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($ipcr_forms as $f) {
        $stmt->execute($f);
    }
    $log[] = count($ipcr_forms) . ' IPCR forms seeded.';

    // ----------------------------------------------------------
    // SAMPLE IPCR ITEMS
    // ----------------------------------------------------------
    $ipcr_items = [
        // ipcr_form_id, kpi_id, function_type, success_indicator, accomplishment, rating, remarks
        // IPCR 1 (Ramon Castillo - Accounting)
        [1, null, 'core',      'Financial Transaction Processing',      'Processed all financial transactions accurately and on time', 5, 'Outstanding'],
        [1, null, 'core',      'Financial Report Submission',           'Submitted all required financial reports before deadlines',   4, 'Very Satisfactory'],
        [1, null, 'strategic', 'Accreditation Support',                 'Assisted in preparation of accreditation financial documents',4, 'Very Satisfactory'],
        [1, null, 'support',   'Meeting Attendance',                    'Attended all scheduled meetings with 100% participation',    5, 'Outstanding'],
        [1, null, 'support',   'BIR and COA Report Submission',         'Submitted BIR and COA reports on time',                     5, 'Outstanding'],
        // IPCR 2 (Sofia Mendoza - HR)
        [2, null, 'core',      'HR Document Processing',                'Processed recruitment, appointment and HR documents for 15 employees', 4, 'Very Satisfactory'],
        [2, null, 'strategic', 'HR Linkages',                           'Coordinated with partner agencies for HR linkages',          4, 'Very Satisfactory'],
        [2, null, 'support',   'Meeting Attendance',                    'Perfect attendance in all meetings and HR activities',       5, 'Outstanding'],
        [2, null, 'support',   'Capacity Building',                     'Attended 3 HR seminars and capacity building events',       5, 'Outstanding'],
        // IPCR 3 (Diego Torres - ITO)
        [3, null, 'core',      'System Uptime Maintenance',             'Maintained and ensured 99% uptime of university systems',   4, 'Very Satisfactory'],
        [3, null, 'strategic', 'System Development',                    'Developed and deployed student information system module',  5, 'Outstanding'],
        [3, null, 'support',   'Meeting Attendance',                    'Attended all IT-related meetings and coordinations',        4, 'Very Satisfactory'],
        [3, null, 'support',   'IT Report Submission',                  'Submitted IT inventory and status reports on schedule',     3, 'Satisfactory'],
        // IPCR 4 (Marco Dela Cruz - Agriculture - pending)
        [4, null, 'core',      'Course Delivery',                       'Taught 5 courses in Agriculture with an average passing rate of 92%', 4, 'Very Satisfactory'],
        [4, null, 'core',      'Syllabus Submission',                   'Submitted syllabi and lesson plans before semester started', 5, 'Outstanding'],
        [4, null, 'core',      'Student Advising',                      'Advised 35 students on academic and career matters',        4, 'Very Satisfactory'],
        [4, null, 'strategic', 'Accreditation Participation',           'Prepared accreditation portfolios for Agriculture program', 4, 'Very Satisfactory'],
        [4, null, 'support',   'Extension Activity',                    'Led extension activity at Barangay Mabantad – 16 hours',   5, 'Outstanding'],
        [4, null, 'support',   'Conference Attendance',                 'Attended 2 national agriculture conferences',               4, 'Very Satisfactory'],
        // IPCR 5 (Jasmine Aquino - CED - disapproved)
        [5, null, 'core',      'Course Delivery',                       'Conducted classes; some students failed due to absences',   3, 'Satisfactory'],
        [5, null, 'core',      'Syllabus Submission',                   'Submitted syllabi 2 weeks late',                            2, 'Unsatisfactory'],
        [5, null, 'strategic', 'Accreditation Participation',           'Partially assisted in accreditation preparation',           3, 'Satisfactory'],
        [5, null, 'support',   'Meeting Attendance',                    'Attended most meetings; missed 2 due to health reasons',   3, 'Satisfactory'],
        [5, null, 'support',   'Report Submission',                     'Submitted reports on time',                                 4, 'Very Satisfactory'],
        // IPCR 6 (Patrick Soriano - CICS - approved)
        [6, null, 'core',      'Course Delivery',                       'Conducted all scheduled classes with 98% student satisfaction rating', 5, 'Outstanding'],
        [6, null, 'core',      'Syllabus Submission',                   'Submitted all syllabi and course materials on Day 1 of semester', 5, 'Outstanding'],
        [6, null, 'core',      'Research Completion',                   'Completed research on AI integration in higher education',  5, 'Outstanding'],
        [6, null, 'strategic', 'Teaching Innovation',                   'Developed innovative programming assessment tools',         5, 'Outstanding'],
        [6, null, 'support',   'Community Extension',                   'Conducted ICT literacy training in local community – 10 hours', 5, 'Outstanding'],
        [6, null, 'support',   'Seminar Attendance',                    'Attended 3 ICT seminars and 1 international webinar',      4, 'Very Satisfactory'],
        // IPCR 7 (Lina Garcia - CCJA - reviewed)
        [7, null, 'core',      'Course Delivery',                       'Handled 4 CJA subjects with average 88% pass rate',        4, 'Very Satisfactory'],
        [7, null, 'core',      'Student Advising',                      'Provided academic guidance to 28 criminology students',    4, 'Very Satisfactory'],
        [7, null, 'strategic', 'Industry Linkages',                     'Coordinated with PNP for student internship programs',     5, 'Outstanding'],
        [7, null, 'support',   'Community Extension',                   'Participated in drug awareness campaign – 8 hours',        4, 'Very Satisfactory'],
        [7, null, 'support',   'Conference Attendance',                 'Attended national CJA conference',                         4, 'Very Satisfactory'],
    ];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO ipcr_items (ipcr_form_id, kpi_id, function_type, success_indicator, accomplishment, rating, remarks)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($ipcr_items as $item) {
        $stmt->execute($item);
    }
    $log[] = count($ipcr_items) . ' IPCR items seeded.';

    // ----------------------------------------------------------
    // SAMPLE OPCR FORMS
    // ----------------------------------------------------------
    $opcr_forms = [
        // id, admin_id, department_id, timeline_id, covered_period, date_submitted, status, overall_rating, reviewed_by, reviewed_at
        [1, 3, 'CAGRI', 4, 'January - June 2026', '2026-06-10', 'approved', 4.4, 1, '2026-06-13 10:00:00'],
        [2, 5, 'CICS',  4, 'January - June 2026', '2026-06-12', 'reviewed', 4.6, 1, '2026-06-15 14:00:00'],
    ];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO opcr_forms (id, admin_id, department_id, timeline_id, covered_period, date_submitted, status, overall_rating, reviewed_by, reviewed_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($opcr_forms as $f) {
        $stmt->execute($f);
    }
    $log[] = count($opcr_forms) . ' OPCR forms seeded.';

    // ----------------------------------------------------------
    // SAMPLE OPCR ITEMS
    // ----------------------------------------------------------
    $opcr_items = [
        // opcr_form_id, function_type, mfo, success_indicator, target, actual, budget, rating
        [1, 'core',     'Instruction', '100% delivery of prescribed curriculum',    '100%',               '100% - all 15 faculty completed course delivery',       0,     5],
        [1, 'core',     'Research',    'Conduct and complete at least 2 research projects', '2 research', '3 research projects completed and published',            50000, 5],
        [1, 'strategic','Accreditation','Achieve Level II reaccreditation by AACCUP','Level II Accreditation','Successfully maintained Level II accreditation',     30000, 5],
        [1, 'support',  'Administrative','Submit all required reports on time',       '100% on time',       '95% on-time submission; 1 delayed due to data issues',  5000,  4],
        [2, 'core',     'Instruction', '100% course delivery with satisfaction ≥ 85%','85% satisfaction',  'Average student satisfaction rating: 94%',              0,     5],
        [2, 'core',     'Research',    'Publish at least 1 research in indexed journal','1 publication',    '2 research papers published in CHED-indexed journals',  40000, 5],
        [2, 'strategic','Innovation',  'Develop and deploy at least 1 campus technology system','1 system deployed','AOPCR/IPCR system developed and piloted',       80000, 5],
        [2, 'support',  'Extension',   'Conduct ICT literacy community extension',   'At least 2 barangays','Conducted ICT training in 3 barangays',                15000, 5],
    ];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO opcr_items (opcr_form_id, function_type, mfo, success_indicator, target, actual, budget, rating)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($opcr_items as $item) {
        $stmt->execute($item);
    }
    $log[] = count($opcr_items) . ' OPCR items seeded.';

    // ----------------------------------------------------------
    // SAMPLE ACTIVITY LOGS
    // ----------------------------------------------------------
    $logs = [
        [8,  'Logged in successfully',                  '127.0.0.1'],
        [8,  'Submitted IPCR form for 2nd Semester 2025-2026', '127.0.0.1'],
        [8,  'Updated IPCR accomplishments',            '127.0.0.1'],
        [9,  'Logged in successfully',                  '127.0.0.1'],
        [9,  'Submitted IPCR form for 2nd Semester 2025-2026', '127.0.0.1'],
        [2,  'Approved IPCR of Mr. Ramon Castillo',    '127.0.0.1'],
        [2,  'Approved IPCR of Ms. Sofia Mendoza',     '127.0.0.1'],
        [1,  'Logged in successfully',                  '127.0.0.1'],
        [1,  'Approved OPCR of College of Agriculture', '127.0.0.1'],
    ];

    $stmt = $db->prepare('INSERT IGNORE INTO activity_logs (user_id, activity, ip_address) VALUES (?, ?, ?)');
    foreach ($logs as $l) {
        $stmt->execute($l);
    }
    $log[] = count($logs) . ' activity log entries seeded.';

    // ----------------------------------------------------------
    // SAMPLE NOTIFICATIONS
    // ----------------------------------------------------------
    $notifs = [
        [8,  'info',    'IPCR submission deadline is on June 15, 2026', 1],
        [8,  'success', 'Your IPCR form has been approved by the administrator.', 0],
        [9,  'success', 'Your IPCR form has been approved.', 0],
        [10, 'info',    'Your IPCR is currently under review.', 0],
        [12, 'warning', 'Your IPCR is pending review by your department head.', 0],
        [13, 'danger',  'Your IPCR has been disapproved. Please check the remarks and resubmit.', 0],
        [14, 'success', 'Your IPCR form has been approved. Outstanding performance!', 0],
    ];

    $stmt = $db->prepare('INSERT IGNORE INTO notifications (user_id, type, message, is_read) VALUES (?, ?, ?, ?)');
    foreach ($notifs as $n) {
        $stmt->execute($n);
    }
    $log[] = count($notifs) . ' notifications seeded.';

    return $log;
}
