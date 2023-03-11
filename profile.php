<?php
session_start();
if (!isset($_SESSION["user_id"])) {
	header("Location: login.php");
	exit();
}

require_once "db_init.php";

// Retrieve the user's information from the database
$stmt = $db->prepare("SELECT username, email, dob FROM users WHERE id = :id");
$stmt->bindValue(":id", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->fetch();

// Extract the first name from the username
$first_name = explode(" ", $user["username"])[0];

if (isset($_POST["add_family_member"])) {
    // Sanitize user input
	$username = htmlspecialchars($_POST["username"], ENT_QUOTES, 'UTF-8');
	$firstname = htmlspecialchars($_POST["firstname"], ENT_QUOTES, 'UTF-8');
	$lastname = htmlspecialchars($_POST["lastname"], ENT_QUOTES, 'UTF-8');
	$number = htmlspecialchars($_POST["number"], ENT_QUOTES, 'UTF-8');
	$dob = htmlspecialchars($_POST["dob"], ENT_QUOTES, 'UTF-8');
	$privilege = htmlspecialchars($_POST["privilege"], ENT_QUOTES, 'UTF-8');

	// Check if the username already exists
	$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
	$stmt->bindValue(":username", $username);
	$stmt->execute();
	$count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $error = "Username already exists";
    } else {
        // Add the family member to the database
        $stmt = $db->prepare("INSERT INTO users (username, firstname, lastname, number, dob, is_sub_user, parent_id) VALUES (:username, :firstname, :lastname, :number, :dob, :is_sub_user, :parent_id)");
        $stmt->bindValue(":username", $username);
        $stmt->bindValue(":firstname", $firstname);
        $stmt->bindValue(":lastname", $lastname);
		$stmt->bindValue(":number", $number);
        $stmt->bindValue(":dob", $dob);
        $stmt->bindValue(":is_sub_user", $privilege == "sub_user" ? 1 : 0);
        $stmt->bindValue(":parent_id", $parent_id);
        $stmt->execute();
        
        $success = "Family member added successfully";
    }
}
// Handle editing a family member
if (isset($_POST["edit_family_member_submit"])) {
    $id = $_POST["id"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $privilege = $_POST["privilege"];
    
    // Update the family member in the database
    $stmt = $db->prepare("UPDATE users SET username = :username, password = :password, is_sub_user = :is_sub_user WHERE id = :id");
    $stmt->bindValue(":id", $id);
    $stmt->bindValue(":username", $username);
    $stmt->bindValue(":password", password_hash($password, PASSWORD_DEFAULT));
    $stmt->bindValue(":is_sub_user", $privilege == "sub_user" ? 1 : 0);
    $stmt->execute();
    
    $success = "Family member edited successfully";
	
}
if (isset($_POST["delete_family_member"])) {
    $id = $_POST["id"];
    // Delete the family member from the database
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindValue(":id", $id);
    $stmt->execute();

    $success = "Family member deleted successfully";
}


// Get the upcoming family birthdays
$family_birthday_stmt = $db->prepare("SELECT firstname, lastname, dob FROM users WHERE DATE(dob) >= DATE('now') AND DATE(dob) <= DATE('now', '+2 day') AND parent_id = :id AND is_sub_user = 1 AND notification_sent = '0'");
$family_birthday_stmt->bindValue(":id", $_SESSION["user_id"]);
$family_birthday_stmt->execute();
$family_birthdays = $family_birthday_stmt->fetchAll();

foreach ($family_birthdays as $family_birthday) {
    $message = "Upcoming birthday: " . $family_birthday["firstname"] . " " . $family_birthday["lastname"] . " on " . $family_birthday["dob"];
    echo "<script>alert('$message');</script>";
    
    // Set notification_sent to 1 to avoid duplicate notifications
    $update_stmt = $db->prepare("UPDATE users SET notification_sent = '1' WHERE firstname = :firstname AND lastname = :lastname AND dob = :dob");
    $update_stmt->bindValue(":firstname", $family_birthday["firstname"]);
    $update_stmt->bindValue(":lastname", $family_birthday["lastname"]);
    $update_stmt->bindValue(":dob", $family_birthday["dob"]);
    $update_stmt->execute();
}


?>

<!DOCTYPE html>
<html>
<head>
	<title>Welcome</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
 	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  
	<style>
		body {
			font-family: 'Poppins', sans-serif;
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
			background-color: #F0F8FF;
			overflow-y: scroll;
		}
		
		.navbar {
			position: fixed;
			top: 0;
			left: 0;
			width: 200px;
			background-color: #333;
			height: 100%;
			color: white;
			padding-top: 20px;
			box-sizing: border-box;
		}
		
		nav {
			display: flex;
			flex-direction: column;
			align-items: center;
			height: 100%;
			padding: 20px;
			box-sizing: border-box;
			font-size: 20px;
			align-items: center;
			text: center; 
		}

		.navbar h3, .navbar p, .navbar a {
			text-align: center;
			color: white;
			text-decoration: none;
			margin-bottom: 10px;
			font-size: 20px;
			}
		

		
		nav a:hover {
			text-decoration: underline;
			color: red; 
		}
		
		
		#add-form {
			display: flex;
			flex-direction: column;
			align-items: center;
			background-color: #DCDCDC;
			padding: 20px;
			border-radius: 10px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
			width: 100%;
			margin-bottom: 30px;
		}
		

		label {
			display: block;
			font-size: 18px;
			font-weight: bold;
			margin-bottom: 10px;
		}
		input[type=text], 
		input[type=password], 
		input[type=number], 
		input[type=date] {
			width: 95%;
			padding: 10px;
			margin-bottom: 20px;
			border-radius: 5px;
			border: none;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
		}

		select {
			width: 100%;
			padding: 10px;
			margin-bottom: 20px;
			border-radius: 5px;
			border: none;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
		}

		input[type=submit] {
			background-color: #000080;
				color: white;
				padding: 12px 20px;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				font-size: 16px;
				margin-top: 10px;
				width: 100%;
				transition: 0.3s ease;
				font-size: 20px; 
		}
		
		input[type=submit]:hover {
			background-color: #4169E1;
				color: #000000; 
		}

		h1{ 
			justify-content: center; 
			align-content: center; 
		}

		/*table*/ 
		#show-form {
			display: flex;
			flex-direction: column;
			align-items: center;
			background-color: #DCDCDC;
			padding: 10px;
			border-radius: 10px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
			width: 7000px;
			margin-bottom: 30px;
			padding-right: 130px;
			
			display: none;
			margin-top: 20px;

			border-radius: 5px;

			max-height: 9999px;
			}

		table {
			align-items: center;
			border-collapse: collapse;
			width: 100%;
			max-width: 600px;
			margin: 0 auto;
			border-collapse: collapse;
			margin-bottom: 20px;
			}

			thead {
			background-color: #ddd;
			}

			th {
			font-weight: bold;
			padding: 10px;
			text-align: left;
			}

			td {
			border: 1px solid #ddd;
			padding: 10px;
			}

			td:first-child {
			font-weight: bold;
			}

			form button {
			background-color: #4CAF50;
			border: none;
			color: white;
			padding: 10px 20px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 16px;
			margin: 4px 2px;
			cursor: pointer;
			}

			form button:hover {
			background-color: #3e8e41;
			}

		
		.success-message, .error-message {
			width: 50%;
			padding: 10px;
			margin-bottom: 20px;
			border-radius: 5px;
			text-align: center;
			font-weight: bold;
		}
		
		.success-message {
			background-color: #D1E7DD;
			color: #333} 

		.show-link{
		color: white;
		text-decoration: none;
		margin-top: 10px;
		padding: 5px;
		border-radius: 5px;
		transition: 0.3s ease;
		}

		.show-link:hover{
			background-color: #555;
			cursor: pointer;
		}

		.add-link{
		color: white;
		text-decoration: none;
		margin-top: 10px;
		padding: 5px;
		border-radius: 5px;
		transition: 0.3s ease;
		}

		.add-link:hover{
			background-color: #555;
			cursor: pointer;

		}
	
		.container {
		margin-left: 120px;
		padding: 20px;
		
		}
	
		h1 {
		margin-bottom: 20px;
		}
	
		form {
		display: flex;
		flex-direction: column;
		max-width: 400px;
		margin-bottom: 20px;
		}
	
		label, input, select {
		margin-bottom: 10px;
		}
	
		.error {
		color: red;
		margin-bottom: 10px;
		}
	
		.success {
		color: green;
		margin-bottom: 10px;
		}
	
		table {
		border-collapse: collapse;
		margin-bottom: 20px;
		}
	
		th, td {
		border: 1px solid black;
		padding: 10px;
		text-align: center;
		}
		#add-form{
			display: none;
		}
		#show-form{
			display: none;
		}
	</style>
	</head>
	<body>
	<nav class="navbar">
		<h3>Welcome <?php echo $first_name; ?></h3>
		<p class="add-link" id="add-toggle">Add Family Members</p>
		<p class="show-link" id="show-toggle">Show Family Members</p>
		<a href="logout.php">Logout</a>
	</nav>
	<div class="container">
		<form method="post" id ="add-form">
			<h1>Add a family member</h1>
			<label for="username">Username:</label>
			<input type="text" id="username" name="username" required>
			<label for="firstname">First name:</label>
			<input type="text" id="firstname" name="firstname" required>
			<label for="lastname">Last name:</label>
			<input type="text" id="lastname" name="lastname" required>
			<label for="number">Phone Number:</label>
			<input type="number" id="number" name="number" required>
			<label for="dob">Date of birth:</label>
			<input type="date" id="dob" name="dob" required>
			<label for="privilege">Privilege:</label>
			<select id="privilege" name="privilege">
				<option value="sub_user">Sub-user</option>
				<option value="normal_user">Normal user</option>
			</select>
			<input type="submit" name="add_family_member" value="Add family member">
		</form>
		<?php if (isset($error)): ?>
			<div class="error"><?php echo $error; ?></div>
		<?php endif; ?>
		<?php if (isset($success)): ?>
			<div class="success"><?php echo $success; ?></div>
		<?php endif; ?>
		<form id="show-form">
    		<h1>Family members</h1>
    		<table>
        		<thead>
            	<tr>
                	<th>Username</th>
                	<th>Name</th>
                	<th>Phone Number</th>
                	<th>Date of birth</th>
                	<th>Privilege</th>
                	<th>Edit</th>
                	<th>Delete</th>
            	</tr>
        		</thead>
        	<tbody>
            	<?php
            	// Retrieve the user's family members from the database
            	$stmt = $db->prepare("SELECT id, username, firstname, lastname, number, dob, is_sub_user FROM users WHERE parent_id = :id");
            	$stmt->bindValue(":id", $_SESSION["user_id"]);
            	$stmt->execute();
            	$family_members = $stmt->fetchAll();
            	foreach ($family_members as $family_member) {
                	$name = $family_member["firstname"] . " " . $family_member["lastname"];
                	$privilege = $family_member["is_sub_user"] ? "Sub user" : "Main user";
            	?>
            	<tr>
                	<td><?php echo $family_member["username"]; ?></td>
                	<td><?php echo $name; ?></td>
                	<td><?php echo $family_member["number"]; ?></td>
                	<td><?php echo $family_member["dob"]; ?></td>
                	<td><?php echo $privilege; ?></td>
                	<td>
                    	<form method="post">
                        	<input type="hidden" name="id" value="<?php echo $family_member['id']; ?>">
                        	<button type="submit" name="edit_family_member_<?php echo $family_member['id']; ?>">Edit</button>
                    	</form>
                	</td>
                	<td>
                    	<form method="post">
                        	<input type="hidden" name="id" value="<?php echo $family_member['id']; ?>">
                        	<button type="submit" name="delete_family_member">Delete</button>
                    	</form>
                	</td>
            	</tr>
            	<?php } ?>
        	</tbody>
    		</table>
		</form>
		<form id="edit-form" method="post" style="display:none;">
    		<input type="hidden" name="id" id="edit-id">
    		<label for="edit-username">Username:</label>
    		<input type="text" name="username" id="edit-username">
    		<label for="edit-password">Password:</label>
    		<input type="password" name="password" id="edit-password">
    		<label for="edit-privilege">Privilege:</label>
    		<select name="privilege" id="edit-privilege">
        		<option value="main_user">Main user</option>
        		<option value="sub_user">Sub user</option>
    		</select>
    		<input type="submit" name="edit_family_member_submit" value="Edit">
		</form>
	</div>
	<script>
	const editForm = document.getElementById('edit-form');
    const editIdInput = document.getElementById('edit-id');
	var addForm = document.getElementById("add-form");
	var addLink = document.getElementById("add-toggle");
	var showForm = document.getElementById("show-form");
	var showLink = document.getElementById("show-toggle");

	const editButtons = document.querySelectorAll('button[name^="edit_family_member_"]');
    editButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            // Get the ID from the button's name attribute
            const id = button.name.replace('edit_family_member_', '');

            // Set the ID in the edit form's ID input field
            editIdInput.value = id;

            // Show the edit form
            editForm.style.display = 'block';

            // Prevent the default form submission
            event.preventDefault();
        });
    });
	addLink.addEventListener("click", function() {
		toggleForm("add");
	});
	showLink.addEventListener("click", function() {
		toggleForm("show");
	});


	function toggleForm(form) {
		if (form == "add") {
			addForm.style.display = "block";
			showForm.style.display = "none";

		} else if (form == "show") {
			addForm.style.display = "none";
			showForm.style.display = "block";

		}
	}
</script>
</body>
</html>