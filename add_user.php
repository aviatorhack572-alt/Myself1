<?php
session_start();
require_once "../config.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone_number = trim($_POST['phone_number']);
    $phone_id = trim($_POST['phone_id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // Plain text password

    if (!empty($phone_number) && !empty($phone_id) && !empty($username) && !empty($password)) {
        
        // 1. Pehle phone_numbers table mein WhatsApp number aur Meta Phone ID insert karein
        $stmt_phone = $conn->prepare("INSERT INTO phone_numbers (phone_number, phone_id) VALUES (?, ?)");
        $stmt_phone->bind_param("ss", $phone_number, $phone_id);
        
        if ($stmt_phone->execute()) {
            $phone_row_id = $stmt_phone->insert_id; // Newly inserted phone number ki ID
            $stmt_phone->close();

            // 2. Ab users table mein username, plain text password, aur phone_number_id insert karein
            $stmt_user = $conn->prepare("INSERT INTO users (username, password, phone_number_id) VALUES (?, ?, ?)");
            $stmt_user->bind_param("ssi", $username, $password, $phone_row_id);

            if ($stmt_user->execute()) {
                $success = "WhatsApp number aur User kamyabi ke sath add ho gaye hain!";
            } else {
                $error = "User create karne mein masla aaya (Shayad yeh username pehle se mojood ho).";
            }
            $stmt_user->close();
        } else {
            $error = "WhatsApp number save karne mein masla aaya.";
        }
    } else {
        $error = "Tamam fields pur karna lazmi hain.";
    }
}
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add WhatsApp User - Admin Panel</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .wa-header-bg {
            background-color: #075e54;
        }
        .wa-btn {
            background-color: #128c7e;
            transition: background-color 0.3s ease;
        }
        .wa-btn:hover {
            background-color: #0d6d63;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Top Navigation / Header -->
    <div class="wa-header-bg text-white py-4 px-6 shadow-md flex justify-between items-center">
        <h1 class="text-xl font-bold"><i class="fa-solid fa-shield-halved mr-2"></i> Admin Panel - Add User</h1>
        <a href="login.php" class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg text-white">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col items-center justify-center flex-grow px-4 py-8">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-8 border-t-4 border-[#075e54]">
            
            <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">Naya WhatsApp Number & User Add Karein</h2>

            <!-- Success Message -->
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 text-sm text-center">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">WhatsApp Number (Misal ke tor par)</label>
                    <input type="text" name="phone_number" required placeholder="923001234567" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#075e54]">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Meta Phone Number ID</label>
                    <input type="text" name="phone_id" required placeholder="Meta developer dashboard se mili ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#075e54]">
                </div>

                <hr class="my-4 border-gray-200">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">User Login Username</label>
                    <input type="text" name="username" required placeholder="Misal: agent1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#075e54]">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">User Login Password (Plain Text)</label>
                    <input type="text" name="password" required placeholder="Password likhein (misal: pass123)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#075e54]">
                </div>

                <button type="submit" class="wa-btn text-white font-semibold py-3 px-4 rounded-lg shadow-md w-full text-center text-lg">
                    User Save Karein
                </button>
            </form>

        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-gray-500 text-sm">
        &copy; 2026 myrealearning.com - Jumla huquq mehfooz hain.
    </footer>

</body>
</html>