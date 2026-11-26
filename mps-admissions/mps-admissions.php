<?php
/**
 * Plugin Name: MPS Admissions CRUD
 * Description: Custom module for Mukumbwe Primary School to manage admissions.
 * Version: 2.0
 * Author: Your Name
 */

// 1. Create Database Tables on Plugin Activation
register_activation_hook(__FILE__, 'mps_create_tables');

function mps_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_classes = $wpdb->prefix . 'mps_classes';
    $sql1 = "CREATE TABLE $table_classes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        class_name varchar(50) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // UPDATED Table with more fields
    $table_apps = $wpdb->prefix . 'mps_applications';
    $sql2 = "CREATE TABLE $table_apps (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        student_first_name varchar(100) NOT NULL,
        student_last_name varchar(100) NOT NULL,
        dob date NOT NULL,
        gender varchar(10) NOT NULL,
        prev_school varchar(150),
        parent_first_name varchar(100) NOT NULL,
        parent_last_name varchar(100) NOT NULL,
        relationship varchar(50) NOT NULL,
        phone varchar(20) NOT NULL,
        email varchar(100),
        address text,
        class_id mediumint(9) NOT NULL,
        status varchar(20) DEFAULT 'Pending' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);

    $wpdb->insert($table_classes, array('class_name' => 'Nursery'));
    $wpdb->insert($table_classes, array('class_name' => 'Primary 1'));
    $wpdb->insert($table_classes, array('class_name' => 'Primary 2'));
    $wpdb->insert($table_classes, array('class_name' => 'Primary 3'));
}

// 2. Front-End Form Shortcode
add_shortcode('mps_admission_form', 'mps_render_application_form');

function mps_render_application_form() {
    global $wpdb;
    $table_classes = $wpdb->prefix . 'mps_classes';
    $classes = $wpdb->get_results("SELECT * FROM $table_classes");

    ob_start();
    ?>
    <style>
        .mps-form-section-title { font-size: 20px; color: #146c2e; font-weight: 700; margin-bottom: 20px; border-bottom: 2px solid #f5b700; padding-bottom: 10px; }
        .mps-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .mps-form-group { margin-bottom: 20px; }
        .mps-form-group label { display: block; font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px; }
        .mps-form-group input, .mps-form-group select, .mps-form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; background: #fafafa; }
        .mps-form-group input:focus, .mps-form-group select:focus { border-color: #146c2e; outline: none; background: #fff; }
        @media (max-width: 768px) { .mps-form-grid { grid-template-columns: 1fr; gap: 0; } }
    </style>

    <div class="mps-form-container">
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;">
                <strong>Success!</strong> Your application has been submitted successfully.
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mps_submit_application">
            <?php wp_nonce_field('mps_application_nonce', 'mps_nonce'); ?>

            <!-- STUDENT DETAILS SECTION -->
            <div class="mps-form-section-title"><i class="fas fa-user-graduate"></i> Student Details</div>
            <div class="mps-form-grid">
                <div class="mps-form-group">
                    <label>Student First Name *</label>
                    <input type="text" name="student_first_name" required>
                </div>
                <div class="mps-form-group">
                    <label>Student Last Name *</label>
                    <input type="text" name="student_last_name" required>
                </div>
            </div>
            <div class="mps-form-grid">
                <div class="mps-form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required>
                </div>
                <div class="mps-form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>
            <div class="mps-form-grid">
                <div class="mps-form-group">
                    <label>Previous School (if any)</label>
                    <input type="text" name="prev_school">
                </div>
                <div class="mps-form-group">
                    <label>Class Applying For *</label>
                    <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php foreach($classes as $class): ?>
                            <option value="<?php echo esc_attr($class->id); ?>"><?php echo esc_html($class->class_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- PARENT/GUARDIAN DETAILS SECTION -->
            <div class="mps-form-section-title" style="margin-top: 40px;"><i class="fas fa-users"></i> Parent / Guardian Details</div>
            <div class="mps-form-grid">
                <div class="mps-form-group">
                    <label>Parent First Name *</label>
                    <input type="text" name="parent_first_name" required>
                </div>
                <div class="mps-form-group">
                    <label>Parent Last Name *</label>
                    <input type="text" name="parent_last_name" required>
                </div>
            </div>
            <div class="mps-form-grid">
                <div class="mps-form-group">
                    <label>Relationship to Student *</label>
                    <select name="relationship" required>
                        <option value="">Select Relationship</option>
                        <option value="Mother">Mother</option>
                        <option value="Father">Father</option>
                        <option value="Guardian">Guardian</option>
                    </select>
                </div>
                <div class="mps-form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" required>
                </div>
            </div>
            <div class="mps-form-grid">
                <div class="mps-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email">
                </div>
                <div class="mps-form-group">
                    <label>Physical Address</label>
                    <input type="text" name="address">
                </div>
            </div>

            <button type="submit" style="background:#146c2e; color:#fff; padding:15px 30px; border:none; border-radius:30px; font-weight:700; cursor:pointer; width:100%; font-size:16px; margin-top: 20px;">Submit Application</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

// 3. Form Submission Handler
add_action('admin_post_nopriv_mps_submit_application', 'mps_handle_form_submission');
add_action('admin_post_mps_submit_application', 'mps_handle_form_submission');

function mps_handle_form_submission() {
    if (!isset($_POST['mps_nonce']) || !wp_verify_nonce($_POST['mps_nonce'], 'mps_application_nonce')) {
        wp_die('Security check failed. Please try again.');
    }

    global $wpdb;
    $table_apps = $wpdb->prefix . 'mps_applications';

    $wpdb->insert(
        $table_apps,
        array(
            'student_first_name' => sanitize_text_field($_POST['student_first_name']),
            'student_last_name'  => sanitize_text_field($_POST['student_last_name']),
            'dob'                => sanitize_text_field($_POST['dob']),
            'gender'             => sanitize_text_field($_POST['gender']),
            'prev_school'        => sanitize_text_field($_POST['prev_school']),
            'parent_first_name'  => sanitize_text_field($_POST['parent_first_name']),
            'parent_last_name'   => sanitize_text_field($_POST['parent_last_name']),
            'relationship'       => sanitize_text_field($_POST['relationship']),
            'phone'              => sanitize_text_field($_POST['phone']),
            'email'              => sanitize_email($_POST['email']),
            'address'            => sanitize_text_field($_POST['address']),
            'class_id'           => intval($_POST['class_id']),
            'status'             => 'Pending'
        )
    );

    $redirect_url = wp_get_referer();
    $redirect_url = add_query_arg('success', '1', $redirect_url);
    wp_redirect($redirect_url);
    exit;
}