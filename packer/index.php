<?php session_start(); ?>
<!DOCTYPE html>
<html>
	<head>

	</head>
	<body>
		<h1>OpenCart Extension Packager</h1>
		<?php if(isset($_SESSION['oep_result'])) { ?>
		<div class="<?php echo $_SESSION['oep_result']['class'] ?>"><?php echo $_SESSION['oep_result']['message'] ?></div>
		<?php } ?>
		<form action="pack.php" enctype="multipart/form-data" method="post">
			<label>
				Destination
				<input type="text" name="dest" />
			</label>
			<input type="submit" />
		</form>
	</body>
</html>
<?php session_unset() ?>
