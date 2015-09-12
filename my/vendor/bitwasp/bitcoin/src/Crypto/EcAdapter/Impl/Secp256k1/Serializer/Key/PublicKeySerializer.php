<?php

namespace BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Serializer\Key;

use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Adapter\EcAdapter;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\Secp256k1\Key\PublicKey;
use BitWasp\Bitcoin\Crypto\EcAdapter\Serializer\Key\PublicKeySerializerInterface;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PublicKeyInterface;
use BitWasp\Buffertools\Parser;

class PublicKeySerializer implements PublicKeySerializerInterface
{
    /**
     * @var EcAdapter
     */
    private $ecAdapter;

    /**
     * @param EcAdapter $ecAdapter
     */
    public function __construct(EcAdapter $ecAdapter)
    {
        $this->ecAdapter = $ecAdapter;
    }

    /**
     * @param PublicKey $publicKey
     * @return Buffer
     */
    private function doSerialize(PublicKey $publicKey)
    {
        $serialized = '';
        if (!secp256k1_ec_pubkey_serialize(
            $this->ecAdapter->getContext(),
            $publicKey->getResource(),
            $publicKey->isCompressed(),
            $serialized
        )) {
            throw new \RuntimeException('Secp256k1: Failed to serialize public key');
        }

        return new Buffer(
            $serialized,
            $publicKey->isCompressed()
            ? PublicKey::LENGTH_COMPRESSED
            : PublicKey::LENGTH_UNCOMPRESSED,
            $this->ecAdapter->getMath()
        );
    }

    /**
     * @param PublicKeyInterface $publicKey
     * @return Buffer
     */
    public function serialize(PublicKeyInterface $publicKey)
    {
        /** @var PublicKey $publicKey */
        return $this->doSerialize($publicKey);
    }

    /**
     * @param $data
     * @return PublicKey
     */
    public function parse($data)
    {
        $buffer = (new Parser($data))->getBuffer();
        $binary = $buffer->getBinary();
        $pubkey_t = '';
        /** @var resource $pubkey_t */
        if (!secp256k1_ec_pubkey_parse($this->ecAdapter->getContext(), $binary, $pubkey_t)) {
            throw new \RuntimeException('Secp256k1 failed to parse public key');
        }

        return new PublicKey(
            $this->ecAdapter,
            $pubkey_t,
            $buffer->getSize() === 33
        );
    }
}
