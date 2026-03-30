<?php

// Public Books API endpoints will work with a blank API key
// Or create an API key in the Google Cloud Console
// https://console.cloud.google.com/

$key = 'AIzaSyDGnbO9SfFZ9ZjUDSVTvilzKBvxErrda6Q';

?>
<!doctype html>
<html>
    <head>
        <title>Google Books API</title>
    </head>
    <body>

        <h1>Google Books API</h1>

        <form action="" method="post">
            Search <input type="text" name="search"><br>
            <input type="submit" value="Submit">
        </form>


        <h2>Search for a Book</h2>

        <!-- <?php

        // Define the endpoint to search the Books API using a keyword
            $search = '';
            // Check if the form was submitted via POST
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $search = htmlspecialchars($_POST['search']);
            }
            print_r($search); 
        

        $url = 'https://www.googleapis.com/books/v1/volumes?q='.$search.'&&maxResults=10&key='.$key; // what is this searching? allow it to seach with multiple words and based on differnt attributes

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);

        if(!isset($data['error']))
        {
            // make title a link to book_details.php
            foreach($data['items'] as $value)
            {
                echo '<div>';

                echo '<h3>'.$value['volumeInfo']['title'].'</h3>';

                if(isset($value['volumeInfo']['industryIdentifiers'][0]['identifier'])) {
                    echo '<p>'.$value['volumeInfo']['industryIdentifiers'][0]['identifier'].'</p>';
                }

                if(isset($value['volumeInfo']['imageLinks']['smallThumbnail'])) {
                    echo '<img src="'.$value['volumeInfo']['imageLinks']['smallThumbnail'].'">';
                }

                echo '</div>';
            }
        }

        echo '<hr>';

        echo '<pre>';
        print_r($data);
        echo '</pre>';

        ?>-->
        <?php
            $search = '';

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $search = htmlspecialchars($_POST['search']);
            }

            if($search !== '') {

                $encodedSearch = urlencode($search);

                $url = "https://www.googleapis.com/books/v1/volumes?q={$encodedSearch}&maxResults=10&key={$key}";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch);
                curl_close($ch);

                $data = json_decode($result, true);

                if(isset($data['items'])) {
                    foreach($data['items'] as $value) {

                        echo '<div>';

                        $id = $value['id'];

                        echo '<h3>
                            <a href="book_details.php?id='.$id.'">
                                '.$value['volumeInfo']['title'].'
                            </a>
                        </h3>';

                        if(isset($value['volumeInfo']['imageLinks']['smallThumbnail'])) {
                            echo '<img src="'.$value['volumeInfo']['imageLinks']['smallThumbnail'].'">';
                        }

                        echo '</div>';
                    }
                }
            }
        ?>
        <hr>

    </body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search | BookTracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="dashboard.html">BookTracker</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="dashboard.html">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="browse.html">Search</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mybooks.html">My Books</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="account.html">Account</a>
          </li>
        </ul>

        <a href="login.html" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Search Page Content -->
  <div class="container py-5">
    <div class="mb-4">
      <h2 class="section-title">Search Books</h2>
      <p class="section-subtitle">Find books by title, author, or ISBN.</p>
    </div>

    <!-- Search Bar -->
    <div class="card p-3 mb-5">
      <form action="browse.html" method="GET" class="d-flex">
        <input class="form-control me-2" type="search" name="search" placeholder="Search for books by title, author, or ISBN">
        <button class="btn btn-primary" type="submit">Search</button>
      </form>
    </div>

    <!-- Search Results Title -->
    <div class="mb-4">
      <h3 class="section-title">Search Results</h3>
    </div>

    <!-- Book Cards -->
    <div class="row g-4">
      <div class="col-md-3">
        <div class="card h-100">
          <div class="book-placeholder">Book Cover</div>
          <div class="card-body">
            <h6 class="card-title">I Know the ants</h6>
            <p class="card-text">Lang Leav</p>
            <p class="card-text small text-muted">destails</p>
            <a href="book.html" class="btn btn-primary btn-sm">View Details</a>
          </div>
        </div>
      </div>


  <!-- Add more book card later -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
