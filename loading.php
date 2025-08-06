<?php
// Handle individual SMS sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone']) && isset($_POST['message'])) {
    header('Content-Type: text/plain');

    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    // Replace [TICKET] with a random ID like ID-1938174
    if (strpos($message, '[TICKET]') !== false) {
        $ticket_id = 'ID-' . rand(1000000, 9999999);
        $message = str_replace('[TICKET]', $ticket_id, $message);
    }

    $payload = [
        'to_number' => $phone,
        'msg_body'  => $message
    ];

    $ch = curl_init("https://biller.lendelio.net/api/sms");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "$phone: ❌ Error - " . curl_error($ch);
    } else {
        echo "$phone: ✅ Success - " . $result;
    }

    curl_close($ch);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk SMS Sender with [TICKET]</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: auto; }
        textarea, input, button { width: 100%; margin-top: 10px; padding: 10px; font-size: 16px; }
        #responseBox { white-space: pre-wrap; background: #f9f9f9; border: 1px solid #ccc; padding: 10px; margin-top: 20px; height: 200px; overflow-y: auto; }
        button { background-color: #0073aa; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #005f8a; }
    </style>
</head>
<body>

<h2>Bulk SMS Sender (1 per second, dynamic [TICKET])</h2>

<form id="smsForm">
    <label for="phones">Phone Numbers (one per line):</label>
    <textarea name="phones" id="phones" rows="5" required placeholder="1234567890&#10;9876543210"></textarea>

    <label for="message">Message (use [TICKET] to insert a random ID):</label>
    <textarea name="message" id="message" rows="4" required placeholder="Example: Your request has been received. Ref: [TICKET]"></textarea>

    <button type="submit">Start Sending</button>
</form>

<div id="responseBox">Waiting...</div>

<script>
document.getElementById('smsForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const phones = document.getElementById('phones').value.split('\n').map(p => p.trim()).filter(p => p !== '');
    const message = document.getElementById('message').value.trim();
    const responseBox = document.getElementById('responseBox');
    responseBox.textContent = '';

    let index = 0;

    async function sendNext() {
        if (index >= phones.length) {
            responseBox.textContent += '\n✅ All messages sent.';
            return;
        }

        const phone = phones[index];
        responseBox.textContent += `⏳ Sending to ${phone}...\n`;
        responseBox.scrollTop = responseBox.scrollHeight;

        const formData = new FormData();
        formData.append('phone', phone);
        formData.append('message', message); // [TICKET] replaced in PHP

        try {
            const res = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
            const text = await res.text();
            responseBox.textContent += text + '\n\n';
        } catch (err) {
            responseBox.textContent += `${phone}: ❌ Network error\n\n`;
        }

        responseBox.scrollTop = responseBox.scrollHeight;
        index++;

        setTimeout(sendNext, 1000); // Wait 1 second
    }

    sendNext();
});
</script>

</body>
</html>
