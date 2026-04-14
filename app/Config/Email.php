<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'no-reply@innovativegraphics.test';
    public string $fromName   = 'Innovative Graphics';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname - Gmail SMTP Server
     */
    public string $SMTPHost = 'smtp.gmail.com';

    /**
     * Which SMTP authentication method to use: login, plain
     */
    public string $SMTPAuthMethod = 'plain';

    /**
     * SMTP Username - Your Gmail email address
     */
    public string $SMTPUser = 'your-email@gmail.com';

    /**
     * SMTP Password - Your Gmail App Password (16 characters)
     */
    public string $SMTPPass = 'xxxxxxxxxxxxxxxx';

    /**
     * SMTP Port - Gmail uses 587 for TLS, 465 for SSL
     */
    public int $SMTPPort = 587;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 30;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     * Use 'tls' for port 587, or 'ssl' for port 465
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = true;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    public function __construct()
    {
        parent::__construct();

        $this->fromEmail = env('email.fromEmail', $this->fromEmail);
        $this->fromName = env('email.fromName', $this->fromName);
        $this->protocol = env('email.protocol', $this->protocol);
        $this->SMTPHost = env('email.SMTPHost', $this->SMTPHost);
        $this->SMTPUser = env('email.SMTPUser', $this->SMTPUser);
        $this->SMTPPass = env('email.SMTPPass', $this->SMTPPass);
        $this->SMTPPort = (int) env('email.SMTPPort', $this->SMTPPort);
        $this->SMTPTimeout = (int) env('email.SMTPTimeout', $this->SMTPTimeout);
        $this->SMTPCrypto = env('email.SMTPCrypto', $this->SMTPCrypto);
        $this->mailType = env('email.mailType', $this->mailType);
    }
}