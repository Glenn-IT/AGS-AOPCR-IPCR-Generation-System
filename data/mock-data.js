// CSU-PIAT AOPCR/IPCR System - Mock Data
// Cagayan State University - Piat Campus

const CSU_PIAT_INFO = {
  university: "Cagayan State University",
  campus: "Piat Campus",
  shortName: "CSU-Piat",
  address: "Ytawes District, Piat, Cagayan, Philippines",
  founded: 1954,
  website: "https://piat.csu.edu.ph",
  vision: "A premier university in the Asia-Pacific region producing globally competitive graduates and research innovations for sustainable development.",
  mission: "Cagayan State University is committed to transform the lives of people and communities through higher technological and professional education, research, extension and production services.",
  tagline: "Excellence in Service"
};

const DEPARTMENTS = [
  { id: "CEO", name: "Office of the Campus Executive Officer", type: "admin" },
  { id: "REG", name: "Registrar's Office", type: "admin" },
  { id: "ACCT", name: "Accounting Office", type: "admin" },
  { id: "HR", name: "Human Resource Office", type: "admin" },
  { id: "RDE", name: "Research, Development & Extension Office", type: "admin" },
  { id: "ITO", name: "IT Office", type: "admin" },
  { id: "PRMO", name: "Partnership & Resource Mobilization Office", type: "admin" },
  { id: "CAGRI", name: "College of Agriculture", type: "academic" },
  { id: "CCJA", name: "College of Criminal Justice Administration", type: "academic" },
  { id: "CICS", name: "College of Information and Computing Sciences", type: "academic" },
  { id: "CED", name: "College of Education", type: "academic" }
];

const USERS_DATA = [
  // Super Admin
  {
    id: 1, username: "superadmin", password: "admin123", role: "superadmin",
    name: "System Administrator", position: "System Administrator",
    department: "CEO", email: "sysadmin@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "SA", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "santos", lastLogin: "2026-06-19 08:00:00", createdAt: "2024-01-01"
  },
  // Admins
  {
    id: 2, username: "admin", password: "admin123", role: "admin",
    name: "Dr. Maria Santos", position: "Campus Executive Officer",
    department: "CEO", email: "maria.santos@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "MS", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "reyes", lastLogin: "2026-06-19 07:45:00", createdAt: "2024-01-05"
  },
  {
    id: 3, username: "jose.reyes", password: "admin123", role: "admin",
    name: "Prof. Jose Reyes", position: "Dean, College of Agriculture",
    department: "CAGRI", email: "jose.reyes@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "JR", securityQuestion: "What city were you born in?",
    securityAnswer: "piat", lastLogin: "2026-06-18 09:00:00", createdAt: "2024-01-10"
  },
  {
    id: 4, username: "anna.cruz", password: "admin123", role: "admin",
    name: "Prof. Anna Cruz", position: "Dean, College of Education",
    department: "CED", email: "anna.cruz@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "AC", securityQuestion: "What is your pet's name?",
    securityAnswer: "lucky", lastLogin: "2026-06-17 10:15:00", createdAt: "2024-01-12"
  },
  {
    id: 5, username: "carlo.bautista", password: "admin123", role: "admin",
    name: "Prof. Carlo Bautista", position: "Dean, College of Information and Computing Sciences",
    department: "CICS", email: "carlo.bautista@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "CB", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "dela cruz", lastLogin: "2026-06-19 08:30:00", createdAt: "2024-01-15"
  },
  {
    id: 6, username: "luz.domingo", password: "admin123", role: "admin",
    name: "Prof. Luz Domingo", position: "Dean, College of Criminal Justice Administration",
    department: "CCJA", email: "luz.domingo@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "LD", securityQuestion: "What city were you born in?",
    securityAnswer: "tuguegarao", lastLogin: "2026-06-16 11:00:00", createdAt: "2024-01-20"
  },
  {
    id: 7, username: "carla.villanueva", password: "admin123", role: "admin",
    name: "Ms. Carla Villanueva", position: "University Registrar",
    department: "REG", email: "carla.villanueva@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "CV", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "garcia", lastLogin: "2026-06-19 07:30:00", createdAt: "2024-01-22"
  },
  // Users / Faculty & Staff
  {
    id: 8, username: "faculty", password: "faculty123", role: "user",
    name: "Mr. Ramon Castillo", position: "Accountant III",
    department: "ACCT", email: "ramon.castillo@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "RC", securityQuestion: "What is your pet's name?",
    securityAnswer: "brownie", lastLogin: "2026-06-19 08:15:00", createdAt: "2024-02-01"
  },
  {
    id: 9, username: "sofia.mendoza", password: "faculty123", role: "user",
    name: "Ms. Sofia Mendoza", position: "Human Resource Management Officer II",
    department: "HR", email: "sofia.mendoza@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "SM", securityQuestion: "What city were you born in?",
    securityAnswer: "cagayan", lastLogin: "2026-06-18 08:45:00", createdAt: "2024-02-05"
  },
  {
    id: 10, username: "diego.torres", password: "faculty123", role: "user",
    name: "Mr. Diego Torres", position: "IT Officer I",
    department: "ITO", email: "diego.torres@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "DT", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "valdez", lastLogin: "2026-06-19 07:00:00", createdAt: "2024-02-10"
  },
  {
    id: 11, username: "elena.ramos", password: "faculty123", role: "user",
    name: "Dr. Elena Ramos", position: "Research Director",
    department: "RDE", email: "elena.ramos@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "ER", securityQuestion: "What city were you born in?",
    securityAnswer: "manila", lastLogin: "2026-06-17 09:30:00", createdAt: "2024-02-12"
  },
  {
    id: 12, username: "marco.delacruz", password: "faculty123", role: "user",
    name: "Prof. Marco Dela Cruz", position: "Assistant Professor II",
    department: "CAGRI", email: "marco.delacruz@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "MD", securityQuestion: "What is your pet's name?",
    securityAnswer: "whitey", lastLogin: "2026-06-18 10:00:00", createdAt: "2024-02-15"
  },
  {
    id: 13, username: "jasmine.aquino", password: "faculty123", role: "user",
    name: "Prof. Jasmine Aquino", position: "Instructor I",
    department: "CED", email: "jasmine.aquino@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "JA", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "pascual", lastLogin: "2026-06-19 09:00:00", createdAt: "2024-02-18"
  },
  {
    id: 14, username: "patrick.soriano", password: "faculty123", role: "user",
    name: "Prof. Patrick Soriano", position: "Assistant Professor I",
    department: "CICS", email: "patrick.soriano@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "PS", securityQuestion: "What city were you born in?",
    securityAnswer: "lal-lo", lastLogin: "2026-06-17 08:30:00", createdAt: "2024-02-20"
  },
  {
    id: 15, username: "lina.garcia", password: "faculty123", role: "user",
    name: "Prof. Lina Garcia", position: "Instructor II",
    department: "CCJA", email: "lina.garcia@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "LG", securityQuestion: "What is your pet's name?",
    securityAnswer: "mittens", lastLogin: "2026-06-16 09:45:00", createdAt: "2024-02-22"
  },
  {
    id: 16, username: "anthony.pascual", password: "faculty123", role: "user",
    name: "Mr. Anthony Pascual", position: "Administrative Aide VI",
    department: "ACCT", email: "anthony.pascual@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "AP", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "miranda", lastLogin: "2026-06-18 07:50:00", createdAt: "2024-03-01"
  },
  {
    id: 17, username: "jennifer.vilar", password: "faculty123", role: "user",
    name: "Ms. Jennifer Vilar", position: "Administrative Assistant II",
    department: "REG", email: "jennifer.vilar@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "JV", securityQuestion: "What city were you born in?",
    securityAnswer: "sto. nino", lastLogin: "2026-06-19 08:00:00", createdAt: "2024-03-05"
  },
  {
    id: 18, username: "bernard.hernandez", password: "faculty123", role: "user",
    name: "Mr. Bernard Hernandez", position: "Administrative Officer I",
    department: "HR", email: "bernard.hernandez@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "BH", securityQuestion: "What is your pet's name?",
    securityAnswer: "rex", lastLogin: "2026-06-17 10:00:00", createdAt: "2024-03-08"
  },
  {
    id: 19, username: "rachel.espiritu", password: "faculty123", role: "user",
    name: "Ms. Rachel Espiritu", position: "Instructor I",
    department: "CAGRI", email: "rachel.espiritu@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "RE", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "flores", lastLogin: "2026-06-18 09:15:00", createdAt: "2024-03-10"
  },
  {
    id: 20, username: "theresa.magno", password: "faculty123", role: "user",
    name: "Ms. Theresa Magno", position: "Instructor II",
    department: "CED", email: "theresa.magno@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "TM", securityQuestion: "What city were you born in?",
    securityAnswer: "aparri", lastLogin: "2026-06-16 08:30:00", createdAt: "2024-03-12"
  },
  // --- PRMO ---
  {
    id: 21, username: "nelson.cabrera", password: "faculty123", role: "user",
    name: "Mr. Nelson Cabrera", position: "Administrative Officer III",
    department: "PRMO", email: "nelson.cabrera@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "NC", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "delos reyes", lastLogin: "2026-06-18 08:00:00", createdAt: "2024-03-15"
  },
  {
    id: 22, username: "gloria.ocampo", password: "faculty123", role: "user",
    name: "Ms. Gloria Ocampo", position: "Administrative Assistant III",
    department: "PRMO", email: "gloria.ocampo@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "GO", securityQuestion: "What city were you born in?",
    securityAnswer: "piat", lastLogin: "2026-06-17 09:00:00", createdAt: "2024-03-18"
  },
  {
    id: 23, username: "victor.tomas", password: "faculty123", role: "user",
    name: "Mr. Victor Tomas", position: "Project Development Officer II",
    department: "PRMO", email: "victor.tomas@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "VT", securityQuestion: "What is your pet's name?",
    securityAnswer: "askal", lastLogin: "2026-06-16 10:00:00", createdAt: "2024-03-20"
  },
  // --- CEO ---
  {
    id: 24, username: "maricel.alvarez", password: "faculty123", role: "user",
    name: "Ms. Maricel Alvarez", position: "Administrative Aide IV",
    department: "CEO", email: "maricel.alvarez@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "MA", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "santiago", lastLogin: "2026-06-19 07:30:00", createdAt: "2024-03-22"
  },
  {
    id: 25, username: "roberto.pineda", password: "faculty123", role: "user",
    name: "Mr. Roberto Pineda", position: "Driver II",
    department: "CEO", email: "roberto.pineda@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "RP", securityQuestion: "What city were you born in?",
    securityAnswer: "solana", lastLogin: "2026-06-18 06:30:00", createdAt: "2024-03-25"
  },
  // --- REG ---
  {
    id: 26, username: "cynthia.flores", password: "faculty123", role: "user",
    name: "Ms. Cynthia Flores", position: "Records Officer II",
    department: "REG", email: "cynthia.flores@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "CF", securityQuestion: "What is your pet's name?",
    securityAnswer: "snowball", lastLogin: "2026-06-19 08:00:00", createdAt: "2024-04-01"
  },
  {
    id: 27, username: "dante.santos", password: "faculty123", role: "user",
    name: "Mr. Dante Santos", position: "Registrar Aide",
    department: "REG", email: "dante.santos@piat.csu.edu.ph", gender: "Male",
    status: "inactive", avatar: "DS", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "bautista", lastLogin: "2026-05-10 08:00:00", createdAt: "2024-04-05"
  },
  {
    id: 28, username: "lorena.macaraeg", password: "faculty123", role: "user",
    name: "Ms. Lorena Macaraeg", position: "Administrative Aide VI",
    department: "REG", email: "lorena.macaraeg@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "LM", securityQuestion: "What city were you born in?",
    securityAnswer: "gattaran", lastLogin: "2026-06-18 08:30:00", createdAt: "2024-04-08"
  },
  // --- ACCT ---
  {
    id: 29, username: "ferdie.lacson", password: "faculty123", role: "user",
    name: "Mr. Ferdie Lacson", position: "Bookkeeper",
    department: "ACCT", email: "ferdie.lacson@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "FL", securityQuestion: "What is your pet's name?",
    securityAnswer: "buddy", lastLogin: "2026-06-17 07:45:00", createdAt: "2024-04-10"
  },
  {
    id: 30, username: "norma.belen", password: "faculty123", role: "user",
    name: "Ms. Norma Belen", position: "Cashier II",
    department: "ACCT", email: "norma.belen@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "NB", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "navarro", lastLogin: "2026-06-19 08:00:00", createdAt: "2024-04-12"
  },
  {
    id: 31, username: "edgar.trinidad", password: "faculty123", role: "user",
    name: "Mr. Edgar Trinidad", position: "Budget Officer II",
    department: "ACCT", email: "edgar.trinidad@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "ET", securityQuestion: "What city were you born in?",
    securityAnswer: "iguig", lastLogin: "2026-06-16 09:00:00", createdAt: "2024-04-15"
  },
  // --- HR ---
  {
    id: 32, username: "marian.capili", password: "faculty123", role: "user",
    name: "Ms. Marian Capili", position: "Personnel Specialist",
    department: "HR", email: "marian.capili@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "MC", securityQuestion: "What is your pet's name?",
    securityAnswer: "peanut", lastLogin: "2026-06-18 09:00:00", createdAt: "2024-04-18"
  },
  {
    id: 33, username: "joel.medrano", password: "faculty123", role: "user",
    name: "Mr. Joel Medrano", position: "Administrative Aide V",
    department: "HR", email: "joel.medrano@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "JM", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "acosta", lastLogin: "2026-06-17 08:00:00", createdAt: "2024-04-20"
  },
  // --- RDE ---
  {
    id: 34, username: "florence.perez", password: "faculty123", role: "user",
    name: "Ms. Florence Perez", position: "Research Associate II",
    department: "RDE", email: "florence.perez@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "FP", securityQuestion: "What city were you born in?",
    securityAnswer: "baggao", lastLogin: "2026-06-19 08:00:00", createdAt: "2024-04-22"
  },
  {
    id: 35, username: "renato.quimpo", password: "faculty123", role: "user",
    name: "Mr. Renato Quimpo", position: "Extension Coordinator",
    department: "RDE", email: "renato.quimpo@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "RQ", securityQuestion: "What is your pet's name?",
    securityAnswer: "tiger", lastLogin: "2026-06-18 07:30:00", createdAt: "2024-04-25"
  },
  {
    id: 36, username: "lydia.campos", password: "faculty123", role: "user",
    name: "Ms. Lydia Campos", position: "Research Assistant I",
    department: "RDE", email: "lydia.campos@piat.csu.edu.ph", gender: "Female",
    status: "inactive", avatar: "LC", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "peralta", lastLogin: "2026-04-10 09:00:00", createdAt: "2024-04-28"
  },
  // --- ITO ---
  {
    id: 37, username: "mark.abad", password: "faculty123", role: "user",
    name: "Mr. Mark Abad", position: "Computer Operator II",
    department: "ITO", email: "mark.abad@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "MA", securityQuestion: "What city were you born in?",
    securityAnswer: "lasam", lastLogin: "2026-06-19 07:00:00", createdAt: "2024-05-01"
  },
  {
    id: 38, username: "anna.basco", password: "faculty123", role: "user",
    name: "Ms. Anna Basco", position: "IT Support Specialist",
    department: "ITO", email: "anna.basco@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "AB", securityQuestion: "What is your pet's name?",
    securityAnswer: "coco", lastLogin: "2026-06-18 08:00:00", createdAt: "2024-05-05"
  },
  // --- CAGRI ---
  {
    id: 39, username: "mario.valencia", password: "faculty123", role: "user",
    name: "Prof. Mario Valencia", position: "Assistant Professor I",
    department: "CAGRI", email: "mario.valencia@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "MV", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "querubin", lastLogin: "2026-06-17 09:00:00", createdAt: "2024-05-08"
  },
  {
    id: 40, username: "arlene.tugade", password: "faculty123", role: "user",
    name: "Ms. Arlene Tugade", position: "Instructor I",
    department: "CAGRI", email: "arlene.tugade@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "AT", securityQuestion: "What city were you born in?",
    securityAnswer: "maddela", lastLogin: "2026-06-16 10:00:00", createdAt: "2024-05-10"
  },
  {
    id: 41, username: "joseph.santos", password: "faculty123", role: "user",
    name: "Mr. Joseph Santos", position: "Laboratory Technician",
    department: "CAGRI", email: "joseph.santos@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "JS", securityQuestion: "What is your pet's name?",
    securityAnswer: "blackie", lastLogin: "2026-06-15 08:30:00", createdAt: "2024-05-12"
  },
  // --- CCJA ---
  {
    id: 42, username: "helen.rafael", password: "faculty123", role: "user",
    name: "Prof. Helen Rafael", position: "Instructor I",
    department: "CCJA", email: "helen.rafael@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "HR", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "tamayo", lastLogin: "2026-06-19 09:00:00", createdAt: "2024-05-15"
  },
  {
    id: 43, username: "ronnie.mercado", password: "faculty123", role: "user",
    name: "Mr. Ronnie Mercado", position: "Assistant Professor II",
    department: "CCJA", email: "ronnie.mercado@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "RM", securityQuestion: "What city were you born in?",
    securityAnswer: "alcala", lastLogin: "2026-06-18 08:30:00", createdAt: "2024-05-18"
  },
  {
    id: 44, username: "jessica.lim", password: "faculty123", role: "user",
    name: "Ms. Jessica Lim", position: "Instructor II",
    department: "CCJA", email: "jessica.lim@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "JL", securityQuestion: "What is your pet's name?",
    securityAnswer: "simba", lastLogin: "2026-06-17 09:30:00", createdAt: "2024-05-20"
  },
  // --- CICS ---
  {
    id: 45, username: "arnold.fajardo", password: "faculty123", role: "user",
    name: "Mr. Arnold Fajardo", position: "Instructor I",
    department: "CICS", email: "arnold.fajardo@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "AF", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "cuevas", lastLogin: "2026-06-19 07:30:00", createdAt: "2024-05-22"
  },
  {
    id: 46, username: "kristine.sy", password: "faculty123", role: "user",
    name: "Ms. Kristine Sy", position: "Instructor II",
    department: "CICS", email: "kristine.sy@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "KS", securityQuestion: "What city were you born in?",
    securityAnswer: "tuao", lastLogin: "2026-06-18 09:00:00", createdAt: "2024-05-25"
  },
  {
    id: 47, username: "erwin.cabantog", password: "faculty123", role: "user",
    name: "Mr. Erwin Cabantog", position: "Laboratory Technician",
    department: "CICS", email: "erwin.cabantog@piat.csu.edu.ph", gender: "Male",
    status: "inactive", avatar: "EC", securityQuestion: "What is your pet's name?",
    securityAnswer: "mochi", lastLogin: "2026-04-20 08:00:00", createdAt: "2024-05-28"
  },
  // --- CED ---
  {
    id: 48, username: "maria.santos2", password: "faculty123", role: "user",
    name: "Prof. Maria Santos", position: "Assistant Professor I",
    department: "CED", email: "maria.santos2@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "MS", securityQuestion: "What is your mother's maiden name?",
    securityAnswer: "delos santos", lastLogin: "2026-06-19 09:00:00", createdAt: "2024-06-01"
  },
  {
    id: 49, username: "oliver.navarro", password: "faculty123", role: "user",
    name: "Mr. Oliver Navarro", position: "Instructor III",
    department: "CED", email: "oliver.navarro@piat.csu.edu.ph", gender: "Male",
    status: "active", avatar: "ON", securityQuestion: "What city were you born in?",
    securityAnswer: "enrile", lastLogin: "2026-06-18 08:00:00", createdAt: "2024-06-05"
  },
  {
    id: 50, username: "analiza.bueno", password: "faculty123", role: "user",
    name: "Ms. Analiza Bueno", position: "Assistant Professor II",
    department: "CED", email: "analiza.bueno@piat.csu.edu.ph", gender: "Female",
    status: "active", avatar: "AB", securityQuestion: "What is your pet's name?",
    securityAnswer: "nemo", lastLogin: "2026-06-17 09:30:00", createdAt: "2024-06-08"
  }
];

const KPI_CATEGORIES = {
  core: [
    { id: "c1", category: "Core Function", mfo: "Instruction", successIndicator: "Conduct effective and efficient delivery of instruction", target: "100%", measure: "Quantity/Quality/Timeliness/Efficiency/Effectiveness" },
    { id: "c2", category: "Core Function", mfo: "Instruction", successIndicator: "Prepare and submit lesson plans and course syllabi on time", target: "100% on time", measure: "Timeliness" },
    { id: "c3", category: "Core Function", mfo: "Instruction", successIndicator: "Administer and check examinations and quizzes", target: "100% administered", measure: "Quantity/Quality" },
    { id: "c4", category: "Core Function", mfo: "Student Services", successIndicator: "Advise and assist students in academic concerns", target: "All students assisted", measure: "Effectiveness" },
    { id: "c5", category: "Core Function", mfo: "Research", successIndicator: "Conduct research relevant to academic programs", target: "At least 1 completed research/year", measure: "Quality/Quantity" }
  ],
  strategic: [
    { id: "s1", category: "Strategic Function", mfo: "Accreditation", successIndicator: "Participate in accreditation activities and preparations", target: "100% participation", measure: "Timeliness/Effectiveness" },
    { id: "s2", category: "Strategic Function", mfo: "Linkages & MOA", successIndicator: "Establish partnership with industry partners", target: "At least 1 MOA/year", measure: "Quantity" },
    { id: "s3", category: "Strategic Function", mfo: "Sustainability", successIndicator: "Implement green campus initiatives", target: "At least 2 initiatives/semester", measure: "Effectiveness" },
    { id: "s4", category: "Strategic Function", mfo: "Innovation", successIndicator: "Develop and adopt innovative teaching strategies", target: "At least 1 per semester", measure: "Quality" }
  ],
  support: [
    { id: "sp1", category: "Support Function", mfo: "Administrative", successIndicator: "Attend and participate in faculty/staff meetings", target: "100% attendance", measure: "Timeliness" },
    { id: "sp2", category: "Support Function", mfo: "Administrative", successIndicator: "Submit required reports on time", target: "100% on time", measure: "Timeliness" },
    { id: "sp3", category: "Support Function", mfo: "Community Extension", successIndicator: "Participate in community extension activities", target: "At least 8 hours/semester", measure: "Quantity" },
    { id: "sp4", category: "Support Function", mfo: "Capacity Building", successIndicator: "Attend trainings, seminars, and workshops", target: "At least 2/year", measure: "Quantity" }
  ]
};

const TIMELINES = [
  { id: "t1", academicYear: "2024-2025", semester: "1st Semester", startDate: "2024-08-01", endDate: "2024-12-31", submissionDeadline: "2025-01-10", status: "closed" },
  { id: "t2", academicYear: "2024-2025", semester: "2nd Semester", startDate: "2025-01-06", endDate: "2025-05-31", submissionDeadline: "2025-06-10", status: "closed" },
  { id: "t3", academicYear: "2025-2026", semester: "1st Semester", startDate: "2025-08-01", endDate: "2025-12-31", submissionDeadline: "2026-01-10", status: "closed" },
  { id: "t4", academicYear: "2025-2026", semester: "2nd Semester", startDate: "2026-01-05", endDate: "2026-05-31", submissionDeadline: "2026-06-15", status: "open" }
];

const IPCR_FORMS = [
  {
    id: "ipcr-001", userId: 8, userName: "Mr. Ramon Castillo", department: "Accounting Office",
    position: "Accountant III", coveredPeriod: "January - June 2026",
    date: "2026-06-10", status: "approved", rating: 4.5, timelineId: "t4",
    coreFunction: [
      { kpiId: "c1", accomplishment: "Processed all financial transactions accurately and on time", rating: 5, remarks: "Outstanding" },
      { kpiId: "c2", accomplishment: "Submitted all required financial reports before deadlines", rating: 4, remarks: "Very Satisfactory" }
    ],
    strategicFunction: [
      { kpiId: "s1", accomplishment: "Assisted in preparation of accreditation financial documents", rating: 4, remarks: "Very Satisfactory" }
    ],
    supportFunction: [
      { kpiId: "sp1", accomplishment: "Attended all scheduled meetings with 100% participation", rating: 5, remarks: "Outstanding" },
      { kpiId: "sp2", accomplishment: "Submitted BIR and COA reports on time", rating: 5, remarks: "Outstanding" }
    ]
  },
  {
    id: "ipcr-002", userId: 9, userName: "Ms. Sofia Mendoza", department: "Human Resource Office",
    position: "HRMO II", coveredPeriod: "January - June 2026",
    date: "2026-06-08", status: "approved", rating: 4.2, timelineId: "t4",
    coreFunction: [
      { kpiId: "c1", accomplishment: "Processed recruitment, appointment and HR documents for 15 employees", rating: 4, remarks: "Very Satisfactory" }
    ],
    strategicFunction: [
      { kpiId: "s2", accomplishment: "Coordinated with partner agencies for HR linkages", rating: 4, remarks: "Very Satisfactory" }
    ],
    supportFunction: [
      { kpiId: "sp1", accomplishment: "Perfect attendance in all meetings and HR activities", rating: 5, remarks: "Outstanding" },
      { kpiId: "sp4", accomplishment: "Attended 3 HR seminars and capacity building events", rating: 5, remarks: "Outstanding" }
    ]
  },
  {
    id: "ipcr-003", userId: 10, userName: "Mr. Diego Torres", department: "IT Office",
    position: "IT Officer I", coveredPeriod: "January - June 2026",
    date: "2026-06-12", status: "reviewed", rating: 4.0, timelineId: "t4",
    coreFunction: [
      { kpiId: "c1", accomplishment: "Maintained and ensured 99% uptime of university systems", rating: 4, remarks: "Very Satisfactory" }
    ],
    strategicFunction: [
      { kpiId: "s4", accomplishment: "Developed and deployed student information system module", rating: 5, remarks: "Outstanding" }
    ],
    supportFunction: [
      { kpiId: "sp1", accomplishment: "Attended all IT-related meetings and coordinations", rating: 4, remarks: "Very Satisfactory" },
      { kpiId: "sp2", accomplishment: "Submitted IT inventory and status reports on schedule", rating: 3, remarks: "Satisfactory" }
    ]
  },
  {
    id: "ipcr-004", userId: 12, userName: "Prof. Marco Dela Cruz", department: "College of Agriculture",
    position: "Assistant Professor II", coveredPeriod: "January - June 2026",
    date: "2026-06-14", status: "pending", rating: 0, timelineId: "t4",
    coreFunction: [
      { kpiId: "c1", accomplishment: "Taught 5 courses in Agriculture with an average passing rate of 92%", rating: 4, remarks: "Very Satisfactory" },
      { kpiId: "c2", accomplishment: "Submitted syllabi and lesson plans before semester started", rating: 5, remarks: "Outstanding" },
      { kpiId: "c4", accomplishment: "Advised 35 students on academic and career matters", rating: 4, remarks: "Very Satisfactory" }
    ],
    strategicFunction: [
      { kpiId: "s1", accomplishment: "Prepared accreditation portfolios for Agriculture program", rating: 4, remarks: "Very Satisfactory" }
    ],
    supportFunction: [
      { kpiId: "sp3", accomplishment: "Led extension activity at Barangay Mabantad – 16 hours", rating: 5, remarks: "Outstanding" },
      { kpiId: "sp4", accomplishment: "Attended 2 national agriculture conferences", rating: 4, remarks: "Very Satisfactory" }
    ]
  },
  {
    id: "ipcr-005", userId: 13, userName: "Prof. Jasmine Aquino", department: "College of Education",
    position: "Instructor I", coveredPeriod: "January - June 2026",
    date: "2026-06-11", status: "disapproved", rating: 2.8, timelineId: "t4",
    coreFunction: [
      { kpiId: "c1", accomplishment: "Conducted classes; some students failed due to absences", rating: 3, remarks: "Satisfactory" },
      { kpiId: "c2", accomplishment: "Submitted syllabi 2 weeks late", rating: 2, remarks: "Unsatisfactory" }
    ],
    strategicFunction: [
      { kpiId: "s1", accomplishment: "Partially assisted in accreditation preparation", rating: 3, remarks: "Satisfactory" }
    ],
    supportFunction: [
      { kpiId: "sp1", accomplishment: "Attended most meetings; missed 2 due to health reasons", rating: 3, remarks: "Satisfactory" },
      { kpiId: "sp2", accomplishment: "Submitted reports on time", rating: 4, remarks: "Very Satisfactory" }
    ]
  },
  {
    id: "ipcr-006", userId: 14, userName: "Prof. Patrick Soriano", department: "College of Information and Computing Sciences",
    position: "Assistant Professor I", coveredPeriod: "January - June 2026",
    date: "2026-06-15", status: "approved", rating: 4.8, timelineId: "t4",
    coreFunction: [
      { kpiId: "c1", accomplishment: "Conducted all scheduled classes with 98% student satisfaction rating", rating: 5, remarks: "Outstanding" },
      { kpiId: "c2", accomplishment: "Submitted all syllabi and course materials on Day 1 of semester", rating: 5, remarks: "Outstanding" },
      { kpiId: "c5", accomplishment: "Completed research on AI integration in higher education", rating: 5, remarks: "Outstanding" }
    ],
    strategicFunction: [
      { kpiId: "s4", accomplishment: "Developed innovative programming assessment tools", rating: 5, remarks: "Outstanding" }
    ],
    supportFunction: [
      { kpiId: "sp3", accomplishment: "Conducted ICT literacy training in local community – 10 hours", rating: 5, remarks: "Outstanding" },
      { kpiId: "sp4", accomplishment: "Attended 3 ICT seminars and 1 international webinar", rating: 4, remarks: "Very Satisfactory" }
    ]
  },
  {
    id: "ipcr-007", userId: 15, userName: "Prof. Lina Garcia", department: "College of Criminal Justice Administration",
    position: "Instructor II", coveredPeriod: "January - June 2026",
    date: "2026-06-13", status: "reviewed", rating: 4.1, timelineId: "t4",
    coreFunction: [
      { kpiId: "c1", accomplishment: "Handled 4 CJA subjects with average 88% pass rate", rating: 4, remarks: "Very Satisfactory" },
      { kpiId: "c4", accomplishment: "Provided academic guidance to 28 criminology students", rating: 4, remarks: "Very Satisfactory" }
    ],
    strategicFunction: [
      { kpiId: "s2", accomplishment: "Coordinated with PNP for student internship programs", rating: 5, remarks: "Outstanding" }
    ],
    supportFunction: [
      { kpiId: "sp3", accomplishment: "Participated in drug awareness campaign – 8 hours", rating: 4, remarks: "Very Satisfactory" },
      { kpiId: "sp4", accomplishment: "Attended national CJA conference", rating: 4, remarks: "Very Satisfactory" }
    ]
  }
];

const OPCR_FORMS = [
  {
    id: "opcr-001", adminId: 3, adminName: "Prof. Jose Reyes", department: "College of Agriculture",
    coveredPeriod: "January - June 2026", date: "2026-06-10", status: "approved",
    overallRating: 4.4, timelineId: "t4",
    coreFunction: [
      { mfo: "Instruction", successIndicator: "100% delivery of prescribed curriculum", target: "100%", actual: "100% - all 15 faculty completed course delivery", budget: "0", rating: 5 },
      { mfo: "Research", successIndicator: "Conduct and complete at least 2 research projects", target: "2 research", actual: "3 research projects completed and published", budget: "50000", rating: 5 }
    ],
    strategicFunction: [
      { mfo: "Accreditation", successIndicator: "Achieve Level II reaccreditation by AACCUP", target: "Level II Accreditation", actual: "Successfully maintained Level II accreditation", budget: "30000", rating: 5 }
    ],
    supportFunction: [
      { mfo: "Administrative", successIndicator: "Submit all required reports on time", target: "100% on time", actual: "95% on-time submission; 1 delayed due to data gathering issues", budget: "5000", rating: 4 }
    ]
  },
  {
    id: "opcr-002", adminId: 5, adminName: "Prof. Carlo Bautista", department: "College of Information and Computing Sciences",
    coveredPeriod: "January - June 2026", date: "2026-06-12", status: "reviewed",
    overallRating: 4.6, timelineId: "t4",
    coreFunction: [
      { mfo: "Instruction", successIndicator: "100% course delivery with student satisfaction ≥ 85%", target: "85% satisfaction", actual: "Average student satisfaction rating: 94%", budget: "0", rating: 5 },
      { mfo: "Research", successIndicator: "Publish at least 1 research in indexed journal", target: "1 publication", actual: "2 research papers published in CHED-indexed journals", budget: "40000", rating: 5 }
    ],
    strategicFunction: [
      { mfo: "Innovation", successIndicator: "Develop and deploy at least 1 campus technology system", target: "1 system deployed", actual: "AOPCR/IPCR system developed and piloted", budget: "80000", rating: 5 }
    ],
    supportFunction: [
      { mfo: "Extension", successIndicator: "Conduct ICT literacy community extension", target: "At least 2 barangays", actual: "Conducted ICT training in 3 barangays", budget: "15000", rating: 5 }
    ]
  }
];

const ACCOUNT_LOGS = [
  { userId: 8, date: "2026-06-19", time: "08:15:32", activity: "Logged in successfully" },
  { userId: 8, date: "2026-06-18", time: "17:00:10", activity: "Submitted IPCR form for 1st Semester 2025-2026" },
  { userId: 8, date: "2026-06-17", time: "10:30:22", activity: "Updated IPCR accomplishments" },
  { userId: 8, date: "2026-06-16", time: "15:45:11", activity: "Uploaded supporting documents" },
  { userId: 8, date: "2026-06-15", time: "09:00:00", activity: "Changed password" }
];

const NOTIFICATIONS = [
  { id: 1, type: "info", message: "IPCR submission deadline is on June 15, 2026", date: "2026-06-01", read: false },
  { id: 2, type: "success", message: "Your IPCR form has been approved", date: "2026-06-10", read: false },
  { id: 3, type: "warning", message: "Please upload your supporting documents", date: "2026-06-08", read: true }
];

// Initialize localStorage with mock data
function initializeMockData() {
  const existingUsers = JSON.parse(localStorage.getItem('csu_piat_users') || '[]');
  // Re-initialize if never done or if data is from an older version (fewer users)
  if (!localStorage.getItem('csu_piat_initialized') || existingUsers.length < USERS_DATA.length) {
    localStorage.setItem('csu_piat_users', JSON.stringify(USERS_DATA));
    localStorage.setItem('csu_piat_departments', JSON.stringify(DEPARTMENTS));
    localStorage.setItem('csu_piat_kpi', JSON.stringify(KPI_CATEGORIES));
    localStorage.setItem('csu_piat_timelines', JSON.stringify(TIMELINES));
    localStorage.setItem('csu_piat_ipcr_forms', JSON.stringify(IPCR_FORMS));
    localStorage.setItem('csu_piat_opcr_forms', JSON.stringify(OPCR_FORMS));
    localStorage.setItem('csu_piat_account_logs', JSON.stringify(ACCOUNT_LOGS));
    localStorage.setItem('csu_piat_notifications', JSON.stringify(NOTIFICATIONS));
    localStorage.setItem('csu_piat_initialized', 'true');
  }
}

function getMockUsers() { return JSON.parse(localStorage.getItem('csu_piat_users')) || USERS_DATA; }
function getMockDepartments() { return JSON.parse(localStorage.getItem('csu_piat_departments')) || DEPARTMENTS; }
function getMockKPI() { return JSON.parse(localStorage.getItem('csu_piat_kpi')) || KPI_CATEGORIES; }
function getMockTimelines() { return JSON.parse(localStorage.getItem('csu_piat_timelines')) || TIMELINES; }
function getMockIPCR() { return JSON.parse(localStorage.getItem('csu_piat_ipcr_forms')) || IPCR_FORMS; }
function getMockOPCR() { return JSON.parse(localStorage.getItem('csu_piat_opcr_forms')) || OPCR_FORMS; }
function getMockLogs() { return JSON.parse(localStorage.getItem('csu_piat_account_logs')) || ACCOUNT_LOGS; }

function getDeptName(deptId) {
  const depts = getMockDepartments();
  const d = depts.find(x => x.id === deptId);
  return d ? d.name : deptId;
}

function getStatusBadge(status) {
  const map = {
    approved: 'success', disapproved: 'danger', pending: 'warning',
    reviewed: 'info', active: 'success', inactive: 'secondary', open: 'success', closed: 'secondary'
  };
  return `<span class="badge bg-${map[status] || 'secondary'} text-capitalize">${status}</span>`;
}

function getRatingLabel(rating) {
  if (rating >= 4.5) return '<span class="text-success fw-bold">Outstanding (O)</span>';
  if (rating >= 3.5) return '<span class="text-primary fw-bold">Very Satisfactory (VS)</span>';
  if (rating >= 2.5) return '<span class="text-warning fw-bold">Satisfactory (S)</span>';
  if (rating >= 1.5) return '<span class="text-danger fw-bold">Unsatisfactory (U)</span>';
  if (rating > 0) return '<span class="text-danger fw-bold">Poor (P)</span>';
  return '<span class="text-muted">Not yet rated</span>';
}

initializeMockData();
