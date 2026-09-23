<?php
// File: /app/Services/StaffService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use RuntimeException;
use Throwable;

class StaffService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getStaffMembers(int $schoolId, array $filters = []): array
    {
        $sql = "SELECT st.*,
                       sc.name AS category_name,
                       ss.name AS status_name,
                       ss.is_active_status,
                       d.name  AS department_name,
                       u.email AS user_account_email
                FROM staff st
                LEFT JOIN staff_categories sc ON st.staff_category_id = sc.id
                LEFT JOIN staff_statuses   ss ON st.staff_status_id   = ss.id
                LEFT JOIN departments       d ON st.department_id     = d.id
                LEFT JOIN users             u ON st.user_id           = u.id
                WHERE st.school_id = :school_id";

        $params = ['school_id' => $schoolId];

        if (!empty($filters['search'])) {
            $term = addcslashes((string) $filters['search'], '%_\\');
            $like = '%' . $term . '%';

            $sql .= " AND (
                        st.first_name   LIKE :search_first
                     OR st.middle_name  LIKE :search_middle
                     OR st.last_name    LIKE :search_last
                     OR st.username     LIKE :search_username
                     OR st.staff_number LIKE :search_number
                     OR st.email        LIKE :search_email
                     OR st.phone        LIKE :search_phone
                     OR st.alt_phone    LIKE :search_alt_phone
                     OR CONCAT_WS(' ', st.first_name, st.middle_name, st.last_name) LIKE :search_full
                     OR u.email         LIKE :search_user_email
                     OR u.username      LIKE :search_user_username
                  )";

            $params['search_first']         = $like;
            $params['search_middle']        = $like;
            $params['search_last']          = $like;
            $params['search_username']      = $like;
            $params['search_number']        = $like;
            $params['search_email']         = $like;
            $params['search_phone']         = $like;
            $params['search_alt_phone']     = $like;
            $params['search_full']          = $like;
            $params['search_user_email']    = $like;
            $params['search_user_username'] = $like;
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND st.staff_category_id = :filter_category";
            $params['filter_category'] = (int) $filters['category_id'];
        }

        if (!empty($filters['status_id'])) {
            $sql .= " AND st.staff_status_id = :filter_status";
            $params['filter_status'] = (int) $filters['status_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND st.department_id = :filter_department";
            $params['filter_department'] = (int) $filters['department_id'];
        }

        $sql .= " ORDER BY st.first_name ASC, st.last_name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getCategories(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM staff_categories
             WHERE school_id = :school_id AND status = 'active'
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }

    public function getStatuses(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM staff_statuses
             WHERE school_id = :school_id AND status = 'active'
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }

    public function getDepartments(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM departments WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $schoolId]
        );
    }

    public function createStaff(array $data, int $schoolId, int $userId): int
    {
        $this->assertUsernameAvailable($data['username'] ?? null, $schoolId, $data['user_id'] ?? null);

        $this->db->execute(
            "INSERT INTO staff (
                school_id, staff_number, username, first_name, middle_name, last_name,
                gender, date_of_birth, marital_status, phone, alt_phone, email, address,
                photo_path, staff_category_id, staff_status_id, department_id, position,
                employment_date, employment_type, user_id
            ) VALUES (
                :school_id, :staff_number, :username, :first_name, :middle_name, :last_name,
                :gender, :dob, :marital_status, :phone, :alt_phone, :email, :address,
                :photo_path, :category_id, :status_id, :department_id, :position,
                :emp_date, :emp_type, :user_id
            )",
            [
                'school_id'      => $schoolId,
                'staff_number'   => $data['staff_number'],
                'username'       => $data['username'] ?? null,
                'first_name'     => $data['first_name'],
                'middle_name'    => $data['middle_name'] ?? null,
                'last_name'      => $data['last_name'],
                'gender'         => $data['gender'] ?? 'male',
                'dob'            => $data['dob'] ?? null,
                'marital_status' => $data['marital_status'] ?? 'single',
                'phone'          => $data['phone'] ?? null,
                'alt_phone'      => $data['alt_phone'] ?? null,
                'email'          => $data['email'] ?? null,
                'address'        => $data['address'] ?? null,
                'photo_path'     => $data['photo_path'] ?? null,
                'category_id'    => (int) $data['category_id'],
                'status_id'      => (int) $data['status_id'],
                'department_id'  => !empty($data['department_id']) ? (int) $data['department_id'] : null,
                'position'       => $data['position'] ?? null,
                'emp_date'       => $data['emp_date'] ?? null,
                'emp_type'       => $data['emp_type'] ?? 'full_time',
                'user_id'        => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            ]
        );

        $staffId = (int) $this->db->lastInsertId();

        if ($staffId) {
            $this->db->execute(
                "INSERT INTO staff_status_history
                    (staff_id, old_status_id, new_status_id, effective_date, reason, changed_by)
                 VALUES
                    (:staff_id, NULL, :new_status, CURDATE(), 'Initial Employment Registration', :changed_by)",
                [
                    'staff_id'   => $staffId,
                    'new_status' => (int) $data['status_id'],
                    'changed_by' => $userId,
                ]
            );
        }

        return $staffId;
    }

    public function updateStatus(
        int $staffId,
        int $newStatusId,
        int $schoolId,
        int $userId,
        string $reason,
        string $effectiveDate
    ): bool {
        $staff = $this->db->fetch(
            "SELECT staff_status_id FROM staff WHERE id = :id AND school_id = :school_id",
            ['id' => $staffId, 'school_id' => $schoolId]
        );

        if (!$staff) {
            return false;
        }

        $oldStatusId = (int) $staff['staff_status_id'];

        if ($oldStatusId === $newStatusId) {
            return false;
        }

        $this->db->update('staff', ['staff_status_id' => $newStatusId], ['id' => $staffId]);

        $this->db->execute(
            "INSERT INTO staff_status_history
                (staff_id, old_status_id, new_status_id, effective_date, reason, changed_by)
             VALUES
                (:staff_id, :old_status, :new_status, :effective_date, :reason, :changed_by)",
            [
                'staff_id'       => $staffId,
                'old_status'     => $oldStatusId,
                'new_status'     => $newStatusId,
                'effective_date' => $effectiveDate,
                'reason'         => $reason,
                'changed_by'     => $userId,
            ]
        );

        return true;
    }

    public function linkAccount(int $staffId, int $userId, int $schoolId): bool
    {
        $exists = $this->db->fetch(
            "SELECT 1 AS found FROM staff WHERE id = :id AND school_id = :school_id LIMIT 1",
            ['id' => $staffId, 'school_id' => $schoolId]
        );

        if (!$exists) {
            return false;
        }

        $this->db->update('staff', ['user_id' => $userId], ['id' => $staffId]);

        return true;
    }

    public function unlinkAccount(int $staffId, int $schoolId): bool
    {
        $exists = $this->db->fetch(
            "SELECT 1 AS found FROM staff WHERE id = :id AND school_id = :school_id LIMIT 1",
            ['id' => $staffId, 'school_id' => $schoolId]
        );

        if (!$exists) {
            return false;
        }

        $this->db->update('staff', ['user_id' => null], ['id' => $staffId]);

        return true;
    }

    public function getStatusHistory(int $staffId): array
    {
        return $this->db->fetchAll(
            "SELECT sh.*,
                    ns.name AS new_status_name,
                    os.name AS old_status_name,
                    u.username AS authorizer
             FROM staff_status_history sh
             INNER JOIN staff_statuses ns ON sh.new_status_id = ns.id
             LEFT  JOIN staff_statuses os ON sh.old_status_id = os.id
             LEFT  JOIN users u            ON sh.changed_by    = u.id
             WHERE sh.staff_id = :id
             ORDER BY sh.id DESC",
            ['id' => $staffId]
        );
    }

    public function getUnlinkedUsers(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.username, u.email
             FROM users u
             LEFT JOIN staff st ON st.user_id = u.id
             WHERE u.school_id = :school_id AND st.id IS NULL
             ORDER BY u.username ASC",
            ['school_id' => $schoolId]
        );
    }

    public function generateStaffNumber(int $schoolId): string
    {
        $row = $this->db->fetch(
            "SELECT id FROM staff WHERE school_id = :school_id ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );

        $next = (int) ($row['id'] ?? 0) + 1;

        return 'STF-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function checkUsernameAvailability(string $username, int $schoolId): bool
    {
        $staff = $this->db->fetch(
            "SELECT 1 AS found FROM staff
             WHERE username = :username AND school_id = :school_id LIMIT 1",
            ['username' => $username, 'school_id' => $schoolId]
        );

        if ($staff) {
            return false;
        }

        $user = $this->db->fetch(
            "SELECT 1 AS found FROM users WHERE username = :username LIMIT 1",
            ['username' => $username]
        );

        return $user === null;
    }

    public function updateStaff(int $id, array $data, int $schoolId): bool
    {
        $params = [
            'id'             => $id,
            'school_id'      => $schoolId,
            'first_name'     => $data['first_name'],
            'middle_name'    => $data['middle_name'] ?? null,
            'last_name'      => $data['last_name'],
            'gender'         => $data['gender'] ?? 'male',
            'dob'            => $data['dob'] ?? null,
            'marital_status' => $data['marital_status'] ?? 'single',
            'phone'          => $data['phone'] ?? null,
            'alt_phone'      => $data['alt_phone'] ?? null,
            'email'          => $data['email'] ?? null,
            'address'        => $data['address'] ?? null,
            'category_id'    => (int) $data['category_id'],
            'status_id'      => (int) $data['status_id'],
            'department_id'  => !empty($data['department_id']) ? (int) $data['department_id'] : null,
            'position'       => $data['position'] ?? null,
            'emp_date'       => $data['emp_date'] ?? null,
            'emp_type'       => $data['emp_type'] ?? 'full_time',
        ];

        $sql = "UPDATE staff SET
                    first_name        = :first_name,
                    middle_name       = :middle_name,
                    last_name         = :last_name,
                    gender            = :gender,
                    date_of_birth     = :dob,
                    marital_status    = :marital_status,
                    phone             = :phone,
                    alt_phone         = :alt_phone,
                    email             = :email,
                    address           = :address,
                    staff_category_id = :category_id,
                    staff_status_id   = :status_id,
                    department_id     = :department_id,
                    position          = :position,
                    employment_date   = :emp_date,
                    employment_type   = :emp_type";

        if (!empty($data['photo_path'])) {
            $sql .= ", photo_path = :photo_path";
            $params['photo_path'] = $data['photo_path'];
        }

        $sql .= " WHERE id = :id AND school_id = :school_id";

        return $this->db->execute($sql, $params);
    }

    public function getStaffMember(int $id, int $schoolId): ?array
    {
        return $this->db->fetch(
            "SELECT st.*,
                    sc.name AS category_name,
                    ss.name AS status_name,
                    ss.is_active_status,
                    d.name  AS department_name,
                    u.email AS user_account_email
             FROM staff st
             LEFT JOIN staff_categories sc ON st.staff_category_id = sc.id
             LEFT JOIN staff_statuses   ss ON st.staff_status_id   = ss.id
             LEFT JOIN departments       d ON st.department_id     = d.id
             LEFT JOIN users             u ON st.user_id           = u.id
             WHERE st.id = :id AND st.school_id = :school_id
             LIMIT 1",
            ['id' => $id, 'school_id' => $schoolId]
        );
    }

    public function getAllCategories(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM staff WHERE staff_category_id = c.id) AS usage_count
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
                'school_id'     => $schoolId,
                'name'          => $data['name'],
                'code'          => strtoupper($data['code']),
                'description'   => $data['description'] ?? null,
                'display_order' => (int) ($data['display_order'] ?? 0),
                'status'        => $data['status'] ?? 'active',
            ]
        );
    }

    public function updateCategory(int $id, array $data, int $schoolId): bool
    {
        return $this->db->execute(
            "UPDATE staff_categories
             SET name = :name, code = :code, description = :description,
                 display_order = :display_order, status = :status
             WHERE id = :id AND school_id = :school_id",
            [
                'id'            => $id,
                'school_id'     => $schoolId,
                'name'          => $data['name'],
                'code'          => strtoupper($data['code']),
                'description'   => $data['description'] ?? null,
                'display_order' => (int) ($data['display_order'] ?? 0),
                'status'        => $data['status'] ?? 'active',
            ]
        );
    }

    public function getAllStatuses(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*,
                    (SELECT COUNT(*) FROM staff WHERE staff_status_id = s.id) AS usage_count
             FROM staff_statuses s
             WHERE s.school_id = :school_id
             ORDER BY s.display_order ASC, s.name ASC",
            ['school_id' => $schoolId]
        );
    }

    public function createStatus(array $data, int $schoolId): bool
    {
        return $this->db->execute(
            "INSERT INTO staff_statuses
                (school_id, name, code, description, is_active_status, allows_login, display_order, status)
             VALUES
                (:school_id, :name, :code, :description, :is_active_status, :allows_login, :display_order, :status)",
            [
                'school_id'        => $schoolId,
                'name'             => $data['name'],
                'code'             => strtoupper($data['code']),
                'description'      => $data['description'] ?? null,
                'is_active_status' => !empty($data['is_active_status']) ? 1 : 0,
                'allows_login'     => !empty($data['allows_login']) ? 1 : 0,
                'display_order'    => (int) ($data['display_order'] ?? 0),
                'status'           => $data['status'] ?? 'active',
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
                'id'               => $id,
                'school_id'        => $schoolId,
                'name'             => $data['name'],
                'code'             => strtoupper($data['code']),
                'description'      => $data['description'] ?? null,
                'is_active_status' => !empty($data['is_active_status']) ? 1 : 0,
                'allows_login'     => !empty($data['allows_login']) ? 1 : 0,
                'display_order'    => (int) ($data['display_order'] ?? 0),
                'status'           => $data['status'] ?? 'active',
            ]
        );
    }

    public function getLinkedUser(int $staffId, int $schoolId): ?array
    {
        return $this->db->fetch(
            "SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.status,
                    (SELECT GROUP_CONCAT(r.name SEPARATOR ', ')
                     FROM user_roles ur
                     INNER JOIN roles r ON ur.role_id = r.id
                     WHERE ur.user_id = u.id) AS role_names
             FROM users u
             INNER JOIN staff st ON st.user_id = u.id
             WHERE st.id = :staff_id AND st.school_id = :school_id
             LIMIT 1",
            ['staff_id' => $staffId, 'school_id' => $schoolId]
        );
    }

    public function getTeacherAssignments(int $staffId, int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT ta.*,
                    c.name   AS class_name,
                    s.name   AS subject_name,
                    st.name  AS stream_name,
                    tat.name AS assignment_type_name
             FROM teacher_assignments ta
             LEFT JOIN classes c                     ON ta.class_id = c.id
             LEFT JOIN subjects s                    ON ta.subject_id = s.id
             LEFT JOIN streams st                    ON ta.stream_id = st.id
             LEFT JOIN teacher_assignment_types tat  ON ta.assignment_type_id = tat.id
             WHERE ta.staff_id = :staff_id AND ta.school_id = :school_id
             ORDER BY c.name, s.name",
            ['staff_id' => $staffId, 'school_id' => $schoolId]
        );
    }

    public function createUserForStaff(int $staffId, array $userData, int $roleId, int $schoolId): int
    {
        if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
            throw new RuntimeException('Username, email, and password are required.');
        }

        if (!$this->checkUsernameAvailability($userData['username'], $schoolId)) {
            throw new RuntimeException("Username '{$userData['username']}' is already taken.");
        }

        $now = date('Y-m-d H:i:s');

        $userId = $this->db->insert('users', [
            'school_id'  => $schoolId,
            'username'   => $userData['username'],
            'email'      => $userData['email'],
            'password'   => password_hash($userData['password'], PASSWORD_BCRYPT),
            'first_name' => $userData['first_name'] ?? '',
            'last_name'  => $userData['last_name']  ?? '',
            'status'     => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (!$userId) {
            throw new RuntimeException('Failed to create user account.');
        }

        if ($roleId) {
            $this->db->insert('user_roles', [
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => $now,
            ]);
        }

        $this->db->update('staff', ['user_id' => $userId], ['id' => $staffId]);

        return (int) $userId;
    }

    public function updateLinkedUser(int $staffId, array $userData, ?int $roleId, int $schoolId): void
    {
        $user = $this->getLinkedUser($staffId, $schoolId);

        if (!$user) {
            throw new RuntimeException('No linked user account found.');
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

        if ($roleId) {
            $this->db->execute("DELETE FROM user_roles WHERE user_id = :uid", ['uid' => $user['id']]);
            $this->db->insert('user_roles', [
                'user_id'    => $user['id'],
                'role_id'    => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function unlinkUser(int $staffId, int $schoolId): void
    {
        $this->db->update('staff', ['user_id' => null], [
            'id' => $staffId, 'school_id' => $schoolId,
        ]);
    }

    private function assertUsernameAvailable(?string $username, int $schoolId, $linkedUserId = null): void
    {
        if (empty($username)) {
            return;
        }

        $staff = $this->db->fetch(
            "SELECT 1 AS found FROM staff
             WHERE username = :username AND school_id = :school_id LIMIT 1",
            ['username' => $username, 'school_id' => $schoolId]
        );

        if ($staff) {
            throw new RuntimeException("Username '{$username}' is already taken by another staff member.");
        }

        $user = $this->db->fetch(
            "SELECT id FROM users WHERE username = :username LIMIT 1",
            ['username' => $username]
        );

        if ($user && (empty($linkedUserId) || (int) $user['id'] !== (int) $linkedUserId)) {
            throw new RuntimeException("Username '{$username}' is already taken by a system user.");
        }
    }
}