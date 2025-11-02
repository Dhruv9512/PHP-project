<?php
/**
 * Generates a full HTML email template.
 *
 * @param array $data An associative array with keys like:
 * 'username' => The user's name.
 * 'subject'  => The email subject (used in <title>).
 * 'body'     => The main HTML content for the email.
 * 'cta_text' => (Optional) Text for a button.
 * 'cta_url'  => (Optional) URL for the button.
 */
function getEmailTemplate(array $data): string {
    $username = htmlspecialchars($data['username'] ?? 'User');
    $subject = htmlspecialchars($data['subject'] ?? 'A message from Event Portal');
    $body = $data['body'] ?? '<p>This is a default message.</p>';
    $cta_text = htmlspecialchars($data['cta_text'] ?? '');
    $cta_url = htmlspecialchars($data['cta_url'] ?? '#');

    $buttonHtml = '';
    if (!empty($cta_text)) {
        $buttonHtml = '
            <tr>
                <td style="padding: 20px 0 30px 0; text-align: center;">
                    <a href="' . $cta_url . '" style="background-color: #6B21A8; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                        ' . $cta_text . '
                    </a>
                </td>
            </tr>
        ';
    }

    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $subject . '</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; }
        .content { padding: 30px; }
        .header { background-color: #6B21A8; color: #ffffff; padding: 20px; text-align: center; }
        .footer { background-color: #f4f4f4; color: #777; padding: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif;">
    <table class="container" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; max-width: 600px; margin: 0 auto;">
        <tr>
            <td class="header" style="background-color: #6B21A8; color: #ffffff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">Event Portal</h1>
            </td>
        </tr>

        <tr>
            <td class="content" style="padding: 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="font-size: 16px; line-height: 1.6;">
                            <p style="margin-bottom: 20px;">Hi ' . $username . ',</p>
                            ' . $body . '
                        </td>
                    </tr>
                    ' . $buttonHtml . '
                </table>
            </td>
        </tr>

        <tr>
            <td class="footer" style="background-color: #f4f4f4; color: #777; padding: 20px; text-align: center; font-size: 12px;">
                <p style="margin: 5px 0;">&copy; ' . date('Y') . ' Event Portal. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>';
}
?>