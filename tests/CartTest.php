<?php

declare(strict_types=1);

require_once __DIR__ . '/DatabaseTestCase.php';

final class CartTest extends DatabaseTestCase
{
    private int $testUserId = 1;
    private int $testProductId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar datos de prueba
        $stmt = $this->conn->prepare(
            "DELETE FROM cart
             WHERE user_id = :user
             AND product_id = :product"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);
    }

    public function test_can_add_product_to_cart(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO cart
             (user_id, product_id, quantity)
             VALUES
             (:user, :product, :qty)"
        );

        $result = $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId,
            'qty' => 1
        ]);

        $this->assertTrue($result);

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM cart
             WHERE user_id = :user
             AND product_id = :product"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);

        $row = $stmt->fetch();

        $this->assertNotFalse($row);
    }

    public function test_can_update_quantity(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO cart
             (user_id, product_id, quantity)
             VALUES
             (:user, :product, 1)"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);

        $stmt = $this->conn->prepare(
            "UPDATE cart
             SET quantity = 5
             WHERE user_id = :user
             AND product_id = :product"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);

        $stmt = $this->conn->prepare(
            "SELECT quantity
             FROM cart
             WHERE user_id = :user
             AND product_id = :product"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);

        $row = $stmt->fetch();

        $this->assertEquals(
            5,
            (int)$row['quantity']
        );
    }

    public function test_can_delete_product_from_cart(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO cart
             (user_id, product_id, quantity)
             VALUES
             (:user, :product, 1)"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);

        $stmt = $this->conn->prepare(
            "DELETE FROM cart
             WHERE user_id = :user
             AND product_id = :product"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) total
             FROM cart
             WHERE user_id = :user
             AND product_id = :product"
        );

        $stmt->execute([
            'user' => $this->testUserId,
            'product' => $this->testProductId
        ]);

        $row = $stmt->fetch();

        $this->assertEquals(
            0,
            (int)$row['total']
        );
    }
}