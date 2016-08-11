<?php

namespace FFMVC\Helpers;

/**
 * Mail Helper Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Mail extends \Prefab
{
    /**
     * Return an instance of PHPMailer populated with application settings
     *
     * @param array $data
     * @return \PHPMailer
     * @url https://github.com/PHPMailer/PHPMailer
     */
    public static function getPhpMailer(array $data = []): \PHPMailer
    {
        $f3 = \Base::instance();

        $mail = new \PHPMailer();
        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->CharSet = $f3->get('ENCODING');
        $mail->Username = $f3->get('email.user');
        $mail->Password = $f3->get('email.pass');
        $mail->Port = $f3->get('email.port');
        $mail->Host = $f3->get('email.host');
        $mail->Timeout = $f3->get('email.timeout');

        if ($f3->get('email.sendmail')) {
            $mail->Sendmail = 'smtp://' .  $f3->get('email.host') . ':' . $f3->get('email.port');
        }

        $mail->FromName = $f3->get('email.from_name');
        $mail->From = $f3->get('email.from');

        // PHPMailer doesn't take 'To' so we have to do that with addAddress()
        if (array_key_exists('To', $data)) {
            $mail->addAddress($data['To']);
            unset($data['To']);
        }

        // finally set other values like overrides
        foreach ($data as $k => $v) {
            $mail->$k = $v;
        }

        return $mail;
    }
}
