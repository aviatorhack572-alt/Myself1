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

// 1. Handle User Delete Request
if (isset($_GET['delete_id'])) {
    $user_id = intval($_GET['delete_id']);
    
    // Pehle user ka phone_number_id nikalna taake phone_numbers table se bhi record delete ho sake (optional)
    $q = $conn->query("SELECT phone_number_id FROM users WHERE id = $user_id");
    if ($q->num_rows > 0) {
        $row = $q->fetch_assoc();
        $phone_row_id = $row['phone_number_id'];
        
        // Delete user
        $conn->query("DELETE FROM users WHERE id = $user_id");
        // Delete phone number entry as well if needed
        if ($phone_row_id) {
            $conn->query("DELETE FROM phone_numbers WHERE id = $phone_row_id");
        }
        $success = "Yوزر اور اس کا واٹس ایپ نمبر کامیابی کے ساتھ ڈیلیٹ کر دیا گیا ہے!";
    }
}

// 2. Handle Update Request (Agar form submit ho)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $phone_row_id = intval($_POST['phone_row_id']);
    $phone_number = trim($_POST['phone_number']);
    $phone_id = trim($_POST['phone_id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($phone_number) && !empty($phone_id) && !empty($username) && !empty($password)) {
        // Update phone_numbers table
        $stmt1 = $conn->prepare("UPDATE phone_numbers SET phone_number = ?, phone_id = ? WHERE id = ?");
        $stmt1->bind_param("ssi", $phone_number, $phone_id, $phone_row_id);
        $stmt1->execute();
        $stmt1->close();

        // Update users table
        $stmt2 = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt2->bind_param("ssi", $username, $password, $user_id);
        $stmt2->execute();
        $stmt2->close();

        $success = "یوزر کی تفصیلات کامیابی کے ساتھ اپڈیٹ ہو گئی ہیں!";
    } else {
        $error = "تمام خانوں کو پُر کرنا لازمی ہے۔";
    }
}

// Fetch all users with their phone numbers
$query = "SELECT u.id as user_id, u.username, u.password, p.id as phone_row_id, p.phone_number, p.phone_id 
          FROM users u 
          LEFT JOIN phone_numbers p ON u.phone_number_id = p.id 
          ORDER BY u.id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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

    <!-- Top Navigation -->
    <div class="wa-header-bg text-white py-4 px-6 shadow-md flex justify-between items-center">
        <h1 class="text-xl font-bold"><i class="fa-solid fa-users-gear mr-2"></i> Admin Panel - Manage Users</h1>
        <div>
            <a href="add_user.php" class="bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded-lg text-white text-sm mr-2"><i class="fa-solid fa-user-plus mr-1"></i> نیا یوزر ایڈ کریں</a>
            <a href="login.php" class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg text-white text-sm">لاگ آؤٹ</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="flex flex-col flex-grow px-6 py-8 max-w-7xl mx-auto w-full">
        
        <h2 class="text-2xl font-bold text-gray-800 mb-6">تمام رجسٹرڈ یوزرز اور واٹس ایپ نمبرز</h2>

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

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-x-auto border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 border-b border-gray-200 text-sm">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">يوزر نیم (Username)</th>
                        <th class="py-3 px-4">پاسورڈ (Password)</th>
                        <th class="py-3 px-4">واٹس ایپ نمبر</th>
                        <th class="py-3 px-4">Meta Phone ID</th>
                        <th class="py-3 px-4 text-center">عمل (Actions)</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4"><?php echo $row['user_id']; ?></td>
                                <td class="py-3 px-4 font-semibold text-gray-800"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="py-3 px-4 font-mono text-xs bg-gray-50 rounded"><?php echo htmlspecialchars($row['password']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['phone_number'] ?? 'Not Assigned'); ?></td>
                                <td class="py-3 px-4 font-mono text-xs"><?php echo htmlspecialchars($row['phone_id'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-4 text-center space-x-2">
                                    <!-- Chat View Link for Admin (Yahan hum aage ja kar chat dashboard ka link lagayenge) -->
                                    <a href="../user/dashboard.php?view_number=<?php echo $row['phone_row_id']; ?>" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded text-xs inline-block" title="چیٹس دیکھیں">
                                        <i class="fa-solid fa-comments"></i> چیٹ کھولیں
                                    </a>

                                    <!-- Edit Button (Modal Trigger ya Edit Mode) -->
                                    <button onclick="openEditModal(<?php echo $row['user_id']; ?>, '<?php echo $row['username']; ?>', '<?php echo $row['password']; ?>', '<?php echo $row['phone_number']; ?>', '<?php echo $row['phone_id']; ?>', <?php echo $row['phone_row_id']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded text-xs">
                                        <i class="fa-solid fa-pen-to-square"></i> تبدیل کریں
                                    </button>

                                    <!-- Delete Button -->
                                    <a href="manage_users.php?delete_id=<?php echo $row['user_id']; ?>" onclick="return confirm('کیا آپ واقعی اس یوزر کو ڈیلیٹ کرنا چاہتے ہیں؟');" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded text-xs inline-block">
                                        <i class="fa-solid fa-trash"></i> ڈیلیٹ
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400">کوئی یوزر موجود نہیں۔</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center px-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
            <h3 class="text-xl font-bold text-gray-800 mb-4">یوزر اور واٹس ایپ نمبر اپڈیٹ کریں</h3>
            
            <form action="" method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="phone_row_id" id="edit_phone_row_id">

                <div class="mb-3">
                    <label class="block text-gray-700 text-xs font-semibold mb-1">WhatsApp Number</label>
                    <input type="text" name="phone_number" id="edit_phone_number" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#075e54]">
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700 text-xs font-semibold mb-1">Meta Phone Number ID (نیا نمبر/بین ہونے پر تبدیل کریں)</label>
                    <input type="text" name="phone_id" id="edit_phone_id" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#075e54]">
                </div>

                <div class="mb-3">
                    <label class="block text-gray-700 text-xs font-semibold mb-1">Username</label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#075e54]">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-semibold mb-1">Password (Plain Text)</label>
                    <input type="text" name="password" id="edit_password" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#075e54]">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded text-sm">منسوخ کریں</button>
                    <button type="submit" name="update_user" class="wa-btn text-white px-4 py-2 rounded text-sm">محفوظ کریں</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Modal -->
    <script>
        function openEditModal(userId, username, password, phoneNumber, phoneId, phoneRowId) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_phone_row_id').value = phoneRowId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_password').value = password;
            document.getElementById('edit_phone_number').value = phoneNumber;
            document.getElementById('edit_phone_id').value = phoneId;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>

    <!-- Footer -->
    <footer class="text-center py-4 text-gray-500 text-sm">
        &copy; 2026 myrealearning.com - Jumla huquq mehfooz hain.
    </footer>

</body>
</html>