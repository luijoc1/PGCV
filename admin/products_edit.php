<?php
include 'includes/session.php';
requireValidCSRFRequest();
include 'includes/slugify.php';
require_once __DIR__ . '/../includes/product_inventory.php';

if (isset($_POST['edit'])) {
	$id = $_POST['id'];
	$name = $_POST['name'];
	$slug = slugify($name);
	$category = $_POST['category'];
	try {
		$inventory = productInventoryInput($_POST);
	} catch (InvalidArgumentException $e) {
		$_SESSION['error'] = $e->getMessage();
		header('location: products.php');
		exit();
	}
	$price = $inventory['price'];
	$stock = $inventory['stock'];
	$stock_minimo = $inventory['stock_minimo'];
	$descuento = $inventory['descuento'];
	$description = safeProductDescription($_POST['description'] ?? '');

	$conn = $pdo->open();

	try {
		// Obtener datos anteriores para el log
		$stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
		$stmt->execute(['id' => $id]);
		$anterior = $stmt->fetch();

		// Actualizar producto
		updateCatalogProduct($conn, ['name' => $name, 'slug' => $slug, 'category' => $category, 'price' => $price, 'stock' => $stock, 'stock_minimo' => $stock_minimo, 'description' => $description, 'descuento' => $descuento, 'id' => $id]);

		// Log producto editado
		registrarLog($conn, 'logs_productos', $id, [
			'name'        => $anterior['name'],
			'category_id' => $anterior['category_id'],
			'price'       => $anterior['price'],
			'stock'       => $anterior['stock'],
			'stock_minimo' => $anterior['stock_minimo'],
			'descuento'   => $anterior['descuento']
		], [
			'name'        => $name,
			'category_id' => $category,
			'price'       => $price,
			'stock'       => $stock,
			'stock_minimo' => $stock_minimo,
			'descuento'   => $descuento
		], 'UPDATE', $admin['email'] ?? 'admin');

		$_SESSION['success'] = 'Producto actualizado con éxito';
	} catch (PDOException $e) {
		$_SESSION['error'] = $e->getMessage();
	}

	$pdo->close();
} else {
	$_SESSION['error'] = 'Rellene el formulario de edición del producto primero';
}

header('location: products.php');
