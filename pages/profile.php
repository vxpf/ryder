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

<!-- Add Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Add Font Awesome for icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<!-- Add Material Design Icons -->
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
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #3366cc 0%, #5e62b0 100%);
        color: white;
        padding: 25px 30px;
        position: relative;
    }
    
    .profile-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 24px;
    }
    
    .profile-header .subtitle {
        margin-top: 5px;
        opacity: 0.9;
        font-size: 14px;
    }
    
    .profile-body {
        padding: 30px;
    }
    
    /* Form styling */
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.2s ease;
        background-color: #f8f9fa;
    }
    
    .form-control:focus {
        border-color: #3366cc;
        box-shadow: 0 0 0 0.25rem rgba(51, 102, 204, 0.15);
        background-color: #fff;
    }
    
    textarea.form-control {
        min-height: 100px;
    }
    
    /* Section styling */
    .section-title {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 15px;
        margin-bottom: 20px;
        font-weight: 600;
        color: #3366cc;
        display: flex;
        align-items: center;
    }
    
    .section-title i {
        margin-right: 8px;
        font-size: 20px;
    }
    
    /* Profile image styling */
    .profile-image-container {
        width: 150px;
        height: 150px;
        margin: 0 auto 20px;
        position: relative;
    }
    
    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .default-profile-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 50px;
        border: 4px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .image-upload-overlay {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: #3366cc;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 5;
        border: 2px solid #fff;
    }
    
    /* Button styling */
    .btn-primary {
        background: linear-gradient(135deg, #3366cc 0%, #5e62b0 100%);
        border: none;
        padding: 12px 25px;
        font-weight: 500;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(51, 102, 204, 0.25);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #2a56ad 0%, #4e5298 100%);
        box-shadow: 0 6px 12px rgba(51, 102, 204, 0.3);
        transform: translateY(-2px);
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }
    
    /* Alert styling */
    .alert {
        border-radius: 8px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .alert-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #842029;
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
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 12px 15px;
        cursor: pointer;
        text-align: center;
        margin-top: 10px;
        transition: all 0.2s ease;
        display: block;
    }
    
    .custom-file-upload:hover {
        background-color: #e9ecef;
    }
    
    input[type="file"] {
        display: none;
    }
    
    /* Card and section stylng */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    hr {
        margin: 30px 0;
        opacity: 0.1;
    }
</style>

<div class="main-container">
    <div class="container">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= isset($_SESSION['error']) ? 'danger' : 'success' ?>">
                        <?php if (isset($_SESSION['error'])): ?>
                            <i class="fas fa-exclamation-circle me-2"></i>
                        <?php else: ?>
                            <i class="fas fa-check-circle me-2"></i>
                        <?php endif; ?>
                        <?= $_SESSION['message']; ?>
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
                        <form action="/update-profile" method="POST" enctype="multipart/form-data">
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
                                    <label for="profile_photo" class="image-upload-overlay">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg, image/png">
                                </div>
                                
                            </div>
                            
                            <!-- Account Information -->
                            <div class="section-title">
                                <i class="fas fa-user-circle"></i> Account Informatie
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">E-mailadres</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <!-- Personal Information -->
                            <div class="section-title mt-4">
                                <i class="fas fa-address-card"></i> Persoonlijke Informatie
                            </div>
                            <div class="form-group">
                                <label for="name" class="form-label">Volledige Naam</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Uw volledige naam">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Telefoonnummer</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Bijv. 06-12345678">
                            </div>
                            
                            <div class="form-group">
                                <label for="bio" class="form-label">Over Mij</label>
                                <textarea class="form-control" id="bio" name="bio" placeholder="Vertel iets over uzelf"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            </div>
                            
                            <!-- Address Information -->
                            <div class="section-title mt-4">
                                <i class="fas fa-map-marker-alt"></i> Adres Informatie
                            </div>
                            <div class="form-group">
                                <label for="address" class="form-label">Straat en Huisnummer</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Bijv. Hoofdstraat 123">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="city" class="form-label">Woonplaats</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="Bijv. Amsterdam">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="postal_code" class="form-label">Postcode</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" placeholder="Bijv. 1234 AB">
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Password Change -->
                            <div class="section-title">
                                <i class="fas fa-lock"></i> Wachtwoord Wijzigen
                            </div>
                            <div class="form-group">
                                <label for="current_password" class="form-label">Huidig Wachtwoord</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Vul uw huidige wachtwoord in">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">Nieuw Wachtwoord</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Voer een nieuw wachtwoord in">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">Bevestig Nieuw Wachtwoord</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Bevestig uw nieuwe wachtwoord">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Opslaan
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
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 