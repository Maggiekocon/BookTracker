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


