<?php require "includes/auth-header.php" ?>
<main>
    <form action="/register-handler" method="post" class="account-form" id="register-form">
        <h2>Maak een account aan</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message" id="error-message">
                <?= $_SESSION['message'] ?>
            </div>
            <?php
            unset($_SESSION['message']);
             endif; ?>
        <label for="email">Uw e-mail</label>
        <input type="email" name="email" id="email" placeholder="Uw e-mail" value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>" required autofocus>
        <label for="password">Uw wachtwoord</label>
        <input type="password" name="password" id="password" placeholder="Uw wachtwoord" required>
        <label for="confirm_password">Herhaal wachtwoord</label>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Uw wachtwoord" required>
        <button type="submit" class="button-primary">Maak account aan</button>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Ensure the form submits correctly
    document.getElementById('register-form').addEventListener('submit', function(e) {
        // Let the form submit normally - no need to prevent default
    });
});
</script>

<?php require "includes/footer.php" ?>
