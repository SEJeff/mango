<?

require_once('Mail.php');
require_once('Mail/mime.php');

class SSHMessage {

    private
        $idx,
        $data_len,
        $data;

    function __construct($data) {
        $this->idx = 0;
        $this->data = $data;
        $this->data_len = strlen($data);
    }

    function _get_bytes($nr) {
        if (($this->idx + $nr) > $this->data_len)
            throw Exception('Not enough bytes available in SSH message');

        $this->idx += $nr;
        return substr($this->data, $this->idx - $nr, $nr);
    }

    function get_int() {
        # XXX - Weird
        $arr = unpack('N', $this->_get_bytes(4));
        return $arr[1];
    }

    function get_string() {
        return $this->_get_bytes($this->get_int());
    }
}

function bit_length($data) {
    $hbyte = ord($data[0]);
    $bitlen = strlen($data) * 8;
    $check = 0x80;
    while ($check && !($hbyte & $check)) {
        $check >>= 1;
        $bitlen -= 1;
    }
    return $bitlen;
}


function is_valid_ssh_pub_key($key, $check_length = True, $return_fingerprint = false) {
    $keytype = '';
    $length = 0;
    $hash = '';
    $comment = '';

    if(empty($key) || substr($key, 0, 4) != "ssh-")
        return array(false, $keytype, $length, $hash, $comment);

    # Split the data
    list($format, $data, $comment) = explode(" ", $key, 3);

    # Format should be DSA or RSA
    if ($format != "ssh-dss" && $format != "ssh-rsa")
        return array(false, $keytype, $length, $hash, $comment);

    $keytype = $format == 'ssh-dss' ? 'DSA' : 'RSA';

    # Data should be a base64 encoded string
    $certificate = base64_decode($data);
    if ($certificate == $data)
        return array(false, $keytype, $length, $hash, $comment);

    $hash = rtrim(chunk_split(md5($certificate), 2, ':'), ':');

    if ($check_length or $return_fingerprint) {
        try {
            $msg = new SSHMessage($certificate);
            $type = $msg->get_string();

            if ($type != $format)
                return array(false, $keytype, $length, $hash, $comment);

            if ($format == 'ssh-rsa') {
                $e = $msg->get_string();
                $n = $msg->get_string();
                $length = bit_length($n);
            }
            else {
                $p = $msg->get_string();
                $q = $msg->get_string();
                $g = $msg->get_string();
                $y = $msg->get_string();
                $length = bit_length($p);
            }
        } catch (Exception $e) {
            return array(false, $keytype, $length, $hash, $comment);
        }
    }

    if ($check_length) {
        if (($format == "ssh-dss" && $length != 1024)
            || ($format == "ssh-rsa" && $length < 2048))
        {
            # Either invalid, or not enough bits in the public key
            return array(false, $keytype, $length, $hash, $comment);
        }
    }

    # All seems ok
    return array(true, $keytype, $length, $hash, $comment);
}

function array_same($array1, $array2) {
    # Note: assumes no duplicate entries!
    return (count($array1) != count($array2)
            || count(array_diff($array1, $array2)) > 0
            || count(array_diff($array2, $array1)) > 0);

}

function send_mail($recipients, $subject, $headers, $body) {
    global $config;
    
    $mime = new Mail_Mime();
    $mime->setTXTBody($body);
    $mime_params = array(
        'head_charset' => 'UTF-8',
        'head_encoding' => 'quoted-printable',
        'text_charset' => 'UTF-8'
    );
    $content = $mime->get($mime_params);
    $headers = $mime->headers($headers);


    $cfg_sendmails = array('sendmail_path', 'sendmail_args');
    $cfg_smtps = array('host', 'port', 'auth', 'username', 'password', 'localhost',
                       'timeout', 'persist');

    $cfgprefix = $config->mail_backend == 'smtp' ? 'mail_smtp_' : 'mail_';
    $cfgopts = $config->mail_backend == 'smtp' ? $cfg_smtps : $cfg_sendmails;

    foreach ($cfgopts as $opt) {
        $cfgopt = $cfgprefix . $opt;
        if (!empty($config->$cfgopt))
            $mail_params[$opt] = $config->$cfgopt;
    }

    // DEVELOPMENT mode
    if ($config->mode == 'development') {
        // Always send mail to support address
        $recipients = $config->support_email;

        // Enable SMTP debug mode
        // if ($config->mail_backend == 'smtp') {
        //   $mail_params['debug'] = true;
        // }
    }

    // Send mail
    $mail = &Mail::factory($config->mail_backend, $mail_params);
    return $mail->send($recipients, $headers, $content);
}

?>
