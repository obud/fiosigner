<?php

declare(strict_types=1);

namespace Obud\FioSigner\Connection;

use Obud\FioSigner\Api\Request;


class ConnectionService
{
    /**
     * @var resource
     */
    private $socket;


    public function __construct(
        string $host = 'podepisovac1.fio.cz',
        int $port = 443,
        float $timeout = 5,
    )
    {
        $socket = stream_socket_client(
            'ssl://' . $host . ':' . $port,
            $errorCode,
            $errorMessage,
            $timeout,
        );
        if ($socket === false) {
            throw new ConnectionException($errorMessage, $errorCode);
        }

        $this->socket = $socket;
    }


    public function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }


    public function __destruct()
    {
        $this->disconnect();
    }


    public function send(Request\Base $message): void
    {
        $rawMessage = unpack('C*', (string)$message);
        if ($rawMessage === false) {
            throw new ProtocolException('Protocol Unpack Exception');
        }
        $rawMessageLen = count($rawMessage);

        $bigEndLen = unpack("C*", pack("L", $rawMessageLen));
        if ($bigEndLen === false) {
            throw new ProtocolException('Protocol Unpack Exception');
        }
        $bigEndLen = array_reverse($bigEndLen);

        $out = [2, ...$bigEndLen, ...$rawMessage, 3];
        $out = implode(array_map('chr', $out));

        fwrite($this->socket, $out);
    }


    public function receive(): string
    {
        $intro = fread($this->socket,5);
        if ($intro === false || strlen($intro) !== 5) {
            throw new ProtocolException('Protocol Intro Length Exception');
        }
        $rawIntro = unpack('C*', $intro);
        if ($rawIntro === false || !isset($rawIntro[5]) || $rawIntro[1] !== 2) {
            throw new ProtocolException('Protocol Intro Format Exception');
        }
        $bigEndLen = unpack("L", pack("C*", $rawIntro[5], $rawIntro[4], $rawIntro[3], $rawIntro[2]));
        if ($bigEndLen === false) {
            throw new ProtocolException('Protocol Unpack Exception');
        }
        $bigEndLen = $bigEndLen[1];
        $out = fread($this->socket, $bigEndLen);
        if ($out === false || strlen($out) !== $bigEndLen) {
            throw new ProtocolException('Protocol Content Length Exception');
        }

        $outro = fread($this->socket,1);
        if ($outro === false || strlen($outro) !== 1) {
            throw new ProtocolException('Protocol Outro Length Exception');
        }
        $rawOutro = unpack('C*', $outro);
        if ($rawOutro === false || $rawOutro[1] !== 3) {
            throw new ProtocolException('Protocol Outro Format Exception');
        }

        return $out;
    }

}