<?

function is_valid_ssh_pub_key($key) {
    if(empty($key) || substr($key, 0, 4) != "ssh-")
        return false;

    # Split the data
    list($format, $data, $comment) = explode(" ", $key, 3);

    # Format should be DSA or RSA
    if ($format != "ssh-dss" && $format != "ssh-rsa")
        return false;

    # Data should be a base64 encoded string
    $certificate = base64_decode($data, true);
    if ($certificate === false)
        return false;

    # DSA certificate data is exactly 433 bytes (always 1024 bits, comparable to 1536 RSA key, has 305 of other data)
    # RSA has to be >= 277 bytes (2048 bits, 21 bytes of other data)
    $cert_length = strlen($certificate);
    if (($format == "ssh-dsa" && $cert_length != 433)
        || ($format == "ssh-rsa" && $cert_length < 277))
    {
        # Either invalid, or not enough bits in the public key
        return false;
    }

    # All seems ok
    return true;
}

?>
