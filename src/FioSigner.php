<?php

declare(strict_types=1);

namespace Obud\FioSigner;

use Obud\FioSigner\Api\Request;
use Obud\FioSigner\Api\Response;
use Obud\FioSigner\Api\ResponseException;
use Obud\FioSigner\Api\AuthenticationException;
use Obud\FioSigner\Connection\ConnectionService;
use Obud\FioSigner\Crypto\CryptoService;


class FioSigner
{
    private string $publicKeyFingerprint;


    public function __construct(
        private string $username,
        private CryptoService $cryptoService,
        private ConnectionService $connectionService,
    )
    {

        $this->publicKeyFingerprint = $this->cryptoService->getPublicKeyFingerprint();

        $helloResponse = new Response\Hello($this->connectionService->receive());
        if ($helloResponse->getVersion() !== '1.0') {
            throw new ResponseException('Invalid Hello response version.');
        }

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->connectionService->send(new Request\Hello($this->username, $this->publicKeyFingerprint));
        $authToken = new Response\AuthToken($this->connectionService->receive());
        $challenge = base64_decode($authToken->getChallenge());

        $sign = $this->cryptoService->sign($challenge);
        $this->connectionService->send(new Request\AuthSignature($sign, $this->publicKeyFingerprint));

        $authResult = new Response\AuthResult($this->connectionService->receive());
        if ($authResult->getStatus() !== 'OK') {
            throw new AuthenticationException($authResult->getStatus());
        }
    }


    /**
     * @return array<int, Response\Order>
     */
    public function getOrders(int $maxItems = 100): array
    {
        $this->connectionService->send(new Request\OrderListRequest($maxItems));
        $orderListResponse = new Response\OrderListResponse($this->connectionService->receive());

        if ($orderListResponse->getAvailableCount() !== count($orderListResponse->getOrders())) {
            throw new ResponseException('The declared number of orders does not match the number sent.');
        }

        return $orderListResponse->getOrders();
    }


    public function sign(Response\Order $order): void
    {
        $this->signProcess($order, 'sign');

    }


    public function discard(Response\Order $order): void
    {
        $this->signProcess($order, 'discard');
    }


    private function signProcess(Response\Order $order, string $action): void
    {
        $sign = null;
        $publicKeyFingerprint = null;
        if ($action === 'sign') {
            $sign = $this->cryptoService->sign($order->getDescription());
            $publicKeyFingerprint = $this->publicKeyFingerprint;
        }
        $this->connectionService->send(new Request\OrderSignRequest($order->getId(), $action, $sign, $publicKeyFingerprint));
        $orderSignResponse = new Response\OrderSignResponse($this->connectionService->receive());
        if ($orderSignResponse->getStatus() !== 'OK') {
            throw new ResponseException($orderSignResponse->getStatus());
        }
        if ($orderSignResponse->getId() !== $order->getId()) {
            throw new ResponseException('The Order ID does not match the ID in the response.');
        }
    }

}