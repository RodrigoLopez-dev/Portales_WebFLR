<?php

return [
    'enabled' => filter_var(env_value('MAIL_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'smtp_host' => env_value('SMTP_HOST', ''),
    'smtp_port' => (int) env_value('SMTP_PORT', 587),
    'smtp_user' => env_value('SMTP_USER', ''),
    'smtp_pass' => env_value('SMTP_PASS', ''),

    'from_email' => env_value('MAIL_FROM_EMAIL', env_value('SMTP_USER', '')),
    'from_name' => env_value('MAIL_FROM_NAME', 'Gestor de Proyectos'),

    'reply_to' => env_value('MAIL_REPLY_TO', ''),
    'reply_name' => env_value('MAIL_REPLY_NAME', 'Gestor de Proyectos'),

    'bcc' => env_value('MAIL_BCC', ''),
];