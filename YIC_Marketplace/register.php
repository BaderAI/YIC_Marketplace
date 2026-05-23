<?php
require_once 'classes/Auth.php';
require_once 'includes/functions.php';

Auth::startSession();
$page_title = 'Register';
include 'includes/header.php';
?>

<main class="auth-container">
    <section class="register-box">
        <h2>Create New Account</h2>
        <p>Join the YIC student community today.</p>

        <?php echo form_flash(); ?>

        <form action="process_register.php" method="POST" id="register-form">
            <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">

            <label for="full-name">Full Name</label>
            <input type="text" id="full-name" name="name" placeholder="Enter your full name" required>

            <label for="reg-email">College Email</label>
            <input type="email" id="reg-email" name="email" placeholder="username@yic.edu.sa" required>

            <label for="student-id">Student ID</label>
            <input type="text" id="student-id" name="student_id" placeholder="e.g. 441000" inputmode="numeric" data-numeric="integer" pattern="[0-9]+" autocomplete="off" required>

            <label for="reg-password">Password</label>
            <input type="password" id="reg-password" name="password" required>

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm_password" required>

            <div class="form-message" aria-live="polite"></div>
            <button type="submit" class="btn-primary">Register Now</button>
        </form>
        <p class="auth-footer">Already have an account? <a href="login.php">Login here</a></p>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
