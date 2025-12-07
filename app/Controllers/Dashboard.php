<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\SubjectModel;
use App\Models\ResultModel;
use App\Models\CalendarModel;
use App\Models\MarkingOpenModel;
use App\Models\NoticeModel;
use App\Models\AttendanceModel;
use App\Models\FeesModel;
use App\Models\FeesAmountModel;
use App\Models\TransactionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Dashboard extends Controller
{
    // new code for test
    protected $userModel;
    protected $subjectModel;
    protected $studentModel;
    protected $resultModel;
    protected $calendarModel;
    protected $noticeModel;
    protected $markingModel;
    protected $attendanceModel;
    protected $feesModel;
    protected $feesAmountModel;
    protected $transactionModel;

    protected $session;
    protected $data;

    public function __construct()
    {
        $this->userModel         = new UserModel();
        $this->subjectModel      = new SubjectModel();
        $this->studentModel      = new StudentModel();
        $this->resultModel       = new ResultModel();
        $this->calendarModel       = new CalendarModel();
        $this->noticeModel       = new NoticeModel();
        $this->markingModel     = new MarkingOpenModel();
        $this->attendanceModel     = new AttendanceModel();
        $this->feesModel         = new FeesModel();
        $this->feesAmountModel     = new FeesAmountModel();
        $this->transactionModel = new TransactionModel();


        $this->session       = session();
        $this->data          = [];

        if (!$this->session->get('isLoggedIn')) {
            redirect()->to(base_url('login'))->send();
            exit;
        }

        $this->data['navbarItems'] = [
            ['label' => 'Dashboard', 'url' => base_url('dashboard')],
            ['label' => 'Calendar', 'url' => base_url('calendar')],
            ['label' => 'Result', 'url' => base_url('ad-result')],
            ['label' => 'Accounts', 'url' => base_url('accounts')],
        ];
        $this->data['sidebarItems'] = [
            [
                'label' => 'Dashboard',
                'url' => base_url('dashboard'),
                'icon' => 'fas fa-tachometer-alt',
                'section' => 'dashboard'
            ],
            [
                'label' => 'Teacher Management',
                'url' => base_url('teacher_management'),
                'icon' => 'fas fa-chalkboard-teacher',
                'section' => 'teacher'
            ],
            [
                'label' => 'Student Management',
                'url' => base_url('admin/student'),
                'icon' => 'fas fa-user-graduate',
                'section' => 'student'
            ],
            [
                'label'   => 'Accounts',
                'url'     => base_url('admin/transactions'),
                'icon'    => 'fa-solid fa-sack-dollar',
                'section' => 'accounts'
            ],
            [
                'label' => 'Attendance',
                'url' => base_url('admin/attendance/calendar'),
                'icon' => 'fas fa-clock',
                'section' => 'attendance'
            ],
            [
                'label' => 'Calendar',
                'url' => base_url('calendar'),
                'icon' => 'fas fa-calendar-alt',
                'section' => 'calendar'
            ],
            [
                'label' => 'Notice',
                'url' => base_url('admin/notices'),
                'icon' => 'fas fa-bullhorn',
                'section' => 'notice'
            ],
            [
                'label' => 'Result',
                'url' => base_url('admin/tabulation_form'),
                'icon' => 'fas fa-chart-bar',
                'section' => 'result'
            ],
        ];
    }

    public function index()
    {

        // Dashboard specific values
        $this->data['title'] = 'Dashboard';
        $this->data['activeSection'] = 'dashboard';

        // Common navbar and sidebar for all views

        $this->data['navbarItems'] = [
            ['label' => 'Dashboard', 'url' => base_url('dashboard')],
            ['label' => 'Calendar', 'url' => base_url('calendar')],
            ['label' => 'Result', 'url' => base_url('ad-result')],
            ['label' => 'Accounts', 'url' => base_url('accounts')],
        ];
        $this->data['total_students'] = $this->studentModel->where('account_status', 0)->countAll();
        $this->data['total_users'] = $this->userModel->where('account_status !=', 0)->countAllResults();
        $this->data['total_new_users'] = $this->userModel->where('account_status', 0)->countAllResults();

        $this->data['total_applications'] = 10;

        $openExams = $this->markingModel
            ->where('status', 'open')
            ->findAll();

        if (!empty($openExams)) {
            // Extract exam names
            $examNames = array_column($openExams, 'exam_name');

            // Get unique teacher IDs from results
            $given_subjects = $this->resultModel
                ->distinct()
                ->select('subject_id')
                ->whereIn('exam', $examNames)
                ->findAll();

            $total_subjects = $this->calendarModel
                ->where('subcategory', $examNames)
                ->where('category', 'Exam')
                ->findAll();
        } else {
            $given_subjects = []; // No open exams → no teachers
            $total_subjects = []; // No open exams → no teachers
        }

        // Count teachers safely
        $this->data['givenSubjects'] = count($given_subjects);
        $this->data['totalSubjects'] = count($total_subjects);
        $this->data['total_income'] = 150000.00;
        $this->data['total_cost'] = 42000.00;

        return view('dashboard/index', $this->data);
    }

    public function profile()
    {
        $this->data['title'] = 'Profile';
        $this->data['activeSection'] = 'dashboard';

        $this->data['navbarItems'] = [
            ['label' => 'Dashboard', 'url' => base_url('dashboard')],
            ['label' => 'Calendar', 'url' => base_url('calendar')],
            ['label' => 'Result', 'url' => base_url('ad-result')],
            ['label' => 'Accounts', 'url' => base_url('accounts')],
        ];

        $userId = $this->session->get('user_id');
        // Load model and get teacher data
        $teacher = $this->userModel->find($userId);

        if (!$teacher) {
            // handle case where teacher not found
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Teacher not found");
        }

        $this->data['user'] = $teacher;

        return view('dashboard/profile', $this->data);
    }

    public function profile_id($id)
    {
        $this->data['title'] = 'Profile';
        $this->data['activeSection'] = 'dashboard';

        $this->data['navbarItems'] = [
            ['label' => 'Dashboard', 'url' => base_url('dashboard')],
            ['label' => 'Calendar', 'url' => base_url('calendar')],
            ['label' => 'Result', 'url' => base_url('ad-result')],
            ['label' => 'Accounts', 'url' => base_url('accounts')],
        ];

        $teacher = $this->userModel->find($id);

        if (!$teacher) {
            // handle case where teacher not found
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Teacher not found");
        }

        $this->data['user'] = $teacher;

        return view('dashboard/profile', $this->data);
    }
    public function restrict($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        $accountStatus = $this->session->get('account_status');
        if ($accountStatus != 2) {
            return redirect()->back()
                ->with('error', 'You are not a supper admin.');
        } else {
            // restrict user (soft delete by updating account_status to 0)
            if ($this->userModel->update($id, ['account_status' => 0])) {
                return redirect()->back()
                    ->with('success', 'User restricted successfully.');
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to restrict user.');
            }
        }
    }

    public function edit_profile_view($id)
    {
        $this->data['title'] = 'Profile edit';
        $this->data['activeSection'] = 'dashboard';

        $this->data['navbarItems'] = [
            ['label' => 'Dashboard', 'url' => base_url('dashboard')],
            ['label' => 'Calendar', 'url' => base_url('calendar')],
            ['label' => 'Result', 'url' => base_url('ad-result')],
            ['label' => 'Accounts', 'url' => base_url('accounts')],
        ];

        $teacher = $this->userModel->find($id);

        if (!$teacher) {
            // handle case where teacher not found
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Teacher not found");
        }

        $this->data['user'] = $teacher;

        return view('dashboard/edit_profile', $this->data);
    }

    public function update_user($id)
    {

        $socialType    = $this->request->getPost('social_type');
        $socialProfile = $this->request->getPost('social_profile');

        // Add prefix if Facebook
        switch ($socialType) {
            case 'facebook':
                $socialProfile = 'f:' . $socialProfile;
                break;
            case 'youtube':
                $socialProfile = 'y:' . $socialProfile;
                break;
            case 'linkedin':
                $socialProfile = 'l:' . $socialProfile;
                break;
        }

        // Get POST data
        $data = [
            'name'           => $this->request->getPost('name'),
            'subject'        => $this->request->getPost('subject'),
            'gender'         => $this->request->getPost('gender'),
            'phone'          => $this->request->getPost('phone'),
            'email'          => $this->request->getPost('email'),
            'social_profile' => $socialProfile,
            'index_number'     => $this->request->getPost('index_number'),
            'dob'            => $this->request->getPost('dob'),
            'joining_date'    => $this->request->getPost('joining_date'),
            'mpo_date'        => $this->request->getPost('mpo_date'),
            'religion'        => $this->request->getPost('religion'),
            'blood_group'    => $this->request->getPost('blood_group'),
            'bio'              => $this->request->getPost('bio'),
        ];

        $photo = $this->request->getFile('photo');

        $userId = $this->session->get('user_id');
        if ($userId == $id) {
            if ($photo && $photo->isValid() && !$photo->hasMoved()) {
                $newName = $photo->getRandomName();
                $uploadPath = FCPATH . 'uploads/users/';

                // Make sure folder exists
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                // Delete old photo first
                $user = $this->userModel->find($id);
                if ($user && !empty($user['picture'])) {
                    $oldFile = FCPATH . $user['picture'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Move new file
                $photo->move($uploadPath, $newName);
                $data['picture'] = 'uploads/users/' . $newName;
            }


            // Update user
            $this->userModel->update($id, $data);

            // Redirect back with success message
            return redirect()->to(base_url('profile'))
                ->with('success', 'User info updated successfully.');
        } else {
            return redirect()->to(base_url('profile'))
                ->with('error', 'You are not able to update this profile.');
        }
    }

    public function calendar()
    {
        $this->data['title'] = 'Calendar';
        $this->data['activeSection'] = 'calendar';

        // Common navbar and sidebar for all views
        $this->data['navbarItems'] = [
            ['label' => 'Dashboard', 'url' => base_url('dashboard')],
            ['label' => 'Calendar', 'url' => base_url('calendar')],
        ];

        $user = [
            'name' => $this->session->get('name'),
            'email' => $this->session->get('email'),
            'phone' => $this->session->get('phone'),
            'role' => $this->session->get('role')
        ];

        $subjects = $this->subjectModel->findAll();

        $this->data['user'] = $user;
        $this->data['subjects'] = $subjects;

        return view('dashboard/calendar', $this->data);
    }

    public function events()
    {

        $events = $this->calendarModel->findAll();

        $data = array_map(function ($event) {
            $hasTime = strpos($event['end_date'], 'T') !== false;

            $endDate = $hasTime
                ? $event['end_date']
                : date('Y-m-d', strtotime($event['end_date'] . ' +1 day'));

            return [
                'id'          => $event['id'],
                'title'       => $event['title'],
                'start'       => $event['start_date'],
                'end'         => $endDate,
                'color'       => $event['color'],
                'description' => $event['description'],
                'allDay'      => true // important for date-only events
            ];
        }, $events);

        return $this->response->setJSON($data);
    }

    public function addEvent()
    {
        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'start_date'  => $this->request->getPost('start_date'),
            'start_time'  => $this->request->getPost('start_time'),
            'end_date'    => $this->request->getPost('end_date'),
            'end_time'    => $this->request->getPost('end_time'),
            'color'       => $this->request->getPost('color') ?? '#007bff',
            'category'    => $this->request->getPost('category'),
            'subcategory' => $this->request->getPost('subcategory'),
            'class'       => $this->request->getPost('class'),
            'subject'     => $this->request->getPost('subject')
        ];

        $this->calendarModel->save($data);
        return $this->response->setJSON(['status' => 'success']);
    }

    public function updateEvent()
    {
        $id = $this->request->getPost('id');

        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'start_date'  => $this->request->getPost('start_date'),
            'start_time'  => $this->request->getPost('start_time'),
            'end_date'    => $this->request->getPost('end_date'),
            'end_time'    => $this->request->getPost('end_time'),
            'color'       => $this->request->getPost('color') ?? '#007bff',
            'category'    => $this->request->getPost('category'),
            'subcategory' => $this->request->getPost('subcategory'),
            'class'       => $this->request->getPost('class'),
            'subject'     => $this->request->getPost('subject')
        ];

        $this->calendarModel->update($id, $data);
        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteEvent()
    {

        $this->calendarModel->delete($this->request->getPost('id'));

        return $this->response->setJSON(['status' => 'success']);
    }

    public function teachers()
    {

        $this->data['title'] = 'Teacher Management';
        $this->data['activeSection'] = 'teacher';

        // Common navbar and sidebar for all views
        $this->data['navbarItems'] = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];



        $users = $this->userModel
            ->where('account_status !=', 0)
            ->orderBy('position', 'ASC')
            ->findAll();
        $totalUsers = count($users);

        // Assign to $this->data
        $this->data['users'] = $users;
        $this->data['total_users'] = $totalUsers;

        return view('dashboard/ad_teacher_list', $this->data);
    }

    public function teachers_mark_given()
    {

        $this->data['title'] = 'Teacher Management';
        $this->data['activeSection'] = 'teacher';

        // Common navbar and sidebar for all views
        $this->data['navbarItems'] = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];

        $openExams = $this->markingModel
            ->where('status', 'open')
            ->findAll();

        if (!empty($openExams)) {
            // Extract exam names
            $examNames = array_column($openExams, 'exam_name');

            $total_subjects = $this->calendarModel
                ->where('subcategory', $examNames)
                ->where('category', 'Exam')
                ->findAll();

            $finalData = [];

            foreach ($total_subjects as $calendar) {

                $subjectId = $calendar['subject']; // adjust if object: $calendar->subject
                $examName  = $calendar['subcategory'];
                $year      = date('Y', strtotime($calendar['start_date']));

                // 1️⃣ Subject info
                $subjectInfo = $this->subjectModel->find($subjectId);

                // 2️⃣ Users assigned to this subject
                $users = $this->userModel
                    ->like('assagin_sub', $subjectId) // matches if subjectId exists in assign_sub string
                    ->findAll();

                // 3️⃣ Results for this subject, exam, and year
                $results = $this->resultModel
                    ->where('subject_id', $subjectId)
                    ->where('exam', $examName)
                    ->where('year', $year)
                    ->findAll();

                // Combine
                $finalData[] = [
                    'calendar' => $calendar,
                    'subject'  => $subjectInfo,
                    'users'    => $users,
                    'results'  => $results
                ];
            }
        } else {
            $finalData = [];
        }

        $this->data['joint_data'] = $finalData;

        // echo "<pre>";
        // print_r($finalData);
        // echo "</pre>";

        return view('dashboard/mark_given_teacher_list', $this->data);
    }

    public function updatePosition($id)
    {
        $position = $this->request->getPost('position');

        if ($position === null) {
            return redirect()->back()->with('error', 'Please select a position.');
        }

        $this->userModel->update($id, ['position' => $position]);

        return redirect()->back()->with('success', 'Position updated successfully.');
    }

    public function newUser()
    {

        $this->data['title'] = 'Teacher Management';
        $this->data['activeSection'] = 'teacher';

        // Common navbar and sidebar for all views
        $this->data['navbarItems'] = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];
        $newUsers = $this->userModel
            ->where('account_status', 0)
            ->findAll();

        $this->data['newUse'] = $newUsers;
        $this->data['total_newUse'] = count($newUsers);
        return view('dashboard/ad_new_user', $this->data);
    }

    public function user_permit($id)
    {
        $permitBy = $this->session->get('user_id');

        $updated = $this->userModel->update($id, [
            'account_status' => 1,
            'permit_by'    => $permitBy,
        ]);

        if ($updated) {
            return redirect()->back()->with('success', 'User approved successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to approve user.');
        }
    }

    public function user_delete($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        // delete user where id = $id
        if ($this->userModel->delete($id)) {
            // success message
            return redirect()->back()
                ->with('success', 'User deleted successfully.');
        } else {
            // fail message
            return redirect()->back()
                ->with('error', 'Failed to delete user.');
        }
    }

    public function teacher_management()
    {
        $subjects = $this->subjectModel->orderBy('id')->findAll();
        $users    = $this->userModel
            ->where('account_status !=', 0)
            ->orderBy('position', 'ASC')
            ->findAll();

        // Use $this->data which already has navbarItems, sidebarItems
        $this->data['title'] = 'Teacher Management';
        $this->data['activeSection'] = 'teacher';
        $this->data['navbarItems'] = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];
        $this->data['users'] = $users;
        $this->data['subjects'] = $subjects;

        return view('dashboard/teacher_management', $this->data);
    }

    public function teacherSubUpdate()
    {
        $id         = $this->request->getPost('id');
        $name       = $this->request->getPost('name');
        $assign_sub = $this->request->getPost('assign_sub'); // e.g., "4,7,9"



        $data = [
            'assagin_sub' => $assign_sub,  // store CSV in DB
        ];

        $this->userModel->update($id, $data);

        return redirect()->back()->with('success', 'Teacher updated with new subjects!');
    }


    public function assignSubject($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'No records found.');
        }

        $subjectIds = array_filter(
            array_map('intval', explode(',', $user['assagin_sub'] ?? ''))
        );

        $subjects = [];
        if (!empty($subjectIds)) {
            $subjects = $this->subjectModel
                ->whereIn('id', $subjectIds)
                ->orderBy('class', 'ASC')
                ->findAll();
        }

        // Use $this->data to avoid repeating common layout data
        $this->data['title']         = 'Assign Subject';
        $this->data['activeSection'] = 'teacher';
        $this->data['navbarItems']   = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];
        $this->data['user']          = $user;
        $this->data['subjects']      = $subjects;

        return view('dashboard/assign_subject', $this->data);
    }


    public function marking_open()
    {
        // Use $this->data which already has navbarItems, sidebarItems
        $this->data['title'] = 'Teacher Management';
        $this->data['activeSection'] = 'teacher';
        $this->data['navbarItems'] = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];


        $calendarModel = new CalendarModel();

        $examNames = $calendarModel
            ->select('subcategory')
            ->distinct()
            ->orderBy('subcategory', 'ASC')
            ->findAll();
        $this->data['exam_name'] = $examNames;

        return view('dashboard/marking_open', $this->data);
    }

    public function processMarkingOpen()
    {
        $examNames = $this->request->getPost('exam_name'); // array of selected exams
        $status    = $this->request->getPost('status');    // single status for all

        if (empty($examNames)) {
            return redirect()->back()->with('error', 'Please select at least one exam!');
        }

        $markingModel = new MarkingOpenModel();

        foreach ($examNames as $examName) {
            $exists = $markingModel->where('exam_name', $examName)->first();
            if ($exists) {
                $markingModel->update($exists['id'], ['status' => $status]);
            } else {
                $markingModel->insert([
                    'exam_name' => $examName,
                    'status'    => $status
                ]);
            }
        }

        return redirect()->to(base_url('marking_open'))
            ->with('success', 'Selected exams saved successfully.');
    }

    public function createStudentForm()
    {
        $this->data['title'] = 'Register Student';
        $this->data['activeSection'] = 'student';
        $this->data['navbarItems']   = [
            ['label' => 'Student List', 'url' => base_url('admin/student')],
            ['label' => 'Add Student', 'url' => base_url('admin/student/create')],
            ['label' => 'Assagin Subject', 'url' => base_url('admin/stAssaginSubView')],
            ['label' => 'Deleted Student', 'url' => base_url('admin/deletedStudent')],
        ];
        return view('dashboard/student_form', $this->data);
    }

    public function saveStudent()
    {
        helper(['form']);

        $rules = [
            'student_name' => 'required',
            'roll'         => 'required|numeric',
            'class'        => 'required',
            'section'      => 'permit_empty',
            'esif'         => 'required',
            'father_name'  => 'required',
            'mother_name'  => 'required',
            'dob'          => 'required|valid_date',
            'gender'       => 'required',
            'phone'        => 'required',
            'student_pic'  => 'uploaded[student_pic]|is_image[student_pic]',
            'birth_registration_number' => 'required',
            'father_nid_number'         => 'required',
            'mother_nid_number'         => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle student picture
        $file = $this->request->getFile('student_pic');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/students', $fileName);
        } else {
            return redirect()->back()->withInput()->with('errors', ['student_pic' => 'File upload failed.']);
        }

        // Prepare student data
        $data = [
            'student_name' => $this->request->getPost('student_name'),
            'roll'         => $this->request->getPost('roll'),
            'class'        => $this->request->getPost('class'),
            'section'      => $this->request->getPost('section'),
            'esif'         => $this->request->getPost('esif'),
            'father_name'  => $this->request->getPost('father_name'),
            'mother_name'  => $this->request->getPost('mother_name'),
            'dob'          => $this->request->getPost('dob'),
            'gender'       => $this->request->getPost('gender'),
            'phone'        => $this->request->getPost('phone'),
            'student_pic'  => 'uploads/students/' . $fileName,
            'birth_registration_number' => $this->request->getPost('birth_registration_number'),
            'father_nid_number'         => $this->request->getPost('father_nid_number'),
            'mother_nid_number'         => $this->request->getPost('mother_nid_number'),
        ];

        // Save to DB
        $this->studentModel->insert($data);

        return redirect()->to(site_url('admin/student/create'))->with('success', 'Student registered successfully!');
    }

    public function student()
    {
        // Get filter inputs
        $q        = $this->request->getGet('q');
        $class    = $this->request->getGet('class');
        $section  = $this->request->getGet('section');
        $religion = $this->request->getGet('religion');
        $gender   = $this->request->getGet('gender'); // ✅ separate variable

        // Distinct religions
        $religions = $this->studentModel
            ->select('religion')
            ->distinct()
            ->where('religion IS NOT NULL')
            ->orderBy('religion')
            ->findAll();

        // Distinct genders
        $genders = $this->studentModel
            ->select('gender')
            ->distinct()
            ->where('gender IS NOT NULL')
            ->orderBy('gender')
            ->findAll();

        // Build query
        $builder = $this->studentModel;

        // Always apply permission filter
        $builder = $builder->where('permission', 0);

        if ($q) {
            $builder = $builder->groupStart()
                ->like('student_name', $q)
                ->orLike('roll', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }
        if ($class) {
            $builder = $builder->where('class', $class);
        }
        if ($section) {
            $builder = $builder->where('section', $section);
        }
        if ($religion) {
            if ($religion === '__NULL__') {
                $builder = $builder->where('religion IS NULL'); // ✅ Matches "Not Set"
            } else {
                $builder = $builder->where('religion', $religion);
            }
        }
        if ($gender) {
            if ($gender === '__NULL__') {
                $builder = $builder->where('gender IS NULL'); // ✅ Matches "Not Set"
            } else {
                $builder = $builder->where('gender', $gender);
            }
        }

        $total = $builder->countAllResults(false);
        $perPage  = 20;
        $students = $builder
            ->orderBy('CAST(class as UNSIGNED) ASC')
            ->orderBy('CAST(roll as UNSIGNED) ASC')
            ->paginate($perPage, 'bootstrap');

        $sections = $this->studentModel->select('section')->distinct()->orderBy('section')->findAll();

        $this->data['title']         = 'Student Management';
        $this->data['activeSection'] = 'student';
        $this->data['navbarItems']   = [
            ['label' => 'Student List', 'url' => base_url('admin/student')],
            ['label' => 'Add Student', 'url' => base_url('admin/student/create')],
            ['label' => 'Assagin Subject', 'url' => base_url('admin/stAssaginSubView')],
            ['label' => 'Deleted Student', 'url' => base_url('admin/deletedStudent')],
        ];
        $this->data['students']   = $students;
        $this->data['pager']      = $this->studentModel->pager;
        $this->data['q']          = $q;
        $this->data['class']      = $class;
        $this->data['section']    = $section;
        $this->data['sections']   = $sections;
        $this->data['religion']   = $religion;
        $this->data['religions']  = $religions;
        $this->data['gender']     = $gender;   // ✅ new
        $this->data['genders']    = $genders;  // ✅ new
        $this->data['total']      = $total;

        return view('dashboard/student', $this->data);
    }

    public function softDelete($id)
    {

        // Get current student
        $student = $this->studentModel->find($id);

        if ($student) {

            // Update student
            $this->studentModel->update($id, ['permission' => 1]);

            return redirect()->back()->with('success', 'Permission updated successfully');
        }

        return redirect()->back()->with('error', 'Student not found');
    }

    public function hardDelete($id)
    {
        // Load student record
        $student = $this->studentModel->find($id);

        if ($student) {
            // Check if student has a photo
            if (!empty($student['student_pic'])) {
                // Build full path to the file
                $photoPath = FCPATH . $student['student_pic'];

                // If file exists, delete it
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }

            // Delete student record from database
            $this->studentModel->delete($id);

            // Redirect with success message
            return redirect()->back()->with('success', 'Student and picture deleted successfully.');
        }

        // If student not found
        return redirect()->back()->with('error', 'Student not found.');
    }

    public function deleted_student()
    {


        // Get filter inputs
        $q       = $this->request->getGet('q');
        $class   = $this->request->getGet('class');
        $section = $this->request->getGet('section');
        $religion = $this->request->getGet('religion');
        $religions = $this->studentModel
            ->select('religion')
            ->distinct()
            ->where('religion IS NOT NULL')
            ->orderBy('religion')
            ->findAll();
        // Build query
        $builder = $this->studentModel;

        // Always apply permission filter
        $builder = $builder->where('permission', 1);

        if ($q) {
            $builder = $builder->groupStart()
                ->like('student_name', $q)
                ->orLike('roll', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }
        if ($class) {
            $builder = $builder->where('class', $class);
        }
        if ($section) {
            $builder = $builder->where('section', $section);
        }
        if ($religion) {
            if ($religion === '__NULL__') {
                $builder = $builder->where('religion IS NULL'); // ✅ Matches "Not Set"
            } else {
                $builder = $builder->where('religion', $religion);
            }
        }
        $total = $builder->countAllResults(false);
        $perPage  = 20;
        $students = $builder
            ->orderBy('CAST(class as UNSIGNED) ASC')
            ->orderBy('CAST(roll as UNSIGNED) ASC')
            ->paginate($perPage, 'bootstrap');

        $sections = $this->studentModel->select('section')->distinct()->orderBy('section')->findAll();

        $this->data['title']         = 'Student Management';
        $this->data['activeSection'] = 'student';
        $this->data['navbarItems']   = [
            ['label' => 'Student List', 'url' => base_url('admin/student')],
            ['label' => 'Add Student', 'url' => base_url('admin/student/create')],
            ['label' => 'Assagin Subject', 'url' => base_url('admin/stAssaginSubView')],
            ['label' => 'Deleted Student', 'url' => base_url('admin/deletedStudent')],
        ];
        $this->data['students']      = $students;
        $this->data['pager']         = $this->studentModel->pager;
        $this->data['q']             = $q;
        $this->data['class']         = $class;
        $this->data['section']       = $section;
        $this->data['sections']      = $sections;
        $this->data['religion']   = $religion;
        $this->data['religions']  = $religions;
        $this->data['total']  = $total;



        return view('dashboard/deleted_student', $this->data);
    }

    public function softActive($id)
    {

        // Get current student
        $student = $this->studentModel->find($id);

        if ($student) {

            // Update student
            $this->studentModel->update($id, ['permission' => 0]);

            return redirect()->back()->with('success', 'Permission updated successfully');
        }

        return redirect()->back()->with('error', 'Student not found');
    }

    public function stAssaginSubView()
    {


        // Get filter inputs
        $q       = $this->request->getGet('q');
        $class   = $this->request->getGet('class');
        $section = $this->request->getGet('section');
        $religion = $this->request->getGet('religion');

        // Build query
        $builder = $this->studentModel;
        if ($q) {
            $builder = $builder->groupStart()
                ->like('student_name', $q)
                ->orLike('roll', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }
        if ($class) {
            $builder = $builder->where('class', $class);
        }
        if ($section) {
            $builder = $builder->where('section', $section);
        }
        if ($religion) {
            $builder = $builder->where('religion', $religion);
        }

        $students = $builder
            ->orderBy('CAST(class as UNSIGNED)', 'ASC')
            ->orderBy('CAST(roll as UNSIGNED)', 'ASC')
            ->get()
            ->getResultArray();

        $sections = $this->studentModel->select('section')->distinct()->orderBy('section')->findAll();
        $religions = $this->studentModel->select('religion')->distinct()->where('religion IS NOT NULL')->orderBy('religion')->findAll();
        $subjectBuilder = $this->subjectModel;

        if ($class) {
            $subjectBuilder = $subjectBuilder->where('class', $class);
        }

        if (stripos($section, 'Vocational') !== false) {
            $filteredSection = 'Vocational';
        } else {
            $filteredSection = 'General';
        }

        if ($filteredSection) {
            $subjectBuilder = $subjectBuilder->where('section', $filteredSection);
        }

        $subjects = $subjectBuilder->findAll();

        $this->data['title']         = 'Student Subject Management';
        $this->data['activeSection'] = 'student';
        $this->data['navbarItems']   = [
            ['label' => 'Student List', 'url' => base_url('admin/student')],
            ['label' => 'Add Student', 'url' => base_url('admin/student/create')],
            ['label' => 'Assagin Subject', 'url' => base_url('admin/stAssaginSubView')],
            ['label' => 'Deleted Student', 'url' => base_url('admin/deletedStudent')],
        ];
        $this->data['students']      = $students;
        $this->data['subjects']      = $subjects;
        $this->data['pager']         = $this->studentModel->pager;
        $this->data['q']             = $q;
        $this->data['class']         = $class;
        $this->data['section']       = $section;
        $this->data['sections']      = $sections;
        $this->data['religion']      = $religion;
        $this->data['religions']     = $religions;

        return view('dashboard/stSubAssaginment', $this->data);
    }

    public function assignStudentsSubjects()
    {
        $students = $this->request->getPost('left_select');
        $subjects = $this->request->getPost('right_select');

        if (!empty($students) && !empty($subjects)) {
            $subjectCodes = implode(',', $subjects);
            $studentModel = new StudentModel();

            foreach ($students as $studentId) {
                $studentModel->update($studentId, ['assign_sub' => $subjectCodes]);
            }
            return redirect()->back()->with('success', 'Subjects assigned successfully.');
        }
        return redirect()->back()->with('error', 'Please select at least one student and one subject.');
    }

    public function exam_name($userId, $subjectId)
    {
        $this->data['title']         = 'Select Exam';
        $this->data['activeSection'] = 'teacher';
        $this->data['navbarItems']   = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];
        $this->data['user_id']    = $userId;
        $this->data['subject_id'] = $subjectId;

        // ✅ fetch all exams where status is open (id + exam_name only)
        $this->data['exams'] = $this->markingModel
            ->select('id, exam_name')
            ->where('status', 'open')
            ->findAll();

        return view('dashboard/exam_name', $this->data);
    }

    public function result()
    {
        $userId     = $this->request->getPost('user_id');
        $subjectId  = $this->request->getPost('subject_id');
        $exam_name  = $this->request->getPost('exam_name');


        $user    = $this->userModel->find($userId);
        $subject = $this->subjectModel->find($subjectId);
        $class = $subject['class'];

        if (!$user) {
            return redirect()->back()->with('error', 'User data is not Found.');
        } elseif (!$subject) {
            return redirect()->back()->with('error', 'Subject is not found.');
        } elseif (!$exam_name) {
            return redirect()->back()->with('error', 'No Exam is selected.');
        } elseif (($exam_name == 'Pre-Test Exam' || $exam_name == 'Test Exam') && $class != 10) {
            return redirect()->back()
                ->with('error', $exam_name . ' is not allowed for class ' . $class);
        }

        $students = $this->studentModel
            ->where("FIND_IN_SET(" . (int)$subjectId . ", assign_sub) >", 0, false)
            ->orderBy('CAST(roll AS UNSIGNED)', 'ASC', false)
            ->findAll();

        // 🔄 Load existing results for this teacher and subject
        $results = $this->resultModel
            ->where('teacher_id', $userId)
            ->where('subject_id', $subjectId)
            ->where('exam', $exam_name)
            ->where('year', date('Y')) // optional filter
            ->findAll();

        // 🔃 Index results by student_id for quick lookup
        $indexedResults = [];
        foreach ($results as $r) {
            $indexedResults[$r['student_id']] = $r;
        }

        $this->data['title']           = 'Result Entry';
        $this->data['activeSection']   = 'teacher';
        $this->data['navbarItems']     = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Open', 'url' => base_url('marking_open')],
        ];
        $this->data['user']            = $user;
        $this->data['subject']         = $subject;
        $this->data['exam_name']         = $exam_name;
        $this->data['students']        = $students;
        $this->data['existingResults'] = $indexedResults;

        return view('dashboard/ad_result', $this->data);
    }

    public function submitResults()
    {
        $students   = $this->request->getPost('students');
        $exam       = $this->request->getPost('exam');
        $year       = $this->request->getPost('year');
        $subjectId  = $this->request->getPost('subject_id');
        $teacherId  = $this->request->getPost('teacher_id');
        $class     = $this->request->getPost('class');

        if (!$students || !$exam || !$year || !$subjectId  || !$teacherId || !$class) {
            return redirect()->back()->with('error', 'Missing data.');
        }

        foreach ($students as $student) {
            $written   = isset($student['written']) ? (int)$student['written'] : 0;
            $mcq       = isset($student['mcq']) ? (int)$student['mcq'] : 0;
            $practical = isset($student['practical']) ? (int)$student['practical'] : 0;
            $total     = $written + $mcq + $practical;

            $existing = $this->resultModel
                ->where('student_id', $student['id'])
                ->where('subject_id', $subjectId)
                ->where('exam', $exam)
                ->where('year', $year)
                ->first();

            $data = [
                'student_id' => $student['id'],
                'subject_id' => $subjectId,
                'exam'       => $exam,
                'year'       => $year,
                'class'      => $class,
                'written'    => $written,
                'mcq'        => $mcq,
                'practical'  => $practical,
                'total'      => $total,
                'teacher_id' => $teacherId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->resultModel->update($existing['id'], $data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->resultModel->insert($data);
            }
        }

        return redirect()->to(base_url('exam_name/' . $teacherId . '/' . $subjectId))
            ->with('success', 'Results submitted successfully.');
    }

    public function exam_name_result_check($userId, $subjectId)
    {
        $this->data['title']         = 'Select Exam';
        $this->data['activeSection'] = 'teacher';
        $this->data['navbarItems']   = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];
        $this->data['user_id']    = $userId;
        $this->data['subject_id'] = $subjectId;

        // ✅ fetch all exams where status is open (id + exam_name only)
        $this->data['exams'] = $this->markingModel
            ->select('id, exam_name')
            ->where('status', 'open')
            ->findAll();

        return view('dashboard/exam_name_result_check', $this->data);
    }

    public function ResultCheck()
    {
        $userId     = $this->request->getPost('user_id');
        $subjectId  = $this->request->getPost('subject_id');
        $exam_name  = $this->request->getPost('exam_name');

        $subject = $this->subjectModel->find($subjectId);
        $users    = $this->userModel->find($userId);

        $class = $subject['class'];

        if (!$users) {
            return redirect()->back()->with('error', 'User data is not Found.');
        } elseif (!$subject) {
            return redirect()->back()->with('error', 'Subject is not found.');
        } elseif (!$exam_name) {
            return redirect()->back()->with('error', 'No Exam is selected.');
        } elseif (($exam_name == 'Pre-Test Exam' || $exam_name == 'Test Exam') && $class != 10) {
            return redirect()->back()
                ->with('error', $exam_name . ' is not allowed for class ' . $class);
        }

        $result = $this->resultModel
            ->select('results.*, students.student_name, students.roll, students.class')
            ->join('students', 'students.id = results.student_id')
            ->where('results.subject_id', $subjectId)
            ->where('results.teacher_id', $userId)
            ->where('results.exam', $exam_name)
            ->orderBy('CAST(students.roll AS UNSIGNED)', 'ASC', false)
            ->findAll();


        $this->data['title'] = 'Student Details';
        $this->data['activeSection'] = 'teacher';
        $this->data['navbarItems'] = [
            ['label' => 'Teacher List', 'url' => base_url('teacher_management')],
            ['label' => 'Marking Action', 'url' => base_url('marking_open')],
        ];
        $this->data['subject'] = $subject;
        $this->data['users'] = $users;
        $this->data['result'] = $result;

        return view('dashboard/resultCheck', $this->data);
    }

    public function selectTabulationForm()
    {


        // ✅ Distinct class list from students
        $classes = $this->studentModel->distinct()->select('class')->orderBy('class', 'ASC')->findAll();

        $rawSections = $this->studentModel
            ->distinct()
            ->select('section')
            ->orderBy('section', 'ASC')
            ->findAll();

        $sections = [
            ['section' => 'General'],
            ['section' => 'Vocational'],
            ['section' => 'Science'],
            ['section' => 'arts'],
        ];


        // ✅ Distinct exam names and years from results
        $exams = $this->resultModel->distinct()->select('exam')->orderBy('exam', 'ASC')->findAll();
        $years = $this->resultModel->distinct()->select('year')->orderBy('year', 'DESC')->findAll();
        // Send to view
        $this->data['title']    = 'Select Tabulation Info';
        $this->data['activeSection'] = 'result';
        $this->data['navbarItems'] = [
            ['label' => 'Tabulation Sheet', 'url' => base_url('admin/tabulation_form')],
            ['label' => 'Marksheet', 'url' => base_url('admin/select-marksheet')],
        ];
        $this->data['classes']  = $classes;
        $this->data['sections'] = $sections;
        $this->data['exams']    = $exams;
        $this->data['years']    = $years;

        return view('dashboard/select_exam_info', $this->data);
    }

    public function mark()
    {
        // Pass data to the view
        $this->data['title']     = 'Tabulation Sheet';
        $this->data['activeSection'] = 'result';
        $this->data['navbarItems'] = [
            ['label' => 'Tabulation Sheet', 'url' => base_url('admin/tabulation_form')],
            ['label' => 'Marksheet', 'url' => base_url('admin/select-marksheet')],
            ['label' => 'Marksheet', 'url' => base_url('admin/tabulation/download')],
        ];


        $class   = $this->request->getPost('class');
        $section = $this->request->getPost('section');
        $exam    = $this->request->getPost('exam');
        $year    = $this->request->getPost('year');


        $builder = $this->studentModel->where('class', $class);

        // If class is NOT 6 to 8, add section filter
        if (!in_array($class, ['6', '7', '8'])) {
            $builder->like('section', $section);
        }

        $students = $builder
            ->orderBy('CAST(roll AS UNSIGNED)', 'ASC', false)
            ->findAll();

        $finalData = [];

        foreach ($students as $student) {
            $studentId = $student['id'];

            // Step 2: Get results for this student, exam, and year
            $results = $this->resultModel
                ->where('student_id', $studentId)
                ->where('exam', $exam)
                ->where('year', $year)
                ->findAll();

            // Step 3: Build subject-wise results array
            $subjectResults = [];
            foreach ($results as $res) {
                $subjectName = $this->subjectModel
                    ->select('subject')
                    ->where('id', $res['subject_id'])
                    ->first()['subject'] ?? 'Unknown';

                $subjectResults[] = [
                    'subject_id' => $res['subject_id'],
                    'subject'   => $subjectName,
                    'written'   => $res['written'] ?? 0,
                    'mcq'       => $res['mcq'] ?? 0,
                    'practical' => $res['practical'] ?? 0,
                    'total'     => $res['total'] ?? 0,
                ];
            }

            usort($subjectResults, function ($a, $b) {
                return $a['subject_id'] <=> $b['subject_id'];
            });

            // Step 4: Append student data with their results
            $finalData[] = [
                'student_id' => $student['id'],
                'name'       => $student['student_name'] ?? 'Unknown',
                'roll'       => $student['roll'],
                'group'      => $section ?? 'general',
                'exam'       => $exam,
                'year'       => $year,
                'results'    => $subjectResults,
            ];
        }
        $this->data['finalData'] = $finalData;
        $this->data['class']     = $class;
        $this->data['exam']      = $exam;
        $this->data['year']      = $year;
        // echo '<pre>';
        // print_r($finalData);
        // echo '</pre>';
        return view('dashboard/mark_copy', $this->data);
    }

    private function getTabulationData(): array
    {
        // Use your actual data fetching logic here
        $class = $this->request->getGet('class') ?? '9';
        $exam  = $this->request->getGet('exam') ?? 'Half Yearly';
        $year  = $this->request->getGet('year') ?? date('Y');

        $studentModel = new \App\Models\StudentModel();
        $resultModel = new \App\Models\ResultModel();

        $students = $studentModel
            ->select('id, roll, student_name') // include 'name' here
            ->where('class', $class)
            ->orderBy('CAST(roll AS UNSIGNED)', 'ASC', false)
            ->findAll();

        $finalData = [];

        foreach ($students as $student) {
            $results = $resultModel
                ->where('student_id', $student['id'])
                ->where('exam', $exam)
                ->where('year', $year)
                ->findAll();

            $finalData[] = [
                'roll' => $student['roll'],
                'name' => $student['student_name'],
                'results' => $results
            ];
        }

        return $finalData;
    }

    public function downloadCSV()
    {
        helper('text');

        // Example: load your finalData array from your model or session
        $finalData = $this->getTabulationData(); // <-- Replace with actual data fetch logic
        $subjectList = [];

        // Get unique subject names
        foreach ($finalData as $student) {
            foreach ($student['results'] as $res) {
                if (!in_array($res['subject'], $subjectList)) {
                    $subjectList[] = $res['subject'];
                }
            }
        }

        // Set CSV headers
        $headers = ['Roll', 'Name'];
        foreach ($subjectList as $subject) {
            $headers[] = "$subject - W";
            $headers[] = "$subject - MCQ";
            $headers[] = "$subject - Prac";
            $headers[] = "$subject - Total";
        }
        $headers[] = 'Total Marks';

        // Set headers for CSV download
        $filename = 'tabulation_sheet_' . date('Ymd_His') . '.csv';

        // Start streaming the CSV
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv");

        $file = fopen('php://output', 'w');

        // Write header row
        fputcsv($file, $headers);

        // Write each student's result row
        foreach ($finalData as $student) {
            $row = [];
            $row[] = $student['roll'];
            $row[] = $student['name'];

            $subjectMap = [];
            foreach ($student['results'] as $res) {
                $subjectMap[$res['subject']] = $res;
            }

            $totalMarks = 0;

            foreach ($subjectList as $subject) {
                $written = $subjectMap[$subject]['written'] ?? '';
                $mcq = $subjectMap[$subject]['mcq'] ?? '';
                $practical = $subjectMap[$subject]['practical'] ?? '';
                $total = $subjectMap[$subject]['total'] ?? '';

                $row[] = $written;
                $row[] = $mcq;
                $row[] = $practical;
                $row[] = $total;

                if (is_numeric($total)) {
                    $totalMarks += $total;
                }
            }

            $row[] = $totalMarks;

            fputcsv($file, $row);
        }

        fclose($file);
        exit;
    }

    public function selectMarksheetForm()
    {

        $classes = $this->studentModel->distinct()->select('class')->orderBy('class', 'ASC')->findAll();
        $sections = [
            ['section' => 'general'],
            ['section' => 'vocational'],
        ];
        $exams = $this->resultModel->distinct()->select('exam')->orderBy('exam', 'ASC')->findAll();
        $years = $this->resultModel->distinct()->select('year')->orderBy('year', 'DESC')->findAll();

        $this->data['title']         = 'Select Marksheet Info';
        $this->data['activeSection'] = 'result';
        $this->data['navbarItems'] = [
            ['label' => 'Tabulation Sheet', 'url' => base_url('admin/tabulation_form')],
            ['label' => 'Marksheet', 'url' => base_url('admin/select-marksheet')],
        ];
        $this->data['classes']       = $classes;
        $this->data['sections']      = $sections;
        $this->data['exams']         = $exams;
        $this->data['years']         = $years;

        return view('dashboard/select_marksheet_info', $this->data);
    }

    public function showMarksheet()
    {
        $this->data['title'] = 'Marksheet';
        $this->data['activeSection'] = 'result';
        $this->data['navbarItems'] = [
            ['label' => 'Tabulation Sheet', 'url' => base_url('admin/tabulation_form')],
            ['label' => 'Marksheet', 'url' => base_url('admin/select-marksheet')],
        ];

        $request = service('request');
        $searchType = $request->getGet('search_type');

        if ($searchType === 'id') {
            $studentId = $request->getGet('student_id');
            $exam      = $request->getGet('exam');
            $year      = $request->getGet('year');

            if (!$studentId) {
                return redirect()->back()->with('error', 'Please enter a Student ID.');
            }

            $student = $this->studentModel->find($studentId);

            if (!$student) {
                return redirect()->back()->with('error', 'Student not found.');
            }

            // Fetch results with subject name
            $marksheet = $this->resultModel
                ->select('results.*, subjects.subject, subjects.full_mark')
                ->join('subjects', 'subjects.id = results.subject_id')
                ->where([
                    'results.student_id' => $studentId,
                    'results.exam'       => $exam,
                    'results.year'       => $year,
                ])
                ->findAll();

            // Sort based on assigned subjects
            $assigned = explode(',', $student['assign_sub'] ?? '');
            $orderMap = array_flip($assigned);

            usort($marksheet, function ($a, $b) use ($orderMap) {
                $posA = $orderMap[$a['subject_id']] ?? PHP_INT_MAX;
                $posB = $orderMap[$b['subject_id']] ?? PHP_INT_MAX;
                return $posA <=> $posB;
            });

            $this->data['examName'] = $exam;
            $this->data['examYear'] = $year;
            $this->data['student'] = $student;
            $this->data['marksheet'] = $marksheet;

            return view('dashboard/marksheet_view', $this->data);
        } elseif ($searchType === 'roll') {
            $class   = $request->getGet('class');
            $section = $request->getGet('section');
            $roll    = $request->getGet('roll');
            $exam    = $request->getGet('exam');
            $year    = $request->getGet('year');

            if (!$class || !$section || !$roll || !$exam || !$year) {
                return redirect()->back()->with('error', 'Please fill in all fields.');
            }

            $builder = $this->studentModel
                ->where('class', $class)
                ->where('roll', $roll);

            if ($section === 'vocational') {
                $builder->like('section', 'vocational');
            } else {
                $builder->groupStart()
                    ->like('section', 'n/a')
                    ->orLike('section', 'general')
                    ->groupEnd();
            }

            $student = $builder->first();

            if (!$student) {
                return redirect()->back()->with('error', 'Student not found for given Class/Roll.');
            }

            // Fetch marksheet with subject join
            $marksheet = $this->resultModel
                ->select('results.*, subjects.subject, subjects.full_mark')
                ->join('subjects', 'subjects.id = results.subject_id')
                ->where([
                    'results.student_id' => $student['id'],
                    'results.exam'       => $exam,
                    'results.year'       => $year,
                ])
                ->findAll();

            // Sort by assigned subjects
            $assignRaw = explode(',', $student['assign_sub']);
            $starredId = null;
            $ordered = [];

            // Separate normal and starred
            foreach ($assignRaw as $id) {
                if (str_ends_with($id, '*')) {
                    $starredId = rtrim($id, '*');
                } else {
                    $ordered[] = $id;
                }
            }

            usort($marksheet, function ($a, $b) use ($ordered, $starredId) {
                // If either subject is the starred one
                if ($a['subject_id'] == $starredId) {
                    return 1;
                }
                if ($b['subject_id'] == $starredId) {
                    return -1;
                }

                // Compare position in ordered list
                $posA = array_search($a['subject_id'], $ordered);
                $posB = array_search($b['subject_id'], $ordered);
                return $posA <=> $posB;
            });

            $this->data['examName'] = $exam;
            $this->data['examYear'] = $year;
            $this->data['student'] = $student;
            $this->data['marksheet'] = $marksheet;
            // echo '<pre>';
            // print_r($marksheet);
            // echo '</pre>';

            return view('dashboard/marksheet_view', $this->data);
        }

        return redirect()->back()->with('error', 'Invalid search method.');
    }

    public function viewStudent($id)
    {
        $this->studentModel = new StudentModel();
        $this->subjectModel = new SubjectModel();

        $student = $this->studentModel->find($id);

        if (!$student) {
            return redirect()->back()->with('error', 'No data found');
        }

        // ✅ Step 1: Parse subject IDs and extract 4th subject
        $subject_str_id = $student['assign_sub'];
        $rawIds = explode(',', $subject_str_id); // e.g. ['12', '13', '14*', '15']

        $subjectIds = [];
        $fourthSubjectId = null;

        foreach ($rawIds as $idEntry) {
            if (str_contains($idEntry, '*')) {
                $fourthSubjectId = str_replace('*', '', $idEntry);
                $subjectIds[] = $fourthSubjectId;
            } else {
                $subjectIds[] = $idEntry;
            }
        }

        $subjects = $this->subjectModel
            ->whereIn('id', $subjectIds)
            ->findAll();

        $fourthSubjectName = null;
        if ($fourthSubjectId) {
            $fourth = $this->subjectModel->find($fourthSubjectId);
            if ($fourth) {
                $fourthSubjectName = $fourth['subject'];
            }
        }

        // ✅ Step 4: Pass to view
        $this->data['title'] = 'Student Details';
        $this->data['activeSection'] = 'student';
        $this->data['navbarItems'] = [
            ['label' => 'Student List', 'url' => base_url('ad-student')],
            ['label' => 'Add Student', 'url' => base_url('student_create')],
            ['label' => 'View Student', 'url' => current_url()],
        ];
        $this->data['student'] = $student;
        $this->data['subjectsStr'] = $subject_str_id;
        $this->data['subjects'] = $subjects;
        $this->data['forthSubject'] = $fourthSubjectName;

        return view('dashboard/student_view', $this->data);
    }

    public function forthsub($id)
    {
        $subjectId = $this->request->getPost('subject_id');

        $subjectId = str_replace('*', '', $subjectId);

        $selectId  = $this->request->getPost('selectid');
        $className = $this->request->getPost('subject_class');



        if (in_array($className, [6, 7, 8])) {
            return redirect()->back()->with('error', 'Sorry Class 6, 7, 8 does not have 4th subject.');
        }
        if (!$selectId) {
            return redirect()->back()->with('error', 'Sir, No subject is selected.');
        } else {
            $subject = $this->subjectModel->find($selectId);
            $subjectNames = array_map('trim', explode(',', $subject['subject']));
            $subjectText = implode(', ', $subjectNames);

            $ids = $this->subjectModel
                ->select('id')
                ->whereIn('subject', [
                    'Higher Mathematics',
                    'Biology',
                    'Agriculture Studies',
                    'Agriculture Studies-1',
                    'Agriculture Studies-2'
                ])
                ->whereIn('class', [9, 10])
                ->findAll();

            $ids = array_column($ids, 'id');

            if (!in_array((int)$selectId, $ids)) {
                return redirect()->back()->with('error', 'Sorry sir, (' . $subjectText . ') is not a 4th subject.');
            } else {
                $replace = $selectId . "*";
                $updated = str_replace($selectId, $replace, $subjectId);

                $updatedResult = $this->studentModel->update($id, [
                    'assign_sub' => $updated,
                ]);

                if ($updatedResult) {
                    return redirect()->back()->with('success', $subjectText . ' is selected as 4th subject updated successfully to ID ' . $id);
                } else {
                    $dbError = $this->studentModel->db->error();
                    $errorMsg = $dbError['message'] ?? 'Unknown error occurred.';

                    return redirect()->back()->with('error', 'Update failed: ' . $errorMsg);
                }
            }
        }
    }

    public function editStudent($id)
    {

        $this->studentModel = new StudentModel();
        $student = $this->studentModel->find($id);

        if (!$student) {
            return redirect()->to('ad-student')->with('error', 'Student not found.');
        }

        $this->data['title'] = 'Edit Student';
        $this->data['activeSection'] = 'student';
        $this->data['navbarItems'] = [
            ['label' => 'Student List', 'url' => base_url('ad-student')],
            ['label' => 'Add Student', 'url' => base_url('student_create')],
            ['label' => 'Edit Student', 'url' => current_url()],
        ];
        $this->data['student'] = $student;

        return view('dashboard/student_edit', $this->data);
    }

    public function updateStudent($id)
    {
        $this->studentModel = new StudentModel();
        $student = $this->studentModel->find($id);

        if (!$student) {
            return redirect()->to('ad-student')->with('error', 'Student not found.');
        }

        $data = $this->request->getPost([
            'student_name',
            'roll',
            'class',
            'section',
            'esif',
            'father_name',
            'mother_name',
            'dob',
            'gender',
            'phone',
            'birth_registration_number',
            'father_nid_number',
            'mother_nid_number',
            'religion',
            'blood_group'
        ]);

        $this->studentModel->update($id, $data);

        return redirect()->to('admin/students/view/' . $id)->with('message', 'Student updated successfully.');
    }

    public function editStudentPhoto($id)
    {
        $this->studentModel = new StudentModel();
        $student = $this->studentModel->find($id);

        if (!$student) {
            return redirect()->to('admin/students')->with('error', 'Student not found.');
        }

        $this->data = [
            'title' => 'Edit Photo',
            'activeSection' => 'student',
            'navbarItems' => [
                ['label' => 'Student List', 'url' => base_url('ad-student')],
                ['label' => 'Edit Photo', 'url' => current_url()],
            ],
            'student' => $student
        ];

        return view('dashboard/edit_photo', $this->data);
    }

    public function updateStudentPhoto($id)
    {
        $this->studentModel = new StudentModel();
        $student = $this->studentModel->find($id);

        if (!$student) {
            return redirect()->to('admin/students')->with('error', 'Student not found.');
        }

        $file = $this->request->getFile('student_pic');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/students', $newName);

            // Delete old photo if it exists and is not default
            if (!empty($student['student_pic']) && file_exists(FCPATH . $student['student_pic'])) {
                unlink(FCPATH . $student['student_pic']);
            }

            // Update DB
            $this->studentModel->update($id, [
                'student_pic' => 'uploads/students/' . $newName,
            ]);

            return redirect()->to('admin/students/view/' . $id)->with('message', 'Photo updated successfully.');
        }

        return redirect()->back()->with('error', 'Photo upload failed.');
    }


    // Show all notices
    public function notices()
    {
        $this->data['title'] = 'Notice List';
        $this->data['activeSection'] = 'notice';
        $this->data['navbarItems'] = [
            ['label' => 'Notice List', 'url' => current_url()],
            ['label' => 'Add Notice', 'url' => base_url('admin/noticeForm')],
        ];
        $this->data['notices'] = $this->noticeModel->orderBy('id', 'DESC')->findAll();
        return view('dashboard/notice_list', $this->data);
    }

    // Show add form
    public function noticeForm()
    {
        $this->data['title'] = 'Notice Form';
        $this->data['activeSection'] = 'notice';
        $this->data['navbarItems'] = [
            ['label' => 'Notice List', 'url' => base_url('admin/notices')],
            ['label' => 'Add Notice', 'url' => current_url()],
        ];
        return view('dashboard/notice_form', $this->data);
    }

    // Save new notice
    public function saveNotice()
    {
        $data = [
            'title'       => $this->request->getPost('title'),
            'body'        => $this->request->getPost('body'),
            'notice_date' => $this->request->getPost('notice_date'),
            'status'      => $this->request->getPost('status'), // <--- Add this line
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        // Handle file upload
        $file = $this->request->getFile('document_url');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/notices', $newName);
            $data['document_url'] = $newName;
        }

        $this->noticeModel->insert($data);
        return redirect()->to('admin/notices')->with('success', 'Notice added successfully!');
    }

    // Edit form
    public function editNotice($id)
    {
        $this->data['title'] = 'Notice Form';
        $this->data['activeSection'] = 'notice';
        $this->data['navbarItems'] = [
            ['label' => 'Notice List', 'url' => base_url('admin/notices')],
            ['label' => 'Add Notice', 'url' => current_url()],
        ];

        $this->data['notice'] = $this->noticeModel->find($id);

        if (!$this->data['notice']) {
            return redirect()->to('admin/notices')->with('error', 'Notice not found');
        }

        return view('dashboard/notice_form_edit', $this->data);
    }

    // Update existing notice
    public function updateNotice($id)
    {
        $noticeModel = new NoticeModel();
        $notice = $noticeModel->find($id);

        if (!$notice) {
            return redirect()->to('admin/notices')->with('error', 'Notice not found');
        }

        $data = [
            'title'       => $this->request->getPost('title'),
            'body'        => $this->request->getPost('body'),
            'notice_date' => $this->request->getPost('notice_date'),
            'status'      => $this->request->getPost('status'), // <--- Add this line
        ];

        // File update
        $file = $this->request->getFile('document_url');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!empty($notice['document_url']) && file_exists('uploads/notices/' . $notice['document_url'])) {
                unlink('uploads/notices/' . $notice['document_url']);
            }
            $newName = $file->getRandomName();
            $file->move('uploads/notices', $newName);
            $data['document_url'] = $newName;
        }

        $noticeModel->update($id, $data);
        return redirect()->to('admin/notices')->with('success', 'Notice updated successfully!');
    }

    // Delete notice
    public function deleteNotice($id)
    {
        $noticeModel = new NoticeModel();
        $notice = $noticeModel->find($id);

        if ($notice) {
            if (!empty($notice['document_url']) && file_exists('uploads/notices/' . $notice['document_url'])) {
                unlink('uploads/notices/' . $notice['document_url']);
            }
            $noticeModel->delete($id);
        }

        return redirect()->to('admin/notices')->with('success', 'Notice deleted successfully!');
    }

    public function attendanceCalendar()
    {
        $this->data['title'] = 'Attendance';
        $this->data['activeSection'] = 'attendance';
        $this->data['navbarItems'] = [
            ['label' => 'Attendance', 'url' => base_url('attendance')],
        ];

        helper(['form', 'url']);

        // Get POST values
        $selectedClass   = $this->request->getPost('class') ?? '';
        $selectedSection = $this->request->getPost('section') ?? '';
        $selectedDate    = $this->request->getPost('date') ?? date('Y-m-d');

        // Base query
        $builder = $this->studentModel->where('permission', 0);

        // Filter by class
        if ($selectedClass) {
            $builder->where('class', $selectedClass);
        }

        // ✅ Section Filter
        if (strtolower($selectedSection) === 'general') {
            // Exclude any section containing "Vocational"
            $builder->notLike('section', 'Vocational');
        } elseif (strtolower($selectedSection) === 'vocational') {
            // Include only sections containing "Vocational"
            $builder->like('section', 'Vocational');
        }

        // Order and fetch
        $students = $builder->orderBy('CAST(roll AS UNSIGNED)', 'ASC')->findAll();

        // Get attendance for the selected date
        $attendances = $this->attendanceModel
            ->select('student_id, remark, DATE(created_at) as date')
            ->where('created_at >=', $selectedDate . ' 00:00:00')
            ->where('created_at <=', $selectedDate . ' 23:59:59')
            ->findAll();

        // Attendance mapping
        $attendanceMap = [];
        foreach ($attendances as $a) {
            if (!isset($attendanceMap[$a['student_id']])) {
                $attendanceMap[$a['student_id']] = [];
            }
            $attendanceMap[$a['student_id']][] = $a['remark'];
        }

        // Pass data to view
        $this->data['selectedClass']   = $selectedClass;
        $this->data['selectedSection'] = $selectedSection;
        $this->data['selectedDate']    = $selectedDate;
        $this->data['students']        = $students;
        $this->data['attendanceMap']   = $attendanceMap;

        return view('dashboard/attendance_calendar', $this->data);
    }

    public function saveAttendance()
    {
        $attendance = $this->request->getPost('attendance');

        $countUpdated = 0;
        $countDeleted = 0;
        $holidayError = false; // track if any date is a holiday

        foreach ($attendance as $studentId => $dates) {
            foreach ($dates as $date => $remark) {

                // 🔹 Check if date is Friday or Saturday
                $dayName = date('D', strtotime($date));
                if ($dayName === 'Fri' || $dayName === 'Sat') {
                    $holidayError = true;
                    continue; // skip saving attendance for this date
                }

                if ($remark === 'P') {
                    // --- Attend Record ---
                    $attendExists = $this->attendanceModel
                        ->where('student_id', $studentId)
                        ->where('remark', 'A')
                        ->where('DATE(created_at)', $date)
                        ->first();

                    if ($attendExists) {
                        $this->attendanceModel->update($attendExists['id'], [
                            'created_at' => $date . ' 10:00:00',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        $this->attendanceModel->insert([
                            'student_id' => $studentId,
                            'remark'     => 'A',
                            'created_at' => $date . ' 10:00:00',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                    // --- Leave Record ---
                    $leaveExists = $this->attendanceModel
                        ->where('student_id', $studentId)
                        ->where('remark', 'L')
                        ->where('DATE(created_at)', $date)
                        ->first();

                    if ($leaveExists) {
                        $this->attendanceModel->update($leaveExists['id'], [
                            'created_at' => $date . ' 16:00:00',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        $this->attendanceModel->insert([
                            'student_id' => $studentId,
                            'remark'     => 'L',
                            'created_at' => $date . ' 16:00:00',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                    $countUpdated++;
                } elseif ($remark === 'A') {
                    // Delete any existing Attend/Leave records
                    $existingRecords = $this->attendanceModel
                        ->where('student_id', $studentId)
                        ->whereIn('remark', ['A', 'L'])
                        ->where('DATE(created_at)', $date)
                        ->findAll();

                    foreach ($existingRecords as $record) {
                        $this->attendanceModel->delete($record['id']);
                    }

                    $countDeleted++;
                }
            }
        }

        // 🔹 Prepare message
        $messages = [];
        if ($holidayError) {
            $messages[] = "This day is a public holiday (Friday or Saturday). Attendance not saved.";
        }
        if ($countUpdated > 0) {
            $messages[] = "Attendance updated for {$countUpdated} students.";
        }
        if ($countDeleted > 0) {
            $messages[] = "Attendance deleted for {$countDeleted} students.";
        }

        $flashMessage = !empty($messages) ? implode(' | ', $messages) : "No changes made.";

        // 🔹 Choose message type
        $alertType = $holidayError ? 'error' : 'success';

        return redirect()->back()->with($alertType, $flashMessage);
    }

    public function transactionDashboard()
    {
        $this->data['title'] = 'Transaction Dashboard';
        $this->data['activeSection'] = 'accounts';
        $this->data['navbarItems'] = [
            ['label' => 'Accounts', 'url' => base_url('admin/transactions')],
            ['label' => 'Teacher', 'url' => base_url('admin/tec_pay')],
            ['label' => 'Students', 'url' => base_url('admin/std_pay')],
            ['label' => 'Statistics', 'url' => base_url('admin/pay_stat')],
            ['label' => 'Set Fees', 'url' => base_url('admin/set_fees')],
        ];

        $this->data['transactions'] = $this->transactionModel->orderBy('created_at', 'DESC')->findAll();

        // ✅ Totals
        $totalEarnRow = $this->transactionModel->where('status', 0)->selectSum('amount')->get()->getRowArray();
        $totalCostRow = $this->transactionModel->where('status', 1)->selectSum('amount')->get()->getRowArray();

        $this->data['totalEarn'] = $totalEarnRow['amount'] ?? 0;
        $this->data['totalCost'] = $totalCostRow['amount'] ?? 0;

        $builder = db_connect()->table('transactions');

        // ✅ Current month (daily earn vs cost)
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $currentMonthData = $builder
            ->select("
            DATE(created_at) as date,
            SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as earn,
            SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as cost
        ")
            ->where('created_at >=', $monthStart)
            ->where('created_at <=', $monthEnd)
            ->groupBy('DATE(created_at)')
            ->orderBy('DATE(created_at)', 'ASC')
            ->get()
            ->getResultArray();

        $this->data['dailyLabels'] = array_column($currentMonthData, 'date');
        $this->data['dailyEarns'] = array_map('floatval', array_column($currentMonthData, 'earn'));
        $this->data['dailyCosts'] = array_map('floatval', array_column($currentMonthData, 'cost'));

        // ✅ 12-month summary (month-wise)
        $yearData = $builder
            ->select("
            MONTH(created_at) as month,
            SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as earn,
            SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as cost
        ")
            ->where('YEAR(created_at)', date('Y'))
            ->groupBy('MONTH(created_at)')
            ->orderBy('MONTH(created_at)', 'ASC')
            ->get()
            ->getResultArray();

        $this->data['monthLabels'] = array_map(fn($m) => date('M', mktime(0, 0, 0, $m['month'], 10)), $yearData);
        $this->data['monthEarns'] = array_map('floatval', array_column($yearData, 'earn'));
        $this->data['monthCosts'] = array_map('floatval', array_column($yearData, 'cost'));

        return view('dashboard/transaction_dashboard', $this->data);
    }

    public function tec_pay()
    {
        $this->data['title'] = 'Transaction Dashboard';
        $this->data['activeSection'] = 'accounts';

        $this->data['navbarItems'] = [
            ['label' => 'Accounts', 'url' => base_url('admin/transactions')],
            ['label' => 'Teacher', 'url' => base_url('admin/tec_pay')],
            ['label' => 'Students', 'url' => base_url('admin/std_pay')],
            ['label' => 'Statistics', 'url' => base_url('admin/pay_stat')],
            ['label' => 'Set Fees', 'url' => base_url('admin/set_fees')],
        ];

        return view('dashboard/tec_pay', $this->data);
    }

    public function std_pay()
    {
        $this->data['title'] = 'Student Payment';
        $this->data['activeSection'] = 'payments';

        $this->data['navbarItems'] = [
            ['label' => 'Accounts', 'url' => base_url('admin/transactions')],
            ['label' => 'Teacher', 'url' => base_url('admin/tec_pay')],
            ['label' => 'Students', 'url' => base_url('admin/std_pay')],
            ['label' => 'Statistics', 'url' => base_url('admin/pay_stat')],
            ['label' => 'Set Fees', 'url' => base_url('admin/set_fees')],
        ];

        $builder = $this->studentModel->builder();

        // ✅ Get search/filter values
        $search = $this->request->getGet('search');
        $class = $this->request->getGet('class');
        $section = $this->request->getGet('section');

        // ✅ Apply search (roll, ID, or name)
        if ($search) {
            $builder->groupStart()
                ->like('roll', $search)
                ->orLike('id', $search)
                ->orLike('student_name', $search)
                ->groupEnd();
        }

        // ✅ Apply class & section filters
        if ($class) {
            $builder->where('class', $class);
        }
        if ($section) {
            $builder->where('section', $section);
        }

        // ✅ Get results
        $this->data['students'] = $builder
            ->orderBy('student_name', 'ASC')
            ->get()
            ->getResultArray();



        // Load total fees per class
        $this->data['fees_summary'] = $this->feesAmountModel
            ->select('class, SUM(fees) AS total_fees')
            ->groupBy('class')
            ->orderBy('class', 'ASC')
            ->get()
            ->getResultArray();

        // Convert fees_summary into an easy-to-lookup array: [class => total_fees]
        $classFees = [];
        foreach ($this->data['fees_summary'] as $row) {
            $classFees[$row['class']] = $row['total_fees'];
        }

        $this->data['classFees'] = $classFees; // ✅ pass to view

        // Load total deposit money per sender
        $this->data['fees_deposit'] = $this->transactionModel
            ->select('sender_id, sender_name, SUM(amount) AS total_deposit')
            ->groupBy('sender_id, sender_name')
            ->orderBy('sender_name', 'ASC')
            ->get()
            ->getResultArray();

        // Convert fees_deposit into an easy-to-lookup array: [sender_id => total_deposit]
        $senderDeposits = [];
        foreach ($this->data['fees_deposit'] as $row) {
            $senderDeposits[$row['sender_id']] = $row['total_deposit'];
        }

        $this->data['senderDeposits'] = $senderDeposits; // ✅ pass to view

        // ✅ Dropdown options
        $this->data['classes'] = $this->studentModel->select('class')->distinct()->orderBy('class', 'ASC')->get()->getResultArray();
        $this->data['sections'] = $this->studentModel->select('section')->distinct()->orderBy('section', 'ASC')->get()->getResultArray();

        // ✅ Pass search values to view
        $this->data['search'] = $search;
        $this->data['selectedClass'] = $class;
        $this->data['selectedSection'] = $section;

        return view('dashboard/std_pay', $this->data);
    }

    public function pay_stat()
    {
        $this->data['title'] = 'Transaction Dashboard';
        $this->data['activeSection'] = 'accounts';

        $this->data['navbarItems'] = [
            ['label' => 'Accounts', 'url' => base_url('admin/transactions')],
            ['label' => 'Teacher', 'url' => base_url('admin/tec_pay')],
            ['label' => 'Students', 'url' => base_url('admin/std_pay')],
            ['label' => 'Statistics', 'url' => base_url('admin/pay_stat')],
            ['label' => 'Set Fees', 'url' => base_url('admin/set_fees')],
        ];

        return view('dashboard/pay_stat', $this->data);
    }

    public function set_fees()
    {
        $this->data['title'] = 'Transaction Dashboard';
        $this->data['activeSection'] = 'accounts';

        $this->data['navbarItems'] = [
            ['label' => 'Accounts', 'url' => base_url('admin/transactions')],
            ['label' => 'Teacher', 'url' => base_url('admin/tec_pay')],
            ['label' => 'Students', 'url' => base_url('admin/std_pay')],
            ['label' => 'Statistics', 'url' => base_url('admin/pay_stat')],
            ['label' => 'Set Fees', 'url' => base_url('admin/set_fees')],
        ];


        $class = $this->request->getGet('class');

        // ✅ Fetch distinct classes dynamically from students
        $classes = $this->studentModel
            ->select('class')
            ->distinct()
            ->orderBy('CAST(class AS UNSIGNED)', 'ASC')
            ->findAll();
        $this->data['classes'] = array_column($classes, 'class');

        $this->data['selectedClass'] = $class;
        $this->data['titles'] = $this->feesModel->findAll();

        $existingAmounts = [];
        $existingUnits = [];
        $existingUpdates = [];

        $totalAmount = 0;

        if ($class) {
            $amounts = $this->feesAmountModel->where('class', $class)->findAll();
            foreach ($amounts as $a) {
                $existingAmounts[$a['title_id']] = $a['fees'];
                $existingUnits[$a['title_id']] = $a['unit'];
                $existingUpdates[$a['title_id']] = $a['updated_at'];

                // ✅ Calculate total = Σ (unit * fees)
                if (is_numeric($a['fees']) && is_numeric($a['unit'])) {
                    $totalAmount += $a['fees'] * $a['unit'];
                }
            }
        }

        $this->data['existingAmounts'] = $existingAmounts;
        $this->data['existingUnits'] = $existingUnits;
        $this->data['existingUpdates'] = $existingUpdates;
        $this->data['totalAmount'] = $totalAmount;

        return view('dashboard/set_fees', $this->data);
    }

    public function save_fees()
    {
        $class = $this->request->getPost('class');
        $feesData = $this->request->getPost('fees');
        $unitsData = $this->request->getPost('units');

        if (!$class) {
            return redirect()->back()->with('error', 'Please select a class before saving.');
        }

        if (empty($feesData)) {
            return redirect()->back()->with('error', 'No fee amounts to save.');
        }

        $amountModel = new FeesAmountModel();

        foreach ($feesData as $title_id => $amount) {
            if ($amount === '' || $amount === null) {
                continue;
            }

            $unit = isset($unitsData[$title_id]) ? $unitsData[$title_id] : null;

            $existing = $this->feesAmountModel->where('class', $class)
                ->where('title_id', $title_id)
                ->first();

            if ($existing) {
                $amountModel->update($existing['id'], [
                    'fees' => $amount,
                    'unit' => $unit,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $amountModel->insert([
                    'class' => $class,
                    'title_id' => $title_id,
                    'fees' => $amount,
                    'unit' => $unit,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return redirect()->back()->with('success', 'Fees updated successfully!');
    }

    public function payStudentRequest($id)
    {
        $this->data['title'] = 'Student Payment';
        $this->data['activeSection'] = 'accounts';

        $this->data['navbarItems'] = [
            ['label' => 'Accounts', 'url' => base_url('admin/transactions')],
            ['label' => 'Teacher', 'url' => base_url('admin/tec_pay')],
            ['label' => 'Students', 'url' => base_url('admin/std_pay')],
            ['label' => 'Statistics', 'url' => base_url('admin/pay_stat')],
            ['label' => 'Set Fees', 'url' => base_url('admin/set_fees')],
        ];


        // ✅ Load models
        // $studentModel = new \App\Models\StudentModel();
        // $feesModel = new \App\Models\FeesModel();
        // $feesAmountModel = new \App\Models\FeesAmountModel();
        // $userModel = new \App\Models\UserModel();

        // 🧍 Get student info
        $student = $this->studentModel->find($id);
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }

        // 🎓 Get all fees titles
        $fees = $this->feesModel->findAll();

        // 💰 Get class-wise fee amounts
        $classFees = $this->feesAmountModel->where('class', $student['class'])->findAll();

        // 🧾 Map fee amounts properly using title_id
        $feeAmounts = [];
        foreach ($classFees as $f) {
            $feeAmounts[$f['title_id']] = $f['fees'];
            $feeUnit[$f['title_id']] = $f['unit'];
        }

        // 👨‍🏫 Receiver (default admin)
        $userId = $this->session->get('user_id');
        $receiver = $this->userModel->find($userId);

        // 📦 Prepare data for view
        $this->data['student'] = $student;
        $this->data['fees'] = $fees;
        $this->data['feeAmounts'] = $feeAmounts;
        $this->data['feeUnit'] = $feeUnit;
        $this->data['receiver'] = $receiver;

        return view('dashboard/payStudentRequest', $this->data);
    }

    public function submitStudentPayment()
    {
        $studentId  = $this->request->getPost('student_id');
        $receiverId = $this->request->getPost('receiver_id');
        $amounts    = $this->request->getPost('amount');
        $feeIds     = $this->request->getPost('fee_id');

        $student  = $this->studentModel->find($studentId);
        $receiver = $this->userModel->find($receiverId);

        if (!$student || !$receiver) {
            return redirect()->back()->with('error', 'Invalid student or receiver.');
        }

        if (empty($amounts) || empty($feeIds)) {
            return redirect()->back()->with('error', 'No payment data provided.');
        }

        $successCount = 0;
        $errorMessages = [];

        foreach ($feeIds as $index => $feeId) {
            $amount = $amounts[$index] ?? 0;
            if ($amount <= 0) {
                continue;
            }

            // Get maximum allowed for this fee for the student's class
            $feeMax = $this->feesAmountModel
                ->where('class', $student['class'])
                ->where('title_id', $feeId)
                ->first();
            $maxAmount = $feeMax['unit'] * $feeMax['fees'] ?? 0;

            // Calculate total already paid by this student for this fee
            $feeTitle = $this->feesModel->find($feeId)['title'] ?? 'Unknown Fee';
            $totalPaid = $this->transactionModel
                ->where('sender_id', $student['id'])
                ->where('purpose', $feeTitle)
                ->select('SUM(amount) as paid')
                ->first();
            $paidAmount = $totalPaid['paid'] ?? 0;

            if ($paidAmount >= $maxAmount) {
                $errorMessages[] = "Sorry, maximum payment for '{$feeTitle}' already received.";
                continue;
            }

            // Prevent overpayment
            if ($paidAmount + $amount > $maxAmount) {
                $amount = $maxAmount - $paidAmount;
            }

            $this->transactionModel->insert([
                'transaction_id' => uniqid('TXN'),
                'sender_id'      => $student['id'],
                'sender_name'    => $student['student_name'],
                'receiver_id'    => $receiver['id'],
                'receiver_name'  => $receiver['name'],
                'amount'         => $amount,
                'purpose'        => $feeTitle,
                'description'    => 'Educational fees payment request',
                'status'         => 0,
            ]);

            $successCount++;
        }

        // Send messages separately
        if ($successCount > 0) {
            session()->setFlashdata('success', "$successCount payment request(s) submitted successfully.");
        }

        if (!empty($errorMessages)) {
            session()->setFlashdata('error', implode(' ', $errorMessages));
        }

        return redirect()->to(base_url('admin/std_pay'));
    }

    public function studentPaymentHistory($studentId)
    {

        // ✅ Page setup (for navbar & active section)
        $this->data['title'] = 'Student Payment';
        $this->data['activeSection'] = 'accounts';
        $this->data['navbarItems'] = [
            ['label' => 'Accounts', 'url' => base_url('admin/transactions')],
            ['label' => 'Teacher', 'url' => base_url('admin/tec_pay')],
            ['label' => 'Students', 'url' => base_url('admin/std_pay')],
            ['label' => 'Statistics', 'url' => base_url('admin/pay_stat')],
            ['label' => 'Set Fees', 'url' => base_url('admin/set_fees')],
        ];

        $student = $this->studentModel->find($studentId);
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }
        // ✅ Fetch all transactions for this student
        $payments = $this->transactionModel
            ->where('sender_id', $studentId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // ✅ Calculate total paid
        $totalPaid = array_sum(array_column($payments, 'amount'));

        // ✅ Pass data to view
        $this->data['student']   = $student;
        $this->data['payments']  = $payments;
        $this->data['totalPaid'] = $totalPaid;

        return view('dashboard/student_payment_history', $this->data);
    }
}
