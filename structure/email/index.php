<?php
function sendEmailViaSMTP($sender, $recipient, $subject, $body)
{
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = $sender['host'];
    //     $mail->SMTPDebug = true;
    $mail->SMTPAuth = true;
    $mail->Username = $sender['address'];
    $mail->Password = $sender['password'];
    $mail->SMTPSecure = $sender['SMTPSecure'];
    $mail->Port = $sender['port'];
    
    $mail->setFrom($sender['address'], $sender['viewName']);
    $mail->addAddress($recipient);
    $mail->addCC($sender['cc']);
    
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    
    $mail->send();
    return true;
}