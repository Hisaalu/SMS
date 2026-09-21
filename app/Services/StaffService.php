<?php
// File: /app/Services/StaffService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class StaffService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getStaffMembers(int $schoolId, array $filters = []): array
    {
        $query = "SELECT st.*, 
                        sc.name as category_name, 
                        ss.name as status_name, 
                        ss.is_active_status, 
                        d.name as department_name, 
                        u.email as user_account_email
                FROM staff st
                LEFT JOIN staff_categories sc ON st.staff_category_id = sc.id
                LEFT JOIN staff_statuses ss ON st.staff_status_id = ss.id
                LEFT JOIN departments d ON st.department_id = d.id
                LEFT JOIN users u ON st.user_id = u.id
                WHERE st.school_id = :school_id";
        
        $params = ['school_id' => $schoolId];
        
        // Bind filters dynamically with separate parameters
        if (!empty($filters['search'])) {
            $query .= " AND (st.first_name LIKE :search_first 
                        OR st.last_name LIKE :search_last 
                        OR st.username LIKE :search_user 
                        OR st.staff_number LIKE :search_num 
                        OR st.email LIKE :search_email 
                        OR st.phone LIKE :search_phone 
                        OR CONCAT(st.first_name, ' ', st.last_name) LIKE :search_fullname)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params['search_first'] = $searchTerm;
            $params['search_last'] = $searchTerm;
            $params['search_user'] = $searchTerm;
            $params['search_num'] = $searchTerm;
            $params['search_email'] = $searchTerm;
            $params['search_phone'] = $searchTerm;
            $params['search_fullname'] = $searchTerm;
        }
        
        if (!empty($filters['category_id'])) {
            $query .= " AND st.staff_category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }
        
        if (!empty($filters['status_id'])) {
            $query .= " AND st.staff_status_id = :status_id";
            $params['status_id'] = (int)$filters['status_id'];
        }
        
        if (!empty($filters['department_id'])) {
            $query .= " AND st.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }
        
        $query .= " ORDER BY st.created_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }
    
    public function getCategories(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM staff_categories WHERE school_id = :school_id AND status = 'active' ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
    
    public function getStatuses(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM staff_statuses WHERE school_id = :school_id AND status = 'active' ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
    
    public function getDepartments(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM departments WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
    }
    
    public function createStaff(array $data, int $schoolId, int $userId): int
    {
        $data['school_id'] = $schoolId;
        
        // Check username uniqueness — but skip if this is the user we just created
        if (!empty($data['username'])) {
            // Check in staff table
            $existingStaff = $this->db->fetch(
                "SELECT id FROM staff WHERE username = :username AND school_id = :school_id",
                ['username' => $data['username'], 'school_id' => $schoolId]
            );
            if ($existingStaff) {
                throw new \Exception("Username '{$data['username']}' is already taken by another staff member.");
            }
            
            // Check in users table — allow if it matches the user_id we just created
            $existingUser = $this->db->fetch(
                "SELECT id FROM users WHERE username = :username",
                ['username' => $data['username']]
            );
            
            $linkedUserId = $data['user_id'] ?? null;
            if ($existingUser && (empty($linkedUserId) || (int)$existingUser['id'] !== (int)$linkedUserId)) {
                throw new \Exception("Username '{$data['username']}' is already taken by a system user.");
            }
        }
        
        $sql = "INSERT INTO staff (
                    school_id, staff_number, username, first_name, middle_name, last_name, 
                    gender, date_of_birth, marital_status, phone, alt_phone, email, address, 
                    photo_path, staff_category_id, staff_status_id, department_id, position, 
                    employment_date, employment_type, user_id
                ) VALUES (
                    :school_id, :staff_number, :username, :first_name, :middle_name, :last_name, 
                    :gender, :dob, :marital_status, :phone, :alt_phone, :email, :address, 
                    :photo_path, :category_id, :status_id, :department_id, :position, 
                    :emp_date, :emp_type, :user_id
                )";
        
        $params = [
            'school_id'      => (int)$data['school_id'],
            'staff_number'   => $data['staff_number'],
            'username'       => $data['username'] ?? null,
            'first_name'     => $data['first_name'],
            'middle_name'    => !empty($data['middle_name']) ? $data['middle_name'] : null,
            'last_name'      => $data['last_name'],
            'gender'         => $data['gender'] ?? 'male',
            'dob'            => !empty($data['dob']) ? $data['dob'] : null,
            'marital_status' => $data['marital_status'] ?? 'single',
            'phone'          => !empty($data['phone']) ? $data['phone'] : null,
            'alt_phone'      => !empty($data['alt_phone']) ? $data['alt_phone'] : null,
            'email'          => !empty($data['email']) ? $data['email'] : null,
            'address'        => !empty($data['address']) ? $data['address'] : null,
            'photo_path'     => !empty($data['photo_path']) ? $data['photo_path'] : null,
            'category_id'    => (int)$data['category_id'],
            'status_id'      => (int)$data['status_id'],
            'department_id'  => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'position'       => !empty($data['position']) ? $data['position'] : null,
            'emp_date'       => !empty($data['emp_date']) ? $data['emp_date'] : null,
            'emp_type'       => $data['emp_type'] ?? 'full_time',
            'user_id'        => !empty($data['user_id']) ? (int)$data['user_id'] : null,
        ];
        
        $this->db->execute($sql, $params);
        $staffId = $this->db->lastInsertId();
        
        if ($staffId) {
            $this->db->execute(
                "INSERT INTO staff_status_history (staff_id, old_status_id, new_status_id, effective_date, reason, changed_by)
                VALUES (:staff_id, NULL, :new_status, CURDATE(), 'Initial Employment Registration', :changed_by)",
                [
                    'staff_id'   => $staffId,
                    'new_status' => (int)$data['status_id'],
                    'changed_by' => $userId
                ]
            );
        }
        
        return $staffId;
    }
    
    public function updateStatus(int $staffId, int $newStatusId, int $schoolId, int $userId, string $reason, string $effectiveDate): bool
    {
        $staff = $this->db->fetch(
            "SELECT * FROM staff WHERE id = :id AND school_id = :school_id",
            ['id' => $staffId, 'school_id' => $schoolId]
        );
        
        if (!$staff) {
            return false;
        }
        
        $oldStatusId = $staff['staff_status_id'];
        
        if ($oldStatusId == $newStatusId) {
            return false;
        }
        
        $this->db->update('staff', ['staff_status_id' => $newStatusId], ['id' => $staffId]);
        
        $this->db->execute(
            "INSERT INTO staff_status_history (staff_id, old_status_id, new_status_id, effective_date, reason, changed_by)
             VALUES (:staff_id, :old_status, :new_status, :effective_date, :reason, :changed_by)",
            [
                'staff_id' => $staffId,
                'old_status' => $oldStatusId,
                'new_status' => $newStatusId,
                'effective_date' => $effectiveDate,
                'reason' => $reason,
                'changed_by' => $userId
            ]
        );
        
        return true;
    }
    
    public function linkAccount(int $staffId, int $userId, int $schoolId): bool
    {
        $staff = $this->db->fetch(
            "SELECT * FROM staff WHERE id = :id AND school_id = :school_id",
            ['id' => $staffId, 'school_id' => $schoolId]
        );
        
        if (!$staff) {
            return false;
        }
        
        $this->db->update('staff', ['user_id' => $userId], ['id' => $staffId]);
        return true;
    }
    
    public function unlinkAccount(int $staffId, int $schoolId): bool
    {
        $staff = $this->db->fetch(
            "SELECT * FROM staff WHERE id = :id AND school_id = :school_id",
            ['id' => $staffId, 'school_id' => $schoolId]
        );
        
        if (!$staff) {
            return false;
        }
        
        $this->db->update('staff', ['user_id' => null], ['id' => $staffId]);
        return true;
    }
    
    public function getStatusHistory(int $staffId): array
    {
        return $this->db->fetchAll(
            "SELECT sh.*, ns.name as new_status_name, os.name as old_status_name, u.username as authorizer
             FROM staff_status_history sh
             JOIN staff_statuses ns ON sh.new_status_id = ns.id
             LEFT JOIN staff_statuses os ON sh.old_status_id = os.id
             LEFT JOIN users u ON sh.changed_by = u.id
             WHERE sh.staff_id = :id ORDER BY sh.id DESC",
            ['id' => $staffId]
        );
    }
    
    public function getUnlinkedUsers(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT id, username, email FROM users 
             WHERE school_id = :school_id 
             AND id NOT IN (SELECT user_id FROM staff WHERE user_id IS NOT NULL)",
            ['school_id' => $schoolId]
        );
    }
    
    public function generateStaffNumber(int $schoolId): string
    {
        $lastStaff = $this->db->fetch(
            "SELECT id FROM staff WHERE school_id = :school_id ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );
        $nextNum = ($lastStaff['id'] ?? 0) + 1;
        return 'STF-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function checkUsernameAvailability(string $username, int $schoolId): bool
    {
        // Check in staff table
        $existingStaff = $this->db->fetch(
            "SELECT id FROM staff WHERE username = :username AND school_id = :school_id",
            ['username' => $username, 'school_id' => $schoolId]
        );
        if ($existingStaff) {
            return false;
        }
        
        // Check in users table (system users)
        $existingUser = $this->db->fetch(
            "SELECT id FROM users WHERE username = :username",
            ['username' => $username]
        );
        if ($existingUser) {
            return false;
        }
        
        return true;
    }

    public function updateStaff(int $id, array $data, int $schoolId): bool
    {
        $sql = "UPDATE staff SET 
                    first_name = :first_name,
                    middle_name = :middle_name,
                    last_name = :last_name,
                    gender = :gender,
                    date_of_birth = :dob,
                    marital_status = :marital_status,
                    phone = :phone,
                    alt_phone = :alt_phone,
                    email = :email,
                    address = :address,
                    staff_category_id = :category_id,
                    staff_status_id = :status_id,
                    department_id = :department_id,
                    position = :position,
                    employment_date = :emp_date,
                    employment_type = :emp_type" . 
                    (!empty($data['photo_path']) ? ", photo_path = :photo_path" : "") . "
                WHERE id = :id AND school_id = :school_id";

        $params = [
            'id' => $id,
            'school_id' => $schoolId,
            'first_name' => $data['first_name'],
            'middle_name' => !empty($data['middle_name']) ? $data['middle_name'] : null,
            'last_name' => $data['last_name'],
            'gender' => $data['gender'] ?? 'male',
            'dob' => !empty($data['dob']) ? $data['dob'] : null,
            'marital_status' => $data['marital_status'] ?? 'single',
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'alt_phone' => !empty($data['alt_phone']) ? $data['alt_phone'] : null,
            'email' => !empty($data['email']) ? $data['email'] : null,
            'address' => !empty($data['address']) ? $data['address'] : null,
            'category_id' => (int)$data['category_id'],
            'status_id' => (int)$data['status_id'],
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'position' => !empty($data['position']) ? $data['position'] : null,
            'emp_date' => !empty($data['emp_date']) ? $data['emp_date'] : null,
            'emp_type' => $data['emp_type'] ?? 'full_time'
        ];

        if (!empty($data['photo_path'])) {
            $params['photo_path'] = $data['photo_path'];
        }

        $this->db->execute($sql, $params);
        return true;
    }

    public function getStaffMember(int $id, int $schoolId): ?array
    {
        $query = "SELECT st.*, 
                        sc.name as category_name, 
                        ss.name as status_name, 
                        ss.is_active_status, 
                        d.name as department_name, 
                        u.email as user_account_email
                FROM staff st
                LEFT JOIN staff_categories sc ON st.staff_category_id = sc.id
                LEFT JOIN staff_statuses ss ON st.staff_status_id = ss.id
                LEFT JOIN departments d ON st.department_id = d.id
                LEFT JOIN users u ON st.user_id = u.id
                WHERE st.id = :id AND st.school_id = :school_id
                LIMIT 1";

        $result = $this->db->fetch($query, [
            'id' => $id,
            'school_id' => $schoolId
        ]);

        return $result ?: null;
    }

    // --- Staff Categories Methods ---

public function getAllCategories(int $schoolId): array
{
    return $this->db->fetchAll(
        "SELECT c.*, (SELECT COUNT(*) FROM staff WHERE staff_category_id = c.id) AS usage_count 
         FROM staff_categories c 
         WHERE c.school_id = :school_id 
         ORDER BY c.display_order ASC, c.name ASC",
        ['school_id' => $schoolId]
    );
}

public function createCategory(array $data, int $schoolId): bool
{
    return $this->db->execute(
        "INSERT INTO staff_categories (school_id, name, code, description, display_order, status) 
         VALUES (:school_id, :name, :code, :description, :display_order, :status)",
        [
            'school_id' => $schoolId,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'description' => $data['description'] ?? null,
            'display_order' => (int)($data['display_order'] ?? 0),
            'status' => $data['status'] ?? 'active'
        ]
    );
}

public function updateCategory(int $id, array $data, int $schoolId): bool
{
    return $this->db->execute(
        "UPDATE staff_categories 
         SET name = :name, code = :code, description = :description, display_order = :display_order, status = :status 
         WHERE id = :id AND school_id = :school_id",
        [
            'id' => $id,
            'school_id' => $schoolId,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'description' => $data['description'] ?? null,
            'display_order' => (int)($data['display_order'] ?? 0),
            'status' => $data['status'] ?? 'active'
        ]
    );
}

    public function getAllStatuses(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, (SELECT COUNT(*) FROM staff WHERE staff_status_id = s.id) AS usage_count 
            FROM staff_statuses s 
            WHERE s.school_id = :school_id 
            ORDER BY s.display_order ASC, s.name ASC",
            ['school_id' => $schoolId]
        );
    }

    public function createStatus(array $data, int $schoolId): bool
    {
        return $this->db->execute(
            "INSERT INTO staff_statuses (school_id, name, code, description, is_active_status, allows_login, display_order, status) 
            VALUES (:school_id, :name, :code, :description, :is_active_status, :allows_login, :display_order, :status)",
            [
                'school_id' => $schoolId,
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'description' => $data['description'] ?? null,
                'is_active_status' => !empty($data['is_active_status']) ? 1 : 0,
                'allows_login' => !empty($data['allows_login']) ? 1 : 0,
                'display_order' => (int)($data['display_order'] ?? 0),
                'status' => $data['status'] ?? 'active'
            ]
        );
    }

    public function updateStatusRecord(int $id, array $data, int $schoolId): bool
    {
        return $this->db->execute(
            "UPDATE staff_statuses 
            SET name = :name, code = :code, description = :description, 
                is_active_status = :is_active_status, allows_login = :allows_login, 
                display_order = :display_order, status = :status 
            WHERE id = :id AND school_id = :school_id",
            [
                'id' => $id,
                'school_id' => $schoolId,
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'description' => $data['description'] ?? null,
                'is_active_status' => !empty($data['is_active_status']) ? 1 : 0,
                'allows_login' => !empty($data['allows_login']) ? 1 : 0,
                'display_order' => (int)($data['display_order'] ?? 0),
                'status' => $data['status'] ?? 'active'
            ]
        );
    }
    
    public function getLinkedUser(int $staffId, int $schoolId): ?array
    {
        return $this->db->fetch(
            "SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.status,
                    (SELECT GROUP_CONCAT(r.name SEPARATOR ', ') 
                    FROM user_roles ur 
                    JOIN roles r ON ur.role_id = r.id 
                    WHERE ur.user_id = u.id) AS role_names
            FROM users u
            INNER JOIN staff st ON st.user_id = u.id
            WHERE st.id = :staff_id AND st.school_id = :school_id
            LIMIT 1",
            ['staff_id' => $staffId, 'school_id' => $schoolId]
        ) ?: null;
    }

    public function getTeacherAssignments(int $staffId, int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT ta.*, 
                    c.name AS class_name, 
                    s.name AS subject_name,
                    st.name AS stream_name,
                    tat.name AS assignment_type_name
            FROM teacher_assignments ta
            LEFT JOIN classes c ON ta.class_id = c.id
            LEFT JOIN subjects s ON ta.subject_id = s.id
            LEFT JOIN streams st ON ta.stream_id = st.id
            LEFT JOIN teacher_assignment_types tat ON ta.assignment_type_id = tat.id
            WHERE ta.staff_id = :staff_id AND ta.school_id = :school_id
            ORDER BY c.name, s.name",
            ['staff_id' => $staffId, 'school_id' => $schoolId]
        );
    }

    /**
     * Create a user account and link it to a staff member.
     */
    public function createUserForStaff(
        int $staffId,
        array $userData,
        int $roleId,
        int $schoolId
    ): int {
        // Validate
        if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
            throw new \Exception('Username, email, and password are required.');
        }
        if (!$this->checkUsernameAvailability($userData['username'], $schoolId)) {
            throw new \Exception("Username '{$userData['username']}' is already taken.");
        }

        // Create user
        $userId = $this->db->insert('users', [
            'school_id'  => $schoolId,
            'username'   => $userData['username'],
            'email'      => $userData['email'],
            'password'   => password_hash($userData['password'], PASSWORD_BCRYPT),
            'first_name' => $userData['first_name'] ?? '',
            'last_name'  => $userData['last_name']  ?? '',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$userId) {
            throw new \Exception('Failed to create user account.');
        }

        // Assign role
        if ($roleId) {
            $this->db->insert('user_roles', [
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Link to staff
        $this->db->update('staff', ['user_id' => $userId], ['id' => $staffId]);

        return $userId;
    }

    /**
     * Update the linked user account.
     */
    public function updateLinkedUser(
        int $staffId,
        array $userData,
        ?int $roleId,
        int $schoolId
    ): void {
        $user = $this->getLinkedUser($staffId, $schoolId);
        if (!$user) {
            throw new \Exception('No linked user account found.');
        }

        $update = [
            'email'      => $userData['email']      ?? $user['email'],
            'first_name' => $userData['first_name'] ?? $user['first_name'],
            'last_name'  => $userData['last_name']  ?? $user['last_name'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($userData['password'])) {
            $update['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
        }

        $this->db->update('users', $update, ['id' => $user['id']]);

        // Update role if provided
        if ($roleId) {
            $this->db->execute("DELETE FROM user_roles WHERE user_id = :uid", ['uid' => $user['id']]);
            $this->db->insert('user_roles', [
                'user_id'    => $user['id'],
                'role_id'    => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Unlink the user account from staff.
     */
    public function unlinkUser(int $staffId, int $schoolId): void
    {
        $this->db->update('staff', ['user_id' => null], [
            'id' => $staffId, 'school_id' => $schoolId
        ]);
    }
}