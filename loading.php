<?php
// Handle the AJAX SMS request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phones']) && isset($_POST['message'])) {
    header('Content-Type: text/plain');

    $phones = explode(',', $_POST['phones']);
    $message = trim($_POST['message']);
    $responses = [];

    foreach ($phones as $phone) {
        $phone = trim($phone);

        // Replace this with your actual SMS API details
        $payload = [
            'to_number' => $phone,
            'msg_body'  => $message
        ];

        // Send request via cURL
        $ch = curl_init("https://biller.lendelio.net/api/sms");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $responses[] = "$phone: ❌ Error - " . curl_error($ch);
        } else {
            $responses[] = "$phone: ✅ Success - " . $result;
        }

        curl_close($ch);
    }

    echo implode("\n", $responses);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Single Page Bulk SMS Sender</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: auto; }
        textarea, input, button { width: 100%; margin-top: 10px; padding: 10px; font-size: 16px; }
        #responseBox { white-space: pre-wrap; background: #f9f9f9; border: 1px solid #ccc; padding: 10px; margin-top: 20px; height: 200px; overflow-y: auto; }
        button { background-color: #0073aa; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #005f8a; }
    </style>
</head>
<body>

<h2>Bulk SMS Sender</h2>

<form id="smsForm">
    <label for="phones">Phone Numbers (comma-separated):</label>
    <textarea name="phones" id="phones" rows="3" required placeholder="e.g. 1234567890,9876543210"></textarea>

    <label for="message">Message:</label>
    <textarea name="message" id="message" rows="4" required placeholder="Your SMS message here..."></textarea>

    <button type="submit">Send SMS</button>
</form>

<div id="responseBox">Waiting to send...</div>

<script>
document.getElementById('smsForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const responseBox = document.getElementById('responseBox');
    responseBox.textContent = 'Sending...';

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        responseBox.textContent = data;
    })
    .catch(err => {
        responseBox.textContent = '❌ Error: ' + err;
    });
});
</script>

</body>
</html>
