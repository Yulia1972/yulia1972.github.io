<?php
// Включаем отображение ошибок для отладки (позже можно убрать)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Укажите ваш реальный email:
    $to = "chestnyjsovetnik@yandex.ru"; // замените на ваш рабочий ящик, если нужно
    
    $subject = "Новая заявка с сайта Честный советник";
    
    $name = isset($_POST["name"]) ? strip_tags(trim($_POST["name"])) : "Не указано";
    $contact = isset($_POST["contact"]) ? strip_tags(trim($_POST["contact"])) : "Не указано";
    $message = isset($_POST["message"]) ? htmlspecialchars(trim($_POST["message"])) : "Не указано";
    
    $email_content = "Имя: $name\n";
    $email_content .= "Контакт: $contact\n\n";
    $email_content .= "Описание ситуации:\n$message\n";
    
    $boundary = md5(time());
    
    $headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    $headers .= "Reply-To: " . $contact . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
    
    $body = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $email_content . "\r\n";
    
    // Обработка прикрепленных файлов
    if (isset($_FILES['docs']) && !empty($_FILES['docs']['name'][0])) {
        foreach ($_FILES['docs']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['docs']['error'][$key] == UPLOAD_ERR_OK) {
                $file_name = $_FILES['docs']['name'][$key];
                $file_size = $_FILES['docs']['size'][$key];
                $file_tmp = $_FILES['docs']['tmp_name'][$key];
                
                if ($file_size > 15 * 1024 * 1024) continue;
                
                $handle = fopen($file_tmp, "r");
                $content = fread($handle, filesize($file_tmp));
                fclose($handle);
                
                $encoded_content = chunk_split(base64_encode($content));
                
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: application/octet-stream; name=\"{$file_name}\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"{$file_name}\"\r\n\r\n";
                $body .= $encoded_content . "\r\n";
            }
        }
    }
    
    $body .= "--{$boundary}--";
    
    // Отправка письма
    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Спасибо! Ваша заявка и документы успешно отправлены.'); window.location.href='index.html';</script>";
        exit;
    } else {
        echo "<script>alert('Ошибка сервера при отправке почты. Функция mail() заблокирована на хостинге.'); window.history.back();</script>";
        exit;
    }
} else {
    // Если на send.php зашли напрямую через адресную строку (GET-запросом)
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Ошибка 405: Метод не поддерживается. Пожалуйста, отправляйте форму со страницы контактов.";
    exit;
}
?>
