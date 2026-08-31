/* =========================================================================
   KATAPALI +3 COLLEGE — Shared Data Layer
   All website content lives here as DEMO DEFAULTS and is copied into
   localStorage on first run. The Admin Panel edits the localStorage copy,
   so every text / image / list on the site is editable without code.
   ========================================================================= */
(function (global) {
  'use strict';

  var PREFIX = 'kc_';
  var IMG = '../images/';

  /* ------------------------------ helpers ------------------------------- */
  function uid() { return 'id' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7); }
  function clone(o) { return JSON.parse(JSON.stringify(o)); }

  /* ------------------------------ defaults ------------------------------ */
  var DEFAULTS = {};

  DEFAULTS.site = {
    name: 'KATAPALI +3 COLLEGE, KATAPALI',
    shortName: 'Katapali +3 College',
    tagline: 'Empowering Rural Education Since 1985',
    address: 'AT/PO - KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA',
    pin: '768032',
    phone: '+91 98765 43210',
    altPhone: '+91 98765 43211',
    email: 'info@katapalicollege.edu.in',
    altEmail: 'principal@katapalicollege.edu.in',
    established: '1985',
    affiliation: 'Affiliated to Sambalpur University | Recognised under UGC 2(f) & 12(B)',
    officeHours: 'Monday to Saturday, 10:00 AM – 5:00 PM (Sunday & Govt. Holidays closed)',
    logo: IMG + 'logo.svg',
    about: 'KATAPALI +3 COLLEGE, KATAPALI is a premier rural degree college of Bargarh district, offering +3 Arts, Science and Commerce streams with a commitment to accessible, affordable and quality higher education.',
    social: {
      facebook: 'https://facebook.com/katapalicollege',
      twitter: 'https://twitter.com/katapalicollege',
      youtube: 'https://youtube.com/@katapalicollege',
      instagram: 'https://instagram.com/katapalicollege'
    }
  };

  DEFAULTS.theme = {
    primary: '#1e40af',
    secondary: '#0f766e',
    accent: '#f59e0b',
    dark: '#0b1e4f',
    headingFont: 'Poppins',
    bodyFont: 'Inter'
  };

  DEFAULTS.map = {
    address: 'KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA, PIN 768032',
    note: 'The college is located on the Bijepur–Katapali road, about 8 km from Bijepur Block Head Quarters and 55 km from Bargarh town. Regular bus and auto services are available.',
    embed: '<iframe src="https://www.google.com/maps?q=Katapali%2C%20Bijepur%2C%20Bargarh%2C%20Odisha%20768032&output=embed" width="100%" height="400" style="border:0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
  };

  DEFAULTS.hero = [
    { id: uid(), image: IMG + 'banner1.svg', title: 'KATAPALI +3 COLLEGE, KATAPALI', subtitle: 'Empowering Rural Education Since 1985', btn1: 'Admissions', link1: 'admissions.html', btn2: 'Know More', link2: 'about.html' },
    { id: uid(), image: IMG + 'banner2.svg', title: 'A Green, Peaceful Campus', subtitle: 'Spacious classrooms, laboratories and playground spread over 6 acres', btn1: 'Our Campus', link1: 'gallery.html', btn2: 'Departments', link2: 'academics.html' },
    { id: uid(), image: IMG + 'banner3.svg', title: 'Our Students, Our Pride', subtitle: 'Over 1200 students from 40+ villages of Bijepur and Bargarh block', btn1: 'Student Corner', link1: 'student-corner.html', btn2: 'Scholarships', link2: 'student-corner.html#scholarships' },
    { id: uid(), image: IMG + 'banner4.svg', title: 'Learn. Grow. Serve.', subtitle: 'Central library with 18,000+ books, e-journals and reading hall', btn1: 'Library', link1: 'student-corner.html#library', btn2: 'Contact Us', link2: 'contact.html' }
  ];

  DEFAULTS.principal = {
    image: IMG + 'principal.svg',
    name: 'Dr. Demo Name',
    designation: 'Principal, Katapali +3 College',
    qualification: 'M.A., Ph.D. (Political Science)',
    message: 'It gives me immense pleasure to welcome you to Katapali +3 College, an institution that has been serving the educational needs of rural Bargarh for four decades. Our aim is not merely to prepare students for examinations, but to shape responsible citizens with knowledge, discipline and compassion. Our dedicated faculty, well-equipped laboratories and library, and a healthy co-curricular environment ensure that every learner who enters this campus leaves with confidence and character. I invite you to explore our website and become a part of the Katapali family.'
  };

  DEFAULTS.stats = [
    { id: uid(), label: 'Total Students', value: 1284, icon: 'fa-user-graduate', color: '#1e40af' },
    { id: uid(), label: 'Total Faculty', value: 42, icon: 'fa-chalkboard-user', color: '#f59e0b' },
    { id: uid(), label: 'Departments', value: 10, icon: 'fa-building-columns', color: '#0f766e' },
    { id: uid(), label: 'Years of Excellence', value: 40, icon: 'fa-award', color: '#be123c' }
  ];

  /* ------------------------------- notices ------------------------------ */
  DEFAULTS.notices = [
    { id: uid(), title: 'Semester Exam Routine Released (Sem-I, III & V)', category: 'Examination', date: '2026-08-24', expiry: '2026-11-30', file: IMG + 'exam-routine.pdf', image: '', isNew: true,
      summary: 'The routine for the odd semester examinations 2026 has been published. Students must collect admit cards from the college office.',
      content: '<p>The examination routine for <strong>Semester I, III and V</strong> under Sambalpur University CBCS pattern has been released. Examinations will commence from <strong>15 November 2026</strong> and continue till <strong>05 December 2026</strong>.</p><ul><li>Reporting time at examination hall: 9:30 AM</li><li>Admit cards will be distributed from 05 November 2026 at the college office counter.</li><li>Students must carry their college identity card along with the admit card.</li><li>Use of mobile phones inside the examination hall is strictly prohibited.</li></ul><p>For any discrepancy in the admit card, contact the Examination Section immediately.</p>' },
    { id: uid(), title: 'Admission Notice 2026-27 — +3 First Year', category: 'Admission', date: '2026-08-18', expiry: '2026-10-15', file: IMG + 'notice-demo.pdf', image: '', isNew: true,
      summary: 'Online applications are invited for admission into +3 First Year Arts, Science and Commerce for the session 2026-27 through SAMS Odisha portal.',
      content: '<p>Applications are invited from eligible candidates for admission into <strong>+3 First Year Degree (Arts / Science / Commerce)</strong> for the academic session 2026-27 through the Student Academic Management System (SAMS), Government of Odisha.</p><ul><li>Start of online application: 01 June 2026</li><li>Last date of application: 25 June 2026</li><li>Publication of first selection merit list: 05 July 2026</li><li>Classes commence: 01 August 2026</li></ul><p>Candidates are advised to keep their +2 mark sheet, caste certificate, residence certificate and passport size photographs ready before applying.</p>' },
    { id: uid(), title: 'Holiday Notice — Ganesh Puja & Nuakhai', category: 'General', date: '2026-08-10', expiry: '2026-09-20', file: '', image: '', isNew: false,
      summary: 'The college will remain closed from 14th to 18th September 2026 on account of Ganesh Puja and Nuakhai festival.',
      content: '<p>All students and staff are hereby informed that the college will remain <strong>closed from 14 September 2026 to 18 September 2026</strong> on account of Ganesh Puja and the Nuakhai festival.</p><p>Regular classes will resume on 19 September 2026. Hostel boarders staying back must inform the hostel superintendent in writing.</p>' },
    { id: uid(), title: 'Post Matric Scholarship — Last Date Extended', category: 'Scholarship', date: '2026-07-30', expiry: '2026-09-30', file: IMG + 'scholarship-form.pdf', image: '', isNew: false,
      summary: 'The last date for submission of Post Matric Scholarship applications for SC/ST/OBC students has been extended to 30 September 2026.',
      content: '<p>The last date for online submission of <strong>Post Matric Scholarship</strong> applications for SC / ST / SEBC students has been extended to <strong>30 September 2026</strong>.</p><p>Students must upload income certificate, caste certificate, bank passbook first page and last year mark sheet. Hard copies of the submitted application should be handed over to the Scholarship Section within seven days of online submission.</p>' },
    { id: uid(), title: 'Annual Sports Meet 2026 — Registration Open', category: 'Event', date: '2026-07-22', expiry: '2026-10-05', file: '', image: IMG + 'gallery4.svg', isNew: false,
      summary: 'Registration for athletics, football, volleyball, kabaddi and indoor games is open at the Physical Education Department.',
      content: '<p>The <strong>Annual Athletic Meet 2026</strong> will be held on 12 and 13 December 2026 at the college play ground. Registration is open for the following events:</p><ul><li>Athletics — 100m, 200m, 400m, long jump, shot put, javelin</li><li>Team games — Football, Volleyball, Kabaddi, Kho-Kho</li><li>Indoor — Chess, Carrom, Table Tennis</li></ul><p>Interested students may register with the Physical Education Teacher on or before 30 November 2026.</p>' },
    { id: uid(), title: 'Library Book Return Notice for Final Year Students', category: 'General', date: '2026-07-05', expiry: '2026-09-15', file: '', image: '', isNew: false,
      summary: 'All 6th semester students must return borrowed library books and obtain a no-dues certificate before collecting their CLC.',
      content: '<p>All students of the 6th semester are directed to return the books borrowed from the central library and obtain the <strong>No Dues Certificate</strong> from the Librarian before applying for the College Leaving Certificate (CLC) and Migration Certificate.</p><p>A fine of Rs. 2/- per day per book will be charged for late return.</p>' }
  ];

  /* ---------------------------- recruitment ----------------------------- */
  DEFAULTS.recruitment = [
    { id: uid(), title: 'Guest Faculty – Department of Odia', dept: 'Odia', type: 'Guest Faculty', vacancies: 2, salary: 'Rs. 15,000/- per month (consolidated)', date: '2026-08-20', lastDate: '2026-09-20', status: 'Open', file: IMG + 'recruitment-notice.pdf',
      qualification: 'M.A. in Odia with minimum 55% marks from a recognised university. NET / SLET / Ph.D. holders will be preferred.',
      content: '<p>Applications are invited from eligible candidates for engagement as <strong>Guest Faculty in Odia</strong> purely on a temporary basis for the academic session 2026-27.</p><ul><li>Number of posts: 02</li><li>Remuneration: Rs. 15,000/- per month (consolidated)</li><li>Age limit: Below 42 years as on 01.08.2026</li></ul><p>Candidates should appear for a walk-in interview with original certificates and one set of self-attested photocopies on 25 September 2026 at 11:00 AM in the Principal\'s chamber.</p>' },
    { id: uid(), title: 'Assistant Professor – Political Science', dept: 'Political Science', type: 'Contractual', vacancies: 1, salary: 'Rs. 25,000/- per month (consolidated)', date: '2026-08-12', lastDate: '2026-09-15', status: 'Open', file: IMG + 'recruitment-notice.pdf',
      qualification: 'M.A. in Political Science with 55% marks and NET/SLET qualified. Prior teaching experience desirable.',
      content: '<p>The college invites applications for the post of <strong>Assistant Professor in Political Science</strong> on a contractual basis for one academic year, extendable based on performance.</p><ul><li>Number of posts: 01</li><li>Remuneration: Rs. 25,000/- per month</li><li>Mode of selection: Academic career marks (70%) + Interview (30%)</li></ul><p>Applications in the prescribed format along with self-attested copies of certificates should reach the Principal on or before 15 September 2026.</p>' },
    { id: uid(), title: 'Laboratory Attendant – Physics & Chemistry', dept: 'Science', type: 'Contractual', vacancies: 1, salary: 'Rs. 9,500/- per month', date: '2026-06-28', lastDate: '2026-07-25', status: 'Closed', file: '',
      qualification: '+2 Science pass with basic knowledge of laboratory handling and computer operation.',
      content: '<p>Engagement of one <strong>Laboratory Attendant</strong> for the Physics and Chemistry laboratories on a contractual basis. Selection will be based on a written test and practical assessment.</p>' }
  ];

  DEFAULTS.applications = [
    { id: uid(), jobTitle: 'Guest Faculty – Department of Odia', name: 'Demo Applicant 1', email: 'applicant1@example.com', phone: '+91 90000 00001', qualification: 'M.A. Odia, NET', date: '2026-08-22', status: 'Shortlisted' },
    { id: uid(), jobTitle: 'Guest Faculty – Department of Odia', name: 'Demo Applicant 2', email: 'applicant2@example.com', phone: '+91 90000 00002', qualification: 'M.A. Odia, M.Phil', date: '2026-08-25', status: 'Received' },
    { id: uid(), jobTitle: 'Assistant Professor – Political Science', name: 'Demo Applicant 3', email: 'applicant3@example.com', phone: '+91 90000 00003', qualification: 'M.A. Pol. Sc., Ph.D.', date: '2026-08-19', status: 'Shortlisted' },
    { id: uid(), jobTitle: 'Assistant Professor – Political Science', name: 'Demo Applicant 4', email: 'applicant4@example.com', phone: '+91 90000 00004', qualification: 'M.A. Pol. Sc., SLET', date: '2026-08-21', status: 'Rejected' }
  ];

  /* ------------------------------- tenders ------------------------------ */
  DEFAULTS.tenders = [
    { id: uid(), tenderId: 'KPC/TND/2026/07', title: 'Supply of Laboratory Equipment for Physics & Chemistry Departments', date: '2026-08-14', lastDate: '2026-09-18', openDate: '2026-09-20', emd: 'Rs. 15,000/-', value: 'Rs. 6,50,000/- (approx.)', status: 'Open', file: IMG + 'tender-doc.pdf',
      content: '<p>Sealed tenders are invited from registered suppliers / firms for the <strong>supply and installation of laboratory equipment</strong> for the Physics and Chemistry departments of the college.</p><ul><li>Tender document cost: Rs. 500/- (non-refundable)</li><li>EMD: Rs. 15,000/- in the form of a demand draft in favour of the Principal, Katapali +3 College</li><li>Last date of submission: 18 September 2026, 4:00 PM</li><li>Date of opening: 20 September 2026, 11:00 AM in the presence of bidders</li></ul><p>The authority reserves the right to accept or reject any or all tenders without assigning any reason thereof.</p>' },
    { id: uid(), tenderId: 'KPC/TND/2026/06', title: 'Annual Maintenance Contract for Campus Housekeeping & Sanitation', date: '2026-07-10', lastDate: '2026-08-05', openDate: '2026-08-08', emd: 'Rs. 10,000/-', value: 'Rs. 3,20,000/- per annum', status: 'Closed', file: IMG + 'tender-doc.pdf',
      content: '<p>Quotations were invited for the <strong>annual housekeeping and sanitation contract</strong> covering the academic block, laboratories, library, toilets and campus premises for the period 2026-27.</p><p>The tender has been finalised and the work order issued to the successful bidder.</p>' }
  ];

  /* ------------------------------- faculty ------------------------------ */
  var facultySeed = [
    ['Prof. Demo Name 1', 'Lecturer in History', 'History', 'M.A., M.Phil. (History)', 18],
    ['Prof. Demo Name 2', 'Lecturer in Political Science', 'Political Science', 'M.A., NET (Pol. Science)', 14],
    ['Prof. Demo Name 3', 'Lecturer in Odia', 'Odia', 'M.A., Ph.D. (Odia)', 21],
    ['Prof. Demo Name 4', 'Lecturer in English', 'English', 'M.A., NET (English)', 11],
    ['Prof. Demo Name 5', 'Lecturer in Mathematics', 'Mathematics', 'M.Sc., M.Phil. (Mathematics)', 9],
    ['Prof. Demo Name 6', 'Lecturer in Physics', 'Physics', 'M.Sc. (Physics), NET', 12],
    ['Prof. Demo Name 7', 'Lecturer in Chemistry', 'Chemistry', 'M.Sc., Ph.D. (Chemistry)', 16],
    ['Prof. Demo Name 8', 'Lecturer in Economics', 'Economics', 'M.A. (Economics)', 8],
    ['Prof. Demo Name 9', 'Lecturer in Botany', 'Botany', 'M.Sc. (Botany), SLET', 10],
    ['Prof. Demo Name 10', 'Lecturer in Zoology', 'Zoology', 'M.Sc., Ph.D. (Zoology)', 13],
    ['Prof. Demo Name 11', 'Lecturer in Commerce', 'Commerce', 'M.Com., NET', 7],
    ['Prof. Demo Name 12', 'Lecturer in Education', 'Education', 'M.A. (Education), B.Ed.', 15],
    ['Prof. Demo Name 13', 'Lecturer in Sanskrit', 'Sanskrit', 'M.A. (Sanskrit), Acharya', 19],
    ['Prof. Demo Name 14', 'Lecturer in Hindi', 'Hindi', 'M.A. (Hindi), NET', 6],
    ['Prof. Demo Name 15', 'Lecturer in Philosophy', 'Philosophy', 'M.A. (Philosophy)', 12],
    ['Prof. Demo Name 16', 'Lecturer in Computer Science', 'Computer Science', 'MCA, M.Tech (CSE)', 5],
    ['Prof. Demo Name 17', 'Physical Education Teacher', 'Physical Education', 'M.P.Ed.', 17],
    ['Prof. Demo Name 18', 'Librarian', 'Library', 'M.Lib.I.Sc., NET', 20],
    ['Prof. Demo Name 19', 'Lecturer in Geography', 'Geography', 'M.A. (Geography)', 4],
    ['Prof. Demo Name 20', 'Lecturer in Statistics', 'Statistics', 'M.Sc. (Statistics)', 6]
  ];
  DEFAULTS.faculty = facultySeed.map(function (f, i) {
    return {
      id: uid(), name: f[0], designation: f[1], department: f[2], qualification: f[3],
      experience: f[4] + ' years', email: 'faculty' + (i + 1) + '@katapalicollege.edu.in',
      phone: '+91 98765 4' + String(3220 + i),
      image: IMG + 'faculty' + (i + 1) + '.svg',
      onSlider: i < 7, order: i + 1
    };
  });

  /* ------------------------------- gallery ------------------------------ */
  var gallerySeed = [
    ['Annual Function 2025', 'Events'], ['Independence Day Celebration', 'Events'],
    ['Science Exhibition', 'Events'], ['Annual Sports Meet', 'Sports'],
    ['New Library Wing', 'Campus'], ['Campus Front View', 'Campus'],
    ['NSS Tree Plantation Drive', 'Events'], ['Cultural Night', 'Annual Function'],
    ["Freshers' Welcome", 'Annual Function'], ['Blood Donation Camp', 'Events'],
    ['Republic Day Parade', 'Events'], ['Computer Laboratory', 'Campus'],
    ['Inter-College Football Tournament', 'Sports'], ['Seminar Hall', 'Campus'],
    ['Botanical Garden', 'Campus'], ['Convocation Ceremony', 'Annual Function']
  ];
  DEFAULTS.gallery = gallerySeed.map(function (g, i) {
    return { id: uid(), title: g[0], category: g[1], image: IMG + 'gallery' + (i + 1) + '.svg', date: '2026-0' + ((i % 8) + 1) + '-1' + (i % 9), order: i + 1, featured: i < 8 };
  });

  DEFAULTS.videos = [
    { id: uid(), title: 'Katapali +3 College — Campus Tour (Demo)', embed: 'https://www.youtube.com/embed/aqz-KE-bpKQ', thumb: IMG + 'gallery6.svg' },
    { id: uid(), title: 'Annual Function 2025 Highlights (Demo)', embed: 'https://www.youtube.com/embed/aqz-KE-bpKQ', thumb: IMG + 'gallery1.svg' },
    { id: uid(), title: 'NSS Special Camp Documentary (Demo)', embed: 'https://www.youtube.com/embed/aqz-KE-bpKQ', thumb: IMG + 'gallery7.svg' }
  ];

  /* ------------------------------ downloads ----------------------------- */
  DEFAULTS.downloads = [
    { id: uid(), title: 'College Prospectus 2026-27', category: 'Prospectus', size: '1.2 MB', date: '2026-06-01', file: IMG + 'prospectus.pdf' },
    { id: uid(), title: 'Admission Application Form', category: 'Forms', size: '240 KB', date: '2026-06-01', file: IMG + 'admission-form.pdf' },
    { id: uid(), title: 'Scholarship Application Form', category: 'Forms', size: '180 KB', date: '2026-07-02', file: IMG + 'scholarship-form.pdf' },
    { id: uid(), title: 'Transfer Certificate (CLC) Form', category: 'Forms', size: '150 KB', date: '2026-05-20', file: IMG + 'tc-form.pdf' },
    { id: uid(), title: '+3 Arts Syllabus (CBCS)', category: 'Syllabus', size: '860 KB', date: '2026-04-15', file: IMG + 'syllabus-arts.pdf' },
    { id: uid(), title: '+3 Science Syllabus (CBCS)', category: 'Syllabus', size: '910 KB', date: '2026-04-15', file: IMG + 'syllabus-science.pdf' },
    { id: uid(), title: '+3 Commerce Syllabus (CBCS)', category: 'Syllabus', size: '780 KB', date: '2026-04-15', file: IMG + 'syllabus-commerce.pdf' },
    { id: uid(), title: 'Academic Calendar 2026-27', category: 'Circulars', size: '320 KB', date: '2026-07-10', file: IMG + 'academic-calendar.pdf' },
    { id: uid(), title: 'Semester Examination Routine', category: 'Circulars', size: '210 KB', date: '2026-08-24', file: IMG + 'exam-routine.pdf' },
    { id: uid(), title: 'Anti-Ragging Undertaking Circular', category: 'Circulars', size: '160 KB', date: '2026-07-18', file: IMG + 'notice-demo.pdf' }
  ];

  DEFAULTS.users = [
    { id: uid(), name: 'Demo Administrator', email: 'admin@katapalicollege.edu.in', password: 'admin123', role: 'Super Admin', status: 'Active', created: '2026-01-10' },
    { id: uid(), name: 'Office Clerk (Demo)', email: 'office@katapalicollege.edu.in', password: 'office123', role: 'Editor', status: 'Active', created: '2026-02-14' },
    { id: uid(), name: 'Exam Section (Demo)', email: 'exam@katapalicollege.edu.in', password: 'exam123', role: 'Editor', status: 'Inactive', created: '2026-03-02' }
  ];

  DEFAULTS.messages = [
    { id: uid(), name: 'Demo Visitor', email: 'visitor@example.com', phone: '+91 90000 11111', subject: 'Enquiry about +3 Science admission', message: 'Respected Sir, I want to know the last date of application for +3 Science first year.', date: '2026-08-26', read: false },
    { id: uid(), name: 'Demo Parent', email: 'parent@example.com', phone: '+91 90000 22222', subject: 'Hostel facility', message: 'Please inform whether girls hostel accommodation is available for the coming session.', date: '2026-08-20', read: true }
  ];

  DEFAULTS.admissions = [
    { id: uid(), name: 'Demo Student 1', stream: '+3 Arts', phone: '+91 90000 33333', email: 'student1@example.com', marks: '68%', date: '2026-06-12', status: 'Selected' },
    { id: uid(), name: 'Demo Student 2', stream: '+3 Science', phone: '+91 90000 44444', email: 'student2@example.com', marks: '74%', date: '2026-06-14', status: 'Pending' }
  ];

  /* ---------------------- menu / sub-menu page content ------------------ */
  function p(id, page, title, html) { return { id: id, page: page, title: title, html: html }; }

  DEFAULTS.pages = [
    p('about-college', 'about', 'About the College',
      '<p><strong>KATAPALI +3 COLLEGE, KATAPALI</strong> was established in the year <strong>1985</strong> by the sustained effort of the educated youth, farmers and philanthropists of the Katapali region, with the singular objective of bringing higher education within the reach of the rural boys and girls of the Bijepur area of Bargarh district.</p><p>Beginning humbly with a single stream of +3 Arts and barely ninety students, the institution today runs <strong>+3 Arts, Science and Commerce</strong> streams under the Choice Based Credit System of Sambalpur University, with more than <strong>1,200 students</strong> on its rolls and a faculty strength of <strong>42</strong>. The college is recognised by the Government of Odisha and is covered under Sections 2(f) and 12(B) of the UGC Act.</p><p>The campus, spread over nearly six acres of green land, houses an academic block, well-equipped Physics, Chemistry, Botany, Zoology and Computer laboratories, a central library holding over 18,000 volumes, a seminar hall, a girls\' common room, an NSS and NCC unit room and a large playground.</p><p>Over four decades the college has produced teachers, administrators, doctors, engineers, advocates and entrepreneurs who serve across Odisha and beyond — a record the institution regards as its truest measure of success.</p>'),

    p('vision-mission', 'about', 'Vision &amp; Mission',
      '<h3>Our Vision</h3><p>To emerge as a leading rural centre of higher learning in western Odisha that transforms first-generation learners into knowledgeable, skilled, self-reliant and socially responsible citizens.</p><h3>Our Mission</h3><ul><li>To provide affordable and quality higher education to students of the rural and economically weaker sections of society.</li><li>To promote academic excellence through effective classroom teaching, laboratory practice and continuous evaluation.</li><li>To develop moral values, discipline, gender sensitivity and environmental awareness among students.</li><li>To encourage participation in NSS, NCC, sports and cultural activities for holistic personality development.</li><li>To strengthen employability through skill development, career counselling and computer literacy programmes.</li><li>To maintain transparency, accountability and a student-friendly administration.</li></ul><h3>Core Values</h3><p><strong>Knowledge · Discipline · Integrity · Service · Equality</strong></p>'),

    p('governing-body', 'about', 'Governing Body / College Committee',
      '<p>The college is administered by a Governing Body constituted as per the provisions of the Odisha Education Act, with representation from the management, teaching staff, non-teaching staff and the Government.</p><table class="kc-table"><thead><tr><th>Sl. No.</th><th>Name</th><th>Designation in the Body</th><th>Category</th></tr></thead><tbody><tr><td>1</td><td>Sri Demo Name (Demo)</td><td>President</td><td>Nominee of the Management</td></tr><tr><td>2</td><td>Dr. Demo Name</td><td>Secretary / Member Convener</td><td>Principal (Ex-Officio)</td></tr><tr><td>3</td><td>Sri Demo Name (Demo)</td><td>Member</td><td>Government Nominee</td></tr><tr><td>4</td><td>Dr. Demo Name (Demo)</td><td>Member</td><td>University Nominee</td></tr><tr><td>5</td><td>Prof. Demo Name 3</td><td>Member</td><td>Teaching Staff Representative</td></tr><tr><td>6</td><td>Prof. Demo Name 7</td><td>Member</td><td>Teaching Staff Representative</td></tr><tr><td>7</td><td>Sri Demo Name (Demo)</td><td>Member</td><td>Non-Teaching Staff Representative</td></tr><tr><td>8</td><td>Smt. Demo Name (Demo)</td><td>Member</td><td>Guardian Representative</td></tr><tr><td>9</td><td>Sri Demo Name (Demo)</td><td>Member</td><td>Donor Representative</td></tr></tbody></table><h3>Statutory Committees</h3><ul><li><strong>IQAC</strong> — Internal Quality Assurance Cell</li><li><strong>Anti-Ragging Committee &amp; Squad</strong></li><li><strong>Women\'s Grievance Cell / Internal Complaints Committee</strong></li><li><strong>SC/ST Cell and Equal Opportunity Cell</strong></li><li><strong>Admission Committee, Examination Committee and Library Committee</strong></li><li><strong>Career Counselling and Placement Cell</strong></li></ul>'),

    p('principal-desk', 'about', "Principal's Desk",
      '<p>Dear Students, Guardians and Well-wishers,</p><p>Education, in its truest sense, is the drawing out of the best that lies within a learner. At Katapali +3 College we have consciously built an academic culture in which a student from the remotest village of Bijepur block feels equally at home and equally capable.</p><p>Since 1985 this institution has stood as a lamp of learning for the region. Our teachers are not merely instructors but mentors; our laboratories and library are open, working spaces; our NSS and NCC units carry the college into the villages around us through cleanliness drives, plantation programmes, health camps and literacy work.</p><p>To the students I say — attend regularly, read beyond the prescribed text, respect your teachers and your peers, and keep your character above your marks. To the guardians I say — stay in touch with us; your cooperation is the strength of this college.</p><p>My office door remains open to every student and every parent.</p><p><strong>Dr. Demo Name</strong><br>Principal, Katapali +3 College, Katapali</p>'),

    p('departments', 'academics', 'Departments',
      '<p>The college offers instruction through ten teaching departments across the Arts, Science and Commerce faculties. Each department maintains its own departmental library, conducts seminars, wall magazines and study tours, and runs a mentor-mentee system for academic support.</p><table class="kc-table"><thead><tr><th>Department</th><th>Faculty</th><th>Courses Offered</th><th>Head of Department</th></tr></thead><tbody><tr><td>Odia</td><td>Arts</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 3</td></tr><tr><td>English</td><td>Arts</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 4</td></tr><tr><td>History</td><td>Arts</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 1</td></tr><tr><td>Political Science</td><td>Arts</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 2</td></tr><tr><td>Economics</td><td>Arts</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 8</td></tr><tr><td>Mathematics</td><td>Science</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 5</td></tr><tr><td>Physics</td><td>Science</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 6</td></tr><tr><td>Chemistry</td><td>Science</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 7</td></tr><tr><td>Botany</td><td>Science</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 9</td></tr><tr><td>Zoology</td><td>Science</td><td>Honours &amp; Elective</td><td>Prof. Demo Name 10</td></tr></tbody></table><p class="note">In addition, the Departments of Commerce, Computer Science, Physical Education and Library Science support the general education and skill components of the CBCS curriculum.</p>'),

    p('courses', 'academics', 'Courses Offered',
      '<p>All degree courses run for three years (six semesters) under the Choice Based Credit System (CBCS) of Sambalpur University.</p><table class="kc-table"><thead><tr><th>Course</th><th>Duration</th><th>Seats</th><th>Honours Subjects Available</th></tr></thead><tbody><tr><td><strong>+3 Arts</strong></td><td>3 Years / 6 Semesters</td><td>256</td><td>Odia, English, History, Political Science, Economics</td></tr><tr><td><strong>+3 Science</strong></td><td>3 Years / 6 Semesters</td><td>128</td><td>Physics, Chemistry, Mathematics, Botany, Zoology</td></tr><tr><td><strong>+3 Commerce</strong></td><td>3 Years / 6 Semesters</td><td>64</td><td>Accounting &amp; Finance, Marketing Management</td></tr></tbody></table><h3>Certificate &amp; Add-on Courses (Demo)</h3><ul><li>Certificate Course in Computer Application (3 months)</li><li>Certificate Course in Spoken English &amp; Communication Skills (3 months)</li><li>Certificate Course in Tally &amp; Basic Accounting (6 months)</li><li>Yoga and Wellness Programme (short term)</li></ul>'),

    p('syllabus', 'academics', 'Syllabus',
      '<p>The college follows the syllabi prescribed by Sambalpur University under the CBCS pattern. Students may download the stream-wise syllabus below. Detailed semester-wise course structures are also displayed on the departmental notice boards.</p><table class="kc-table"><thead><tr><th>Stream</th><th>Pattern</th><th>Session</th><th>Download</th></tr></thead><tbody><tr><td>+3 Arts</td><td>CBCS</td><td>2026-27</td><td><a href="../images/syllabus-arts.pdf" target="_blank"><i class="fa-solid fa-file-pdf"></i> Download PDF</a></td></tr><tr><td>+3 Science</td><td>CBCS</td><td>2026-27</td><td><a href="../images/syllabus-science.pdf" target="_blank"><i class="fa-solid fa-file-pdf"></i> Download PDF</a></td></tr><tr><td>+3 Commerce</td><td>CBCS</td><td>2026-27</td><td><a href="../images/syllabus-commerce.pdf" target="_blank"><i class="fa-solid fa-file-pdf"></i> Download PDF</a></td></tr></tbody></table><p class="note">Demo PDF placeholders — replace with the actual syllabus files from Admin Panel &gt; Downloads Management.</p>'),

    p('academic-calendar', 'academics', 'Academic Calendar 2026-27',
      '<table class="kc-table"><thead><tr><th>Activity</th><th>Period / Date</th></tr></thead><tbody><tr><td>Commencement of classes (1st Semester)</td><td>01 August 2026</td></tr><tr><td>Freshers\' Welcome</td><td>22 August 2026</td></tr><tr><td>Internal Assessment — I</td><td>10 – 16 September 2026</td></tr><tr><td>Puja Vacation</td><td>14 – 25 October 2026</td></tr><tr><td>Internal Assessment — II</td><td>03 – 08 November 2026</td></tr><tr><td>Odd Semester University Examination</td><td>15 November – 05 December 2026</td></tr><tr><td>Annual Athletic Meet</td><td>12 – 13 December 2026</td></tr><tr><td>Commencement of Even Semester classes</td><td>02 January 2027</td></tr><tr><td>Annual Function</td><td>18 February 2027</td></tr><tr><td>Study Leave</td><td>20 – 30 April 2027</td></tr><tr><td>Even Semester University Examination</td><td>02 – 25 May 2027</td></tr><tr><td>Summer Vacation</td><td>01 June – 30 June 2027</td></tr></tbody></table><p><a href="../images/academic-calendar.pdf" target="_blank" class="btn btn-accent"><i class="fa-solid fa-download"></i> Download Academic Calendar (PDF)</a></p>'),

    p('admission-process', 'admissions', 'Admission Process',
      '<p>Admission into +3 First Year is conducted entirely online through the <strong>Student Academic Management System (SAMS), Government of Odisha</strong>. The college does not accept any offline application or capitation fee of any kind.</p><h3>Step by Step</h3><ol><li><strong>Register online</strong> on the SAMS Odisha portal and obtain the applicant login credentials.</li><li><strong>Fill the Common Application Form (CAF)</strong> selecting Katapali +3 College and the desired stream / honours subject in order of preference.</li><li><strong>Upload documents</strong> — +2 mark sheet, pass certificate, caste certificate, residence certificate, passport photograph and signature.</li><li><strong>Pay the application fee</strong> online and download the submitted CAF for record.</li><li><strong>Check the merit list</strong> published on the SAMS portal and the college notice board.</li><li><strong>Report for document verification</strong> at the college on the allotted date with all originals.</li><li><strong>Pay the admission fee</strong> at the college counter and collect the money receipt and identity card.</li></ol><h3>Documents Required at Verification</h3><ul><li>+2 / HSC original mark sheet and pass certificate</li><li>College Leaving Certificate (CLC) and Conduct Certificate</li><li>Caste and income certificate (if applicable)</li><li>Migration certificate (for boards other than CHSE, Odisha)</li><li>Four recent passport size photographs</li><li>Aadhaar card and bank passbook photocopy</li></ul><p class="note">Helpline: +91 98765 43210 (Admission Cell, 10:00 AM – 5:00 PM on working days)</p>'),

    p('eligibility', 'admissions', 'Eligibility Criteria',
      '<table class="kc-table"><thead><tr><th>Course</th><th>Minimum Qualification</th><th>Additional Requirement</th></tr></thead><tbody><tr><td>+3 Arts</td><td>Pass in +2 (any stream) from CHSE Odisha or an equivalent board</td><td>Honours in a subject requires the subject or an allied subject at +2 level</td></tr><tr><td>+3 Science</td><td>Pass in +2 Science with Physics, Chemistry and Mathematics / Biology</td><td>Minimum 45% aggregate for Honours (40% for SC/ST candidates)</td></tr><tr><td>+3 Commerce</td><td>Pass in +2 Commerce; +2 Arts / Science candidates may apply for pass courses</td><td>Minimum 45% aggregate for Honours</td></tr></tbody></table><h3>Reservation of Seats (as per Government norms)</h3><ul><li>Scheduled Caste — 16.25%</li><li>Scheduled Tribe — 22.50%</li><li>Socially &amp; Educationally Backward Class — as per State policy</li><li>Persons with Disability — 5%</li><li>Ex-Servicemen wards, Green Card holders and Sports quota — as per prevailing Government instructions</li></ul><p class="note">Age, weightage and relaxation norms follow the current SAMS admission guidelines of the Department of Higher Education, Government of Odisha.</p>'),

    p('fee-structure', 'admissions', 'Fee Structure 2026-27 (Demo)',
      '<table class="kc-table"><thead><tr><th>Particulars</th><th>+3 Arts</th><th>+3 Science</th><th>+3 Commerce</th></tr></thead><tbody><tr><td>Admission Fee (one time)</td><td>Rs. 500</td><td>Rs. 500</td><td>Rs. 500</td></tr><tr><td>Tuition Fee (per annum)</td><td>Rs. 2,400</td><td>Rs. 3,600</td><td>Rs. 2,800</td></tr><tr><td>Laboratory Fee</td><td>—</td><td>Rs. 1,800</td><td>Rs. 400</td></tr><tr><td>Library Fee</td><td>Rs. 300</td><td>Rs. 300</td><td>Rs. 300</td></tr><tr><td>Examination &amp; Internal Assessment</td><td>Rs. 700</td><td>Rs. 900</td><td>Rs. 700</td></tr><tr><td>Sports, Cultural &amp; NSS</td><td>Rs. 350</td><td>Rs. 350</td><td>Rs. 350</td></tr><tr><td>Identity Card &amp; Miscellaneous</td><td>Rs. 250</td><td>Rs. 250</td><td>Rs. 250</td></tr><tr><td><strong>Total (First Year)</strong></td><td><strong>Rs. 4,500</strong></td><td><strong>Rs. 7,700</strong></td><td><strong>Rs. 5,300</strong></td></tr></tbody></table><h3>Concessions</h3><ul><li>Tuition fee is fully exempted for SC / ST students and for girl students up to the +3 level as per Government of Odisha policy.</li><li>Students receiving Post Matric Scholarship must submit the sanction copy at the accounts section.</li></ul><p class="note">Fees are indicative demo figures. The Principal reserves the right to revise fees as per Government / University directions.</p>'),

    p('exam-routine', 'examination', 'Examination Routine',
      '<p>The odd semester examination of Sambalpur University for the session 2026-27 will be conducted at this centre as per the routine below.</p><table class="kc-table"><thead><tr><th>Date</th><th>Semester I</th><th>Semester III</th><th>Semester V</th></tr></thead><tbody><tr><td>15 Nov 2026</td><td>Core – I</td><td>Core – V</td><td>Core – XI</td></tr><tr><td>18 Nov 2026</td><td>Core – II</td><td>Core – VI</td><td>Core – XII</td></tr><tr><td>21 Nov 2026</td><td>AECC – I</td><td>Core – VII</td><td>DSE – I</td></tr><tr><td>24 Nov 2026</td><td>GE – I</td><td>SEC – I</td><td>DSE – II</td></tr><tr><td>27 Nov 2026</td><td>MIL / Env. Studies</td><td>GE – III</td><td>DSE – III</td></tr><tr><td>02 Dec 2026</td><td>Practical / Viva</td><td>Practical / Viva</td><td>Project &amp; Viva</td></tr></tbody></table><p><strong>Time:</strong> 10:00 AM to 1:00 PM (Theory) | 10:00 AM to 4:00 PM (Practical)</p><p><a href="../images/exam-routine.pdf" target="_blank" class="btn btn-accent"><i class="fa-solid fa-download"></i> Download Routine (PDF)</a></p>'),

    p('results', 'examination', 'Results',
      '<p>Semester results are published by Sambalpur University on its official portal. The college simultaneously displays the consolidated result on the notice board and updates the summary below.</p><table class="kc-table"><thead><tr><th>Examination</th><th>Session</th><th>Appeared</th><th>Passed</th><th>Pass %</th><th>Result</th></tr></thead><tbody><tr><td>+3 6th Semester (Arts)</td><td>2025-26</td><td>212</td><td>197</td><td>92.9%</td><td>Published</td></tr><tr><td>+3 6th Semester (Science)</td><td>2025-26</td><td>104</td><td>99</td><td>95.2%</td><td>Published</td></tr><tr><td>+3 6th Semester (Commerce)</td><td>2025-26</td><td>48</td><td>44</td><td>91.7%</td><td>Published</td></tr><tr><td>+3 4th Semester (All streams)</td><td>2025-26</td><td>361</td><td>338</td><td>93.6%</td><td>Published</td></tr><tr><td>+3 2nd Semester (All streams)</td><td>2025-26</td><td>402</td><td>371</td><td>92.3%</td><td>Published</td></tr></tbody></table><h3>Toppers of the College (Demo)</h3><ul><li>Demo Student A — +3 Science (Physics Hons.) — 8.92 CGPA</li><li>Demo Student B — +3 Arts (Odia Hons.) — 8.74 CGPA</li><li>Demo Student C — +3 Commerce — 8.51 CGPA</li></ul>'),

    p('exam-rules', 'examination', 'Rules &amp; Regulations',
      '<h3>Examination Rules</h3><ol><li>A student must have at least <strong>75% attendance</strong> in each semester to be eligible to appear in the University examination.</li><li>Admit cards must be collected from the Examination Section at least three days before the commencement of the examination.</li><li>Candidates must occupy their seats fifteen minutes before the scheduled start time. No candidate will be allowed after 30 minutes of commencement.</li><li>Mobile phones, smart watches, programmable calculators and printed material are strictly prohibited inside the examination hall.</li><li>Any candidate found adopting unfair means will be dealt with as per the Malpractice Rules of Sambalpur University.</li><li>Answer scripts must be handed over to the invigilator before leaving the hall.</li></ol><h3>General Discipline</h3><ol><li>Students must carry their identity cards inside the campus at all times.</li><li>Ragging in any form is a criminal offence punishable under the Odisha Education (Prevention of Ragging) Act.</li><li>Smoking, consumption of tobacco or intoxicants and damage to college property are strictly forbidden.</li><li>Students must maintain silence in the library and reading room.</li><li>The decision of the Principal shall be final in all matters of discipline.</li></ol>'),

    p('scholarships', 'student-corner', 'Scholarships',
      '<p>A large majority of our students receive financial assistance under State and Central Government schemes. The Scholarship Cell of the college assists students in online application, document verification and follow-up.</p><table class="kc-table"><thead><tr><th>Scheme</th><th>Eligibility</th><th>Amount (approx.)</th><th>Apply Through</th></tr></thead><tbody><tr><td>Post Matric Scholarship (SC / ST)</td><td>Family income below Rs. 2.50 lakh</td><td>Rs. 3,000 – 12,000 / year</td><td>National Scholarship Portal</td></tr><tr><td>Post Matric Scholarship (SEBC / OBC)</td><td>Family income below Rs. 1.50 lakh</td><td>Rs. 2,500 – 9,000 / year</td><td>e-Medhabruti, Odisha</td></tr><tr><td>Medhabruti (Merit Scholarship)</td><td>Top rank holders in +2 examination</td><td>Rs. 10,000 / year</td><td>e-Medhabruti, Odisha</td></tr><tr><td>Prerana (Minority)</td><td>Minority community students</td><td>Rs. 5,000 / year</td><td>e-Medhabruti, Odisha</td></tr><tr><td>Green Card Holders Assistance</td><td>Wards of green card holding families</td><td>Fee concession</td><td>College Office</td></tr><tr><td>Students Aid Fund (College)</td><td>Poor and meritorious students</td><td>Rs. 2,000 (one time)</td><td>Application to Principal</td></tr></tbody></table><p><a href="../images/scholarship-form.pdf" target="_blank" class="btn btn-accent"><i class="fa-solid fa-download"></i> Download Scholarship Form</a></p>'),

    p('student-union', 'student-corner', 'Students\' Union',
      '<p>The Students\' Union of Katapali +3 College is elected every academic session in accordance with the Lyngdoh Committee recommendations. The Union organises the annual function, sports meet, college magazine, debate and quiz competitions, and acts as the bridge between the students and the administration.</p><h3>Office Bearers 2026-27 (Demo)</h3><table class="kc-table"><thead><tr><th>Post</th><th>Name</th><th>Class</th></tr></thead><tbody><tr><td>President</td><td>Demo Student 1</td><td>+3 3rd Year Arts</td></tr><tr><td>Vice President</td><td>Demo Student 2</td><td>+3 2nd Year Science</td></tr><tr><td>Secretary</td><td>Demo Student 3</td><td>+3 3rd Year Science</td></tr><tr><td>Assistant Secretary</td><td>Demo Student 4</td><td>+3 2nd Year Commerce</td></tr><tr><td>Cultural Secretary</td><td>Demo Student 5</td><td>+3 2nd Year Arts</td></tr><tr><td>Athletic Secretary</td><td>Demo Student 6</td><td>+3 3rd Year Arts</td></tr><tr><td>Magazine Editor</td><td>Demo Student 7</td><td>+3 3rd Year Arts</td></tr></tbody></table><h3>Annual Activities</h3><ul><li>Freshers\' Welcome and Farewell Ceremony</li><li>Annual Function and Cultural Night</li><li>Inter-class debate, quiz, essay and painting competitions</li><li>Publication of the college magazine <em>“Katapali Kalika”</em> (demo)</li></ul>'),

    p('sports-nss', 'student-corner', 'Sports &amp; NCC / NSS',
      '<h3>Sports &amp; Games</h3><p>The college maintains a full-size playground with facilities for football, cricket, volleyball, kabaddi and athletics, and an indoor room for chess, carrom and table tennis. Students represent the college at the Sambalpur University inter-college tournaments every year.</p><ul><li>Annual Athletic Meet held in December</li><li>Inter-class tournaments in football, volleyball and kabaddi</li><li>Coaching support from the Physical Education Department</li><li>Demo achievement: Runners-up, Inter-College Football Tournament 2025</li></ul><h3>National Service Scheme (NSS)</h3><p>The college runs two NSS units with a combined strength of 200 volunteers. Regular and special camping activities are conducted in the adopted villages around Katapali.</p><ul><li>Swachh Bharat cleanliness drives and plastic-free campaigns</li><li>Tree plantation and water conservation programmes</li><li>Blood donation and health check-up camps</li><li>Adult literacy and voter awareness drives</li><li>Seven-day special camp in an adopted village every year</li></ul><h3>National Cadet Corps (NCC)</h3><p>An NCC unit (Army Wing) functions in the college under the 2nd Odisha Battalion. Cadets undergo regular drill, participate in the Annual Training Camp and appear for the A and B certificate examinations.</p>'),

    p('library', 'student-corner', 'Central Library',
      '<p>The central library is the academic heart of the college. It occupies a spacious hall in the main academic block with a separate reading room for students and a reference section for faculty.</p><h3>Library at a Glance (Demo)</h3><table class="kc-table"><tbody><tr><th>Total books</th><td>18,450 volumes</td></tr><tr><th>Reference books</th><td>2,300 volumes</td></tr><tr><th>Journals &amp; magazines</th><td>24 titles</td></tr><tr><th>Daily newspapers</th><td>6 (Odia and English)</td></tr><tr><th>Reading room capacity</th><td>80 students</td></tr><tr><th>Library hours</th><td>10:00 AM – 5:00 PM on all working days</td></tr><tr><th>Librarian</th><td>Prof. Demo Name 18</td></tr></tbody></table><h3>Services</h3><ul><li>Book lending — two books for fourteen days per student</li><li>Book bank facility for SC / ST and economically weaker students</li><li>Question paper bank of previous University examinations</li><li>Internet-enabled computers for access to N-LIST e-resources</li><li>Career and competitive examination corner</li></ul>'),

    p('alumni-association', 'alumni', 'Alumni Association',
      '<p>The <strong>Katapali +3 College Alumni Association</strong> was formally registered in 2005 and brings together former students who now serve in teaching, administration, medicine, engineering, defence, agriculture and business.</p><h3>Objectives</h3><ul><li>To maintain a lasting bond between the alumni and their alma mater.</li><li>To assist the college in infrastructure development and student welfare.</li><li>To support poor and meritorious students through the Alumni Students\' Aid Fund.</li><li>To organise career guidance and motivational sessions for current students.</li></ul><h3>Executive Committee (Demo)</h3><table class="kc-table"><thead><tr><th>Post</th><th>Name</th><th>Batch</th></tr></thead><tbody><tr><td>President</td><td>Demo Alumnus 1</td><td>1992</td></tr><tr><td>Vice President</td><td>Demo Alumnus 2</td><td>1998</td></tr><tr><td>General Secretary</td><td>Demo Alumnus 3</td><td>2004</td></tr><tr><td>Treasurer</td><td>Demo Alumnus 4</td><td>2009</td></tr></tbody></table><h3>Alumni Meet</h3><p>The annual alumni meet is held on the first Sunday of January every year on the college campus. Registration is free for all former students. Please contact the college office to enrol your name in the alumni register.</p>'),

    p('notable-alumni', 'alumni', 'Notable Alumni',
      '<p>The institution takes pride in the achievements of its former students across diverse fields. The list below is demo content and may be updated from the Admin Panel.</p><table class="kc-table"><thead><tr><th>Name</th><th>Batch</th><th>Present Position (Demo)</th></tr></thead><tbody><tr><td>Demo Alumnus 1</td><td>1990</td><td>Officer, Odisha Administrative Service</td></tr><tr><td>Demo Alumnus 2</td><td>1994</td><td>Professor, State University</td></tr><tr><td>Demo Alumnus 3</td><td>1999</td><td>Medical Officer, Government Hospital</td></tr><tr><td>Demo Alumnus 4</td><td>2003</td><td>Assistant Engineer, Public Works Department</td></tr><tr><td>Demo Alumnus 5</td><td>2008</td><td>Advocate, District Court, Bargarh</td></tr><tr><td>Demo Alumnus 6</td><td>2012</td><td>Entrepreneur &amp; Progressive Farmer</td></tr></tbody></table>'),

    p('downloads-intro', 'downloads', 'Downloads',
      '<p>Frequently required forms, circulars and the college prospectus are available here for download. All files are demo placeholders and can be replaced from the Admin Panel without touching any code.</p>'),

    p('contact-intro', 'contact', 'Contact Us',
      '<p>The college office remains open on all working days from 10:00 AM to 5:00 PM. For admission related queries please contact the Admission Cell during office hours, or write to us using the enquiry form below — we usually respond within two working days.</p>')
  ];

  /* ------------------------------ store API ----------------------------- */
  var Store = {
    DEFAULTS: DEFAULTS,
    uid: uid,
    key: function (k) { return PREFIX + k; },
    get: function (k) {
      try {
        var raw = localStorage.getItem(PREFIX + k);
        if (raw === null) return clone(DEFAULTS[k]);
        return JSON.parse(raw);
      } catch (e) { return clone(DEFAULTS[k]); }
    },
    set: function (k, v) {
      try { localStorage.setItem(PREFIX + k, JSON.stringify(v)); return true; }
      catch (e) { alert('Storage limit reached. Try uploading smaller images.'); return false; }
    },
    seed: function (force) {
      Object.keys(DEFAULTS).forEach(function (k) {
        if (force || localStorage.getItem(PREFIX + k) === null) {
          try { localStorage.setItem(PREFIX + k, JSON.stringify(DEFAULTS[k])); } catch (e) {}
        }
      });
      try { localStorage.setItem(PREFIX + 'seeded', '1'); } catch (e) {}
    },
    reset: function () {
      Object.keys(DEFAULTS).forEach(function (k) { localStorage.removeItem(PREFIX + k); });
      Store.seed(true);
    },
    /* list helpers */
    add: function (k, item) { var l = Store.get(k); item.id = item.id || uid(); l.unshift(item); Store.set(k, l); return item; },
    update: function (k, id, patch) {
      var l = Store.get(k);
      for (var i = 0; i < l.length; i++) if (l[i].id === id) { l[i] = Object.assign(l[i], patch); break; }
      Store.set(k, l); return l;
    },
    remove: function (k, id) { var l = Store.get(k).filter(function (x) { return x.id !== id; }); Store.set(k, l); return l; },
    find: function (k, id) { var l = Store.get(k); for (var i = 0; i < l.length; i++) if (l[i].id === id) return l[i]; return null; },
    page: function (id) {
      var l = Store.get('pages');
      for (var i = 0; i < l.length; i++) if (l[i].id === id) return l[i];
      return { id: id, page: '', title: '', html: '' };
    },
    pagesOf: function (section) { return Store.get('pages').filter(function (p) { return p.page === section; }); }
  };

  Store.seed(false);
  global.KC = { Store: Store, IMG: IMG, uid: uid };
})(window);
