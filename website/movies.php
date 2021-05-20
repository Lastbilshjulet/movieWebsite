<?php
    include_once 'connection.php';
?>

<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FF movies</title>

    <link rel="shortcut icon" type="image/ico" href="media/favicon.ico"/>
    <link rel="stylesheet" href="stylesheet.css">

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">
</head>

<body class="d-flex flex-column h-100">

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Eighth navbar example">
            <div class="container">
                <a class="navbar-brand" href="index.php">FF movies</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarsExample07" aria-controls="navbarsExample07" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarsExample07">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="movies.php">Movie list</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="whowatched.php">Who watched</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="latest.php">Latest watched</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stats.php">Stats</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="follow.php">Follow</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <?php
        if (isset($_GET["submit"]))
        {
            if (empty($_GET['username']))
            {
                $errName= "<div class=\"alert alert-danger col-sm-4 col-12\" role=\"alert\">Please enter your username.</div>";
            }
            else if (strlen($_GET['username']) > 30)
            {
                $errName= "<div class=\"alert alert-danger col-sm-4 col-12\" role=\"alert\">Make sure your username is under 30 characters long.</div>";
            }
            else
            {
                // Check if user exists
                $sql_getName = "SELECT COUNT(userName) AS \"antal\" FROM user WHERE userName = \"".$_GET['username']."\"";
                $sql_insertname = "INSERT INTO user (`userName`) VALUES (\"".$_GET['username']."\")";
                $name = mysqli_query($conn, $sql_getName);
                while ($row = mysqli_fetch_assoc($name))
                {
                    if ($row["antal"] == 0)
                    {
                        // if user does not exist, add to DB
                        if (!mysqli_query($conn, $sql_insertname))
                        {
                            echo "Error: ".$sql_insertname."".mysqli_error($conn)."<br>";
                        }
                    }
                    break;
                }
            }
        }
    ?>
    <!-- Begin page content -->
    <main class="container p-0">
        <div id="usernamePrompt" class="m-4 d-none">
            <h2 class="mb-4">Enter your username:</h2>
            <form role="form" method="GET" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <div class="form-floating col-sm-4 col-12">
                    <input type="text" class="form-control mb-3" id="floatingInput" placeholder="Bob" name="username">
                    <label for="floatingInput">Username</label>
                    <input id="submitButton" type="submit" value="Submit" name="submit" class="btn btn-primary"/>
                    <?php echo $errName; ?>
                </div>
            </form>
        </div>
        <div id="movieList" class="d-none">
            <div>
            <h2 id="title" class="m-4 d-inline-block">Top 250 movies</h2>
            <button id="hideseenmovies" type="button" class="btn btn-primary d-inline-block mx-4">Hide seen movies</button>
            </div>
            <?php
                $sql_getMovies = "SELECT * FROM movie ORDER BY movieRank";
                $movies = mysqli_query($conn, $sql_getMovies);
                if (mysqli_num_rows($movies) > 0)
                {
                    while ($row = mysqli_fetch_assoc($movies))
                    {
                        if ($row['movieRank'] > 0)
                        {
                            $duration = $row['movieDuration'];
                            $minutes = $duration % 60;
                            $hours = ($duration - $minutes)/60;
                            $rating = $row['movieRatings'];
                            $shortRatings = substr($rating, 0, strpos($rating, " b"));
                            $rating = substr($rating, 0, strpos($rating, " rat"));
                            $sql_getwatchlist = "SELECT COUNT(watchlist.movieID) AS \"antal\" 
                            FROM movie 
                            INNER JOIN watchlist 
                            ON movie.movieID = watchlist.movieID 
                            INNER JOIN user 
                            ON watchlist.userID = user.userID 
                            WHERE movieRank = ".$row['movieRank']." AND userName = \"".$_COOKIE["usrnm"]."\"";
                            $watchlist = mysqli_query($conn, $sql_getwatchlist);
                            while ($rowWatchlist = mysqli_fetch_assoc($watchlist))
                            {
                                if ($rowWatchlist["antal"] == 0)
                                {
                                    if ($row['movieRank'] != 1)
                                    {
                                        echo "<hr class='m-0'>";
                                    }
                                    echo "<div class='m-0'>";
                                    echo "<h5 id='movie".$row['movieRank']."' class='m-0 p-3 d-block text-truncate pointer'><b>".$row['movieRank'].".</b> ".$row['movieName'];
                                    echo "<span class='rating d-none d-md-inline-block'><span class='m-2'>".$shortRatings."</span></span><i class='fas fa-chevron-down'></i></h5>";
                                }
                                else
                                {
                                    if ($row['movieRank'] != 1)
                                    {
                                        echo "<hr class='m-0 hideifseen'>";
                                    }
                                    echo "<div class='m-0 hideifseen'>";
                                    echo "<h5 id='movie".$row['movieRank']."' class='m-0 p-3 d-block text-truncate pointer text-muted'><b>".$row['movieRank'].".</b> ".$row['movieName'];
                                    echo "<span class='rating d-none d-md-inline-block'><span class='m-2'>".$shortRatings."</span></span><i class='fas fa-chevron-down'></i></h5>";
                                }
                            }
                            echo "<div id='movieInfo".$row['movieRank']."' class='mx-2' style='display: none;'>";
                            echo "<h6 class='dividers mt-2'><span class='d-sm-inline-block d-md-none'>Year: </span>".$row['movieYear']."<br class='d-sm-inline-block d-md-none'>";
                            echo "<b class='dividers d-none d-md-inline-block'>|</b><span class='d-sm-inline-block d-md-none'></span>Duration: ".$hours."h ".$minutes."min<br class='d-sm-inline-block d-md-none'>";
                            echo "<b class='dividers d-none d-md-inline-block'>|</b><span class='d-sm-inline-block d-md-none'></span>Rating: ".$rating."s<br class='d-sm-inline-block d-md-none'></h6><hr>";
                            echo "<div class='m-3'>";
                            echo "<h6><b>Director:</b> ".$row['movieDirector']."</h6>";
                            echo "<h6><b>Writers:</b> ".$row['movieWriters']."</h6>";
                            echo "<h6><b>Stars:</b> ".$row['movieStars']."</h6>";
                            echo "<h6><b>Summary:</b> ".$row['movieSummary']."</h6>";
                            echo "<h6><b>IMDB-link:</b> <a target='_blank' href='".$row['movieLink']."'>".$row['movieLink']."</a></h6>";
                            $sql_getStreamble = "SELECT streamsite.streamsiteName, streamable.streamLink
                            FROM streamsite
                            INNER JOIN streamable
                            ON streamsite.streamsiteID = streamable.streamsiteID
                            INNER JOIN movie
                            ON movie.movieID = streamable.movieID
                            WHERE movie.movieID = ".$row['movieID'];
                            $sql_getStreamsites = mysqli_query($conn, $sql_getStreamble);
                            if (mysqli_num_rows($sql_getStreamsites) > 0)
                            {
                                echo "<h6><b>Streamable: </b>";
                                $counter = 0;
                                while ($rowStreamlist = mysqli_fetch_assoc($sql_getStreamsites))
                                {
                                    if ($counter != 0)
                                    {
                                        echo ", ";
                                    }
                                    echo "<a class='link-dark' href='".$rowStreamlist["streamLink"]."' target='_blank'>".$rowStreamlist["streamsiteName"]."</a>";
                                    $counter++;
                                }
                                echo "</h6>";
                            }
                            echo "</div><div class='form-check'>";
                            echo "<form role='form' method='GET'>";
                            $sql_getwatchlist = "SELECT COUNT(watchlist.movieID) AS \"antal\" 
                            FROM movie 
                            INNER JOIN watchlist 
                            ON movie.movieID = watchlist.movieID 
                            INNER JOIN user 
                            ON watchlist.userID = user.userID 
                            WHERE movieRank = ".$row['movieRank']." AND userName = \"".$_COOKIE["usrnm"]."\"";
                            $watchlist = mysqli_query($conn, $sql_getwatchlist);
                            while ($rowWatchlist = mysqli_fetch_assoc($watchlist))
                            {
                                $link =  "onclick=\"window.location.href='#movie".$row['movieRank']."';\"";
                                if ($rowWatchlist["antal"] == 0)
                                {
                                    echo "<input id='seen".$row['movieRank']."' type='submit' ".$link." value='Seen' name='seen".$row['movieRank']."' class='btn btn-primary mb-2'/>";
                                    echo "<label class='form-check-label mx-3' for='seen".$row['movieRank']."'><h6>Seen? </h6></label><br>";
                                }
                                else
                                {
                                    echo "<input id='notseen".$row['movieRank']."' type='submit' ".$link." value='Not seen' name='notseen".$row['movieRank']."' class='btn btn-secondary mb-2'/>";
                                    echo "<label class='form-check-label mx-3' for='notseen".$row['movieRank']."'><h6>Not seen anymore? </h6></label>";
                                }
                            }
                            $sql_getwatchlist = "SELECT user.userID, userName
                            FROM user 
                            INNER JOIN watchlist 
                            ON user.userID = watchlist.userID 
                            WHERE movieID = ".$row['movieID']." 
                                AND user.userID IN 
                                    (SELECT followlist.following 
                                    FROM followlist 
                                    INNER JOIN user 
                                    ON user.userID = followlist.user
                                    WHERE user.userName = \"".$_COOKIE["usrnm"]."\")
                            ORDER BY userName";
                            $watchlist = mysqli_query($conn, $sql_getwatchlist);
                            if (mysqli_num_rows($watchlist) > 0)
                            {
                                $counter = 0;
                                echo "<h6>";
                                while ($rowWatchlist = mysqli_fetch_assoc($watchlist))
                                {
                                    if (mysqli_num_rows($watchlist) == 1 && $rowWatchlist["userName"] == $_COOKIE["usrnm"])
                                    {
                                        echo "You are the only one that has seen this movie. <br>";
                                    }
                                    else
                                    {
                                        if ($counter == 0)
                                        {
                                            echo "These have already seen this movie: <br>";
                                            $counter++;
                                        }
                                        if ($rowWatchlist["userName"] == $_COOKIE["usrnm"])
                                        {
                                            echo "You<br>";
                                        }
                                        else
                                        {
                                            echo "<a class='link-dark' href='whowatched.php?user=".$rowWatchlist["userID"]."&submitWho=See+movies'>".$rowWatchlist["userName"]."</a><br>";
                                        }
                                    }
                                }
                                echo "</h6>";
                            }
                            else
                            {
                                $sql_getwatchlist = "SELECT COUNT(watchlist.movieID) 
                                FROM movie 
                                INNER JOIN watchlist 
                                ON movie.movieID = watchlist.movieID 
                                INNER JOIN user 
                                ON watchlist.userID = user.userID 
                                WHERE movieRank = ".$row['movieRank']." AND userName = \"".$_COOKIE["usrnm"]."\"";
                                $watchlist = mysqli_query($conn, $sql_getwatchlist);
                                $watchedornot = mysqli_fetch_row($watchlist);
                                if ($watchedornot[0] == 0)
                                {
                                    echo "<h6>No one has seen this movie yet.</h6>";
                                }
                                else
                                {
                                    echo "<h6>You are the only one that has seen this movie. </h6>";
                                }
                            }
                            echo "</div></form></div></div>";
                        }
                    }
                }
            ?>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <h6 class="text-center text-muted pt-2"><i class="far fa-copyright"></i> 2021</h6>
        </div>
    </footer>

    <?php
        for ($i = 1; $i <= 250; $i++)
        {
            if (array_key_exists("seen".$i, $_GET))
            {
                addtoWatchlist($i, $conn);
            }
            if (array_key_exists("notseen".$i, $_GET))
            {
                deletefromWatchlist($i, $conn);
            }
        }

        function deletefromWatchlist($num, $conn)
        {
            $sql_getmovieID = "SELECT movieID FROM movie WHERE movieRank = ".$num;
            $movie = mysqli_query($conn, $sql_getmovieID);
            while ($rowmovieID = mysqli_fetch_assoc($movie))
            {
                $movieID = $rowmovieID["movieID"];
                $username = $_COOKIE["usrnm"];
                $sql_getuserID = "SELECT userID FROM user WHERE userName = \"".$username."\"";
                $user = mysqli_query($conn, $sql_getuserID);
                while ($rowuserID = mysqli_fetch_assoc($user))
                {
                    $userID = $rowuserID["userID"];
                    $sql_deleteWatchlist = "DELETE FROM watchlist WHERE movieID = ".$movieID." AND userID = ".$userID;
                    if (!mysqli_query($conn, $sql_deleteWatchlist))
                    {
                        echo "Error: ".$sql_deleteWatchlist."".mysqli_error($conn)."<br>";
                    }
                }
            }
        }

        function addtoWatchlist($num, $conn)
        {
            $sql_getwatchlist = "SELECT COUNT(watchlist.movieID) AS \"antal\" 
            FROM movie 
            INNER JOIN watchlist ON movie.movieID = watchlist.movieID 
            INNER JOIN user ON watchlist.userID = user.userID 
            WHERE movieRank = ".$num." AND userName = \"".$_COOKIE["usrnm"]."\"";
            $watchlist = mysqli_query($conn, $sql_getwatchlist);
            while ($rowWatchlist = mysqli_fetch_assoc($watchlist))
            {
                if ($rowWatchlist["antal"] == 0)
                {
                    $sql_getmovieID = "SELECT movieID FROM movie WHERE movieRank = ".$num;
                    $movie = mysqli_query($conn, $sql_getmovieID);
                    while ($rowmovieID = mysqli_fetch_assoc($movie))
                    {
                        $movieID = $rowmovieID["movieID"];
                        $username = $_COOKIE["usrnm"];
                        $sql_getuserID = "SELECT userID FROM user WHERE userName = \"".$username."\"";
                        $user = mysqli_query($conn, $sql_getuserID);
                        while ($rowuserID = mysqli_fetch_assoc($user))
                        {
                            $userID = $rowuserID["userID"];
                            $sql_insertWatchlist = "INSERT INTO watchlist (`movieID`, `userID`) VALUES (".$movieID.", ".$userID.")";
                            if (!mysqli_query($conn, $sql_insertWatchlist))
                            {
                                echo "Error: ".$sql_insertWatchlist."".mysqli_error($conn)."<br>";
                            }
                        }
                    }
                }
            }
        }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf"
        crossorigin="anonymous"></script>

    <script src="movieScript.js"></script>

</body>

</html>
