<?php

use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    private $baseUrl = 'http://localhost:8000/api';

    public function testGetProducts()
    {
        $ch = curl_init($this->baseUrl . '/products.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);

        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('products', $data);
        $this->assertNotEmpty($data['products']);

        $firstProduct = $data['products'][0];
        $this->assertArrayHasKey('id', $firstProduct);
        $this->assertArrayHasKey('name', $firstProduct);
        $this->assertArrayHasKey('price', $firstProduct);
    }
}
