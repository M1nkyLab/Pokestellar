<?php
session_start();
include 'db_connect.php';

// Simulate admin login for testing (Replace with real authentication)
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle card and order operations (Edit, Delete, Add)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

    // Handle card updates (Edit, Delete, Add)
    if (isset($_POST['edit_card'])) {
        $card_id = $_POST['card_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $rarity = $_POST['rarity'];
    
        // Update the card in database
        $update_card_sql = "UPDATE pokestellar_cards SET 
                            name = ?, 
                            description = ?, 
                            price = ?, 
                            stock = ?, 
                            rarity = ?
                            WHERE id = ?";
    
        $stmt = $conn->prepare($update_card_sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            echo "<script>alert('Database error'); window.location.href='admin.php';</script>";
            exit;
        }
    
        $stmt->bind_param('ssdisi', $name, $description, $price, $stock, $rarity, $card_id);
    
        if ($stmt->execute()) {
            echo "<script>alert('Card updated successfully!'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Error updating card: " . addslashes($stmt->error) . "'); window.location.href='admin.php';</script>";
        }
    }

    // Handle card deletion
    if (isset($_POST['delete_card'])) {
        $card_id = $_POST['card_id'];
        
        // Delete the card from database
        $stmt = $conn->prepare("DELETE FROM pokestellar_cards WHERE id=?");
        $stmt->bind_param("i", $card_id);

        if ($stmt->execute()) {
            echo "<script>alert('Card deleted successfully!'); window.location.href='admin.php';</script>";
        } else {
            error_log("Error deleting card: " . $stmt->error);
            echo "<script>alert('Error deleting card: " . $stmt->error . "'); window.location.href='admin.php';</script>";
        }
    }

    // Handle card addition
    if (isset($_POST['add_card'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $rarity = $_POST['rarity'];
    
        // Insert card details into database
        $insert_card_sql = "INSERT INTO pokestellar_cards (name, description, price, stock, rarity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_card_sql);
        $stmt->bind_param('ssdis', $name, $description, $price, $stock, $rarity);

        if ($stmt->execute()) {
            echo "<script>alert('Card added successfully!'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Error adding card: " . $stmt->error . "'); window.location.href='admin.php';</script>";
        }
    }

    // Handle order updates (Edit, Remove)
    if (isset($_POST['edit_order'])) {
        $order_id = $_POST['order_id'];
        $customer_name = $_POST['customer_name'];
        $order_date = $_POST['order_date'];
        $status = $_POST['status'];

        if (empty($customer_name) || empty($order_date)) {
            echo "<script>alert('Please fill in all fields.'); window.location.href='admin.php';</script>";
        } else {
            $update_sql = "UPDATE pokestellar_orders SET customer_name = ?, order_date = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('sssi', $customer_name, $order_date, $status, $order_id);

            if ($stmt->execute()) {
                echo "<script>alert('Order updated successfully!'); window.location.href='admin.php';</script>";
            } else {
                echo "<script>alert('Error updating order: " . $stmt->error . "'); window.location.href='admin.php';</script>";
            }
        }
    }

    if (isset($_POST['delete_order'])) {
        $order_id = $_POST['order_id'];
        $stmt = $conn->prepare("DELETE FROM pokestellar_orders WHERE id=?");
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            echo "<script>alert('Order deleted successfully!'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Error deleting order: " . $stmt->error . "'); window.location.href='admin.php';</script>";
        }
    }

    // Add Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_admin'])) {
    $username = $_POST['email'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert_admin_sql = "INSERT INTO pokestellar_admin (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_admin_sql);
    $stmt->bind_param('ss', $username, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Admin added successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Error adding admin: " . $stmt->error . "'); window.location.href='admin.php';</script>";
    }
    $stmt->close();
}

// Delete Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_admin'])) {
    // Check CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $admin_id = intval($_POST['admin_id']);

    $delete_admin_sql = "DELETE FROM pokestellar_admin WHERE id = ?";
    $stmt = $conn->prepare($delete_admin_sql);
    $stmt->bind_param('i', $admin_id);

    if ($stmt->execute()) {
        echo "<script>alert('Admin deleted successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Error deleting admin: " . $stmt->error . "'); window.location.href='admin.php';</script>";
    }
    $stmt->close();
}
}

// Fetch All Admins
$admin_result = $conn->query("SELECT * FROM pokestellar_admin");

// Fetch all cards from the database
$sql = "SELECT * FROM pokestellar_cards ORDER BY id DESC";
$result = $conn->query($sql);

// Fetch all orders from the database
$order_sql = "SELECT * FROM pokestellar_orders ORDER BY order_date DESC";
$order_result = $conn->query($order_sql);

// Fetch all admins from the database
$admin_sql = "SELECT id, email, password FROM pokestellar_admin ORDER BY id DESC";
$admin_result = $conn->query($admin_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PokéStellar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans leading-relaxed">

<main class="w-full min-h-screen px-8 py-12">

    <!-- Navigation Section with Logout Button -->
<section class="flex justify-between items-center border-b-2 border-gray-300 py-4 w-full">
    <h1 class="text-4xl font-semibold text-gray-900">Welcome, Admin! 🎉</h1>
    <a href="login.php" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-all text-lg font-medium">🚪 Logout</a>
</section>


    <!-- Add Card Section -->
    <section class=" bg-white p-8 rounded-lg shadow-lg w-full min-h-screen">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">🆕 Add New Card</h2>
        <form method="POST" class="grid gap-6 max-w-4xl mx-auto">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="text" name="name" placeholder="Card Name" class="p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" required>
            <textarea name="description" placeholder="Card Description" rows="3" class="p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" required></textarea>
            <input type="number" name="price" placeholder="Price" step="0.01" min="0" class="p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" required>
            <input type="number" name="stock" placeholder="Stock" min="0" class="p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" required>
            <input type="text" name="rarity" placeholder="Rarity" class="p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" required>
            
            <button type="submit" name="add_card" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all text-lg font-medium">Add Card</button>
        </form>
    </section>

    <!-- Manage Cards -->
    <section id="cards" class="mb-8 w-full min-h-screen">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">📋 Manage Cards</h2>
        <div class="bg-white p-8 rounded-lg shadow-lg overflow-x-auto w-full">
            <table class="w-full table-auto border-collapse border border-gray-300 rounded-lg">
                <thead class="bg-gradient-to-r from-red-500 to-red-600 text-white">
                    <tr class="text-center">
                        <th class="p-6 text-lg">Card ID</th>
                        <th class="p-6 text-lg">Name</th>
                        <th class="p-6 text-lg">Description</th>
                        <th class="p-6 text-lg">Price</th>
                        <th class="p-6 text-lg">Stock</th>
                        <th class="p-6 text-lg">Rarity</th>
                        <th class="p-6 text-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="text-center bg-gray-50 hover:bg-gray-100 transition-all ease-in-out duration-200">
                        <form method="POST">
                            <input type="hidden" name="card_id" value="<?= $row['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <td class="p-6 border-b"><?= $row['id']; ?></td>
                            <td class="p-6 border-b">
                                <input type="text" name="name" value="<?= htmlspecialchars($row['name']); ?>" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            </td>
                            <td class="p-6 border-b">
                                <textarea name="description" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"><?= htmlspecialchars($row['description']); ?></textarea>
                            </td>
                            <td class="p-6 border-b">
                                <input type="number" name="price" value="<?= number_format($row['price'], 2); ?>" step="0.01" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            </td>
                            <td class="p-6 border-b">
                                <input type="number" name="stock" value="<?= $row['stock']; ?>" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            </td>
                            <td class="p-6 border-b">
                                <input type="text" name="rarity" value="<?= htmlspecialchars($row['rarity']); ?>" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            </td>
                            <td class="p-6 border-b">
                                <button type="submit" name="edit_card" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-all text-lg font-medium">Edit</button>
                                <button type="submit" name="delete_card" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all text-lg font-medium">Delete</button>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Manage Orders -->
    <section id="orders" class="mb-8 w-full min-h-screen">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">📦 Manage Orders</h2>
        <div class="bg-white p-8 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full table-auto border-collapse border border-gray-300 rounded-lg">
                <thead class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                    <tr class="text-center">
                        <th class="p-6 text-lg">Order ID</th>
                        <th class="p-6 text-lg">Customer Name</th>
                        <th class="p-6 text-lg">Order Date</th>
                        <th class="p-6 text-lg">Status</th>
                        <th class="p-6 text-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $order_result->fetch_assoc()): ?>
                    <tr class="text-center bg-gray-50 hover:bg-gray-100 transition-all ease-in-out duration-200">
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <td class="p-6 border-b"><?= $order['id']; ?></td>
                            <td class="p-6 border-b">
                                <input type="text" name="customer_name" value="<?= htmlspecialchars($order['customer_name']); ?>" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            </td>
                            <td class="p-6 border-b">
                                <input type="text" name="order_date" value="<?= $order['order_date']; ?>" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                            </td>
                            <td class="p-6 border-b">
                                <select name="status" class="p-3 border rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                </select>
                            </td>
                            <td class="p-6 border-b">
                                <button type="submit" name="edit_order" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-all text-lg font-medium">Edit</button>
                                <button type="submit" name="delete_order" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all text-lg font-medium">Delete</button>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="admins" class="mb-8 w-full min-h-screen">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">👥 Manage Admins</h2>
        <div class="bg-white p-8 rounded-lg shadow-lg overflow-x-auto">
            <!-- Add New Admin Form -->
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Add New Admin</h3>
            <form method="POST" class="grid gap-6 max-w-lg mx-auto">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <input type="email" name="email" placeholder="Admin Email" class="p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" required>
                <input type="password" name="password" placeholder="Password" class="p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" required>
                <button type="submit" name="add_admin" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all text-lg font-medium">Add Admin</button>
            </form>

            <hr class="my-8">

            <!-- Display All Admins -->
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Existing Admins</h3>
            <table class="w-full table-auto border-collapse border border-gray-300 rounded-lg">
                <thead class="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                    <tr class="text-center">
                        <th class="p-6 text-lg">Admin ID</th>
                        <th class="p-6 text-lg">Email</th>
                        <th class="p-6 text-lg">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($admin = $admin_result->fetch_assoc()): ?>
                    <tr class="text-center bg-gray-50 hover:bg-gray-100 transition-all ease-in-out duration-200">
                        <td class="p-6 border-b"><?= $admin['id']; ?></td>
                        <td class="p-6 border-b"><?= htmlspecialchars($admin['email']); ?></td>
                        <td class="p-6 border-b">
                            <!-- Delete Admin Button -->
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="admin_id" value="<?= $admin['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                <button type="submit" name="delete_admin" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>
</body>
</html>

