<?php
session_start();
require_once "../config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone_number = trim($_POST['phone_number']);
    $password = trim($_POST['password']);

    if (!empty($phone_number) && !empty($password)) {
        // Users aur phone_numbers table ko join kar ke check karna ke number aur password theek hain ya nahi
        $stmt = $conn->prepare("SELECT u.id, u.password, p.id as phone_row_id, p.phone_number FROM users u JOIN phone_numbers p ON u.phone_number_id = p.id WHERE p.phone_number = ?");
        $stmt->bind_param("s", $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            
            // Password verify karna (agar password hashed ho ya plain text ho dono ko support kare ga)
            if (password_verify($password, $row['password']) || $password === $row['password']) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['phone_number'] = $row['phone_number'];
                $_SESSION['phone_number_id'] = $row['phone_row_id'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Ghalat password darj kiya gaya hai.";
            }
        } else {
            $error = "Yeh WhatsApp number system mein registered nahi hai.";
        }
        $stmt->close();
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
    <title>User Login - myrealearning.com</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .wa-header-bg {
            background-color: #00a884;
        }
        .wa-btn {
            background-color: #25d366;
            transition: background-color 0.3s ease;
        }
        .wa-btn:hover {
            background-color: #20ba5a;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Top Green Strip -->
    <div class="wa-header-bg h-32 w-full absolute top-0 left-0 z-0"></div>

    <!-- Main Container -->
    <div class="flex flex-col items-center justify-center flex-grow z-10 px-4 mt-12">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 border-t-4 border-[#00a884]">
            
            <!-- Icon -->
            <div class="w-16 h-16 bg-[#25d366] rounded-full flex items-center justify-center mx-auto mb-4 shadow-md text-white text-2xl">
                <i class="fa-solid fa-user-lock"></i>
            </div>

            <!-- Heading -->
            <h1 class="text-2xl font-bold text-gray-800 text-center mb-2">User Login</h1>
            <p class="text-gray-500 text-sm text-center mb-6">Apna WhatsApp number aur password darj karein</p>

            <!-- Error Message Display -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="" method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">WhatsApp Number</label>
                    <input type="text" name="phone_number" required placeholder="Misal: 923001234567" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#00a884]">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                    <input type="password" name="password" required placeholder="Apna password likhein" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#00a884]">
                </div>

                <button type="submit" class="wa-btn text-white font-semibold py-3 px-4 rounded-lg shadow-md w-full text-center text-lg">
                    Login Karein
                </button>
            </form>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="../index.php" class="text-sm text-[#00a884] hover:underline">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Wapas Home Page par jayein
                </a>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-gray-500 text-sm z-10">
        &copy; 2026 myrealearning.com - Jumla huquq mehfooz hain.
    </footer>

</body>
</html>