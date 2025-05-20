<?php require "includes/auth-header.php" ?>
<main>
    <form action="/login-handler" class="account-form" method="post" id="login-form">
        <h2>Log in</h2>
        <?php if (isset($_SESSION['success'])) { ?>
            <div class="succes-message" id="success-message"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); // Clear the message from session ?>
        <?php } ?>
        <?php if (isset($_SESSION['error'])) { ?>
            <div class="error-message" id="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); // Clear the error from session ?>
        <?php } ?>
        <label for="email">Uw e-mail</label>
        <input type="email" name="email" id="email" placeholder="Uw e-mail" required>
        <label for="password">Uw wachtwoord</label>
        <input type="password" name="password" id="password" placeholder="Uw wachtwoord" required>
        <button type="submit" class="button-primary">Log in</button>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle success message
    const successMessage = document.getElementById('success-message');
    if (successMessage) {
        setTimeout(function() {
            successMessage.style.opacity = '0';
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 300); // Wait for fade out animation to complete
        }, 2000); // Hide after 2 seconds
    }

    // Handle error message
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        setTimeout(function() {
            errorMessage.style.opacity = '0';
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 300); // Wait for fade out animation to complete
        }, 3000); // Show error for 3 seconds
    }
    
    // Ensure the form submits correctly and doesn't trigger any modal
    document.getElementById('login-form').addEventListener('submit', function(e) {
        // Let the form submit normally - no need to prevent default
    });
});
</script>

<?php require "includes/footer.php" ?>
