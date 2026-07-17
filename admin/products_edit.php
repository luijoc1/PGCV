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
		$stmt = $conn->prepare("UPDATE products SET name=:name, slug=:slug, category_id=:category, price=:price, stock=:stock, description=:description, descuento=:descuento WHERE id=:id");
		$stmt->execute(['name' => $name, 'slug' => $slug, 'category' => $category, 'price' => $price, 'stock' => $stock, 'description' => $description, 'descuento' => $descuento, 'id' => $id]);
		$_SESSION['success'] = 'Producto actualizado con éxito';
	} catch (PDOException $e) {
		$_SESSION['error'] = $e->getMessage();
	}

	$pdo->close();
} else {
	$_SESSION['error'] = 'Rellene el formulario de edición del producto primero';
}

header('location: products.php');
