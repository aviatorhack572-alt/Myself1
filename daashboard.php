<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$phone_number_id = $_SESSION['phone_number_id'] ?? 1;
$active_chat = isset($_GET['chat']) ? $_GET['chat'] : '';
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>WhatsApp CRM Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body, html { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; height: 100%; margin: 0; overflow: hidden; }
        .wa-chat-bg { background-color: #efeae2; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); }
        .wa-header-top { background-color: #00a884; }
        .wa-panel-header { background-color: #ededed; }
        .msg-in { background-color: #ffffff; }
        .msg-out { background-color: #d9fdd3; }
    </style>
</head>
<body class="flex flex-col h-full w-full">

    <div class="wa-header-top h-16 md:h-28 w-full absolute top-0 left-0 z-0"></div>

    <div class="flex-grow z-10 max-w-[1600px] w-full mx-auto p-0 md:py-3 md:px-4 h-full flex">
        <div class="bg-white w-full h-full shadow-2xl flex overflow-hidden rounded-none md:rounded-lg border border-gray-300 relative">
            
            <!-- Sidebar: Chats List -->
            <div class="w-full md:w-[380px] lg:w-[420px] bg-white border-r border-gray-200 flex flex-col h-full z-20 <?php echo !empty($active_chat) ? 'hidden md:flex' : 'flex'; ?>">
                <div class="wa-panel-header px-4 py-3 flex justify-between items-center border-b border-gray-300 flex-shrink-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-bold">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800">واٹس ایپ CRM</h2>
                            <span class="text-xs text-green-600 font-medium">● آن لائن</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 text-gray-600">
                        <a href="logout.php" title="لاگ آؤٹ" class="hover:text-red-600"><i class="fa-solid fa-right-from-bracket text-lg"></i></a>
                    </div>
                </div>

                <div class="flex-grow overflow-y-auto divide-y divide-gray-100">
                    <?php
                    $chats_q = $conn->prepare("SELECT DISTINCT sender_phone FROM messages WHERE phone_number_id = ? ORDER BY id DESC");
                    $chats_q->bind_param("i", $phone_number_id);
                    $chats_q->execute();
                    $chats_res = $chats_q->get_result();

                    if ($chats_res->num_rows > 0) {
                        while ($chat = $chats_res->fetch_assoc()) {
                            $cust_phone = $chat['sender_phone'];
                            $is_active = ($active_chat == $cust_phone) ? 'bg-gray-100' : '';
                            echo '
                            <a href="dashboard.php?chat=' . urlencode($cust_phone) . '" class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer transition ' . $is_active . '">
                                <div class="w-12 h-12 rounded-full bg-[#00a884] text-white flex items-center justify-center font-bold text-base mr-3 flex-shrink-0">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">+' . htmlspecialchars($cust_phone) . '</h3>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">گفتگو دیکھنے کے لیے کلک کریں...</p>
                                </div>
                            </a>';
                        }
                    } else {
                        echo '<div class="p-6 text-center text-gray-400 text-sm">ابھی تک کوئی کسٹمر چیٹ موجود نہیں ہے۔</div>';
                    }
                    $chats_q->close();
                    ?>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="w-full md:flex-grow h-full bg-[#efeae2] wa-chat-bg flex flex-col z-30 absolute md:relative inset-0 <?php echo empty($active_chat) ? 'hidden md:flex' : 'flex'; ?>">
                
                <?php if (!empty($active_chat)): ?>
                    <div class="wa-panel-header px-4 py-3 flex justify-between items-center border-b border-gray-300 flex-shrink-0">
                        <div class="flex items-center space-x-3">
                            <a href="dashboard.php" class="md:hidden text-gray-600 mr-2 text-lg"><i class="fa-solid fa-arrow-left"></i></a>
                            <div class="w-10 h-10 rounded-full bg-gray-400 text-white flex items-center justify-center font-bold">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">+<?php echo htmlspecialchars($active_chat); ?></h3>
                                <span class="text-xs text-gray-500">آن لائن</span>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div id="msg-container" class="flex-grow overflow-y-auto p-4 space-y-2">
                        <!-- Loaded dynamically -->
                    </div>

                    <!-- Reply Form -->
                    <form id="chat-form" class="bg-[#f0f2f5] px-3 py-2 flex items-center space-x-2 border-t border-gray-300 flex-shrink-0">
                        <input type="hidden" name="recipient_wa_id" value="<?php echo htmlspecialchars($active_chat); ?>">
                        
                        <!-- Attachment Icon -->
                        <div class="flex items-center text-gray-600 text-xl px-1 space-x-2">
                            <label class="cursor-pointer hover:text-gray-800" title="تصویر یا فائل منتخب کریں">
                                <i class="fa-solid fa-paperclip"></i>
                                <input type="file" name="media_file" id="media-file-input" class="hidden" onchange="submitMessageForm()">
                            </label>
                            
                            <!-- Voice Recording Button -->
                            <button type="button" id="record-btn" onclick="toggleRecording()" class="text-gray-600 hover:text-red-600 focus:outline-none" title="وائس ریکارڈنگ شروع/بند کریں">
                                <i class="fa-solid fa-microphone text-lg" id="mic-icon"></i>
                            </button>
                        </div>
                        
                        <input type="text" name="message" id="message-input" autocomplete="off" placeholder="پیغام یہاں لکھیں..." class="flex-grow py-2 px-3 bg-white rounded-lg text-sm focus:outline-none border border-gray-300">
                        
                        <button type="button" onclick="submitMessageForm()" class="bg-[#00a884] hover:bg-[#008f72] text-white w-10 h-10 rounded-full flex items-center justify-center shadow transition flex-shrink-0" title="بھیجیں">
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                        </button>
                    </form>

                <?php else: ?>
                    <div class="hidden md:flex flex-col items-center justify-center h-full text-center p-6">
                        <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-3xl mb-4">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">WhatsApp CRM ڈیش بورڈ</h3>
                        <p class="text-sm text-gray-500">گفتگو شروع کرنے یا دیکھنے کے لیے بائیں جانب سے کسی بھی کسٹمر پر کلک کریں۔</p>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <?php if (!empty($active_chat)): ?>
    <script>
        const chatId = "<?php echo $active_chat; ?>";
        let isScrolledToBottom = true;
        const msgContainer = document.getElementById('msg-container');

        msgContainer.addEventListener('scroll', function() {
            isScrolledToBottom = msgContainer.scrollHeight - msgContainer.scrollTop <= msgContainer.clientHeight + 50;
        });

        function fetchMessages() {
            fetch('fetch_messages.php?chat_id=' + encodeURIComponent(chatId))
                .then(response => response.text())
                .then(data => {
                    if (msgContainer.innerHTML !== data) {
                        msgContainer.innerHTML = data;
                        if (isScrolledToBottom) {
                            msgContainer.scrollTop = msgContainer.scrollHeight;
                        }
                    }
                })
                .catch(err => console.error('Error fetching messages:', err));
        }

        fetchMessages();
        setInterval(fetchMessages, 2000);

        window.onload = function() {
            msgContainer.scrollTop = msgContainer.scrollHeight;
        };

        function submitMessageForm() {
            const formElement = document.getElementById('chat-form');
            const formData = new FormData(formElement);
            
            document.getElementById('message-input').value = '';

            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('media-file-input').value = '';
                fetchMessages();
            })
            .catch(err => {
                console.error('Error sending message:', err);
            });
        }

        document.getElementById('message-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitMessageForm();
            }
        });

        // Strict Voice Recording Logic (Ogg/Opus for WhatsApp)
        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        function toggleRecording() {
            const micIcon = document.getElementById('mic-icon');
            const recordBtn = document.getElementById('record-btn');

            if (!isRecording) {
                let options = { mimeType: 'audio/ogg;codecs=opus' };
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options = { mimeType: 'audio/webm;codecs=opus' };
                    if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                        options = { mimeType: 'audio/webm' };
                    }
                }

                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(stream => {
                        mediaRecorder = new MediaRecorder(stream, options);
                        audioChunks = [];

                        mediaRecorder.ondataavailable = event => {
                            if (event.data.size > 0) {
                                audioChunks.push(event.data);
                            }
                        };

                        mediaRecorder.onstop = () => {
                            let fileExt = 'ogg';
                            let finalMime = options.mimeType || 'audio/ogg';
                            if (finalMime.includes('webm')) {
                                fileExt = 'webm';
                            }

                            const audioBlob = new Blob(audioChunks, { type: finalMime });
                            const audioFile = new File([audioBlob], "voice_note." + fileExt, { type: finalMime });

                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(audioFile);
                            document.getElementById('media-file-input').files = dataTransfer.files;
                            
                            submitMessageForm();
                        };

                        mediaRecorder.start();
                        isRecording = true;
                        micIcon.classList.remove('fa-microphone');
                        micIcon.classList.add('fa-stop', 'text-red-600', 'animate-pulse');
                        recordBtn.title = "ریکارڈنگ بند کر کے بھیجیں";
                    })
                    .catch(err => {
                        alert('مائیکروفون کی اجازت نہیں ملی یا براؤزر سپورٹ نہیں کرتا۔');
                        console.error(err);
                    });
            } else {
                mediaRecorder.stop();
                isRecording = false;
                micIcon.classList.remove('fa-stop', 'text-red-600', 'animate-pulse');
                micIcon.classList.add('fa-microphone');
            }
        }
    </script>
    <?php endif; ?>
</body>
</html>
