<?php
include 'includes/session.php';
requireValidCSRFRequest();
include 'includes/slugify.php';
require_once __DIR__ . '/../includes/image_upload.php';
require_once __DIR__ . '/../includes/product_inventory.php';

unset($_SESSION['error'], $_SESSION['success']);
if (isset($_POST['add'])) {
	try {
		$identity = productCatalogIdentity($_POST);
		$name = $identity['name'];
		$category = $identity['category'];
		$slug = slugify($name);
		if ($slug === '' || strlen($slug) > 200) throw new InvalidArgumentException('Ingresa un nombre de producto válido.');
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
		requireProductCategory($conn, $category);
	} catch (InvalidArgumentException $e) {
		$_SESSION['error'] = $e->getMessage();
		$pdo->close();
		header('location: products.php');
		exit();
	}

	$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM products WHERE slug=:slug");
	$stmt->execute(['slug' => $slug]);
	$row = $stmt->fetch();

	if ($row['numrows'] > 0) {
		$_SESSION['error'] = 'Producto ya existe';
	} else {
		$new_filename = photoUploadOrRedirect($_FILES['photo'] ?? null, '', 'products.php');

		try {
			$conn->beginTransaction();
			$product_id = insertCatalogProduct($conn, ['category' => $category, 'name' => $name, 'description' => $description, 'slug' => $slug, 'price' => $price, 'stock' => $stock, 'stock_minimo' => $stock_minimo, 'photo' => $new_filename, 'descuento' => $descuento]);

			// Log producto agregado
			registrarLog($conn, 'logs_productos', $product_id, null, [
				'name'        => $name,
				'category_id' => $category,
				'price'       => $price,
				'stock'       => $stock,
				'stock_minimo' => $stock_minimo,
				'descuento'   => $descuento,
				'slug'        => $slug
			], 'INSERT', $admin['email'] ?? 'admin');

			$conn->commit();
			$_SESSION['success'] = 'Producto agregado exitosamente';
		} catch (PDOException $e) {
			if ($conn->inTransaction()) $conn->rollBack();
			removeNewImageUpload($new_filename);
			$_SESSION['error'] = $e->getMessage();
		}
	}

	$pdo->close();
} else {
	$_SESSION['error'] = 'Rellene el formulario del producto primero';
}

header('location: products.php');
