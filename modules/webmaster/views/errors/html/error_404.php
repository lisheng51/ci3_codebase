<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>404 Page Not Found</title>
	<?php
	echo F_asset::favicon();
	echo F_asset::bootstrap();
	?>
</head>

<body>
	<div class="d-flex align-items-center justify-content-center vh-100">
		<div class="text-center">
			<h1 class="display-1 fw-bold">404</h1>
			<p class="fs-3"> <span class="text-danger">Opps!</span> Page not found.</p>
			<p class="lead">
				<?= $message; ?>
			</p>
			<a href="<?= site_url() ?>" class="btn btn-primary">Go Home</a>
		</div>
	</div>
</body>

</html>