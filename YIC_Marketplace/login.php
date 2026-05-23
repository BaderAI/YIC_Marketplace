<?php
require_once 'classes/Auth.php';
require_once 'includes/functions.php';

Auth::startSession();
$page_title = 'Login';
include 'includes/header.php';
?>

<main class="auth-page">
    <section class="login-box">
        <img src="assets/img/yic-logo.svg" alt="YIC Logo" class="auth-logo">
        <h2>Login to Portal</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <?php
                if ($_GET['error'] === 'invalid_credentials') {
                    echo 'Invalid email or password.';
                } elseif ($_GET['error'] === 'access_denied') {
                    echo 'Please login first to access this page.';
                } elseif ($_GET['error'] === 'csrf') {
                    echo 'Your session expired. Please try again.';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'registered'): ?>
            <div class="alert success">Registration successful! You can now login.</div>
        <?php endif; ?>

        <form action="auth.php" method="POST" id="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">

            <label for="email">College Email</label>
            <input type="email" id="email" name="email" placeholder="example@yic.edu.sa" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <div class="form-message" aria-live="polite"></div>
            <button type="submit">Enter Marketplace</button>
        </form>
        <p>New student? <a href="register.php">Create Account</a></p>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
