<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Real Learning - Private Chat System</title>
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

    <!-- Top Green Strip (WhatsApp Style) -->
    <div class="wa-header-bg h-32 w-full absolute top-0 left-0 z-0"></div>

    <!-- Main Container -->
    <div class="flex flex-col items-center justify-center flex-grow z-10 px-4 mt-12">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 text-center border-t-4 border-[#00a884]">
            
            <!-- Logo / Icon -->
            <div class="w-20 h-20 bg-[#25d366] rounded-full flex items-center justify-center mx-auto mb-6 shadow-md text-white text-3xl">
                <i class="fa-brands fa-whatsapp"></i>
            </div>

            <!-- Heading -->
            <h1 class="text-2xl font-bold text-gray-800 mb-2">myrealearning.com</h1>
            <p class="text-gray-500 text-sm mb-8">پرائیویٹ واٹس ایپ API چیٹ سسٹم</p>

            <!-- Login Button -->
            <a href="user/login.php" class="wa-btn text-white font-semibold py-3 px-6 rounded-lg shadow-md w-full block text-center text-lg">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> لاگ ان کریں (User Login)
            </a>

            <!-- Footer Note -->
            <p class="text-xs text-gray-400 mt-6">
                صرف مجاز صارفین کے لیے اندرونی رسائی۔ پبلک رجسٹریشن بند ہے۔
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-gray-500 text-sm z-10">
        &copy; 2026 myrealearning.com - جملہ حقوق محفوظ ہیں۔
    </footer>

</body>
</html>