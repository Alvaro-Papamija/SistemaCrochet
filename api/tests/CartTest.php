<?php

use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    private $baseUrl = 'http://localhost:8000/api';
    private $cookieFile = '/tmp/cookie.txt';

    protected function tearDown(): void
    {
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }

    public function testGetCartEmpty()
    {
        $ch = curl_init($this->baseUrl . '/cart.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);

        $data = json_decode($response, true);
        $this->assertTrue($data['success']);
        $this->assertEquals(0, $data['cartCount']);
        $this->assertEquals(0, $data['subtotal']);
        $this->assertEmpty($data['items']);
    }

    public function testAddToCart()
    {
        // 1. Initial request to get session cookie
        $ch = curl_init($this->baseUrl . '/cart.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_exec($ch);
        curl_close($ch);

        // 2. Add product to cart
        $postData = json_encode([
            'action' => 'add',
            'productId' => 'ranita-sabana',
            'quantity' => 2
        ]);

        $ch = curl_init($this->baseUrl . '/cart.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $data = json_decode($response, true);

        $this->assertTrue($data['success']);
        $this->assertEquals(2, $data['cartCount']);
        $this->assertArrayHasKey('ranita-sabana', $data['items']);
        $this->assertEquals(2, $data['items']['ranita-sabana']['quantity']);
    }
}
