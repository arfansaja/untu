<?php
// Inisialisasi variabel untuk pesan dan status
$message = '';
$msisdn = '';
$otp = '';
$showVerifyForm = false;

// Proses form jika ada pengiriman data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msisdn = $_POST['msisdn'] ?? '';
    $otp = $_POST['otp'] ?? '';
    $action = $_POST['action'] ?? '';

    // URL API
    $api_url = 'https://circlecuan.com/api.php?route=' . ($action === 'verify' ? 'otp/verify' : 'otp/request');

    // Data yang akan dikirim
    $data = ['msisdn' => $msisdn];
    if ($action === 'verify') {
        $data['otp'] = $otp;
    }

    // Inisialisasi cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    // Eksekusi dan dapatkan respons
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Dekode respons JSON
    $result = json_decode($response, true);

    // Tampilkan pesan berdasarkan hasil
    if ($http_code == 200 && isset($result['status']) && $result['status'] === 'success') {
        $message = $result['message'];
        if ($action === 'request' && $message === 'OTP request successful') {
            $message .= '. Periksa ponsel Anda untuk kode OTP.';
            $showVerifyForm = true; // Tampilkan form verifikasi
        }
    } else {
        $message = $result['message'] ?? ($error ?: 'Terjadi kesalahan saat menghubungi API.');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Website Saya</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            color: #555;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #4CAF50;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
            display: none;
            font-size: 14px;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            display: block;
        }
        .error {
            background-color: #fce4ec;
            color: #c62828;
            display: block;
        }
        #otpVerifyForm {
            display: <?php echo $showVerifyForm ? 'block' : 'none'; ?>;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verifikasi OTP</h2>

        <!-- Form untuk meminta OTP -->
        <form method="POST" action="" id="otpRequestForm">
            <input type="hidden" name="action" value="request">
            <div class="form-group">
                <label for="msisdn">Nomor Telepon (contoh: 628xxxxx):</label>
                <input type="text" id="msisdn" name="msisdn" value="<?php echo htmlspecialchars($msisdn); ?>" required>
            </div>
            <button type="submit">Kirim OTP</button>
        </form>

        <!-- Form untuk verifikasi OTP -->
        <form method="POST" action="" id="otpVerifyForm">
            <input type="hidden" name="action" value="verify">
            <div class="form-group">
                <label for="msisdn_verify">Nomor Telepon:</label>
                <input type="text" id="msisdn_verify" name="msisdn" value="<?php echo htmlspecialchars($msisdn); ?>" required>
            </div>
            <div class="form-group">
                <label for="otp">Kode OTP (6 digit):</label>
                <input type="text" id="otp" name="otp" value="<?php echo htmlspecialchars($otp); ?>" required>
            </div>
            <button type="submit">Verifikasi OTP</button>
        </form>

        <!-- Tampilkan pesan -->
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>