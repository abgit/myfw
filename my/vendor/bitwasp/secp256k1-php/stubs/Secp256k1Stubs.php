<?php

namespace {

    define('SECP256K1_CONTEXT_SIGN', 1 << 0);
    define('SECP256K1_CONTEXT_VERIFY', 1 << 1);
    define('SECP256K1_TYPE_CONTEXT', "secp256k1_context_t");
    define('SECP256K1_TYPE_PUBKEY', "secp256k1_pubkey_t");
    define('SECP256K1_TYPE_SIG', "secp256k1_ecdsa_signature_t");
    define('SECP256K1_TYPE_RECOVERABLE_SIG', "secp256k1_ecdsa_recoverable_signature_t");

    /**
     * Create a Secp256k1 context resource
     *
     * @param $flags - create a VERIFY (or/and) SIGN context
     * @return resource
     */
    function secp256k1_context_create($flags)
    {
    }

    /**
     * Destroy a Secp256k1 context resource
     *
     * @param resource $secp256k1_context_t - context to destroy
     * @return bool
     */
    function secp256k1_context_destroy($secp256k1_context_t)
    {
    }

    /**
     * Clone a Secp256k1 context resource
     *
     * @param resource $secp256k1_context_t - context to clone
     * @return resource
     */
    function secp256k1_context_clone($secp256k1_context_t)
    {
    }

    /**
     * Updates the context randomization (used only internally for blinding)
     *
     * @param resource $secp256k1_context_t
     * @return int
     */
    function secp256k1_context_randomize($secp256k1_context_t)
    {
    }

    /**
     * Serializes a secp256k1_ecdsa_signature_t resource as DER into $signatureOut.
     *
     * @param resource $secp256k1_context_t
     * @param resource $secp256k1_ecdsa_signature_t
     * @param string $signatureOut
     * @return int
     */
    function secp256k1_ecdsa_signature_serialize_der($secp256k1_context_t, $secp256k1_ecdsa_signature_t, &$signatureOut)
    {
    }

    /**
     * Parses a DER signature into a secp256k1_ecdsa_signature_t resource.
     *
     * @param resource $secp256k1_context_t
     * @param string $signatureIn
     * @param resource|string $secp256k1_ecdsa_signature_t
     * @return int
     */
    function secp256k1_ecdsa_signature_parse_der($secp256k1_context_t, $signatureIn, $secp256k1_ecdsa_signature_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $msg32
     * @param resource $secp256k1_ecdsa_signature_t - signature resource
     * @param resource $secp256k1_pubkey_t - the public key resource
     * @return int
     */
    function secp256k1_ecdsa_verify($secp256k1_context_t, $msg32, $secp256k1_ecdsa_signature_t, $secp256k1_pubkey_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $msg32
     * @param string $privateKey
     * @param resource|string $secp256k1_ecdsa_signature_t
     * @return int
     */
    function secp256k1_ecdsa_sign($secp256k1_context_t, $msg32, $privateKey, $secp256k1_ecdsa_signature_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $msg32
     * @param string $privateKey
     * @param resource|string $secp256k1_ecdsa_recoverable_signature_t
     * @return int
     */
    function secp256k1_ecdsa_sign_recoverable($secp256k1_context_t, $msg32, $privateKey, $secp256k1_ecdsa_recoverable_signature_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param resource $secp256k1_ecdsa_recoverable_signature_t
     * @param string $signatureOut
     * @param int $recid
     * @return int
     */
    function secp256k1_ecdsa_recoverable_signature_serialize_compact($secp256k1_context_t, $secp256k1_ecdsa_recoverable_signature_t, $signatureOut, $recid)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $output64
     * @param int $recid
     * @param resource $secp256k1_ecdsa_recoverable_signature_t
     * @return int
     */
    function secp256k1_ecdsa_recoverable_signature_parse_compact($secp256k1_context_t, $input64, $recid, $secp256k1_ecdsa_recoverable_signature_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param resource $secp256k1_ecdsa_recoverable_signature_t
     * @param resource|string $secp256k1_ecdsa_signature_t
     * @return int
     */
    function secp256k1_ecdsa_recoverable_signature_convert($secp256k1_context_t, $secp256k1_ecdsa_recoverable_signature_t, $secp256k1_ecdsa_signature_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $msg32
     * @param resource $secp256k1_ecdsa_recoverable_signature_t
     * @param resource|string $secp256k1_pubkey_t
     * @return int
     */
    function secp256k1_ecdsa_recover($secp256k1_context_t, $msg32, $secp256k1_ecdsa_recoverable_signature_t, $secp256k1_pubkey_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $secretKey
     * @param resource|string $secp256k1_pubkey_t
     * @return int
     */
    function secp256k1_ec_pubkey_create($secp256k1_context_t, $secretKey, $secp256k1_pubkey_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $pubkeyIn
     * @param resource|string $secp256k1_pubkey_t
     * @return int
     */
    function secp256k1_ec_pubkey_parse($secp256k1_context_t, $pubkeyIn, $secp256k1_pubkey_t)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param resource $secp256k1_pubkey_t
     * @param bool $compressed
     * @param string $pubkeyOut
     * @return int
     */
    function secp256k1_ec_pubkey_serialize($secp256k1_context_t, $secp256k1_pubkey_t, $compressed, $pubkeyOut)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $privateKey
     * @param string $tweak
     * @return int
     */
    function secp256k1_ec_privkey_tweak_add($secp256k1_context_t, $privateKey, $tweak)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $privateKey
     * @param string $tweak
     * @return int
     */
    function secp256k1_ec_privkey_tweak_mul($secp256k1_context_t, $privateKey, $tweak)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param resource $secp256k1_pubkey_t
     * @param string $tweak
     * @return int
     */
    function secp256k1_ec_pubkey_tweak_add($secp256k1_context_t, $secp256k1_pubkey_t, $tweak)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param resource $secp256k1_pubkey_t
     * @param string $tweak
     * @return int
     */
    function secp256k1_ec_pubkey_tweak_mul($secp256k1_context_t, $secp256k1_pubkey_t, $tweak)
    {
    }

    /**
     * @param resource $secp256k1_context_t
     * @param string $secKey
     * @return int
     */
    function secp256k1_ec_seckey_verify($secp256k1_context_t, $secKey)
    {
    }
}

