<?php
if (isset($_POST['add_staff_member']) && is_admin()) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $position = sanitize_input($_POST['position']);
    $schedule = sanitize_input($_POST['schedule']);
    $password = password_hash('staff123', PASSWORD_DEFAULT);
    
    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $staff_error = "Email already exists";
    } else {
        $user_sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
                     VALUES ('$first_name', '$last_name', '$email', '$phone', '$password', 'staff')";
        
        if ($conn->query($user_sql)) {
            $user_id = $conn->insert_id;
            
            $staff_sql = "INSERT INTO staff (user_id, position, schedule) 
                         VALUES ($user_id, '$position', '$schedule')";
            
            if ($conn->query($staff_sql)) {
                $staff_success = "Staff member added successfully! Default password: staff123";
            } else {
                $staff_error = "Failed to add staff record";
            }
        } else {
            $staff_error = "Failed to create user account";
        }
    }
}

if (isset($_GET['delete_staff']) && is_admin()) {
    $staff_id = $_GET['delete_staff'];
    
    $get_user_sql = "SELECT user_id FROM staff WHERE id = $staff_id";
    $get_user_result = $conn->query($get_user_sql);
    
    if ($get_user_result->num_rows > 0) {
        $user_data = $get_user_result->fetch_assoc();
        $user_id = $user_data['user_id'];
        
        $delete_sql = "DELETE FROM users WHERE id = $user_id";
        if ($conn->query($delete_sql)) {
            $staff_success = "Staff member deleted successfully";
        }
    }
}

if (isset($_POST['update_staff']) && is_admin()) {
    $staff_id = sanitize_input($_POST['staff_id']);
    $position = sanitize_input($_POST['position']);
    $schedule = sanitize_input($_POST['schedule']);
    
    $update_sql = "UPDATE staff SET position = '$position', schedule = '$schedule' WHERE id = $staff_id";
    
    if ($conn->query($update_sql)) {
        $staff_success = "Staff member updated successfully";
    } else {
        $staff_error = "Failed to update staff member";
    }
}
?>

<div id="admin-staff" class="dashboard-page <?php echo $dashboard_page == 'staff' ? 'active' : ''; ?>">
    <div class="dashboard-header">
        <h3>Staff Management</h3>
        <button class="btn btn-primary" onclick="showModal('addStaffModal')">Add New Staff</button>
    </div>
    
    <?php if (isset($staff_success)): ?>
        <div class="alert alert-success"><?php echo $staff_success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($staff_error)): ?>
        <div class="alert alert-danger"><?php echo $staff_error; ?></div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Schedule</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $staff_sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone 
                         FROM staff s
                         JOIN users u ON s.user_id = u.id
                         ORDER BY u.first_name, u.last_name";
            $staff_result = $conn->query($staff_sql);
            
            if ($staff_result->num_rows > 0):
                while ($staff = $staff_result->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                <td><?php echo htmlspecialchars($staff['position']); ?></td>
                <td><?php echo htmlspecialchars($staff['email']); ?></td>
                <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                <td><?php echo htmlspecialchars($staff['schedule']); ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-warning btn-sm" 
                                onclick="editStaff(<?php echo $staff['id']; ?>, '<?php echo htmlspecialchars($staff['position']); ?>', '<?php echo htmlspecialchars($staff['schedule']); ?>')">
                            Edit
                        </button>
                        <a href="?view=staff&delete_staff=<?php echo $staff['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this staff member?')">
                            Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="6" style="text-align: center;">No staff members found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="addStaffModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="hideModal('addStaffModal')">&times;</span>
        <h2>Add New Staff Member</h2>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="staff_first_name">First Name</label>
                    <input type="text" id="staff_first_name" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="staff_last_name">Last Name</label>
                    <input type="text" id="staff_last_name" name="last_name" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label for="staff_email">Email</label>
                <input type="email" id="staff_email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="staff_phone">Phone</label>
                <input type="text" id="staff_phone" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="staff_position">Position</label>
                <select id="staff_position" name="position" class="form-control" required>
                    <option value="">Select Position</option>
                    <option value="Senior Stylist">Senior Stylist</option>
                    <option value="Stylist">Stylist</option>
                    <option value="Color Specialist">Color Specialist</option>
                    <option value="Nail Technician">Nail Technician</option>
                    <option value="Esthetician">Esthetician</option>
                    <option value="Makeup Artist">Makeup Artist</option>
                    <option value="Receptionist">Receptionist</option>
                </select>
            </div>
            <div class="form-group">
                <label for="staff_schedule">Schedule</label>
                <input type="text" id="staff_schedule" name="schedule" class="form-control" 
                       placeholder="e.g., Mon-Fri, 9am-5pm" required>
            </div>
            <button type="submit" name="add_staff_member" class="btn btn-primary">Add Staff Member</button>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">
                <strong>Note:</strong> Default password will be "staff123" - staff should change it on first login
            </p>
        </form>
    </div>
</div>

<div id="editStaffModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="hideModal('editStaffModal')">&times;</span>
        <h2>Edit Staff Member</h2>
        <form method="POST" action="">
            <input type="hidden" id="edit_staff_id" name="staff_id">
            <div class="form-group">
                <label for="edit_position">Position</label>
                <select id="edit_position" name="position" class="form-control" required>
                    <option value="">Select Position</option>
                    <option value="Senior Stylist">Senior Stylist</option>
                    <option value="Stylist">Stylist</option>
                    <option value="Color Specialist">Color Specialist</option>
                    <option value="Nail Technician">Nail Technician</option>
                    <option value="Esthetician">Esthetician</option>
                    <option value="Makeup Artist">Makeup Artist</option>
                    <option value="Receptionist">Receptionist</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_schedule">Schedule</label>
                <input type="text" id="edit_schedule" name="schedule" class="form-control" required>
            </div>
            <button type="submit" name="update_staff" class="btn btn-primary">Update Staff Member</button>
        </form>
    </div>
</div>

<script>
function editStaff(staffId, position, schedule) {
    document.getElementById('edit_staff_id').value = staffId;
    document.getElementById('edit_position').value = position;
    document.getElementById('edit_schedule').value = schedule;
    showModal('editStaffModal');
}
</script>