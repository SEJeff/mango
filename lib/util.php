<?

function is_valid_ssh_pub_key($key, $check_length = True, $return_fingerprint = false) {
    if(empty($key) || substr($key, 0, 4) != "ssh-")
        return false;

    # Split the data
    list($format, $data, $comment) = explode(" ", $key, 3);

    # Format should be DSA or RSA
    if ($format != "ssh-dss" && $format != "ssh-rsa")
        return false;

    # Data should be a base64 encoded string
    $certificate = base64_decode($data);
    if ($certificate == $data)
        return false;

    if ($check_length) {
        # DSA certificate data is exactly 433 bytes (always 1024 bits, comparable to 1536 RSA key, has 305 of other data)
        # RSA has to be >= 277 bytes (2048 bits, 21 bytes of other data)
        #
        # However, old ssh-keugen versions allowed DSA keys with != 1024 bits...
        $cert_length = strlen($certificate);
        if (($format == "ssh-dss" && $cert_length < 433)
            || ($format == "ssh-rsa" && $cert_length < 277))
        {
            # Either invalid, or not enough bits in the public key
            return false;
        }
    }

    # All seems ok
    return $return_fingerprint ? rtrim(chunk_split(md5($certificate), 2, ':'), ':') . ' ' . $comment : true;
}

function array_same($array1, $array2) {
    # Note: assumes no duplicate entries!
    return (count($array1) != count($array2)
            || count(array_diff($array1, $array2)) > 0
            || count(array_diff($array2, $array1)) > 0);

}

?>
