<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "pokestellar";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = ""; // Initialize error message

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_email = $conn->real_escape_string($_POST['email']);
    $input_password = $_POST['password'];

    // Query to check if the user exists
    $sql = "SELECT * FROM pokestellar_admin WHERE email = '$input_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Assuming password is stored in plain text (not recommended)
        if ($user['password'] == $input_password) {
            header("Location: admin.php");
            exit();
        } else {
            $error_message = "Invalid password!";
        }
    } else {
        $error_message = "No user found with that email!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokestellar Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center mb-6 text-red-600">Pokestellar Admin Login</h2>
        <p class="text-center text-sm mb-4 text-gray-600">Please enter your credentials to access the admin page.</p>
        
        <!-- Display error message -->
        <?php if (!empty($error_message)): ?>
            <div class="mb-4 text-sm text-center text-red-600 bg-red-100 border border-red-400 px-3 py-2 rounded-md">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md focus:ring-red-500 focus:border-red-500" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md focus:ring-red-500 focus:border-red-500" required>
            </div>
            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-md hover:bg-red-700">Login</button>
        </form>

        <!-- Back to Homepage Button -->
        <div class="mt-4 text-center">
            <a href="index.php" class="text-red-600 hover:text-red-700">Back to Homepage</a>
        </div>
    </div>
</body>
</html>
