<!--
File Creator: Tathluach Chol

File Description:
    This file supports the front end of the admin song page view. It handles the back end code that gets specific
    values from the database given specific values from the front end. This allows the user to see specific
    song elements and interact with elements on the page.

All Coding Sections: Tathluach Chol
-->

<?php

require_once '../src/config/config.php';

class indSongAdminController {
    private $conn;

    ///////////////////////////////////
    // Database Connection Functions //
    ///////////////////////////////////

    // Connect to database
    public function connect() {
        $this->conn =  mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Disconnect from database
    public function disconnect() {
        $this->conn->close();
    }


    //////////////////////////
    // SQL SELECT Functions //
    //////////////////////////

    // Get information on the current song
    public function getSongInfo() {
        $this->connect();

        // Collects all songs created by artist
        // song_id hard coded for now
        $sql = "SELECT songs.song_title, songs.listens, songs.length, songs.release_date, songs.release_time
            FROM songs
            WHERE songs.song_id = 79";

        $result = mysqli_query($this->conn, $sql);

        // Check if query execution was successful
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }

        // Store songs in array
        $song = array();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $song[] = $row['song_title'];
            $song[] = $row['listens'];
            $song[] = $row['length'];
            $song[] = $row['release_date'];
            $song[] = $row['release_time'];
        }
        $this->disconnect();
        return $song;
    }

    // Get all reviews on the current song
    public function getSongReviews() {
        $this->connect();

        // Collects all songs created by artist
        // song_id hard coded for now
        $sql = "SELECT reviews.review_id, reviews.user_id, users.username, reviews.comment, reviews.rating
                FROM reviews
                JOIN users ON reviews.user_id = users.user_id
                WHERE reviews.song_id = 79";


        $result = mysqli_query($this->conn, $sql);

        // Check if query execution was successful
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }

        // Store songs in array
        $songReviews = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $songReviews[] = $row;
            }
        }
        $this->disconnect();
        return $songReviews;
    }

    // Get flagged reviews on the current song
    public function getFlaggedReviews() {
        $this->connect();

        // Collect reviews with popular curse words in the comments
        $sql = "SELECT reviews.review_id, reviews.user_id, users.username, reviews.comment, reviews.rating
            FROM reviews
            JOIN users ON reviews.user_id = users.user_id
            WHERE reviews.song_id = 79 AND (
                reviews.comment LIKE '%first curse%' OR
                reviews.comment LIKE '%second curse%' OR
                reviews.comment LIKE '%third curse%')";

        $result = mysqli_query($this->conn, $sql);

        // Check if query execution was successful
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }

        // Store songs in array
        $flaggedReviews = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $flaggedReviews[] = $row;
            }
        }
        $this->disconnect();
        return $flaggedReviews;
    }

    // Get all playlists created by the user
    public function getUserPlaylists() {
        $this->connect();

        // Collects all songs created by artist
        // song_id hard coded for now
        $sql = "SELECT playlists.playlist_title
            FROM playlists
            WHERE playlists.user_id = 2";


        $result = mysqli_query($this->conn, $sql);

        // Check if query execution was successful
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }

        // Store songs in array
        $userPlaylists = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $userPlaylists[] = $row;
            }
        }
        $this->disconnect();
        return $userPlaylists;
    }


    //////////////////////////
    // SQL INSERT Functions //
    //////////////////////////

    // Save information for new review
    function saveSongReview($user_id = 3, $song_id ='NULL', $album_id ='NULL', $comment = 'Default comment.', $rating = 5) {

        $this->connect();

        // Get information from page
        $user_id = 3;
        $song_id = 79;

        // Get information from review form
        $comment = $_POST['comment'];
        $rating = $_POST['rating'];

        // Handle empty cases
        if (empty($comment) || empty($rating)) {
            return;
        }

        if ($album_id === 'NULL' || empty($album_id) ) {
            $album_id = NULL;
        }

        // Query for inputting new review
        $reviewInput = mysqli_prepare($this->conn, 'INSERT INTO reviews (user_id, song_id, album_id, comment, rating) VALUES (?,?,?,?,?)');
        mysqli_stmt_bind_param($reviewInput, 'ssiss', $user_id, $song_id, $album_id, $comment, $rating);
        mysqli_stmt_execute($reviewInput);
        mysqli_stmt_close($reviewInput);

        $this->disconnect();

    }

    // Save information to add song to playlist
    function savePlaylistSong($song_id = 79, $playlist_id = 7) {

        $this->connect();

        // Handle empty cases
        if (empty($song_id) || empty($playlist_id)) {
            return;
        }


        // Query for inputting new song_playlists
        $songPlaylistsInput = mysqli_prepare($this->conn, 'INSERT INTO song_playlists (playlist_id, song_id) VALUES (?,?)');
        mysqli_stmt_bind_param($songPlaylistsInput, 'ii', $playlist_id, $song_id);
        mysqli_stmt_execute($songPlaylistsInput);
        mysqli_stmt_close($songPlaylistsInput);

        $this->disconnect();

    }

    //////////////////////////
    // SQL Delete Functions //
    //////////////////////////

    // Function to delete song from playlist
    function deleteSongReview($reviewID) {
        $this->connect();

        // Delete the corresponding entity from the reviews table
        $stmt = $this->conn->prepare("DELETE FROM reviews WHERE review_id = ?");
        $stmt->bind_param("i", $reviewID);
        $stmt->execute();

        $this->disconnect();
    }

    //////////////////////////
    // SQL Update Functions //
    //////////////////////////

    // Function to update the reviews comment
    function editReviewComment($reviewID, $newComment) {
        $this->connect();

        // Update the playlist title for the given playlist ID
        $stmt = $this->conn->prepare("UPDATE reviews SET comment = ? WHERE review_id = ?");
        $stmt->bind_param("si", $newComment, $reviewID);
        $stmt->execute();

        $this->disconnect();
    }

    /////////////////////////////
    // Handle Form Submissions //
    /////////////////////////////

    // Handle what to function to do depending on form
    public function handleFormSubmit() {

        // If a review information is sent, call the function to add it to the database
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
            $this->saveSongReview();
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }

        // If playlist information is sent, call the function to add it to the database
        else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['playlist_id'])) {
            $this->savePlaylistSong();
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }

        // If a review needs to be changed call the appropriate function to delete or update it
        else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review_id'])) {
            $reviewID = $_POST['review_id'];

            if (isset($_POST['review_comment']) && !empty($_POST['review_comment'])) {
                $reviewComment = $_POST['review_comment'];
                $this->editReviewComment($reviewID, $reviewComment);
            }
            else {
                $this->deleteSongReview($reviewID);
            }

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}