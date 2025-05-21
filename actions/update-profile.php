<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: /login-form');
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Function to handle error and redirect
function redirectWithError($message) {
    $_SESSION['error'] = true;
    $_SESSION['message'] = $message;
    header('Location: /profile');
    exit;
}

// Function to handle success and redirect
function redirectWithSuccess($message) {
    $_SESSION['message'] = $message;
    header('Location: /profile');
    exit;
}

try {
    // Get current user data for verification
    $stmt = $conn->prepare("
        SELECT a.*, p.name, p.phone, p.profile_photo, p.bio, p.address, p.city, p.postal_code
        FROM account a 
        LEFT JOIN user_profiles p ON a.id = p.account_id
        WHERE a.id = :id
    ");
    $stmt->bindParam(":id", $_SESSION['id']);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        redirectWithError("Gebruiker niet gevonden.");
    }
    
    // Initialize variables for update
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_photo_path = $user['profile_photo']; // Default to current value
    
    // Validate email
    if (empty($email)) {
        redirectWithError("E-mailadres is verplicht.");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithError("Ongeldig e-mailadres formaat.");
    }
    
    // Check if email is already in use by another user
    if ($email !== $user['email']) {
        $check_email = $conn->prepare("SELECT id FROM account WHERE email = :email AND id != :id");
        $check_email->bindParam(":email", $email);
        $check_email->bindParam(":id", $_SESSION['id']);
        $check_email->execute();
        
        if ($check_email->rowCount() > 0) {
            redirectWithError("Dit e-mailadres is al in gebruik.");
        }
    }
    
    // Handle password change if requested
    $password_updated = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // All password fields must be provided
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            redirectWithError("Alle wachtwoordvelden moeten worden ingevuld.");
        }
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            redirectWithError("Huidig wachtwoord is onjuist.");
        }
        
        // Check if new passwords match
        if ($new_password !== $confirm_password) {
            redirectWithError("Nieuwe wachtwoorden komen niet overeen.");
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => 12]);
        $password_updated = true;
    }
    
    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_photo'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => "Het bestand is te groot (overschrijdt php.ini upload_max_filesize).",
                UPLOAD_ERR_FORM_SIZE => "Het bestand is te groot (overschrijdt MAX_FILE_SIZE in HTML-formulier).",
                UPLOAD_ERR_PARTIAL => "Het bestand is slechts gedeeltelijk geüpload.",
                UPLOAD_ERR_NO_FILE => "Er is geen bestand geüpload.",
                UPLOAD_ERR_NO_TMP_DIR => "Tijdelijke map ontbreekt.",
                UPLOAD_ERR_CANT_WRITE => "Kan bestand niet naar schijf schrijven.",
                UPLOAD_ERR_EXTENSION => "Bestandsupload gestopt door een PHP-extensie."
            ];
            
            $error_message = $error_messages[$file['error']] ?? "Onbekende uploadfout.";
            redirectWithError($error_message);
        }
        
        // Check file size (2MB max)
        if ($file['size'] > 2 * 1024 * 1024) {
            redirectWithError("Het bestand is te groot. Maximaal 2MB toegestaan.");
        }
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowed_types)) {
            redirectWithError("Ongeldig bestandstype. Alleen JPG en PNG zijn toegestaan.");
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/profile_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = $_SESSION['id'] . '_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = $upload_dir . $filename;
        
        // Move the file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            redirectWithError("Fout bij uploaden bestand. Probeer het opnieuw.");
        }
        
        // Delete old profile photo if exists
        if (!empty($user['profile_photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $user['profile_photo'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $user['profile_photo']);
        }
        
        // Set the new relative path to database
        $profile_photo_path = '/assets/uploads/profile_photos/' . $filename;
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    // Update account in database
    $sql_account = "UPDATE account SET email = :email WHERE id = :id";
    
    // Add password to update if changed
    if ($password_updated) {
        $sql_account .= ", password = :password";
    }
    
    $update_account = $conn->prepare($sql_account);
    $update_account->bindParam(":email", $email);
    $update_account->bindParam(":id", $_SESSION['id']);
    
    if ($password_updated) {
        $update_account->bindParam(":password", $hashed_password);
    }
    
    $update_account->execute();
    
    // Check if user has a profile already or need to insert
    $check_profile = $conn->prepare("SELECT id FROM user_profiles WHERE account_id = :account_id");
    $check_profile->bindParam(":account_id", $_SESSION['id']);
    $check_profile->execute();
    
    if ($check_profile->rowCount() > 0) {
        // Update existing profile
        $sql_profile = "UPDATE user_profiles SET 
            name = :name, 
            phone = :phone, 
            bio = :bio, 
            address = :address, 
            city = :city, 
            postal_code = :postal_code";
        
        if ($profile_photo_path !== $user['profile_photo']) {
            $sql_profile .= ", profile_photo = :profile_photo";
        }
        
        $sql_profile .= " WHERE account_id = :account_id";
        
        $update_profile = $conn->prepare($sql_profile);
        $update_profile->bindParam(":name", $name);
        $update_profile->bindParam(":phone", $phone);
        $update_profile->bindParam(":bio", $bio);
        $update_profile->bindParam(":address", $address);
        $update_profile->bindParam(":city", $city);
        $update_profile->bindParam(":postal_code", $postal_code);
        $update_profile->bindParam(":account_id", $_SESSION['id']);
        
        if ($profile_photo_path !== $user['profile_photo']) {
            $update_profile->bindParam(":profile_photo", $profile_photo_path);
        }
        
        $update_profile->execute();
    } else {
        // Insert new profile
        $sql_profile = "INSERT INTO user_profiles (
            account_id, name, phone, bio, address, city, postal_code, profile_photo
        ) VALUES (
            :account_id, :name, :phone, :bio, :address, :city, :postal_code, :profile_photo
        )";
        
        $insert_profile = $conn->prepare($sql_profile);
        $insert_profile->bindParam(":account_id", $_SESSION['id']);
        $insert_profile->bindParam(":name", $name);
        $insert_profile->bindParam(":phone", $phone);
        $insert_profile->bindParam(":bio", $bio);
        $insert_profile->bindParam(":address", $address);
        $insert_profile->bindParam(":city", $city);
        $insert_profile->bindParam(":postal_code", $postal_code);
        $insert_profile->bindParam(":profile_photo", $profile_photo_path);
        
        $insert_profile->execute();
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Update session email if changed
    if ($email !== $user['email']) {
        $_SESSION['email'] = $email;
    }
    
    // Update session profile photo if changed
    if ($profile_photo_path !== $user['profile_photo']) {
        $_SESSION['profile_photo'] = $profile_photo_path;
    }
    
    redirectWithSuccess("Profiel succesvol bijgewerkt!");
    
} catch (PDOException $e) {
    // Rollback the transaction in case of error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error (in a production environment)
    error_log("Profile update error: " . $e->getMessage());
    redirectWithError("Er is een systeemfout opgetreden. Probeer het later opnieuw.");
} 