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
	<title>profile</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
 	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
	
	<style>
		*{ 
			margin: 0; 
			padding: 0; 
			box-sizing: border-box; 
		}
		body {
			font-family: 'Poppins', sans-serif;
			display: flex;
			min-height: 100vh;
			background-color: #F0F8FF;
			overflow-y: scroll;
			align-items: center;
			justify-items: center; 
		}
				
		#add-form {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-items: center; 
			background-color: #DCDCDC;
			padding: 20px;
			border-radius: 10px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
			width: 100%;
			margin-bottom: 30px;
			display: none;
		}
		

		label {
			display: block;
			
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
				
				margin-top: 10px;
				width: 100%;
				transition: 0.3s ease;
 
		}
		
		input[type=submit]:hover {
			background-color: #4169E1;
				color: #000000; 
		}

		h1{ 
			justify-content: center; 
			align-content: center; 
			margin-bottom: 20px;
		}

		/*table*/ 
		#show-form {
			background-color: #DCDCDC;
			padding: 20px;
			border-radius: 10px;
			max-width: 900px;
			margin: 0 auto;
			display: block;
			align-items: center; 
			justify-content: center; 
			display: none;
			}

			table {
			border-collapse: collapse;
			width: 100%;
			max-width: 600px;
			margin: 0 auto;
			margin-bottom: 20px;
			font-size: 16px;
			}

			thead {background-color: #ddd;}

			th {
			font-weight: bold;
			padding: 10px;
			text-align: center;
			border: 1px solid black;
			background-color: #f2f2f2;
			color: #333;
			}

			td {
			border: 1px solid #ddd;
			padding: 10px;
			text-align: center;
			}

			td:first-child {font-weight: bold;}

			form button {
			background-color: #4CAF50;
			border: none;
			color: white;
			padding: 10px 20px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			margin: 4px 2px;
			cursor: pointer;
			}

			form button:hover {background-color: #3e8e41;}

			
		/*to make it responsive for different screen dimensions */
			@media (max-width: 768px) {
			table {
				font-size: 12px;
				max-width: 100%;
			}
			}

			
		/*to make it responsive for different screen dimensions */
			@media only screen and (max-width: 414px) {
			table {
				font-size: 10px;
				max-width: 100%;
			}

			th, td {padding: 5px;}}



		/* nav bar */
		h3{ color: white;}

		nav {
		height: 100vh;
		background-color: #333;
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		padding: 20px;
		}

		.nav-header a {
		color: #fff;
		font-size: 24px;
		}

		.nav-toggle {
		display: none;
		background: transparent;
		border: none;
		cursor: pointer;
		height: 40px;
		width: 40px;
		display: flex;
		align-items: left;
		justify-content: left;
		position: absolute;
		top: 6%;
		right: 6%;
		}

		.hamburger::before,
		.hamburger::after {
		content: "";
		display: block;
		background-color: #000;
		height: 3px;
		width: 25px;
		border-radius: 3px;
		transition: all 0.2s ease-in-out;
		}

		.hamburger::before { transform: translateY(-8px);}
		.hamburger::after { transform: translateY(5px);}
		.nav-links { display: none;}

		.nav-links.active {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: flex-start;
		}

		.nav-links li { margin: 10px 0; }

		.nav-links a {
		color: #fff;
		font-size: 18px;
		text-transform: uppercase;
		text-decoration: none;
		}

		/*to make it responsive for different screen dimensions */
		@media screen and (min-width: 769px) {
		.nav-toggle {
		cursor: pointer;
		height: 40px;
		width: 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		position: absolute;
		top: 6px;
		right: 6px;
		}
		.nav-header {margin-bottom: 20px;}
		.nav-toggle {display: none; }
		.nav-links {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: flex-start;
			margin-top: 50px;
		}

		.nav-links li { margin: 20px 0;} }

		
		/*to make it responsive for different screen dimensions */
		@media screen and (max-width: 768px) {
		.nav-header {margin-bottom: 10px;}
		.nav-toggle {display: block;}
		.nav-links li {margin: 10px 0;} }

		/* Create the hamburger icon */
		.hamburger {
		display: inline-block;
		width: 20px;
		height: 3px;
		background-color: #333;
		margin: 4px 0;
		transition: transform 0.3s ease-in-out;
		}

		/* fixing the way that hamburger sign looks when in use*/
		.nav-links.active .hamburger:first-child {transform: rotate(45deg) translate(5px, 5px);}
		.nav-links.active .hamburger:last-child {transform: rotate(-45deg) translate(5px, -5px);}
		.nav-links.active .hamburger:nth-child(2) {	opacity: 0; }
		
		
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

		.add-link{
		color: white;
		text-decoration: none;
		margin-top: 10px;
		padding: 5px;
		border-radius: 5px;
		transition: 0.3s ease;
		}

		.container {
		margin-left: 120px;
		padding: 20px;
		
		}
	
		form {
		display: flex;
		flex-direction: column;
		max-width: 400px;
		margin-bottom: 20px;
		}
	
		label, input, select {margin-bottom: 10px;}
	
		.error {
		color: red;
		margin-bottom: 10px;
		}
	
		.success {
		color: green;
		margin-bottom: 10px;
		}
	
	</style>
	</head>
	<body>

	<nav>
    <div class="nav-header">
      <button class="nav-toggle">
        <span class="hamburger"></span>
      </button>
    </div>
    <ul class="nav-links">
	  <h3>Welcome <?php echo $first_name; ?></h3> 
      <li class="add-link" id="add-toggle"><a href="#add-form">Add Family Memebers</a></li>
      <li class="show-link" id="show-toggle"><a href="#show-form">Show Family Memebers</a></li>
      <li><a href="#">pay bills</a></li>
      <li class = "logout" ><a href="logout.php"><a href="#">Logout</a></li>
    </ul>
  </nav>


	<div class="container">
		<form method="post" id ="add-form">
			<h2>Add a family member </h2>
			<br>
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
    		<h2>Family members</h2>

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

//show form table javascript 
// get the table and the background container elements
const table = document.querySelector('table');
const formContainer = document.querySelector('#show-form');

// set the initial min-height of the background container to the table height
formContainer.style.minHeight = table.offsetHeight + 'px';

// update the min-height of the background container whenever the table content changes
const observer = new MutationObserver(() => {
  formContainer.style.minHeight = table.offsetHeight + 'px';
});
observer.observe(table, { childList: true, subtree: true });

// update the min-height of the background container whenever the window is resized
window.addEventListener('resize', () => {
  formContainer.style.minHeight = table.offsetHeight + 'px';
});


// nav bar javascript
const navToggle = document.querySelector(".nav-toggle");
const navLinks = document.querySelector(".nav-links");

navToggle.addEventListener("click", () => {
  navLinks.classList.toggle("active");

  if (navLinks.classList.contains("active")) {
    navToggle.innerHTML = '<span class="hamburger"></span><span class="hamburger"></span><span class="hamburger"></span>';
  } else {
    navToggle.innerHTML = '<span class="hamburger"></span>';
  }
});


</script>
</body>
</html>