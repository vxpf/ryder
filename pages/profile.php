<?php
// User must be logged in to access this page
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: /login-form');
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Get user data and profile data
$stmt = $conn->prepare("
    SELECT a.*, p.name, p.phone, p.profile_photo, p.bio, p.address, p.city, p.postal_code
    FROM account a 
    LEFT JOIN user_profiles p ON a.id = p.account_id
    WHERE a.id = :id
");
$stmt->bindParam(":id", $_SESSION['id']);
$stmt->execute();
$user = $stmt->fetch();

$pageTitle = "Mijn Profiel";
include_once __DIR__ . '/../includes/profile-header.php';
?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">

<style>
    /* Professional design styles */
    body {
        background-color: #f8f9fa;
        color: #212529;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    /* Custom styles for profile page header */
    .profile-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: relative;
        z-index: 100;
    }
    
    .profile-topbar nav {
        margin-left: auto;
        margin-right: 20px;
    }
    
    /* Main container styles */
    .main-container {
        padding: 40px 0;
        background-color: #f8f9fa;
    }
    
    /* Profile section styles */
    .profile-section {
        background-color: #fff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .profile-header {
        background: linear-gradient(135deg, #3563e9 0%, #4e6fff 100%);
        color: white;
        padding: 30px;
        position: relative;
    }
    
    .profile-header h3 {
        margin: 0;
        font-weight: 700;
        font-size: 28px;
    }
    
    .profile-header .subtitle {
        margin-top: 8px;
        opacity: 0.9;
        font-size: 16px;
        font-weight: 400;
    }
    
    .profile-body {
        padding: 40px;
    }
    
    /* Form styling */
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 15px;
    }
    
    .form-control {
        border: 1px solid #e0e4e8;
        border-radius: 10px;
        padding: 14px 18px;
        font-size: 15px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        height: auto;
    }
    
    .form-control:focus {
        border-color: #3563e9;
        box-shadow: 0 0 0 3px rgba(53, 99, 233, 0.15);
        background-color: #fff;
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    /* Section styling */
    .section-title {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 15px;
        margin-bottom: 25px;
        font-weight: 700;
        color: #3563e9;
        display: flex;
        align-items: center;
        font-size: 20px;
    }
    
    .section-title i {
        margin-right: 12px;
        font-size: 22px;
    }
    
    /* Profile image styling */
    .profile-image-container {
        width: 160px;
        height: 160px;
        margin: 0 auto 30px;
        position: relative;
    }
    
    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .default-profile-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 60px;
        border: 5px solid #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .image-upload-overlay {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background-color: #3563e9;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        z-index: 5;
        border: 3px solid #fff;
        transition: all 0.3s ease;
    }
    
    .image-upload-overlay:hover {
        background-color: #2954d4;
        transform: scale(1.05);
    }
    
    /* Button styling */
    .btn-primary {
        background: linear-gradient(135deg, #3563e9 0%, #4e6fff 100%);
        border: none;
        padding: 14px 28px;
        font-weight: 600;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(53, 99, 233, 0.25);
        transition: all 0.3s ease;
        font-size: 16px;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #2954d4 0%, #3e5fe6 100%);
        box-shadow: 0 6px 15px rgba(53, 99, 233, 0.35);
        transform: translateY(-2px);
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }
    
    /* Alert styling */
    .alert {
        border-radius: 12px;
        border: none;
        padding: 18px 24px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
    }
    
    .alert-success {
        background-color: #e6f7ee;
        color: #0f5132;
        border-left: 4px solid #0f5132;
    }
    
    .alert-danger {
        background-color: #fff5f5;
        color: #842029;
        border-left: 4px solid #842029;
    }
    
    .alert i {
        font-size: 20px;
        margin-right: 15px;
    }
    
    /* Grid and responsive styles */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }
    
    [class*="col-"] {
        padding: 0 15px;
    }
    
    @media (max-width: 767px) {
        .profile-section {
            margin-left: 15px;
            margin-right: 15px;
        }
        
        .profile-header, .profile-body {
            padding: 20px;
        }
        
        .main-container {
            padding: 20px 0;
        }
    }
    
    /* Custom file input styling */
    .custom-file-upload {
        background-color: #f8f9fa;
        border: 1px solid #e0e4e8;
        border-radius: 10px;
        padding: 14px;
        cursor: pointer;
        text-align: center;
        margin-top: 10px;
        transition: all 0.3s ease;
        display: block;
    }
    
    .custom-file-upload:hover {
        background-color: #e9ecef;
    }
    
    input[type="file"] {
        display: none;
    }
    
    /* Card and section styling */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .form-group {
        margin-bottom: 24px;
    }
    
    hr {
        margin: 40px 0;
        opacity: 0.1;
    }
    
    /* Form field icons */
    .input-with-icon {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 15px;
        color: #6c757d;
    }
    
    .input-with-icon .form-control {
        padding-left: 45px;
    }
    
    /* Password strength indicator */
    .password-strength {
        height: 5px;
        margin-top: 10px;
        border-radius: 5px;
        background-color: #e9ecef;
        overflow: hidden;
    }
    
    .password-strength-meter {
        height: 100%;
        border-radius: 5px;
        width: 0;
        transition: all 0.3s ease;
    }
    
    .strength-weak {
        width: 25%;
        background-color: #dc3545;
    }
    
    .strength-medium {
        width: 50%;
        background-color: #ffc107;
    }
    
    .strength-strong {
        width: 75%;
        background-color: #20c997;
    }
    
    .strength-very-strong {
        width: 100%;
        background-color: #198754;
    }
    
    .password-feedback {
        font-size: 12px;
        margin-top: 5px;
        color: #6c757d;
    }
    
    /* Form section card styling */
    .form-section-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    
    .form-section-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    
    /* Form field focus animation */
    .form-control:focus + .input-icon {
        color: #3563e9;
    }
    
    /* Save button container */
    .save-button-container {
        display: flex;
        justify-content: flex-end;
        margin-top: 30px;
    }
    
    /* Form section header */
    .form-section-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .form-section-header i {
        font-size: 24px;
        color: #3563e9;
        margin-right: 12px;
        width: 40px;
        height: 40px;
        background-color: rgba(53, 99, 233, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-section-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 18px;
        color: #212529;
    }
</style>

<div class="main-container">
    <div class="container">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= isset($_SESSION['error']) ? 'danger' : 'success' ?> d-flex align-items-center">
                        <?php if (isset($_SESSION['error'])): ?>
                            <i class="fas fa-exclamation-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-check-circle"></i>
                        <?php endif; ?>
                        <div><?= $_SESSION['message']; ?></div>
                        <?php unset($_SESSION['message']); ?>
                        <?php if (isset($_SESSION['error'])) unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-section">
                    <div class="profile-header">
                        <h3>Mijn Profiel</h3>
                        <div class="subtitle">Beheer uw persoonlijke informatie en voorkeuren</div>
                    </div>
                    
                    <div class="profile-body">
                        <form action="/update-profile" method="POST" enctype="multipart/form-data" id="profile-form">
                            <!-- Profile Image Section -->
                            <div class="text-center mb-4">
                                <div class="profile-image-container">
                                    <?php if (!empty($user['profile_photo'])): ?>
                                        <img src="<?= htmlspecialchars($user['profile_photo']) ?>" class="profile-image" alt="Profile Photo">
                                    <?php else: ?>
                                        <div class="default-profile-image">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <label for="profile_photo" class="image-upload-overlay" title="Profielfoto wijzigen">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg, image/png">
                                </div>
                                <p class="text-muted small mt-2">Klik op het camera-icoon om een profielfoto te uploaden</p>
                            </div>
                            
                            <!-- Account Information -->
                            <div class="form-section-card">
                                <div class="form-section-header">
                                    <i class="fas fa-user-circle"></i>
                                    <h4>Account Informatie</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">E-mailadres</label>
                                    <div class="input-with-icon">
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                        <i class="fas fa-envelope input-icon"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Personal Information -->
                            <div class="form-section-card">
                                <div class="form-section-header">
                                    <i class="fas fa-address-card"></i>
                                    <h4>Persoonlijke Informatie</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="name" class="form-label">Volledige Naam</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Uw volledige naam">
                                        <i class="fas fa-user input-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">Telefoonnummer</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Bijv. 06-12345678">
                                        <i class="fas fa-phone input-icon"></i>
                                    </div>
                                </div>
                                
                                

                                
                            </div>
                            
                            <!-- Address Information -->
                            <div class="form-section-card">
                                <div class="form-section-header">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <h4>Adres Informatie</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address" class="form-label">Straat en Huisnummer</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Bijv. Hoofdstraat 123">
                                        <i class="fas fa-home input-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="city" class="form-label">Woonplaats</label>
                                            <div class="input-with-icon">
                                                <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="Bijv. Amsterdam">
                                                <i class="fas fa-city input-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="postal_code" class="form-label">Postcode</label>
                                            <div class="input-with-icon">
                                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" placeholder="Bijv. 1234 AB">
                                                <i class="fas fa-map-pin input-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Password Change -->
                            <div class="form-section-card">
                                <div class="form-section-header">
                                    <i class="fas fa-lock"></i>
                                    <h4>Wachtwoord Wijzigen</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Huidig Wachtwoord</label>
                                    <div class="input-with-icon">
                                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Vul uw huidige wachtwoord in">
                                        <i class="fas fa-key input-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="new_password" class="form-label">Nieuw Wachtwoord</label>
                                            <div class="input-with-icon">
                                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Voer een nieuw wachtwoord in">
                                                <i class="fas fa-lock input-icon"></i>
                                            </div>
                                            <div class="password-strength">
                                                <div class="password-strength-meter" id="password-strength-meter"></div>
                                            </div>
                                            <div class="password-feedback" id="password-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="confirm_password" class="form-label">Bevestig Nieuw Wachtwoord</label>
                                            <div class="input-with-icon">
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Bevestig uw nieuwe wachtwoord">
                                                <i class="fas fa-lock input-icon"></i>
                                            </div>
                                            <div id="password-match-feedback" class="password-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="save-button-container">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Wijzigingen Opslaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Show filename when a file is selected
    document.getElementById('profile_photo').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                // Create a new image element
                var img = document.querySelector('.profile-image');
                
                // If there's no image yet, create one
                if (!img) {
                    if (document.querySelector('.default-profile-image')) {
                        document.querySelector('.default-profile-image').remove();
                    }
                    
                    img = document.createElement('img');
                    img.className = 'profile-image';
                    img.alt = 'Profile Photo';
                    document.querySelector('.profile-image-container').prepend(img);
                }
                
                // Update the image source
                img.src = e.target.result;
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Password strength meter
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        const meter = document.getElementById('password-strength-meter');
        const feedback = document.getElementById('password-feedback');
        
        // Reset meter
        meter.className = 'password-strength-meter';
        
        if (password.length === 0) {
            feedback.textContent = '';
            meter.style.width = '0';
            return;
        }
        
        // Check password strength
        let strength = 0;
        
        // Check length
        if (password.length >= 8) strength += 1;
        
        // Check for lowercase and uppercase letters
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
        
        // Check for numbers
        if (/\d/.test(password)) strength += 1;
        
        // Check for special characters
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        
        // Update meter and feedback
        switch(strength) {
            case 0:
                meter.classList.add('strength-weak');
                feedback.textContent = 'Zwak wachtwoord';
                feedback.style.color = '#dc3545';
                break;
            case 1:
                meter.classList.add('strength-weak');
                feedback.textContent = 'Zwak wachtwoord';
                feedback.style.color = '#dc3545';
                break;
            case 2:
                meter.classList.add('strength-medium');
                feedback.textContent = 'Gemiddeld wachtwoord';
                feedback.style.color = '#ffc107';
                break;
            case 3:
                meter.classList.add('strength-strong');
                feedback.textContent = 'Sterk wachtwoord';
                feedback.style.color = '#20c997';
                break;
            case 4:
                meter.classList.add('strength-very-strong');
                feedback.textContent = 'Zeer sterk wachtwoord';
                feedback.style.color = '#198754';
                break;
        }
    });
    
    // Password match check
    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = this.value;
        const feedback = document.getElementById('password-match-feedback');
        
        if (confirmPassword.length === 0) {
            feedback.textContent = '';
            return;
        }
        
        if (newPassword === confirmPassword) {
            feedback.textContent = 'Wachtwoorden komen overeen';
            feedback.style.color = '#198754';
        } else {
            feedback.textContent = 'Wachtwoorden komen niet overeen';
            feedback.style.color = '#dc3545';
        }
    });
    
    // Form validation
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const currentPassword = document.getElementById('current_password').value;
        
        // Check if any password field is filled
        if (newPassword || confirmPassword || currentPassword) {
            // Check if all password fields are filled
            if (!newPassword || !confirmPassword || !currentPassword) {
                e.preventDefault();
                alert('Als u uw wachtwoord wilt wijzigen, vul dan alle wachtwoordvelden in.');
                return;
            }
            
            // Check if passwords match
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Nieuwe wachtwoorden komen niet overeen.');
                return;
            }
        }
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> ''

// sdsdsdsada