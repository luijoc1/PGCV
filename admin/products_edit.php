<?php
include 'includes/session.php';
include 'includes/slugify.php';

if (isset($_POST['edit'])) {
	$id = $_POST['id'];
	$name = $_POST['name'];
	$slug = slugify($name);
	$category = $_POST['category'];
	$price = $_POST['price'];
	$stock = $_POST['stock'];
	$descuento = isset($_POST['descuento']) ? intval($_POST['descuento']) : 0;
	$description = $_POST['description'];

	$conn = $pdo->open();

	try {
		// Obtener datos anteriores para el log
		$stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
		$stmt->execute(['id' => $id]);
		$anterior = $stmt->fetch();

		// Actualizar producto
		$stmt = $conn->prepare("UPDATE products SET name=:name, slug=:slug, category_id=:category, price=:price, stock=:stock, description=:description, descuento=:descuento WHERE id=:id");
		$stmt->execute(['name' => $name, 'slug' => $slug, 'category' => $category, 'price' => $price, 'stock' => $stock, 'description' => $description, 'descuento' => $descuento, 'id' => $id]);

		// Log producto editado
		registrarLog($conn, 'logs_productos', $id, [
			'name'        => $anterior['name'],
			'category_id' => $anterior['category_id'],
			'price'       => $anterior['price'],
			'stock'       => $anterior['stock'],
			'descuento'   => $anterior['descuento']
		], [
			'name'        => $name,
			'category_id' => $category,
			'price'       => $price,
			'stock'       => $stock,
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
