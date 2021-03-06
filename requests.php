<?php
	require_once 'db.php';
	$requestManager = new RequestManager($mysqli);
	session_start();
?>

<?php
	// When cancel button is clicked and the reqestId is sent back
	if (isset($_POST['requestId'])) {
		$requestManager -> deleteRequest($_POST['requestId']);
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>The Reel World</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="fav.ico">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
  <link href='https://fonts.googleapis.com/css?family=Alegreya' rel='stylesheet'>

	<style>
		#message {
			text-align: center;
			margin:0 auto;
			margin-top:20px;
			color: white;
		}

		.requestCard {
			margin: 20px;
		}

	</style>
	<script>
		$(document).ready (function() {
			$('.cancelButton').on('click', function() {
				let requestId = $(this).attr('requestId');
				$.ajax({
					url: './requests.php',
					type: 'POST',
					data: {
						requestId: requestId
					},
					success: function(data) {
						console.log(data);
						window.alert("Request #" + requestId + " is successfully cancelled");
						location.reload();
					}
				});
			});
		});
	</script>
</head>
	<body style="font-family:Alegreya;background-color:#1e272e;">
		<nav class="navbar navbar-expand-md navbar-dark bg-dark">
		  <div class="navbar-header">
		    <a class="navbar-brand" href="index.php">THE REEL WORLD</a>
		  </div>
		  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		    <span class="navbar-toggler-icon"></span>
		  </button>

		  <div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav mr-auto">
				  <li class="nav-item ">
				    <a class="nav-link navTab" href="index.php">Home<span class="sr-only">(current)</span></a>
				  </li>
				  <li class="nav-item active">
				    <a class="nav-link navTab" href="requests.php">My Requests</a>
				  </li>
				  <?php
				    if ($_SESSION['loggedIn'] && $_SESSION['role'] == 'Admin') {
				      $item = '
				      <li class="nav-item">
				        <a class="nav-link navTab" href="pendingRequests.php">Pending Requests</a>
				      </li>';

				      print $item;
				    }
				  ?>
				</ul>
				<div class="text-right">
					<button class="btn btn-warning" type="button" style="margin:5px; "><a href="addrequest.php" style="text-decoration: none;
					color:black;">Add A Movie</a></button>
				</div>
				<ul class="navbar-nav">
						<li class="nav-item">
								<a class="nav-link" href="./signup.php"><span class="glyphicon glyphicon-user"></span>SIGN UP</a>
						</li>
						<li class="nav-item">
								<a class="nav-link" data-toggle="modal" data-target="#loginModal"><span class="glyphicon glyphicon-log-in"></span>LOGIN</a>
						</li>
				</ul>
		  </div>
		</nav>

		<div class=".container">
			<?php
				if (!isset($_SESSION['loggedIn'])) {
					print '<div id="message">Please <a href="" data-toggle="modal" data-target="#loginModal">log in</a> first.</div>';
				} else {
					$content = '';
					$statement = $requestManager -> getRequests($_SESSION['userId']); //Gets all requests belonging to this user
					$result = $statement -> get_result();

					while ($row = $result -> fetch_assoc()) { // Populate the page with this user's requests
						$requestDescription = $row['description'];
						if ($requestDescription != "") {
							$info = json_decode($requestDescription, true); // Request description is encoded and thus needs to be decoded

							$content = $content.'
							<div class="card mb-3 requestCard">
								<div class="row no-gutters">
									<div class="col-md-2">
										<img src="'.$info['imageLink'].'" class="card-img" alt="'.$info['movieTitle'].'">
									</div>
									<div class="col-md-10">
										<div class="card-body">
											<h5 class="card-title"><b>'.$info['movieTitle'].'</b></h5>
											<p class="card-text">Genres: '.implode(", ", $info['genreToDisplay']).'</p>
											<p class="card-text">Actors: '.implode(", ", $info['actorToDisplay']).'</p>
											<p class="card-text">Description: '.$info['movieDescription'].'</p>
											<p class="card-text">Rating: '.$info['movieRating'].' /10</p>';
											if (isset($info['requestComment'])) {
												$content = $content.'<p class="card-text"><small class="text-muted">Comments: '.$info['requestComment'].'</small></p>';
											}

											$content = $content.'
											<p class="card-text"><small class="text-muted">'.$row['status'].' on '.$row['requestDate'].'</small></p>';

											if ($row['status'] === 'Submitted') {
												$content .= '
												<div class="text-right">
													<button class="btn btn-secondary cancelButton" requestId="'.$row['requestId'].'">Cancel</button>
												</div>
												';
											} 
											$content .= '
										</div>
									</div>
								</div>
							</div>
							';
						} else {
							$content = $content.'
							<div class="card mb-3 requestCard">
								<div class="row no-gutters">
									<div class="col-md-12">
										<div class="card-body">
											<h5 class="card-title">'.$row['requestName'].'</h5>
											<p class="card-text">Request Description: '.$row['description'].'</p>
											<p class="card-text"><small class="text-muted">'.$row['status'].' on '.$row['requestDate'].'</small></p>
											<div class="text-right">
												<button class="btn btn-secondary cancelButton" requestId="'.$row['requestId'].'">Cancel</button>
											</div>
										</div>
									</div>
								</div>
							</div>
							';
						}
					}
					print $content;
				}
			?>
			<?php include 'login.php' ?>
		</div>
	</body>
<html>
