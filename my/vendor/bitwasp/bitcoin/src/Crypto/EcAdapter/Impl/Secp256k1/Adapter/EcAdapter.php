<?php

namespace BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Adapter;

use BitWasp\Bitcoin\Crypto\EcAdapter\Adapter\EcAdapterInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Key\PublicKey;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Key\PrivateKey;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Signature\CompactSignature;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Signature\Signature;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PublicKeyInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Signature\CompactSignatureInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Signature\SignatureInterface;
use BitWasp\Bitcoin\Crypto\Random\RbgInterface;
use BitWasp\Bitcoin\Math\Math;
use BitWasp\Buffertools\Buffer;
use Mdanter\Ecc\Primitives\GeneratorPoint;

class EcAdapter implements EcAdapterInterface
{
    /**
     * @var Math
     */
    private $math;

    /**
     * @var GeneratorPoint
     */
    private $generator;

    /**
     * @var resource
     */
    private $context;

    /**
     * @param Math $math
     * @param GeneratorPoint $generator
     * @param $secp256k1_context_t
     */
    public function __construct(Math $math, GeneratorPoint $generator, $secp256k1_context_t)
    {
        if (!is_resource($secp256k1_context_t) || !get_resource_type($secp256k1_context_t) == SECP256K1_TYPE_CONTEXT) {
            throw new \InvalidArgumentException('Secp256k1: Must pass a secp256k1_context_t resource');
        }
        $this->math = $math;
        $this->generator = $generator;
        $this->context = $secp256k1_context_t;
    }

    /**
     * @return Math
     */
    public function getMath()
    {
        return $this->math;
    }

    /**
     * @return GeneratorPoint
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * @param Buffer $privateKey
     * @return bool
     */
    public function validatePrivateKey(Buffer $privateKey)
    {
        return (bool) secp256k1_ec_seckey_verify($this->context, $privateKey->getBinary());
    }

    /**
     * @param array $signatures
     * @param Buffer $messageHash
     * @param \BitWasp\Bitcoin\Crypto\EcAdapter\Key\PublicKeyInterface[] $publicKeys
     * @return array
     */
    public function associateSigs(array $signatures, Buffer $messageHash, array $publicKeys)
    {
        $sigCount = count($signatures);
        $linked = [];
        foreach ($signatures as $c => $signature) {
            foreach ($publicKeys as $key) {
                $verify = $this->verify($messageHash, $key, $signature);
                if ($verify) {
                    $linked[$key->getPubKeyHash()->getHex()][] = $signature;
                    if (count($linked) == $sigCount) {
                        break 2;
                    } else {
                        break;
                    }
                }
            }
        }
        return $linked;
    }

    /**
     * @param int|string $element
     * @param bool $half
     * @return bool
     */
    public function validateSignatureElement($element, $half = false)
    {
        $math = $this->getMath();
        $against = $this->getGenerator()->getOrder();
        if ($half) {
            $against = $math->rightShift($against, 1);
        }

        return $math->cmp($element, $against) < 0 && $math->cmp($element, 0) !== 0;
    }

    /**
     * @param int|string $int
     * @param bool|false $compressed
     * @return PrivateKey
     */
    public function getPrivateKey($int, $compressed = false)
    {
        return new PrivateKey($this, $int, $compressed);
    }

    /**
     * @return resource
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Buffer $msg32
     * @param PrivateKey $privateKey
     * @return Signature
     */
    private function doSign(Buffer $msg32, PrivateKey $privateKey)
    {
        /** @var resource $sig_t */
        $sig_t = '';
        if (1 !== secp256k1_ecdsa_sign($this->context, $msg32->getBinary(), $privateKey->getBinary(), $sig_t)) {
            throw new \RuntimeException('Secp256k1: failed to sign');
        }

        $derSig = '';
        secp256k1_ecdsa_signature_serialize_der($this->context, $sig_t, $derSig);

        $rL = ord($derSig[3]);
        $r = (new Buffer(substr($derSig, 4, $rL), $rL, $this->math))->getInt();

        $sL = ord($derSig[4+$rL + 1]);
        $s = (new Buffer(substr($derSig, 4 + $rL + 2, $sL), $rL, $this->math))->getInt();

        return new Signature($this, $r, $s, $sig_t);
    }

    /**
     * @param Buffer $msg32
     * @param PrivateKeyInterface $privateKey
     * @param RbgInterface|null $rbg
     * @return Signature
     */
    public function sign(Buffer $msg32, PrivateKeyInterface $privateKey, RbgInterface $rbg = null)
    {
        /** @var PrivateKey $privateKey */
        return $this->doSign($msg32, $privateKey);
    }

    /**
     * @param Buffer $msg32
     * @param PublicKey $publicKey
     * @param Signature $signature
     * @return bool
     */
    private function doVerify(Buffer $msg32, PublicKey $publicKey, Signature $signature)
    {
        return (bool) secp256k1_ecdsa_verify($this->context, $msg32->getBinary(), $signature->getResource(), $publicKey->getResource());
    }

    /**
     * @param Buffer $msg32
     * @param PublicKeyInterface $publicKey
     * @param SignatureInterface $signature
     * @return bool
     */
    public function verify(Buffer $msg32, PublicKeyInterface $publicKey, SignatureInterface $signature)
    {
        /** @var PublicKey $publicKey */
        /** @var Signature $signature */
        return $this->doVerify($msg32, $publicKey, $signature);
    }

    /**
     * @param Buffer $msg32
     * @param CompactSignature $compactSig
     * @return PublicKey
     */
    private function doRecover(Buffer $msg32, CompactSignature $compactSig)
    {
        $publicKey = '';
        /** @var resource $publicKey */
        $context = $this->context;
        $sig = $compactSig->getResource();
        if (1 != secp256k1_ecdsa_recover($context, $msg32->getBinary(), $sig, $publicKey)) {
            throw new \RuntimeException('Unable to recover Public Key');
        }

        return new PublicKey($this, $publicKey, $compactSig->isCompressed());
    }

    /**
     * @param Buffer $msg32
     * @param CompactSignatureInterface $compactSig
     * @return PublicKey
     */
    public function recover(Buffer $msg32, CompactSignatureInterface $compactSig)
    {
        /** @var CompactSignature $compactSig */
        return $this->doRecover($msg32, $compactSig);
    }

    /**
     * @param Buffer $msg32
     * @param PrivateKey $privateKey
     * @return CompactSignature
     */
    private function doSignCompact(Buffer $msg32, PrivateKey $privateKey)
    {
        $sig_t = '';
        /** @var resource $sig_t */
        if (1 !== secp256k1_ecdsa_sign_recoverable($this->context, $msg32->getBinary(), $privateKey->getBinary(), $sig_t)) {
            throw new \RuntimeException('Secp256k1: failed to sign');
        }

        $recid = '';
        $ser = '';
        if (!secp256k1_ecdsa_recoverable_signature_serialize_compact($this->context, $sig_t, $ser, $recid)) {
            throw new \RuntimeException('Failed to obtain recid');
        }

        unset($ser);
        return new CompactSignature(
            $this,
            $sig_t,
            $recid,
            $privateKey->isCompressed()
        );
    }

    /**
     * @param Buffer $msg32
     * @param PrivateKeyInterface $privateKey
     * @param RbgInterface|null $rbg
     * @return CompactSignatureInterface
     */
    public function signCompact(Buffer $msg32, PrivateKeyInterface $privateKey, RbgInterface $rbg = null)
    {
        /** @var PrivateKey $privateKey */
        return $this->doSignCompact($msg32, $privateKey);
    }
}
