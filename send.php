<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Укажите ваш реальный email сюда:
    $to = "sumerechnyj.duh@gmail.com"; 
    
    $subject = "Новая заявка на первичную консультацию с сайта Честный советник";
    
    $name = strip_tags(trim($_POST["name"]));
    $contact = strip_tags(trim($_POST["contact"]));
    $message = htmlspecialchars(trim($_POST["message"]));
    
    $email_content = "Имя: $name\n";
    $email_content .= "Контакт (Телефон/Email): $contact\n\n";
    $email_content .= "Описание ситуации:\n$message\n";
    
    $boundary = md5(time());
    
    $headers = "From: робот сайта <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
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
                
                // Ограничение на размер файла (например, до 15 МБ)
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
    
    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Спасибо! Ваша заявка и документы успешно отправлены. Мы свяжемся с вами в ближайшее время.'); window.location.href='index.html';</script>";
    } else {
        echo "<script>alert('Ошибка при отправке. Пожалуйста, свяжитесь с нами напрямую по телефону.'); window.history.back();</script>";
    }
} else {
    header("Location: index.html");
}
?>